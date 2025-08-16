<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'subscription_id',
        'tenant_id',
        'email_type',
        'recipient_email',
        'subject',
        'status',
        'sent_at',
        'failed_at',
        'error_message',
        'metadata',
        'queue_job_id',
        'attempts',
        'invoice_attached',
        'email_size_bytes',
        'processing_time_ms'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'json',
        'invoice_attached' => 'boolean',
        'attempts' => 'integer',
        'email_size_bytes' => 'integer',
        'processing_time_ms' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scopes
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('email_type', $type);
    }

    public function scopeWithInvoice($query)
    {
        return $query->where('invoice_attached', true);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Helper Methods
     */
    public function markAsSent($processingTimeMs = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'processing_time_ms' => $processingTimeMs
        ]);
    }

    public function markAsFailed($errorMessage, $processingTimeMs = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
            'processing_time_ms' => $processingTimeMs,
            'attempts' => $this->attempts + 1
        ]);
    }

    public function incrementAttempts()
    {
        $this->increment('attempts');
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatus()
    {
        $statuses = [
            'pending' => 'Pendiente',
            'queued' => 'En Cola',
            'sent' => 'Enviado',
            'failed' => 'Fallido',
            'retrying' => 'Reintentando'
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get formatted email type
     */
    public function getFormattedEmailType()
    {
        $types = [
            'registration_confirmation' => 'Confirmación de Registro',
            'welcome_with_invoice' => 'Bienvenida con Factura',
            'trial_welcome' => 'Bienvenida Trial',
            'admin_notification' => 'Notificación Admin',
            'subscription_reminder' => 'Recordatorio Suscripción',
            'payment_confirmation' => 'Confirmación de Pago',
            'invoice_delivery' => 'Entrega de Factura'
        ];

        return $types[$this->email_type] ?? ucfirst($this->email_type);
    }

    /**
     * Static methods for logging
     */
    public static function logEmailQueued($emailType, $recipientEmail, $subject, $user = null, $subscription = null, $tenant = null, $metadata = [])
    {
        return static::create([
            'user_id' => $user?->id,
            'subscription_id' => $subscription?->id,
            'tenant_id' => $tenant?->id,
            'email_type' => $emailType,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'status' => 'queued',
            'metadata' => $metadata,
            'attempts' => 0
        ]);
    }

    /**
     * Get statistics
     */
    public static function getStats($period = 'today')
    {
        $query = static::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
        }

        return [
            'total' => $query->count(),
            'sent' => $query->where('status', 'sent')->count(),
            'failed' => $query->where('status', 'failed')->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'queued' => $query->where('status', 'queued')->count(),
            'success_rate' => $query->count() > 0 ? round(($query->where('status', 'sent')->count() / $query->count()) * 100, 2) : 0
        ];
    }
}
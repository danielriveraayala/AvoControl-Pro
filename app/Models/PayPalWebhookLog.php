<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PayPalWebhookLog extends Model
{
    protected $table = 'paypal_webhook_logs';

    protected $fillable = [
        'webhook_id',
        'event_type',
        'event_version',
        'resource_type',
        'resource_id',
        'subscription_id',
        'tenant_id',
        'payload',
        'status',
        'processing_notes',
        'ip_address',
        'user_agent',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    protected $dates = [
        'received_at',
        'processed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Subscription relationship
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Tenant relationship
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for successful processing
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope for failed processing
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending processing
     */
    public function scopePending($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope for specific event types
     */
    public function scopeEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed(string $notes = null): void
    {
        $this->update([
            'status' => 'processed',
            'processing_notes' => $notes,
            'processed_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'processing_notes' => $reason,
            'processed_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark as ignored
     */
    public function markAsIgnored(string $reason = 'Event type not handled'): void
    {
        $this->update([
            'status' => 'ignored',
            'processing_notes' => $reason,
            'processed_at' => Carbon::now(),
        ]);
    }

    /**
     * Reset for retry
     */
    public function resetForRetry(): void
    {
        $this->update([
            'status' => 'received',
            'processing_notes' => null,
            'processed_at' => null,
        ]);
    }

    /**
     * Check if webhook can be retried
     */
    public function canRetry(): bool
    {
        return in_array($this->status, ['failed', 'ignored']);
    }

    /**
     * Get formatted status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'received' => '<span class="badge badge-warning">Recibido</span>',
            'processed' => '<span class="badge badge-success">Procesado</span>',
            'failed' => '<span class="badge badge-danger">Fallido</span>',
            'ignored' => '<span class="badge badge-secondary">Ignorado</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-light">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Get formatted event type
     */
    public function getFormattedEventTypeAttribute(): string
    {
        $types = [
            'BILLING.SUBSCRIPTION.ACTIVATED' => 'Suscripción Activada',
            'BILLING.SUBSCRIPTION.CANCELLED' => 'Suscripción Cancelada',
            'BILLING.SUBSCRIPTION.SUSPENDED' => 'Suscripción Suspendida',
            'BILLING.SUBSCRIPTION.EXPIRED' => 'Suscripción Expirada',
            'BILLING.SUBSCRIPTION.PAYMENT.FAILED' => 'Pago Fallido',
            'PAYMENT.SALE.COMPLETED' => 'Pago Completado',
            'PAYMENT.SALE.REFUNDED' => 'Pago Reembolsado',
        ];

        return $types[$this->event_type] ?? $this->event_type;
    }

    /**
     * Get processing duration in seconds
     */
    public function getProcessingDurationAttribute(): ?int
    {
        if (!$this->processed_at || !$this->received_at) {
            return null;
        }

        return $this->processed_at->diffInSeconds($this->received_at);
    }

    /**
     * Check if webhook is critical
     */
    public function isCritical(): bool
    {
        $criticalEvents = [
            'BILLING.SUBSCRIPTION.CANCELLED',
            'BILLING.SUBSCRIPTION.SUSPENDED',
            'BILLING.SUBSCRIPTION.PAYMENT.FAILED',
        ];

        return in_array($this->event_type, $criticalEvents);
    }

    /**
     * Get recent webhook logs
     */
    public static function getRecent(int $limit = 50)
    {
        return static::with(['subscription.tenant', 'tenant'])
            ->orderBy('received_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get webhook statistics
     */
    public static function getStats()
    {
        return [
            'total' => static::count(),
            'processed' => static::processed()->count(),
            'failed' => static::failed()->count(),
            'pending' => static::pending()->count(),
            'today' => static::whereDate('received_at', Carbon::today())->count(),
            'this_week' => static::whereBetween('received_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
        ];
    }
}

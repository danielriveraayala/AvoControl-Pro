<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'mailable_class',
        'priority',
        'status',
        'sent_at',
        'failed_at',
        'error_message',
        'attempts',
        'data'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'data' => 'array'
    ];

    /**
     * Relationship with notification
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Scope for successful emails
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed emails
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending emails
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for specific priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Static method to log email sending attempt
     */
    public static function logAttempt($notificationId, $recipient, $subject, $mailableClass, $priority = 'normal', $additionalData = [])
    {
        return static::create([
            'notification_id' => $notificationId,
            'recipient_email' => is_array($recipient) ? $recipient['email'] : $recipient,
            'recipient_name' => is_array($recipient) ? ($recipient['name'] ?? null) : null,
            'subject' => $subject,
            'mailable_class' => $mailableClass,
            'priority' => $priority,
            'status' => 'pending',
            'attempts' => 1,
            'data' => $additionalData
        ]);
    }

    /**
     * Mark email as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null
        ]);
    }

    /**
     * Mark email as failed
     */
    public function markAsFailed($errorMessage, $isRetryable = true)
    {
        $this->update([
            'status' => $isRetryable ? 'retrying' : 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
            'attempts' => $this->attempts + 1
        ]);
    }

    /**
     * Get email statistics for a date range
     */
    public static function getStats($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        } elseif (!$startDate && !$endDate) {
            // Default to last 30 days
            $query->where('created_at', '>=', now()->subDays(30));
        }

        $stats = [
            'total_emails' => $query->count(),
            'sent_emails' => $query->clone()->successful()->count(),
            'failed_emails' => $query->clone()->failed()->count(),
            'pending_emails' => $query->clone()->pending()->count(),
        ];

        // Calculate success rate
        $stats['success_rate'] = $stats['total_emails'] > 0 
            ? round(($stats['sent_emails'] / $stats['total_emails']) * 100, 2) 
            : 0;

        // Get stats by priority
        $stats['by_priority'] = [
            'critical' => $query->clone()->byPriority('critical')->count(),
            'high' => $query->clone()->byPriority('high')->count(),
            'normal' => $query->clone()->byPriority('normal')->count(),
            'low' => $query->clone()->byPriority('low')->count(),
        ];

        // Get recent failures for analysis
        $stats['recent_failures'] = static::failed()
            ->where('failed_at', '>=', now()->subHours(24))
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get(['recipient_email', 'subject', 'error_message', 'failed_at'])
            ->toArray();

        return $stats;
    }
}
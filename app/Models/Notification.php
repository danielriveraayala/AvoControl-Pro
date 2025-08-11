<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Notification extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'priority',
        'channel',
        'category',
        'metadata',
        'sent_email',
        'sent_push',
        'scheduled_at'
    ];

    protected $casts = [
        'data' => 'array',
        'metadata' => 'array',
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'sent_email' => 'boolean',
        'sent_push' => 'boolean'
    ];

    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): void
    {
        if (!is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if a notification has been read.
     */
    public function read(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     */
    public function unread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include notifications of a certain priority.
     */
    public function scopePriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include notifications of a certain category.
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include scheduled notifications that are ready to be sent.
     */
    public function scopeScheduledForNow(Builder $query): Builder
    {
        return $query->where('scheduled_at', '<=', now())
                    ->whereNotNull('scheduled_at');
    }

    /**
     * Get the priority badge color for display.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'danger',
            'high' => 'warning', 
            'normal' => 'info',
            'low' => 'secondary',
            default => 'info'
        };
    }

    /**
     * Get the category icon for display.
     */
    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'inventory' => 'fas fa-boxes',
            'payments' => 'fas fa-credit-card',
            'sales' => 'fas fa-chart-line',
            'system' => 'fas fa-cog',
            default => 'fas fa-bell'
        };
    }

    /**
     * Get notification title from data.
     */
    public function getTitleAttribute(): string
    {
        return $this->data['title'] ?? 'Notificación';
    }

    /**
     * Get notification message from data.
     */
    public function getMessageAttribute(): string
    {
        return $this->data['message'] ?? '';
    }

    /**
     * Check if notification should be sent via email.
     */
    public function shouldSendEmail(): bool
    {
        return in_array($this->channel, ['email', 'all']) && !$this->sent_email;
    }

    /**
     * Check if notification should be sent via push.
     */
    public function shouldSendPush(): bool
    {
        return in_array($this->channel, ['push', 'all']) && !$this->sent_push;
    }

    /**
     * Mark email as sent.
     */
    public function markEmailSent(): void
    {
        $this->update(['sent_email' => true]);
    }

    /**
     * Mark push as sent.
     */
    public function markPushSent(): void
    {
        $this->update(['sent_push' => true]);
    }
}

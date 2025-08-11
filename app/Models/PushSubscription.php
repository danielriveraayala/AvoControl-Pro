<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh_key',
        'auth_token',
        'browser',
        'device',
        'user_agent',
        'ip_address',
        'active',
        'last_used_at',
        'preferences'
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_used_at' => 'datetime',
        'preferences' => 'array'
    ];

    /**
     * Get the user that owns the push subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark subscription as used.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Activate the subscription.
     */
    public function activate(): void
    {
        $this->update(['active' => true]);
    }

    /**
     * Deactivate the subscription.
     */
    public function deactivate(): void
    {
        $this->update(['active' => false]);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Get subscription data formatted for web push.
     */
    public function getSubscriptionData(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'p256dh' => $this->p256dh_key,
                'auth' => $this->auth_token
            ]
        ];
    }

    /**
     * Scope query to only active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope query to only inactive subscriptions.
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * Get browser icon for display.
     */
    public function getBrowserIconAttribute(): string
    {
        return match (strtolower($this->browser)) {
            'chrome' => 'fab fa-chrome',
            'firefox' => 'fab fa-firefox-browser',
            'safari' => 'fab fa-safari',
            'edge' => 'fab fa-edge',
            'opera' => 'fab fa-opera',
            default => 'fas fa-globe'
        };
    }

    /**
     * Get device icon for display.
     */
    public function getDeviceIconAttribute(): string
    {
        return match (strtolower($this->device)) {
            'mobile' => 'fas fa-mobile-alt',
            'tablet' => 'fas fa-tablet-alt',
            'desktop' => 'fas fa-desktop',
            default => 'fas fa-device'
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'user_id',
        'paypal_subscription_id',
        'paypal_plan_id',
        'paypal_order_id',
        'plan',
        'amount',
        'currency',
        'interval',
        'status',
        'is_trial',
        'trial_starts_at',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'next_billing_date',
        'cancelled_at',
        'suspended_at',
        'grace_period_days',
        'grace_period_ends_at',
        'billing_cycle_count',
        'failed_payment_count',
        'paypal_metadata',
        'plan_features',
        'cancellation_reason',
        'cancelled_by',
        'suspension_reason',
        'suspended_by',
        'reactivated_at',
        'reactivation_reason',
        'reactivated_by',
        'grace_period_started_at',
        'data_backed_up_before_suspension',
        'data_backup_created_at',
        'data_backup_path',
        'custom_grace_period_days',
        'scheduled_for_deletion_at',
        'deletion_warning_sent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_trial' => 'boolean',
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'next_billing_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'suspended_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'billing_cycle_count' => 'integer',
        'failed_payment_count' => 'integer',
        'grace_period_days' => 'integer',
        'reactivated_at' => 'datetime',
        'grace_period_started_at' => 'datetime',
        'data_backed_up_before_suspension' => 'boolean',
        'data_backup_created_at' => 'datetime',
        'custom_grace_period_days' => 'integer',
        'scheduled_for_deletion_at' => 'datetime',
        'deletion_warning_sent' => 'boolean',
        'paypal_metadata' => 'array',
        'plan_features' => 'array'
    ];

    /**
     * Get the tenant that owns the subscription
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created the subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * Get the subscription payment logs
     */
    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->is_trial && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired
     */
    public function isTrialExpired(): bool
    {
        return $this->is_trial && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isPast();
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if subscription is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if subscription is in grace period
     */
    public function isInGracePeriod(): bool
    {
        return $this->grace_period_ends_at && 
               $this->grace_period_ends_at->isFuture() &&
               $this->failed_payment_count > 0;
    }

    /**
     * Check if grace period has ended
     */
    public function isGracePeriodExpired(): bool
    {
        return $this->grace_period_ends_at && 
               $this->grace_period_ends_at->isPast() &&
               $this->failed_payment_count > 0;
    }

    /**
     * Check if subscription can be suspended
     */
    public function canBeSuspended(): bool
    {
        return $this->isActive() && 
               ($this->isGracePeriodExpired() || $this->failed_payment_count >= 3);
    }

    /**
     * Check if subscription can be reactivated
     */
    public function canBeReactivated(): bool
    {
        return $this->isSuspended() && 
               $this->suspended_at &&
               $this->suspended_at->diffInDays(now()) <= 30; // 30 days suspension limit
    }

    /**
     * Suspend the subscription
     */
    public function suspend(string $reason = 'Payment failure', string $suspendedBy = 'system'): bool
    {
        if (!$this->canBeSuspended() && $this->status !== 'active') {
            return false;
        }

        $this->update([
            'status' => 'suspended',
            'suspended_at' => Carbon::now(),
            'suspension_reason' => $reason,
            'suspended_by' => $suspendedBy,
        ]);

        // Also suspend the tenant
        $this->tenant->update(['status' => 'suspended']);

        return true;
    }

    /**
     * Reactivate the subscription
     */
    public function reactivate(string $reason = 'Payment successful', string $reactivatedBy = 'system'): bool
    {
        if (!$this->canBeReactivated()) {
            return false;
        }

        $this->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null,
            'suspended_by' => null,
            'reactivated_at' => Carbon::now(),
            'reactivation_reason' => $reason,
            'reactivated_by' => $reactivatedBy,
            'failed_payment_count' => 0,
            'grace_period_ends_at' => null,
        ]);

        // Also reactivate the tenant
        $this->tenant->update(['status' => 'active']);

        return true;
    }

    /**
     * Get days until suspension (if in grace period)
     */
    public function daysUntilSuspension(): ?int
    {
        if (!$this->isInGracePeriod()) {
            return null;
        }

        return max(0, Carbon::now()->diffInDays($this->grace_period_ends_at, false));
    }

    /**
     * Get days since suspension
     */
    public function daysSinceSuspension(): ?int
    {
        if (!$this->isSuspended() || !$this->suspended_at) {
            return null;
        }

        return $this->suspended_at->diffInDays(Carbon::now());
    }

    /**
     * Start grace period
     */
    public function startGracePeriod(int $days = null): void
    {
        $graceDays = $days ?? $this->grace_period_days;
        
        $this->update([
            'grace_period_ends_at' => Carbon::now()->addDays($graceDays),
            'grace_period_started_at' => Carbon::now(),
        ]);
    }

    /**
     * Check if subscription requires immediate suspension
     */
    public function requiresImmediateSuspension(): bool
    {
        return $this->failed_payment_count >= 3 || 
               $this->isGracePeriodExpired() ||
               ($this->is_trial && $this->isTrialExpired());
    }

    /**
     * Check if subscription has failed payments
     */
    public function hasFailedPayments(): bool
    {
        return $this->failed_payment_count > 0;
    }

    /**
     * Get days remaining in trial
     */
    public function getTrialDaysRemaining(): int
    {
        if (!$this->isOnTrial()) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Get days remaining in grace period
     */
    public function getGracePeriodDaysRemaining(): int
    {
        if (!$this->isInGracePeriod()) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->grace_period_ends_at, false));
    }

    /**
     * Get next billing date formatted
     */
    public function getNextBillingDateFormatted(): string
    {
        if (!$this->next_billing_date) {
            return 'N/A';
        }

        return $this->next_billing_date->format('M d, Y');
    }

    /**
     * Get subscription status badge
     */
    public function getStatusBadge(): array
    {
        $badges = [
            'pending' => ['class' => 'badge-warning', 'text' => 'Pendiente'],
            'active' => ['class' => 'badge-success', 'text' => 'Activa'],
            'suspended' => ['class' => 'badge-danger', 'text' => 'Suspendida'],
            'cancelled' => ['class' => 'badge-secondary', 'text' => 'Cancelada'],
            'expired' => ['class' => 'badge-dark', 'text' => 'Expirada'],
            'failed' => ['class' => 'badge-danger', 'text' => 'Falló']
        ];

        return $badges[$this->status] ?? ['class' => 'badge-secondary', 'text' => 'Desconocido'];
    }

    /**
     * Get plan name formatted
     */
    public function getPlanNameFormatted(): string
    {
        $plans = [
            'trial' => 'Trial Gratuito',
            'basic' => 'Plan Básico',
            'premium' => 'Plan Premium',
            'enterprise' => 'Plan Enterprise',
            'corporate' => 'Plan Corporativo'
        ];

        return $plans[$this->plan] ?? ucfirst($this->plan);
    }

    /**
     * Get amount formatted with currency
     */
    public function getAmountFormatted(): string
    {
        if ($this->plan === 'trial') {
            return 'Gratis';
        }

        if ($this->plan === 'corporate') {
            return 'Personalizado';
        }

        $symbol = $this->currency === 'USD' ? '$' : '$';
        return $symbol . number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get subscription features
     */
    public function getFeatures(): array
    {
        if ($this->plan_features) {
            return $this->plan_features;
        }

        // Default features based on plan
        $defaultFeatures = [
            'trial' => [
                'users' => 1,
                'lots_per_month' => 50,
                'storage_gb' => 0.5,
                'support' => false,
                'reports' => 'basic'
            ],
            'basic' => [
                'users' => 5,
                'lots_per_month' => 500,
                'storage_gb' => 2,
                'support' => 'email',
                'reports' => 'all',
                'notifications' => 'email'
            ],
            'premium' => [
                'users' => 25,
                'lots_per_month' => 2000,
                'storage_gb' => 10,
                'support' => 'priority',
                'reports' => 'advanced',
                'notifications' => 'email+push+sms',
                'api_access' => true,
                'backup' => 'automatic'
            ],
            'enterprise' => [
                'users' => 100,
                'lots_per_month' => 'unlimited',
                'storage_gb' => 50,
                'support' => '24/7',
                'reports' => 'custom',
                'notifications' => 'all',
                'api_access' => 'full',
                'backup' => 'automatic',
                'multi_location' => true,
                'custom_branding' => true
            ],
            'corporate' => [
                'users' => 'unlimited',
                'tenants' => 'unlimited',
                'storage_gb' => 'unlimited',
                'support' => 'dedicated',
                'reports' => 'unlimited',
                'dedicated_server' => true,
                'sla_guarantee' => true,
                'custom_development' => true
            ]
        ];

        return $defaultFeatures[$this->plan] ?? [];
    }

    /**
     * Check if subscription allows feature
     */
    public function allowsFeature(string $feature): bool
    {
        $features = $this->getFeatures();
        return isset($features[$feature]) && $features[$feature] !== false;
    }

    /**
     * Get feature limit
     */
    public function getFeatureLimit(string $feature): mixed
    {
        $features = $this->getFeatures();
        return $features[$feature] ?? null;
    }

    /**
     * Check if subscription is within limits
     */
    public function isWithinLimits(): array
    {
        $limits = $this->getFeatures();
        $usage = $this->getCurrentUsage();
        $violations = [];

        foreach ($limits as $feature => $limit) {
            if ($limit === 'unlimited' || $limit === false) {
                continue;
            }

            $currentUsage = $usage[$feature] ?? 0;
            
            if (is_numeric($limit) && $currentUsage > $limit) {
                $violations[$feature] = [
                    'limit' => $limit,
                    'usage' => $currentUsage,
                    'exceeded_by' => $currentUsage - $limit
                ];
            }
        }

        return $violations;
    }

    /**
     * Get current usage statistics
     */
    public function getCurrentUsage(): array
    {
        $tenant = $this->tenant;
        
        if (!$tenant) {
            return [];
        }

        return [
            'users' => $tenant->users()->count(),
            'lots_per_month' => $tenant->lots()->whereMonth('created_at', now()->month)->count(),
            'storage_gb' => 0, // TODO: Calculate actual storage usage
            'tenants' => 1 // Current tenant
        ];
    }

    /**
     * Get successful payments
     */
    public function getSuccessfulPayments(): HasMany
    {
        return $this->payments()->where('status', 'completed');
    }

    /**
     * Get failed payments
     */
    public function getFailedPayments(): HasMany
    {
        return $this->payments()->where('status', 'failed');
    }

    /**
     * Get total amount paid
     */
    public function getTotalAmountPaid(): float
    {
        return $this->getSuccessfulPayments()->sum('amount');
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for trial subscriptions
     */
    public function scopeTrial($query)
    {
        return $query->where('is_trial', true);
    }

    /**
     * Scope for subscriptions ending soon
     */
    public function scopeEndingSoon($query, int $days = 3)
    {
        return $query->where('trial_ends_at', '<=', Carbon::now()->addDays($days))
                    ->where('trial_ends_at', '>', Carbon::now());
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('trial_ends_at', '<', Carbon::now())
                    ->orWhere('grace_period_ends_at', '<', Carbon::now());
    }

    /**
     * Scope for subscriptions in grace period
     */
    public function scopeInGracePeriod($query)
    {
        return $query->where('grace_period_ends_at', '>', Carbon::now())
                    ->where('failed_payment_count', '>', 0);
    }
}
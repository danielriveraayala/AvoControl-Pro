<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_name',
        'tenant_domain',
        'contact_name',
        'contact_email',
        'contact_phone',
        'tenant_plan_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'suspended_at',
        'suspension_reason',
        'current_users',
        'current_suppliers',
        'current_customers',
        'lots_this_month',
        'sales_this_month',
        'storage_used_gb',
        'billing_name',
        'billing_address',
        'tax_id',
        'tenant_config',
        'feature_flags',
        'created_by',
        'last_usage_update',
        'notes'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'suspended_at' => 'datetime',
        'storage_used_gb' => 'decimal:3',
        'tenant_config' => 'array',
        'feature_flags' => 'array',
        'last_usage_update' => 'datetime'
    ];

    /**
     * Get the plan for this subscription
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(TenantPlan::class, 'tenant_plan_id');
    }

    /**
     * Get the user who created this subscription
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringsSoon($query, int $days = 7)
    {
        return $query->whereIn('status', ['active', 'trial'])
                    ->where('current_period_end', '<=', Carbon::now()->addDays($days));
    }

    /**
     * Status helpers
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->current_period_end && $this->current_period_end->isPast());
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Trial helpers
     */
    public function isTrialExpired(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function getTrialDaysRemainingAttribute(): int
    {
        if (!$this->trial_ends_at || $this->trial_ends_at->isPast()) {
            return 0;
        }
        
        return $this->trial_ends_at->diffInDays(Carbon::now());
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'trial' => 'Período de Prueba',
            'active' => 'Activa',
            'suspended' => 'Suspendida',
            'cancelled' => 'Cancelada',
            'expired' => 'Expirada'
        ];

        return $statuses[$this->status] ?? 'Desconocido';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'trial' => 'yellow',
            'active' => 'green',
            'suspended' => 'red',
            'cancelled' => 'gray',
            'expired' => 'red'
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get full domain URL
     */
    public function getFullDomainAttribute(): string
    {
        return $this->tenant_domain . '.' . config('app.domain', 'avocontrol.com');
    }

    /**
     * Check if subscription has feature enabled
     */
    public function hasFeature(string $feature): bool
    {
        // Check feature flags first
        $featureFlags = $this->feature_flags ?? [];
        if (isset($featureFlags[$feature])) {
            return $featureFlags[$feature];
        }

        // Check plan features
        return $this->plan ? $this->plan->hasFeature($feature) : false;
    }

    /**
     * Check if subscription is over any limits
     */
    public function isOverLimits(): bool
    {
        if (!$this->plan) return false;
        
        return count($this->plan->checkLimits($this)) > 0;
    }

    /**
     * Get all limit violations
     */
    public function getLimitViolations(): array
    {
        return $this->plan ? $this->plan->checkLimits($this) : [];
    }

    /**
     * Update usage statistics
     */
    public function updateUsageStats(): void
    {
        // This would be called by a scheduled task or when usage changes
        // For now, we'll just update the timestamp
        $this->last_usage_update = Carbon::now();
        $this->save();
    }

    /**
     * Suspend subscription
     */
    public function suspend(string $reason = null, User $suspendedBy = null): bool
    {
        $this->status = 'suspended';
        $this->suspended_at = Carbon::now();
        $this->suspension_reason = $reason;
        
        // Log the action
        if ($suspendedBy) {
            $this->notes = ($this->notes ?? '') . "\nSuspendida por {$suspendedBy->name} el " . Carbon::now()->format('d/m/Y H:i') . ($reason ? ": {$reason}" : '');
        }
        
        return $this->save();
    }

    /**
     * Activate subscription
     */
    public function activate(): bool
    {
        $this->status = 'active';
        $this->suspended_at = null;
        $this->suspension_reason = null;
        
        return $this->save();
    }

    /**
     * Extend trial period
     */
    public function extendTrial(int $days): bool
    {
        if ($this->status === 'trial') {
            $currentEnd = $this->trial_ends_at ?? Carbon::now();
            $this->trial_ends_at = $currentEnd->addDays($days);
            return $this->save();
        }
        
        return false;
    }

    /**
     * Get usage summary
     */
    public function getUsageSummary(): array
    {
        if (!$this->plan) {
            return [];
        }

        return [
            'users' => [
                'current' => $this->current_users,
                'limit' => $this->plan->max_users,
                'percentage' => $this->plan->getUsagePercentage($this, 'users')
            ],
            'suppliers' => [
                'current' => $this->current_suppliers,
                'limit' => $this->plan->max_suppliers,
                'percentage' => $this->plan->getUsagePercentage($this, 'suppliers')
            ],
            'customers' => [
                'current' => $this->current_customers,
                'limit' => $this->plan->max_customers,
                'percentage' => $this->plan->getUsagePercentage($this, 'customers')
            ],
            'lots' => [
                'current' => $this->lots_this_month,
                'limit' => $this->plan->max_lots_per_month,
                'percentage' => $this->plan->getUsagePercentage($this, 'lots')
            ],
            'sales' => [
                'current' => $this->sales_this_month,
                'limit' => $this->plan->max_sales_per_month,
                'percentage' => $this->plan->getUsagePercentage($this, 'sales')
            ],
            'storage' => [
                'current' => $this->storage_used_gb,
                'limit' => $this->plan->storage_limit_gb,
                'percentage' => $this->plan->getUsagePercentage($this, 'storage')
            ]
        ];
    }
}
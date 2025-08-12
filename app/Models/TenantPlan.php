<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantPlan extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'price',
        'billing_cycle',
        'max_users',
        'max_suppliers',
        'max_customers',
        'max_lots_per_month',
        'max_sales_per_month',
        'storage_limit_gb',
        'features',
        'is_active',
        'is_featured',
        'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean'
    ];

    /**
     * Get subscriptions for this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    /**
     * Get active subscriptions for this plan
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class)
                    ->where('status', 'active');
    }

    /**
     * Scope for active plans only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured plans
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Check if plan has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return in_array($feature, $features) || isset($features[$feature]);
    }

    /**
     * Get feature value or default
     */
    public function getFeature(string $feature, $default = null)
    {
        $features = $this->features ?? [];
        return $features[$feature] ?? $default;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get price per year for monthly plans
     */
    public function getYearlyPriceAttribute(): float
    {
        if ($this->billing_cycle === 'yearly') {
            return $this->price;
        }
        
        return $this->price * 12 * 0.8; // 20% discount for yearly
    }

    /**
     * Get formatted yearly price
     */
    public function getFormattedYearlyPriceAttribute(): string
    {
        return '$' . number_format($this->yearly_price, 2);
    }

    /**
     * Get billing cycle display name
     */
    public function getBillingCycleDisplayAttribute(): string
    {
        return $this->billing_cycle === 'monthly' ? 'Mensual' : 'Anual';
    }

    /**
     * Check if plan is over limits for subscription
     */
    public function checkLimits(TenantSubscription $subscription): array
    {
        $limits = [];

        if ($subscription->current_users > $this->max_users) {
            $limits['users'] = "Usuarios: {$subscription->current_users}/{$this->max_users}";
        }

        if ($subscription->current_suppliers > $this->max_suppliers) {
            $limits['suppliers'] = "Proveedores: {$subscription->current_suppliers}/{$this->max_suppliers}";
        }

        if ($subscription->current_customers > $this->max_customers) {
            $limits['customers'] = "Clientes: {$subscription->current_customers}/{$this->max_customers}";
        }

        if ($subscription->lots_this_month > $this->max_lots_per_month) {
            $limits['lots'] = "Lotes este mes: {$subscription->lots_this_month}/{$this->max_lots_per_month}";
        }

        if ($subscription->sales_this_month > $this->max_sales_per_month) {
            $limits['sales'] = "Ventas este mes: {$subscription->sales_this_month}/{$this->max_sales_per_month}";
        }

        if ($subscription->storage_used_gb > $this->storage_limit_gb) {
            $limits['storage'] = "Almacenamiento: {$subscription->storage_used_gb}GB/{$this->storage_limit_gb}GB";
        }

        return $limits;
    }

    /**
     * Get usage percentage for a subscription
     */
    public function getUsagePercentage(TenantSubscription $subscription, string $metric): int
    {
        switch ($metric) {
            case 'users':
                return min(100, round(($subscription->current_users / $this->max_users) * 100));
            case 'suppliers':
                return min(100, round(($subscription->current_suppliers / $this->max_suppliers) * 100));
            case 'customers':
                return min(100, round(($subscription->current_customers / $this->max_customers) * 100));
            case 'lots':
                return min(100, round(($subscription->lots_this_month / $this->max_lots_per_month) * 100));
            case 'sales':
                return min(100, round(($subscription->sales_this_month / $this->max_sales_per_month) * 100));
            case 'storage':
                return min(100, round(($subscription->storage_used_gb / $this->storage_limit_gb) * 100));
            default:
                return 0;
        }
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'price',
        'annual_price',
        'annual_discount_percentage',
        'currency',
        'billing_cycle',
        'trial_days',
        'is_active',
        'is_featured',
        'is_custom',
        'show_on_landing',
        'button_text',
        'popular_badge',
        'paypal_plan_id',
        'paypal_annual_plan_id',
        'stripe_plan_id',
        'stripe_annual_plan_id',
        'sort_order',
        'max_users',
        'max_lots_per_month',
        'max_storage_gb',
        'max_locations',
        'features',
        'metadata',
        'color',
        'icon',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'annual_discount_percentage' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_custom' => 'boolean',
        'show_on_landing' => 'boolean',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
        'max_users' => 'integer',
        'max_lots_per_month' => 'integer',
        'max_storage_gb' => 'integer',
        'max_locations' => 'integer',
        'features' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Scope for active plans
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
     * Scope for custom plans
     */
    public function scopeCustom($query)
    {
        return $query->where('is_custom', true);
    }

    /**
     * Scope for standard plans (not custom)
     */
    public function scopeStandard($query)
    {
        return $query->where('is_custom', false);
    }

    /**
     * Scope for ordering plans
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Scope for plans visible on landing page
     */
    public function scopeVisibleOnLanding($query)
    {
        return $query->where('show_on_landing', true)->where('is_active', true);
    }


    /**
     * Subscriptions relationship
     * Using 'plan' field instead of 'plan_id' since existing table uses plan string
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan', 'key');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price == 0) {
            return 'Gratis';
        }
        
        return '$' . number_format($this->price, 0) . '/' . ($this->billing_cycle === 'yearly' ? 'año' : 'mes');
    }

    /**
     * Get feature value
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get feature value with parameter
     */
    public function getFeatureValue(string $feature, $default = null)
    {
        $features = $this->features ?? [];
        return $features[$feature] ?? $default;
    }

    /**
     * Check if plan is unlimited for a specific limit
     */
    public function isUnlimited(string $limit): bool
    {
        return $this->$limit === -1;
    }

    /**
     * Get usage limit display
     */
    public function getLimitDisplay(string $limit): string
    {
        $value = $this->$limit;
        
        if ($value === -1) {
            return 'Ilimitado';
        }
        
        if ($value === null) {
            return 'No disponible';
        }
        
        return number_format($value);
    }

    /**
     * Check if plan can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Can't delete if has active subscriptions
        if ($this->subscriptions()->count() > 0) {
            return false;
        }
        
        // Can't delete if it's a system plan (trial, basic, premium, enterprise) and not custom
        $systemPlans = ['trial', 'basic', 'premium', 'enterprise'];
        if (in_array($this->key, $systemPlans) && !$this->is_custom) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if plan has annual pricing
     */
    public function hasAnnualPricing(): bool
    {
        return !is_null($this->annual_price) && $this->annual_price > 0;
    }

    /**
     * Get price for specific billing cycle
     */
    public function getPriceForCycle(string $cycle): ?float
    {
        return $cycle === 'annual' ? $this->annual_price : $this->price;
    }

    /**
     * Get PayPal plan ID for specific billing cycle
     */
    public function getPayPalPlanId(string $cycle): ?string
    {
        return $cycle === 'annual' ? $this->paypal_annual_plan_id : $this->paypal_plan_id;
    }

    /**
     * Get formatted price for specific cycle
     */
    public function getFormattedPrice(string $cycle = 'monthly'): string
    {
        $price = $this->getPriceForCycle($cycle);
        
        if ($price == 0) {
            return 'Gratis';
        }
        
        $period = $cycle === 'annual' ? 'año' : 'mes';
        return '$' . number_format($price, 0) . '/' . $period;
    }

    /**
     * Calculate annual price automatically with discount
     */
    public function calculateAnnualPrice(int $discountPercentage = 15): float
    {
        if ($this->price <= 0) {
            return 0;
        }
        
        $monthlyTotal = $this->price * 12;
        return $monthlyTotal * (1 - ($discountPercentage / 100));
    }

    /**
     * Get annual savings amount
     */
    public function getAnnualSavings(): float
    {
        if (!$this->hasAnnualPricing()) {
            return 0;
        }
        
        $monthlyTotal = $this->price * 12;
        return $monthlyTotal - $this->annual_price;
    }

    /**
     * Get monthly equivalent for annual pricing
     */
    public function getMonthlyEquivalent(): float
    {
        if (!$this->hasAnnualPricing()) {
            return $this->price;
        }
        
        return $this->annual_price / 12;
    }

    /**
     * Scope for plans with annual pricing
     */
    public function scopeWithAnnualPricing($query)
    {
        return $query->whereNotNull('annual_price')->where('annual_price', '>', 0);
    }

    /**
     * Get all available features for plans
     */
    public static function getAvailableFeatures(): array
    {
        return [
            'reports' => [
                'basic_reports' => 'Reportes Básicos',
                'advanced_reports' => 'Reportes Avanzados',
                'custom_reports' => 'Reportes Personalizados',
                'export_excel' => 'Exportar a Excel',
                'export_pdf' => 'Exportar a PDF',
            ],
            'notifications' => [
                'email_notifications' => 'Notificaciones por Email',
                'push_notifications' => 'Notificaciones Push',
                'sms_notifications' => 'Notificaciones SMS',
                'custom_alerts' => 'Alertas Personalizadas',
            ],
            'api' => [
                'api_access' => 'Acceso a API',
                'api_rate_limit_basic' => 'API Límite Básico (100/hora)',
                'api_rate_limit_premium' => 'API Límite Premium (1000/hora)',
                'api_rate_limit_unlimited' => 'API Sin Límites',
            ],
            'storage' => [
                'automatic_backups' => 'Respaldos Automáticos',
                'manual_backups' => 'Respaldos Manuales',
                'cloud_storage' => 'Almacenamiento en la Nube',
            ],
            'customization' => [
                'custom_branding' => 'Marca Personalizada',
                'custom_domain' => 'Dominio Personalizado',
                'white_label' => 'Etiqueta Blanca',
            ],
            'support' => [
                'email_support' => 'Soporte por Email',
                'priority_support' => 'Soporte Prioritario',
                'phone_support' => 'Soporte Telefónico',
                'dedicated_manager' => 'Gerente Dedicado',
            ],
            'advanced' => [
                'multi_location' => 'Multi-ubicación',
                'advanced_permissions' => 'Permisos Avanzados',
                'audit_logs' => 'Logs de Auditoría',
                'sla_guarantee' => 'Garantía SLA',
            ],
        ];
    }
}

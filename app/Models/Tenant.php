<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'uuid',
        'domain',
        'subdomain',
        'database_name',
        'plan',
        'status',
        'settings',
        'features',
        'expires_at',
        'trial_ends_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'features' => 'array',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime'
    ];

    protected $dates = [
        'expires_at',
        'trial_ends_at',
        'deleted_at'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (!$tenant->uuid) {
                $tenant->uuid = (string) Str::uuid();
            }
            if (!$tenant->slug && $tenant->name) {
                $tenant->slug = Str::slug($tenant->name);
            }
        });
    }

    /**
     * Relationship: Users belonging to this tenant
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_users')
                    ->withPivot([
                        'role_within_tenant', 
                        'permissions', 
                        'status', 
                        'invited_at', 
                        'joined_at', 
                        'last_access_at',
                        'settings'
                    ])
                    ->withTimestamps();
    }

    /**
     * Relationship: Tenant users (pivot table records)
     */
    public function tenantUsers()
    {
        return $this->hasMany(TenantUser::class);
    }

    /**
     * Relationship: Tenant settings
     */
    public function tenantSettings()
    {
        return $this->hasMany(TenantSetting::class);
    }

    /**
     * Relationship: Suppliers belonging to this tenant
     */
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    /**
     * Relationship: Customers belonging to this tenant
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Relationship: Quality grades belonging to this tenant
     */
    public function qualityGrades()
    {
        return $this->hasMany(QualityGrade::class);
    }

    /**
     * Relationship: Lots belonging to this tenant
     */
    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    /**
     * Relationship: Sales belonging to this tenant
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relationship: Payments belonging to this tenant
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relationship: Notifications belonging to this tenant
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Scope: Active tenants only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: By plan
     */
    public function scopePlan($query, $plan)
    {
        return $query->where('plan', $plan);
    }

    /**
     * Check if tenant is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if tenant is on trial
     */
    public function isOnTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant has expired
     */
    public function hasExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get tenant setting by key
     */
    public function getSetting($key, $default = null)
    {
        $setting = $this->tenantSettings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set tenant setting
     */
    public function setSetting($key, $value, $type = 'string', $category = 'general')
    {
        return $this->tenantSettings()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'category' => $category
            ]
        );
    }

    /**
     * Check if tenant has feature enabled
     */
    public function hasFeature($feature)
    {
        return in_array($feature, $this->features ?: []);
    }

    /**
     * Get full domain URL
     */
    public function getFullDomainAttribute()
    {
        if ($this->domain) {
            return $this->domain;
        }
        
        if ($this->subdomain) {
            return $this->subdomain . '.' . config('app.domain', 'localhost');
        }
        
        return null;
    }

    /**
     * Route model binding by slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
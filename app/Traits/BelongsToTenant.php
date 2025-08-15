<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToTenant()
    {
        // Agregar el global scope para filtrado automático por tenant
        static::addGlobalScope(new TenantScope);

        // Al crear un nuevo modelo, asignar automáticamente el tenant_id actual
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = session('current_tenant_id') ?? auth()->user()?->current_tenant_id ?? null;
            }
        });
    }

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope: For specific tenant
     */
    public function scopeForTenant(Builder $query, $tenantId = null)
    {
        $tenantId = $tenantId ?? session('current_tenant_id') ?? auth()->user()?->current_tenant_id;
        
        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Scope: Without tenant scope (for admin operations)
     */
    public function scopeWithoutTenantScope(Builder $query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope: All tenants (alias for withoutTenantScope)
     */
    public function scopeAllTenants(Builder $query)
    {
        return $query->withoutTenantScope();
    }

    /**
     * Check if model belongs to specific tenant
     */
    public function belongsToTenant($tenantId = null)
    {
        $tenantId = $tenantId ?? session('current_tenant_id') ?? auth()->user()?->current_tenant_id;
        
        return $this->tenant_id == $tenantId;
    }

    /**
     * Check if model belongs to current tenant
     */
    public function belongsToCurrentTenant()
    {
        return $this->belongsToTenant();
    }

    /**
     * Assign model to specific tenant
     */
    public function assignToTenant($tenantId)
    {
        $this->tenant_id = $tenantId;
        $this->save();
        
        return $this;
    }

    /**
     * Get tenant name for this model
     */
    public function getTenantName()
    {
        return $this->tenant ? $this->tenant->name : null;
    }

    /**
     * Create a new instance scoped to current tenant
     */
    public static function createForCurrentTenant(array $attributes = [])
    {
        $tenantId = session('current_tenant_id') ?? auth()->user()?->current_tenant_id;
        
        if (!$tenantId) {
            throw new \Exception('No current tenant set');
        }

        $attributes['tenant_id'] = $tenantId;
        
        return static::create($attributes);
    }

    /**
     * Find model by ID within current tenant
     */
    public static function findForCurrentTenant($id)
    {
        $tenantId = session('current_tenant_id') ?? auth()->user()?->current_tenant_id;
        
        return static::where('id', $id)
                    ->where('tenant_id', $tenantId)
                    ->first();
    }

    /**
     * Find or fail model by ID within current tenant
     */
    public static function findOrFailForCurrentTenant($id)
    {
        $model = static::findForCurrentTenant($id);
        
        if (!$model) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }
        
        return $model;
    }

    /**
     * Get all models for current tenant
     */
    public static function allForCurrentTenant()
    {
        return static::forTenant()->get();
    }

    /**
     * Get paginated results for current tenant
     */
    public static function paginateForCurrentTenant($perPage = 15)
    {
        return static::forTenant()->paginate($perPage);
    }

    /**
     * Override the newQuery method to automatically apply tenant scope
     */
    public function newQuery()
    {
        $query = parent::newQuery();
        
        // Si el usuario actual no es super admin, aplicar el filtro de tenant
        if (auth()->check() && !auth()->user()->hasRole('super_admin')) {
            $tenantId = session('current_tenant_id') ?? auth()->user()->current_tenant_id;
            if ($tenantId) {
                $query->where($this->getTable() . '.tenant_id', $tenantId);
            }
        }
        
        return $query;
    }
}
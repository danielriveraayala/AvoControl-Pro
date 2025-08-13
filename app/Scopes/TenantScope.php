<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Solo aplicar el scope si hay un usuario autenticado y no es super admin
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();
        
        // Los super admins pueden ver todos los registros
        if ($user->hasRole('super_admin')) {
            return;
        }

        // Obtener el tenant_id actual del usuario o de la sesión
        $tenantId = session('current_tenant_id') ?? $user->current_tenant_id ?? null;
        
        // Si no hay tenant_id, no mostrar nada (excepto para super admins)
        if (!$tenantId) {
            $builder->whereRaw('1 = 0'); // No results
            return;
        }

        // Aplicar filtro por tenant_id
        $builder->where($model->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Extend the query builder with macros.
     */
    public function extend(Builder $builder): void
    {
        // Macro para remover temporalmente el scope de tenant
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        // Macro para filtrar por un tenant específico
        $builder->macro('forTenant', function (Builder $builder, $tenantId = null) {
            $tenantId = $tenantId ?? session('current_tenant_id') ?? auth()->user()?->current_tenant_id;
            
            return $builder->withoutGlobalScope($this)
                          ->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
        });

        // Macro para obtener todos los registros sin filtro de tenant (solo super admins)
        $builder->macro('allTenants', function (Builder $builder) {
            // Verificar permisos
            if (!auth()->check() || !auth()->user()->hasRole('super_admin')) {
                throw new \Exception('Unauthorized: Only super admins can access all tenants data');
            }
            
            return $builder->withoutGlobalScope($this);
        });

        // Macro para filtrar por múltiples tenants
        $builder->macro('forTenants', function (Builder $builder, array $tenantIds) {
            return $builder->withoutGlobalScope($this)
                          ->whereIn($builder->getModel()->getTable() . '.tenant_id', $tenantIds);
        });

        // Macro para verificar si un modelo pertenece al tenant actual
        $builder->macro('belongsToCurrentTenant', function (Builder $builder) {
            $tenantId = session('current_tenant_id') ?? auth()->user()?->current_tenant_id;
            
            return $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
        });
    }
}
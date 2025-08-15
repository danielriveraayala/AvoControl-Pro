<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

trait HasPermissions
{
    /**
     * Boot the trait.
     */
    public static function bootHasPermissions()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }
            $model->roles()->detach();
        });
    }

    /**
     * Check if the model has a specific permission directly or through roles.
     */
    public function can($permission): bool
    {
        if (is_string($permission)) {
            return $this->hasPermission($permission);
        }

        if ($permission instanceof Permission) {
            return $this->hasPermission($permission->name);
        }

        return false;
    }

    /**
     * Check if the model cannot perform a specific permission.
     */
    public function cannot($permission): bool
    {
        return !$this->can($permission);
    }

    /**
     * Check multiple permissions with AND logic.
     */
    public function canAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check multiple permissions with OR logic.
     */
    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all permissions for caching.
     */
    protected function getAllPermissions(): array
    {
        $cacheKey = $this->getPermissionCacheKey();
        
        return Cache::remember($cacheKey, 3600, function () {
            return $this->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('name')
                ->unique()
                ->toArray();
        });
    }

    /**
     * Get the cache key for permissions.
     */
    protected function getPermissionCacheKey(): string
    {
        return sprintf('permissions_%s_%s', 
            class_basename($this),
            $this->getKey()
        );
    }

    /**
     * Flush the permission cache.
     */
    public function flushPermissionCache(): void
    {
        Cache::forget($this->getPermissionCacheKey());
    }

    /**
     * Check if model has permission to access a module.
     */
    public function canAccessModule(string $module): bool
    {
        $permissions = $this->getAllPermissions();
        
        foreach ($permissions as $permission) {
            if (strpos($permission, $module . '.') === 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get all accessible modules.
     */
    public function getAccessibleModules(): array
    {
        $permissions = $this->getAllPermissions();
        $modules = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission);
            if (count($parts) > 0) {
                $modules[] = $parts[0];
            }
        }
        
        return array_unique($modules);
    }

    /**
     * Check if model has any role with a specific hierarchy level or higher.
     */
    public function hasRoleWithHierarchy(int $minLevel): bool
    {
        return $this->roles()
            ->where('hierarchy_level', '>=', $minLevel)
            ->exists();
    }

    /**
     * Get permissions grouped by module.
     */
    public function getPermissionsByModule(): array
    {
        $permissions = Permission::whereIn('name', $this->getAllPermissions())
            ->get()
            ->groupBy('module');
        
        return $permissions->toArray();
    }

    /**
     * Revoke all permissions by clearing roles.
     */
    public function revokeAllPermissions(): void
    {
        $this->roles()->detach();
        $this->flushPermissionCache();
    }

    /**
     * Check if model has administrative privileges.
     */
    public function isAdministrator(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Check if model has management privileges.
     */
    public function isManager(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'gerente']);
    }

    /**
     * Check if model has read-only access.
     */
    public function isReadOnly(): bool
    {
        return $this->hasRole('visualizador') && 
               $this->roles()->count() === 1;
    }
}
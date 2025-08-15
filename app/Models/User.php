<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'current_tenant_id',
        'suspended_at',
        'suspension_reason',
        'password_changed_at',
        'created_by',
        'email_verified_at',
        'pending_cleanup_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'suspended_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'pending_cleanup_at' => 'datetime',
        'role' => 'string',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get all permissions for the user through their roles.
     */
    public function permissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    /**
     * Get cached permissions for better performance.
     */
    public function getCachedPermissions()
    {
        $cacheKey = 'user_permissions_' . $this->id;
        
        return Cache::remember($cacheKey, 3600, function () {
            // Get all permissions from all user's roles
            $permissions = [];
            foreach ($this->roles as $role) {
                foreach ($role->permissions as $permission) {
                    $permissions[] = $permission->name;
                }
            }
            return array_unique($permissions);
        });
    }

    /**
     * Clear permissions cache for the user.
     */
    public function clearPermissionsCache(): void
    {
        Cache::forget('user_permissions_' . $this->id);
    }

    /**
     * Get all permissions for the user (from all roles).
     */
    public function getAllPermissions(): array
    {
        return $this->getCachedPermissions();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        // Super admin has all permissions
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        $permissions = $this->getCachedPermissions();
        return in_array($permissionName, $permissions);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        // Super admin has all permissions
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        $userPermissions = $this->getCachedPermissions();
        return !empty(array_intersect($permissions, $userPermissions));
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        // Super admin has all permissions
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        $userPermissions = $this->getCachedPermissions();
        return count(array_intersect($permissions, $userPermissions)) === count($permissions);
    }

    /**
     * Assign roles to user.
     */
    public function assignRoles(array $roleIds): void
    {
        $this->roles()->syncWithoutDetaching($roleIds);
        $this->clearPermissionsCache();
    }

    /**
     * Remove roles from user.
     */
    public function removeRoles(array $roleIds): void
    {
        $this->roles()->detach($roleIds);
        $this->clearPermissionsCache();
    }

    /**
     * Sync roles with user.
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
        $this->clearPermissionsCache();
    }

    /**
     * Get the primary role of the user.
     */
    public function getPrimaryRole()
    {
        return $this->roles()->wherePivot('is_primary', true)->first();
    }

    /**
     * Set the primary role for the user.
     */
    public function setPrimaryRole(int $roleId): void
    {
        // Reset all roles to non-primary
        $this->roles()->updateExistingPivot(
            $this->roles()->pluck('id')->toArray(),
            ['is_primary' => false]
        );
        
        // Set the specified role as primary
        $this->roles()->updateExistingPivot($roleId, ['is_primary' => true]);
        $this->clearPermissionsCache();
    }

    /**
     * Check if user can perform an action on a module.
     */
    public function canPerformAction(string $module, string $action): bool
    {
        $permission = $module . '.' . $action;
        return $this->hasPermission($permission);
    }

    /**
     * Get the highest role hierarchy level for the user.
     */
    public function getHighestHierarchyLevel(): int
    {
        return $this->roles()->max('hierarchy_level') ?? 0;
    }

    /**
     * Check if user has higher authority than another user.
     */
    public function hasHigherAuthorityThan(User $user): bool
    {
        return $this->getHighestHierarchyLevel() > $user->getHighestHierarchyLevel();
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user can manage a specific role (based on hierarchy).
     */
    public function canManageRole(Role $role): bool
    {
        // Super admin can manage all roles
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Can only manage roles with lower hierarchy level
        return $this->getHighestHierarchyLevel() > $role->hierarchy_level;
    }

    /**
     * Check if user can assign a specific role to another user.
     */
    public function canAssignRole(Role $role): bool
    {
        // Super admin can assign any role
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Can only assign roles with lower hierarchy level than own
        return $this->getHighestHierarchyLevel() > $role->hierarchy_level;
    }

    /**
     * Check if user can manage another user (based on hierarchy).
     */
    public function canManageUser(User $targetUser): bool
    {
        // Super admin can manage all users
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Cannot manage users with equal or higher hierarchy
        return $this->getHighestHierarchyLevel() > $targetUser->getHighestHierarchyLevel();
    }

    /**
     * Get roles that this user can manage (assign/edit).
     */
    public function getManageableRoles()
    {
        if ($this->isSuperAdmin()) {
            return Role::all();
        }

        $userHierarchy = $this->getHighestHierarchyLevel();
        return Role::where('hierarchy_level', '<', $userHierarchy)->get();
    }

    /**
     * Get users that this user can manage.
     */
    public function getManageableUsers()
    {
        if ($this->isSuperAdmin()) {
            return User::where('id', '!=', $this->id);
        }

        $userHierarchy = $this->getHighestHierarchyLevel();
        
        return User::whereHas('roles', function ($query) use ($userHierarchy) {
            $query->where('hierarchy_level', '<', $userHierarchy);
        })->where('id', '!=', $this->id);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isVendedor()
    {
        return $this->role === 'vendedor';
    }

    public function isContador()
    {
        return $this->role === 'contador';
    }

    public function canManageUsers()
    {
        return $this->isAdmin();
    }

    public function canManageSuppliers()
    {
        return in_array($this->role, ['admin', 'contador']);
    }

    public function canManageCustomers()
    {
        return in_array($this->role, ['admin', 'vendedor']);
    }

    public function canManageLots()
    {
        return in_array($this->role, ['admin', 'contador']);
    }

    public function canManageSales()
    {
        return in_array($this->role, ['admin', 'vendedor']);
    }

    public function canManagePayments()
    {
        return in_array($this->role, ['admin', 'contador']);
    }

    // ================================
    // Multi-Tenant Relationships
    // ================================

    /**
     * Relationship: Tenants where this user is a member
     */
    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
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
     * Relationship: Current tenant
     */
    public function currentTenant()
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    /**
     * Get active tenants for this user
     */
    public function activeTenants()
    {
        return $this->tenants()->wherePivot('status', 'active');
    }

    /**
     * Switch to a different tenant
     */
    public function switchToTenant($tenantId)
    {
        $tenantUser = $this->tenantUsers()->where('tenant_id', $tenantId)->first();
        
        if (!$tenantUser || !$tenantUser->isActive()) {
            throw new \Exception('User does not have access to this tenant');
        }

        $this->current_tenant_id = $tenantId;
        $this->save();

        // Update session
        session(['current_tenant_id' => $tenantId]);
        
        // Update last access
        $tenantUser->updateLastAccess();

        return $this;
    }

    /**
     * Check if user belongs to a specific tenant
     */
    public function belongsToTenant($tenantId)
    {
        return $this->tenantUsers()
                   ->where('tenant_id', $tenantId)
                   ->where('status', 'active')
                   ->exists();
    }

    /**
     * Check if user is admin in any tenant
     */
    public function isAdminInAnyTenant()
    {
        return $this->tenantUsers()
                   ->whereIn('role_within_tenant', ['admin', 'owner', 'super_admin'])
                   ->where('status', 'active')
                   ->exists();
    }

    /**
     * Check if user is admin in specific tenant
     */
    public function isAdminInTenant($tenantId)
    {
        $tenantUser = $this->tenantUsers()
                          ->where('tenant_id', $tenantId)
                          ->where('status', 'active')
                          ->first();

        return $tenantUser && $tenantUser->isAdmin();
    }

    /**
     * Get user's role in specific tenant
     */
    public function getRoleInTenant($tenantId)
    {
        $tenantUser = $this->tenantUsers()
                          ->where('tenant_id', $tenantId)
                          ->first();

        return $tenantUser ? $tenantUser->role_within_tenant : null;
    }

    /**
     * Get user's permissions in specific tenant
     */
    public function getPermissionsInTenant($tenantId)
    {
        $tenantUser = $this->tenantUsers()
                          ->where('tenant_id', $tenantId)
                          ->first();

        return $tenantUser ? ($tenantUser->permissions ?: []) : [];
    }

    /**
     * Check if user has permission in current tenant
     */
    public function hasPermissionInCurrentTenant($permission)
    {
        if (!$this->current_tenant_id) {
            return false;
        }

        $tenantUser = $this->tenantUsers()
                          ->where('tenant_id', $this->current_tenant_id)
                          ->first();

        return $tenantUser && $tenantUser->hasPermission($permission);
    }

    /**
     * Get current tenant information
     */
    public function getCurrentTenantInfo()
    {
        if (!$this->current_tenant_id) {
            return null;
        }

        $tenantUser = $this->tenantUsers()
                          ->where('tenant_id', $this->current_tenant_id)
                          ->with('tenant')
                          ->first();

        return $tenantUser ? [
            'tenant' => $tenantUser->tenant,
            'role' => $tenantUser->role_within_tenant,
            'permissions' => $tenantUser->permissions,
            'settings' => $tenantUser->settings
        ] : null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'hierarchy_level',
        'is_system'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
        'hierarchy_level' => 'integer'
    ];

    /**
     * Get the permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Get the users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()
            ->where('name', $permissionName)
            ->exists();
    }

    /**
     * Check if role has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()
            ->whereIn('name', $permissions)
            ->exists();
    }

    /**
     * Check if role has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return $this->permissions()
            ->whereIn('name', $permissions)
            ->count() === count($permissions);
    }

    /**
     * Grant permissions to role.
     */
    public function grantPermissions(array $permissionIds): void
    {
        $this->permissions()->syncWithoutDetaching($permissionIds);
    }

    /**
     * Revoke permissions from role.
     */
    public function revokePermissions(array $permissionIds): void
    {
        $this->permissions()->detach($permissionIds);
    }

    /**
     * Sync permissions with role.
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Check if role is higher in hierarchy than another role.
     */
    public function isHigherThan(Role $role): bool
    {
        return $this->hierarchy_level > $role->hierarchy_level;
    }

    /**
     * Check if role is lower in hierarchy than another role.
     */
    public function isLowerThan(Role $role): bool
    {
        return $this->hierarchy_level < $role->hierarchy_level;
    }

    /**
     * Check if role is at same level in hierarchy as another role.
     */
    public function isSameLevelAs(Role $role): bool
    {
        return $this->hierarchy_level === $role->hierarchy_level;
    }

    /**
     * Check if this is a system role (cannot be deleted).
     */
    public function isSystemRole(): bool
    {
        return $this->is_system;
    }

    /**
     * Get permissions grouped by module.
     */
    public function getPermissionsByModule(): array
    {
        return $this->permissions()
            ->get()
            ->groupBy('module')
            ->toArray();
    }

    /**
     * Scope to get roles by hierarchy level.
     */
    public function scopeByHierarchy($query, string $operator = '>=', int $level = 0)
    {
        return $query->where('hierarchy_level', $operator, $level);
    }

    /**
     * Scope to get only custom (non-system) roles.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope to get only system roles.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Get audit logs for this role.
     */
    public function audits()
    {
        return $this->hasMany(RoleAudit::class)->with('user')->latest();
    }
}
<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Support\Collection;

trait HasRoles
{
    /**
     * Assign a role by name.
     */
    public function assignRole(string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }
        
        $this->roles()->syncWithoutDetaching([$role->id]);
        
        if (method_exists($this, 'flushPermissionCache')) {
            $this->flushPermissionCache();
        }
        
        return true;
    }

    /**
     * Assign multiple roles by names.
     */
    public function assignRolesByName(array $roleNames): int
    {
        $roles = Role::whereIn('name', $roleNames)->pluck('id');
        
        if ($roles->isEmpty()) {
            return 0;
        }
        
        $this->roles()->syncWithoutDetaching($roles);
        
        if (method_exists($this, 'flushPermissionCache')) {
            $this->flushPermissionCache();
        }
        
        return $roles->count();
    }

    /**
     * Remove a role by name.
     */
    public function removeRole(string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }
        
        $this->roles()->detach($role->id);
        
        if (method_exists($this, 'flushPermissionCache')) {
            $this->flushPermissionCache();
        }
        
        return true;
    }

    /**
     * Remove multiple roles by names.
     */
    public function removeRolesByName(array $roleNames): int
    {
        $roles = Role::whereIn('name', $roleNames)->pluck('id');
        
        if ($roles->isEmpty()) {
            return 0;
        }
        
        $this->roles()->detach($roles);
        
        if (method_exists($this, 'flushPermissionCache')) {
            $this->flushPermissionCache();
        }
        
        return $roles->count();
    }

    /**
     * Sync roles by names (replaces all existing roles).
     */
    public function syncRolesByName(array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)->pluck('id');
        
        $this->roles()->sync($roles);
        
        if (method_exists($this, 'flushPermissionCache')) {
            $this->flushPermissionCache();
        }
    }

    /**
     * Get role names as collection.
     */
    public function getRoleNames(): Collection
    {
        return $this->roles()->pluck('name');
    }

    /**
     * Get role display names as collection.
     */
    public function getRoleDisplayNames(): Collection
    {
        return $this->roles()->pluck('display_name');
    }

    /**
     * Check if has role by ID.
     */
    public function hasRoleById(int $roleId): bool
    {
        return $this->roles()->where('id', $roleId)->exists();
    }

    /**
     * Get the highest priority role.
     */
    public function getHighestRole(): ?Role
    {
        return $this->roles()
            ->orderBy('hierarchy_level', 'desc')
            ->first();
    }

    /**
     * Get the lowest priority role.
     */
    public function getLowestRole(): ?Role
    {
        return $this->roles()
            ->orderBy('hierarchy_level', 'asc')
            ->first();
    }

    /**
     * Switch to a different role temporarily.
     */
    public function switchToRole(string $roleName): bool
    {
        if (!$this->hasRole($roleName)) {
            return false;
        }
        
        $role = Role::where('name', $roleName)->first();
        
        // Set this role as primary
        $this->roles()->updateExistingPivot(
            $this->roles()->pluck('id')->toArray(),
            ['is_primary' => false]
        );
        
        $this->roles()->updateExistingPivot($role->id, ['is_primary' => true]);
        
        if (method_exists($this, 'flushPermissionCache')) {
            $this->flushPermissionCache();
        }
        
        return true;
    }

    /**
     * Get active/primary role.
     */
    public function getActiveRole(): ?Role
    {
        $primaryRole = $this->roles()->wherePivot('is_primary', true)->first();
        
        if ($primaryRole) {
            return $primaryRole;
        }
        
        // If no primary role, return the highest hierarchy role
        return $this->getHighestRole();
    }

    /**
     * Check if the model has roles above a certain hierarchy level.
     */
    public function hasRoleAboveLevel(int $level): bool
    {
        return $this->roles()
            ->where('hierarchy_level', '>', $level)
            ->exists();
    }

    /**
     * Check if the model has roles below a certain hierarchy level.
     */
    public function hasRoleBelowLevel(int $level): bool
    {
        return $this->roles()
            ->where('hierarchy_level', '<', $level)
            ->exists();
    }

    /**
     * Get roles within a hierarchy range.
     */
    public function getRolesInRange(int $minLevel, int $maxLevel): Collection
    {
        return $this->roles()
            ->whereBetween('hierarchy_level', [$minLevel, $maxLevel])
            ->get();
    }
}
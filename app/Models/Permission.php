<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'module',
        'description'
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Get all users that have this permission through their roles.
     */
    public function users()
    {
        return User::whereHas('roles.permissions', function ($query) {
            $query->where('permissions.id', $this->id);
        });
    }

    /**
     * Check if permission belongs to a specific module.
     */
    public function belongsToModule(string $module): bool
    {
        return $this->module === $module;
    }

    /**
     * Scope to get permissions by module.
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to get permissions by multiple modules.
     */
    public function scopeByModules($query, array $modules)
    {
        return $query->whereIn('module', $modules);
    }

    /**
     * Get all unique modules.
     */
    public static function getModules(): array
    {
        return static::distinct('module')
            ->pluck('module')
            ->toArray();
    }

    /**
     * Group permissions by module.
     */
    public static function groupByModule()
    {
        return static::all()->groupBy('module');
    }

    /**
     * Check if this is a critical permission.
     */
    public function isCritical(): bool
    {
        $criticalPermissions = [
            'users.delete',
            'users.manage_roles',
            'system.backup',
            'system.restore',
            'configuration.edit_system'
        ];

        return in_array($this->name, $criticalPermissions);
    }

    /**
     * Get the action part of the permission name (e.g., 'view' from 'lots.view').
     */
    public function getAction(): string
    {
        $parts = explode('.', $this->name);
        return end($parts);
    }

    /**
     * Get the resource part of the permission name (e.g., 'lots' from 'lots.view').
     */
    public function getResource(): string
    {
        $parts = explode('.', $this->name);
        return $parts[0] ?? '';
    }

    /**
     * Check if this is a read-only permission.
     */
    public function isReadOnly(): bool
    {
        $readOnlyActions = ['view', 'view_basic', 'view_financial', 'view_analytics', 'view_balance'];
        return in_array($this->getAction(), $readOnlyActions);
    }

    /**
     * Check if this is a write permission.
     */
    public function isWrite(): bool
    {
        $writeActions = ['create', 'edit', 'update', 'delete', 'approve', 'cancel', 'deliver', 'manage'];
        return in_array($this->getAction(), $writeActions);
    }
}
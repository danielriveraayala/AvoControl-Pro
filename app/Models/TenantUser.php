<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TenantUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role_within_tenant',
        'permissions',
        'status',
        'invited_at',
        'joined_at',
        'last_access_at',
        'settings'
    ];

    protected $casts = [
        'permissions' => 'array',
        'settings' => 'array',
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
        'last_access_at' => 'datetime'
    ];

    protected $dates = [
        'invited_at',
        'joined_at',
        'last_access_at'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenantUser) {
            if ($tenantUser->status === 'active' && !$tenantUser->joined_at) {
                $tenantUser->joined_at = now();
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
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Active users only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: By role
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role_within_tenant', $role);
    }

    /**
     * Scope: By tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Check if user is active in this tenant
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is admin in this tenant
     */
    public function isAdmin()
    {
        return in_array($this->role_within_tenant, ['admin', 'owner', 'super_admin']);
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissions ?: []);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions)
    {
        if ($this->isAdmin()) {
            return true;
        }

        return !empty(array_intersect($permissions, $this->permissions ?: []));
    }

    /**
     * Grant permission to user
     */
    public function grantPermission($permission)
    {
        $permissions = $this->permissions ?: [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }

        return $this;
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission($permission)
    {
        $permissions = $this->permissions ?: [];
        
        if (($key = array_search($permission, $permissions)) !== false) {
            unset($permissions[$key]);
            $this->permissions = array_values($permissions);
            $this->save();
        }

        return $this;
    }

    /**
     * Update last access timestamp
     */
    public function updateLastAccess()
    {
        $this->last_access_at = now();
        $this->save();
    }

    /**
     * Activate user in tenant
     */
    public function activate()
    {
        $this->status = 'active';
        if (!$this->joined_at) {
            $this->joined_at = now();
        }
        $this->save();

        return $this;
    }

    /**
     * Deactivate user in tenant
     */
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();

        return $this;
    }

    /**
     * Suspend user in tenant
     */
    public function suspend()
    {
        $this->status = 'suspended';
        $this->save();

        return $this;
    }

    /**
     * Invite user to tenant
     */
    public function invite()
    {
        $this->status = 'invited';
        $this->invited_at = now();
        $this->save();

        return $this;
    }

    /**
     * Get user setting for this tenant
     */
    public function getSetting($key, $default = null)
    {
        $settings = $this->settings ?: [];
        return $settings[$key] ?? $default;
    }

    /**
     * Set user setting for this tenant
     */
    public function setSetting($key, $value)
    {
        $settings = $this->settings ?: [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();

        return $this;
    }

    /**
     * Get available roles for tenant users
     */
    public static function getAvailableRoles()
    {
        return [
            'owner' => 'Propietario',
            'admin' => 'Administrador',
            'manager' => 'Gerente',
            'vendedor' => 'Vendedor',
            'contador' => 'Contador',
            'member' => 'Miembro',
            'viewer' => 'Solo Lectura'
        ];
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName()
    {
        $roles = self::getAvailableRoles();
        return $roles[$this->role_within_tenant] ?? $this->role_within_tenant;
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName()
    {
        $statuses = [
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'invited' => 'Invitado',
            'suspended' => 'Suspendido'
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
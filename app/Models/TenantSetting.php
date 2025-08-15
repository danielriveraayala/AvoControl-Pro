<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class TenantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
        'description',
        'category',
        'is_public',
        'is_encrypted'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($setting) {
            if ($setting->is_encrypted && $setting->isDirty('value')) {
                $setting->value = Crypt::encrypt($setting->value);
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
     * Get the decrypted value
     */
    public function getDecryptedValueAttribute()
    {
        if ($this->is_encrypted) {
            try {
                return Crypt::decrypt($this->value);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $this->value;
    }

    /**
     * Get the typed value based on the 'type' field
     */
    public function getTypedValueAttribute()
    {
        $value = $this->is_encrypted ? $this->decrypted_value : $this->value;

        switch ($this->type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            
            case 'integer':
                return (int) $value;
            
            case 'float':
                return (float) $value;
            
            case 'json':
                return json_decode($value, true);
            
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            
            default:
                return $value;
        }
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Public settings only
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Private settings only
     */
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope: By tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Set value with automatic type conversion
     */
    public function setTypedValue($value)
    {
        switch ($this->type) {
            case 'boolean':
                $this->value = $value ? '1' : '0';
                break;
            
            case 'json':
            case 'array':
                $this->value = json_encode($value);
                break;
            
            default:
                $this->value = (string) $value;
        }

        $this->save();
        return $this;
    }

    /**
     * Get available setting categories
     */
    public static function getAvailableCategories()
    {
        return [
            'general' => 'General',
            'company' => 'Empresa',
            'email' => 'Email',
            'notifications' => 'Notificaciones',
            'security' => 'Seguridad',
            'api' => 'API',
            'integrations' => 'Integraciones',
            'reports' => 'Reportes',
            'payments' => 'Pagos',
            'inventory' => 'Inventario',
            'system' => 'Sistema'
        ];
    }

    /**
     * Get available setting types
     */
    public static function getAvailableTypes()
    {
        return [
            'string' => 'Texto',
            'integer' => 'Número Entero',
            'float' => 'Número Decimal',
            'boolean' => 'Verdadero/Falso',
            'json' => 'JSON',
            'array' => 'Array',
            'text' => 'Texto Largo',
            'email' => 'Email',
            'url' => 'URL',
            'password' => 'Contraseña'
        ];
    }

    /**
     * Check if setting is editable by non-admin users
     */
    public function isEditableByUsers()
    {
        return $this->is_public && !in_array($this->category, ['security', 'system', 'api']);
    }

    /**
     * Get display value for UI
     */
    public function getDisplayValue()
    {
        if ($this->type === 'password') {
            return '********';
        }

        if ($this->type === 'boolean') {
            return $this->typed_value ? 'Sí' : 'No';
        }

        if (in_array($this->type, ['json', 'array'])) {
            return json_encode($this->typed_value, JSON_PRETTY_PRINT);
        }

        return $this->typed_value;
    }
}
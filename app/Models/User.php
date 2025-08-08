<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        'role' => 'string',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'status',
        'notes'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

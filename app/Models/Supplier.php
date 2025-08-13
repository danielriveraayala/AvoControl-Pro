<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Supplier extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
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
        'balance_owed' => 'decimal:2',
        'total_purchased' => 'decimal:2',
    ];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function updateBalance()
    {
        // Calculate total purchases from lots
        $totalPurchases = $this->lots->sum(function($lot) {
            return ($lot->total_weight ?? 0) * ($lot->purchase_price_per_kg ?? 0);
        });

        // Calculate total payments made to this supplier
        $totalPaid = \App\Models\Payment::where('payable_type', 'App\\Models\\Lot')
                           ->whereIn('payable_id', $this->lots->pluck('id'))
                           ->where('status', 'confirmed')
                           ->sum('amount');

        // Update balance
        $this->balance_owed = $totalPurchases - $totalPaid;
        $this->total_purchased = $totalPurchases;
        $this->save();

        return $this->balance_owed;
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->lots->sum(function($lot) {
            return ($lot->total_weight ?? 0) * ($lot->purchase_price_per_kg ?? 0);
        });
    }

    public function getTotalPaidAttribute()
    {
        return \App\Models\Payment::where('payable_type', 'App\\Models\\Lot')
                     ->whereIn('payable_id', $this->lots->pluck('id'))
                     ->where('status', 'confirmed')
                     ->sum('amount');
    }
}

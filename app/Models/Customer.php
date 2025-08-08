<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
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
        'customer_type',
        'credit_limit',
        'current_balance',
        'status',
        'notes'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'status' => 'string',
        'customer_type' => 'string',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function totalSales()
    {
        return $this->sales()->sum('total_amount');
    }

    public function updateBalance()
    {
        // Calculate balance from sales and payments
        $totalSales = $this->sales()->sum('total_amount');
        $totalPayments = Payment::where('payable_type', Sale::class)
            ->whereIn('payable_id', $this->sales()->pluck('id'))
            ->sum('amount');
        
        $this->current_balance = $totalSales - $totalPayments;
        $this->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_code',
        'invoice_number',
        'customer_id',
        'created_by',
        'sale_date',
        'delivery_date',
        'total_weight',
        'total_amount',
        'payment_status',
        'status',
        'due_date',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'delivery_date' => 'date',
        'due_date' => 'date',
        'total_weight' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function updatePaymentStatus()
    {
        $totalPaid = $this->payments()->sum('amount');
        
        if ($totalPaid >= $this->total_amount) {
            $this->payment_status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }

        $this->save();
    }

    public function calculateTotals()
    {
        $this->total_weight = $this->saleItems()->sum('weight');
        $this->total_amount = $this->saleItems()->sum('subtotal');
        $this->save();
        
        // Update payment status after calculating totals
        $this->updatePaymentStatus();
    }
}

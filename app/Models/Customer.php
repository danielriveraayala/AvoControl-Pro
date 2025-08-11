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
        $totalSales = $this->sales()->whereIn('status', ['confirmed', 'shipped', 'delivered'])->sum('total_amount');
        $totalPayments = Payment::where('payable_type', Sale::class)
            ->whereIn('payable_id', $this->sales()->pluck('id'))
            ->sum('amount');
        
        $this->current_balance = $totalSales - $totalPayments;
        $this->save();
        
        return $this->current_balance;
    }

    /**
     * Get available credit amount
     */
    public function getAvailableCreditAttribute()
    {
        return $this->credit_limit - $this->current_balance;
    }

    /**
     * Check if customer has enough credit for an amount
     */
    public function hasAvailableCredit($amount)
    {
        return $this->getAvailableCreditAttribute() >= $amount;
    }

    /**
     * Get credit utilization percentage
     */
    public function getCreditUtilizationAttribute()
    {
        if ($this->credit_limit <= 0) return 0;
        return min(($this->current_balance / $this->credit_limit) * 100, 100);
    }

    /**
     * Get credit status
     */
    public function getCreditStatusAttribute()
    {
        $utilization = $this->getCreditUtilizationAttribute();
        
        if ($utilization >= 100) return 'exceeded';
        if ($utilization >= 90) return 'critical';
        if ($utilization >= 75) return 'warning';
        return 'good';
    }

    /**
     * Get sales with payment status
     */
    public function salesWithPayments()
    {
        return $this->sales()->with(['payments' => function($query) {
            $query->select('payable_id', 'amount', 'payment_date', 'payment_method');
        }])->get()->map(function($sale) {
            $totalPaid = $sale->payments->sum('amount');
            $sale->total_paid = $totalPaid;
            $sale->balance_due = $sale->total_amount - $totalPaid;
            $sale->payment_status = $this->calculatePaymentStatus($sale->total_amount, $totalPaid);
            return $sale;
        });
    }

    /**
     * Calculate payment status for a sale
     */
    private function calculatePaymentStatus($totalAmount, $totalPaid)
    {
        if ($totalPaid >= $totalAmount) return 'paid';
        if ($totalPaid > 0) return 'partial';
        return 'pending';
    }

    /**
     * Get payment summary
     */
    public function getPaymentSummaryAttribute()
    {
        $sales = $this->sales()->whereIn('status', ['confirmed', 'shipped', 'delivered'])->get();
        $totalSales = $sales->sum('total_amount');
        $totalPaid = 0;
        
        foreach ($sales as $sale) {
            $totalPaid += $sale->payments()->sum('amount');
        }
        
        return [
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'balance_due' => $totalSales - $totalPaid,
            'payment_percentage' => $totalSales > 0 ? ($totalPaid / $totalSales) * 100 : 0
        ];
    }
}

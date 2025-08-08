<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'status',
        'notes',
        'payment_details',
        'user_id'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'payment_method' => 'string',
        'status' => 'string',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($payment) {
            // Actualizar estado de pago de la venta
            $payment->sale->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            // Actualizar estado de pago de la venta
            $payment->sale->updatePaymentStatus();
        });
    }
}

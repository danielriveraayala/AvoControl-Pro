<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_code',
        'type',
        'concept',
        'payable_type',
        'payable_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'payment_method' => 'string',
        'status' => 'string',
    ];

    // Relación polimórfica para el objeto pagable (Sale o Lot)
    public function payable()
    {
        return $this->morphTo();
    }

    // Mantener para compatibilidad con versiones anteriores
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($payment) {
            // Actualizar estado de pago de la venta (relación polimórfica)
            if ($payment->payable && method_exists($payment->payable, 'updatePaymentStatus')) {
                $payment->payable->updatePaymentStatus();
            }
        });

        static::deleted(function ($payment) {
            // Actualizar estado de pago de la venta (relación polimórfica)
            if ($payment->payable && method_exists($payment->payable, 'updatePaymentStatus')) {
                $payment->payable->updatePaymentStatus();
            }
        });
    }
}

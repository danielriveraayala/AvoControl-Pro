<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_id',
        'amount',
        'payment_date',
        'payment_type',
        'paid_by_user_id',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }
}

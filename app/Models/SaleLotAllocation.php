<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleLotAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_item_id',
        'lot_id',
        'allocated_weight',
        'cost_per_kg',
        'allocated_cost'
    ];

    protected $casts = [
        'allocated_weight' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'allocated_cost' => 'decimal:2',
    ];

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Calcula automáticamente el costo asignado
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($allocation) {
            $allocation->allocated_cost = $allocation->allocated_weight * $allocation->cost_per_kg;
        });
    }
}

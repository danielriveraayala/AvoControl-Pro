<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'lot_id',
        'weight',
        'price_per_kg',
        'subtotal'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function calculateSubtotal()
    {
        $this->subtotal = $this->weight * $this->price_per_kg;
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($saleItem) {
            // Actualizar pesos del lote
            $saleItem->lot->updateWeights();
            
            // Actualizar totales de la venta
            $saleItem->sale->calculateTotals();
        });

        static::deleted(function ($saleItem) {
            // Actualizar pesos del lote
            $saleItem->lot->updateWeights();
            
            // Actualizar totales de la venta
            $saleItem->sale->calculateTotals();
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_code',
        'supplier_id',
        'harvest_date',
        'entry_date',
        'total_weight',
        'purchase_price_per_kg',
        'total_purchase_cost',
        'quality_grade',
        'status',
        'weight_sold',
        'weight_available',
        'metadata'
    ];

    protected $casts = [
        'total_weight' => 'decimal:2',
        'purchase_price_per_kg' => 'decimal:2',
        'total_purchase_cost' => 'decimal:2',
        'weight_sold' => 'decimal:2',
        'weight_available' => 'decimal:2',
        'harvest_date' => 'date',
        'entry_date' => 'datetime',
        'metadata' => 'array',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', '!=', 'sold')->where('weight_available', '>', 0);
    }

    public function updateWeights()
    {
        $soldWeight = $this->saleItems()->sum('weight');
        $this->weight_sold = $soldWeight;
        $this->weight_available = $this->total_weight - $soldWeight;
        
        // Update status based on weights
        if ($this->weight_available <= 0) {
            $this->status = 'sold';
        } elseif ($this->weight_sold > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'active';
        }
        
        $this->save();
    }
}

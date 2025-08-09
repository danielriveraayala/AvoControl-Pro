<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    use HasFactory;

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Clean up related records when deleting a lot
        static::deleting(function ($lot) {
            // Delete related sale allocations
            $lot->saleAllocations()->delete();
            
            // Delete related lot payments
            $lot->lotPayments()->delete();
        });
    }

    protected $fillable = [
        'lot_code',
        'supplier_id',
        'harvest_date',
        'entry_date',
        'total_weight',
        'purchase_price_per_kg',
        'total_purchase_cost',
        'amount_paid',
        'amount_owed',
        'payment_status',
        'quality_grade',
        'quality_grade_id', // Nueva columna para relación
        'status',
        'weight_sold',
        'weight_available',
        'notes'
    ];

    protected $casts = [
        'total_weight' => 'decimal:2',
        'purchase_price_per_kg' => 'decimal:2',
        'total_purchase_cost' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_owed' => 'decimal:2',
        'weight_sold' => 'decimal:2',
        'weight_available' => 'decimal:2',
        'harvest_date' => 'date',
        'entry_date' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relación con la calidad del lote
     */
    public function qualityGrade()
    {
        return $this->belongsTo(QualityGrade::class);
    }

    /**
     * Relación con asignaciones de ventas (para trazabilidad)
     */
    public function saleAllocations()
    {
        return $this->hasMany(SaleLotAllocation::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function lotPayments()
    {
        return $this->hasMany(LotPayment::class)->orderBy('payment_date', 'desc');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', '!=', 'sold')->where('weight_available', '>', 0);
    }

    /**
     * Los pesos se actualizan automáticamente desde SaleItem::allocateToLots()
     * Mantener este método por compatibilidad pero simplificado
     */
    public function updateWeights()
    {
        // Los pesos ya se actualizan automáticamente en SaleItem
        // Solo necesitamos recalcular el estado basado en peso disponible
        if ($this->weight_available <= 0) {
            $this->status = 'sold';
        } elseif ($this->weight_sold > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'active';
        }
        
        $this->save();
    }

    public function updatePaymentAmounts()
    {
        $totalPaid = $this->lotPayments()->sum('amount');
        $this->amount_paid = $totalPaid;
        $this->amount_owed = $this->total_purchase_cost - $totalPaid;
        
        // Update payment status
        if ($this->amount_owed <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }
        
        $this->save();
    }

    /**
     * Update payment status for new polymorphic payment system
     * (Following Sales pattern)
     */
    public function updatePaymentStatus()
    {
        $totalPaid = $this->payments()->sum('amount');
        
        if ($totalPaid >= $this->total_purchase_cost) {
            $this->payment_status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }
        
        $this->save();
    }
}

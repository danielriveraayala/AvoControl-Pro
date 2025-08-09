<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'quality_grade',
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

    /**
     * Relación con las asignaciones de lotes para esta venta
     */
    public function allocations()
    {
        return $this->hasMany(SaleLotAllocation::class);
    }

    /**
     * Obtiene todos los lotes que contribuyeron a esta venta
     */
    public function lots()
    {
        return $this->belongsToMany(Lot::class, 'sale_lot_allocations')
                    ->withPivot(['allocated_weight', 'cost_per_kg', 'allocated_cost'])
                    ->withTimestamps();
    }

    public function calculateSubtotal()
    {
        $this->subtotal = $this->weight * $this->price_per_kg;
        // Don't save here to avoid infinite loops when called from boot method
    }

    /**
     * Distribuye automáticamente la venta entre lotes disponibles de la calidad especificada
     */
    public function allocateToLots()
    {
        // Evitar múltiples asignaciones si ya se ha procesado
        if ($this->allocations()->exists()) {
            return;
        }
        
        // Limpiar asignaciones anteriores (por seguridad)
        $this->allocations()->delete();

        // Buscar la calidad por nombre para obtener el ID
        $qualityGradeRecord = \App\Models\QualityGrade::where('name', $this->quality_grade)->first();
        if (!$qualityGradeRecord) {
            throw new \Exception("Calidad '{$this->quality_grade}' no encontrada");
        }
        
        // Obtener lotes disponibles de la calidad especificada, ordenados por FIFO (primero en entrar, primero en salir)
        $availableLots = Lot::where('quality_grade_id', $qualityGradeRecord->id)
                            ->where('weight_available', '>', 0)
                            ->orderBy('entry_date', 'asc') // FIFO
                            ->get();

        if ($availableLots->isEmpty()) {
            throw new \Exception("No hay inventario disponible de calidad '{$this->quality_grade}'");
        }

        $remainingWeight = $this->weight;
        
        foreach ($availableLots as $lot) {
            if ($remainingWeight <= 0) break;

            // Calcular cuánto peso asignar de este lote
            $allocatedWeight = min($remainingWeight, $lot->weight_available);
            
            // Crear la asignación
            SaleLotAllocation::create([
                'sale_item_id' => $this->id,
                'lot_id' => $lot->id,
                'allocated_weight' => $allocatedWeight,
                'cost_per_kg' => $lot->purchase_price_per_kg,
            ]);

            // Actualizar peso disponible del lote
            $lot->weight_sold += $allocatedWeight;
            $lot->weight_available -= $allocatedWeight;
            
            // Actualizar estado del lote
            if ($lot->weight_available <= 0) {
                $lot->status = 'sold';
            } elseif ($lot->weight_sold > 0) {
                $lot->status = 'partial';
            }
            
            $lot->save();

            $remainingWeight -= $allocatedWeight;
        }

        if ($remainingWeight > 0) {
            throw new \Exception("Inventario insuficiente. Faltaron {$remainingWeight} kg de calidad '{$this->quality_grade}'");
        }
    }

    /**
     * Revierte las asignaciones cuando se elimina o modifica una venta
     */
    public function revertAllocations()
    {
        foreach ($this->allocations as $allocation) {
            $lot = $allocation->lot;
            
            // Revertir cambios en el lote
            $lot->weight_sold -= $allocation->allocated_weight;
            $lot->weight_available += $allocation->allocated_weight;
            
            // Recalcular estado
            if ($lot->weight_available > 0 && $lot->weight_sold > 0) {
                $lot->status = 'partial';
            } elseif ($lot->weight_sold <= 0) {
                $lot->status = 'active';
            }
            
            $lot->save();
        }
        
        // Eliminar asignaciones
        $this->allocations()->delete();
    }

    /**
     * Calcula el costo total de la venta basado en las asignaciones
     */
    public function getTotalCostAttribute()
    {
        return $this->allocations()->sum('allocated_cost');
    }

    /**
     * Calcula la ganancia de esta venta específica
     */
    public function getProfitAttribute()
    {
        return $this->subtotal - $this->total_cost;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($saleItem) {
            // Calcular subtotal antes de crear
            $saleItem->calculateSubtotal();
        });

        static::updating(function ($saleItem) {
            // Calcular subtotal antes de actualizar
            $saleItem->calculateSubtotal();
        });

        static::created(function ($saleItem) {
            // Realizar asignación automática a lotes después de crear
            $saleItem->allocateToLots();
            
            // Actualizar totales de la venta
            $saleItem->sale->calculateTotals();
        });

        static::updated(function ($saleItem) {
            // Realizar asignación automática a lotes después de actualizar
            $saleItem->allocateToLots();
            
            // Actualizar totales de la venta
            $saleItem->sale->calculateTotals();
        });

        static::deleted(function ($saleItem) {
            // Revertir asignaciones
            $saleItem->revertAllocations();
            
            // Actualizar totales de la venta
            if ($saleItem->sale) {
                $saleItem->sale->calculateTotals();
            }
        });
    }
}

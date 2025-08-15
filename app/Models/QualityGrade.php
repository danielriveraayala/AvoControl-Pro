<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class QualityGrade extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'caliber_min',
        'caliber_max',
        'weight_min',
        'weight_max',
        'description',
        'color',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
        'caliber_min' => 'integer',
        'caliber_max' => 'integer',
        'weight_min' => 'integer',
        'weight_max' => 'integer',
        'sort_order' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getCaliberRangeAttribute()
    {
        if ($this->caliber_min && $this->caliber_max) {
            return "{$this->caliber_min} - {$this->caliber_max}";
        }
        return $this->caliber_min ?: $this->caliber_max ?: 'Sin especificar';
    }

    public function getWeightRangeAttribute()
    {
        if ($this->weight_min && $this->weight_max) {
            return "{$this->weight_min}g - {$this->weight_max}g";
        }
        return ($this->weight_min ? $this->weight_min . 'g+' : '') . 
               ($this->weight_max ? $this->weight_max . 'g-' : '');
    }

    /**
     * Get all lots that belong to this quality grade.
     */
    public function lots()
    {
        return $this->hasMany(Lot::class, 'quality_grade_id');
    }

    /**
     * Get count of lots using this quality grade.
     */
    public function getLotsCountAttribute()
    {
        return $this->lots()->count();
    }

    /**
     * Check if this quality grade can be deleted.
     */
    public function canBeDeleted()
    {
        return $this->lots()->count() === 0;
    }
}

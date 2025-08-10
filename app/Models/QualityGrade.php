<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityGrade extends Model
{
    use HasFactory;

    protected $fillable = [
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
}

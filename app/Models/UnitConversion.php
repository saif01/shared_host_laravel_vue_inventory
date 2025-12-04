<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_unit_id',
        'to_unit_id',
        'conversion_factor',
        'operation',
        'description',
        'is_active',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function fromUnit()
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }
}


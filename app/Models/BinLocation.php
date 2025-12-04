<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BinLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'name',
        'aisle',
        'rack',
        'shelf',
        'bin',
        'type',
        'capacity',
        'capacity_unit',
        'description',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productBinLocations()
    {
        return $this->hasMany(ProductBinLocation::class);
    }
}


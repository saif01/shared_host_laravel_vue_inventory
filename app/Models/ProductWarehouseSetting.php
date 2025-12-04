<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouseSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'minimum_stock_level',
        'maximum_stock_level',
        'reorder_level',
        'reorder_quantity',
        'custom_selling_price',
        'is_available',
    ];

    protected $casts = [
        'minimum_stock_level' => 'integer',
        'maximum_stock_level' => 'integer',
        'reorder_level' => 'integer',
        'reorder_quantity' => 'integer',
        'custom_selling_price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}


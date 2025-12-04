<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variation_type',
        'variation_value',
        'sku',
        'barcode',
        'additional_cost',
        'additional_price',
        'stock_quantity',
        'is_available',
    ];

    protected $casts = [
        'additional_cost' => 'decimal:2',
        'additional_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_available' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}


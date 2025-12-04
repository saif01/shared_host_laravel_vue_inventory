<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'sub_category_id',
        'unit_id',
        'description',
        'manufacturer',
        'brand',
        'specifications',
        'warranty_period',
        'weight',
        'weight_unit',
        'dimensions_length',
        'dimensions_width',
        'dimensions_height',
        'dimensions_unit',
        'image',
        'cost_price',
        'selling_price',
        'tax_rate',
        'is_taxable',
        'discount_percentage',
        'minimum_stock_level',
        'low_stock_alert',
        'expiry_alert',
        'expiry_alert_days',
        'track_serial',
        'track_batch',
        'track_imei',
        'has_variations',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions_length' => 'decimal:2',
        'dimensions_width' => 'decimal:2',
        'dimensions_height' => 'decimal:2',
        'warranty_period' => 'integer',
        'expiry_alert_days' => 'integer',
        'track_serial' => 'boolean',
        'track_batch' => 'boolean',
        'track_imei' => 'boolean',
        'has_variations' => 'boolean',
        'is_taxable' => 'boolean',
        'low_stock_alert' => 'boolean',
        'expiry_alert' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class);
    }

    public function warehouseSettings(): HasMany
    {
        return $this->hasMany(ProductWarehouseSetting::class);
    }
}

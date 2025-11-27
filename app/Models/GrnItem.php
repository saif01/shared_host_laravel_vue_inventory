<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id',
        'product_id',
        'ordered_quantity',
        'received_quantity',
        'unit_price',
        'total',
        'serial_numbers',
        'notes',
    ];

    protected $casts = [
        'ordered_quantity' => 'integer',
        'received_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'serial_numbers' => 'array',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

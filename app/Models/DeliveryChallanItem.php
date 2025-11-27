<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryChallanItem extends Model
{
    protected $fillable = [
        'delivery_challan_id',
        'product_id',
        'quantity',
        'serial_numbers',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'serial_numbers' => 'array',
    ];

    public function deliveryChallan(): BelongsTo
    {
        return $this->belongsTo(DeliveryChallan::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

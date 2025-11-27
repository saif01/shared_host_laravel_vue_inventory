<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryChallan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'challan_number',
        'sales_order_id',
        'customer_id',
        'warehouse_id',
        'challan_date',
        'status',
        'delivery_address',
        'notes',
        'created_by',
        'delivered_by',
        'delivered_at',
    ];

    protected $casts = [
        'challan_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryChallanItem::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}

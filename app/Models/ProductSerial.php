<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'serial_number',
        'imei_number',
        'status',
        'batch_id',
        'notes',
        'sold_to_customer_id',
        'sold_date',
    ];

    protected $casts = [
        'sold_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'sold_to_customer_id');
    }
}


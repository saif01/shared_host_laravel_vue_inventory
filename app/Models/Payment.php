<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'payment_type',
        'party_type',
        'party_id',
        'reference_type',
        'reference_id',
        'reference_number',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_reference',
        'bank_name',
        'account_number',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function party()
    {
        if ($this->party_type === 'supplier') {
            return $this->belongsTo(Supplier::class, 'party_id');
        } elseif ($this->party_type === 'customer') {
            return $this->belongsTo(Customer::class, 'party_id');
        }
        return null;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}


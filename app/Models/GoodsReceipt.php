<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'supplier_id',
        'receipt_date',
        'reference_number',
        'delivered_by',
        'status',
        'notes',
        'user_id',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
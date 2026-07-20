<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'sale_date',
        'invoice_no',
        'vat_rate',
        'subtotal',
        'total_tax',
        'total_amount',
        'payment_type',
        'notes',
        'status',
        'user_id',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ProductSaleItem::class);
    }
}
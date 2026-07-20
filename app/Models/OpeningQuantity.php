<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpeningQuantity extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'lot_number',
        'expiry_date',
        'quantity',
        'package',
        'unit',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
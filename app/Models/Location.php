<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    const TYPE_STORE = 'store';
    const TYPE_SALE = 'sale';
    const TYPE_POINT_OF_USE = 'point_of_use';

    protected $fillable = [
        'name',
        'type',
    ];
}
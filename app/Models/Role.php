<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'superadmin',
        'manage_user',
        'manage_categories',
        'manage_products',
        'manage_locations',
        'manage_supplier',
        'manage_opening_quantity',
        'manage_goods_receipt',
        'manage_inventory_transfer',
        'manage_inventory_adjustment',
        'manage_patients',
        'manage_treatment_consumption',
        'manage_product_sales',
        'view_reports',
    ];
}
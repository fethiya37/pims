<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'category_id',
        'unit',
        'default_pack_size',
        'description',
        'status',
        'packaging_type',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function openingQuantities()
    {
        return $this->hasMany(OpeningQuantity::class, 'product_id');
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function locationSettings()
    {
        return $this->hasMany(ProductLocationSetting::class);
    }

    public function saleItems()
{
    return $this->hasMany(ProductSaleItem::class);
}

public function treatmentItems()
{
    return $this->hasMany(TreatmentConsumptionItem::class);
}

public function transferItems()
{
    return $this->hasMany(InventoryTransferItem::class);
}

public function receiptItems()
{
    return $this->hasMany(GoodsReceiptItem::class);
}

public function adjustments()
{
    return $this->hasMany(InventoryAdjustment::class);
}

public function transactions()
{
    return $this->hasMany(InventoryTransaction::class);
}
}
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSale;
use App\Models\InventoryTransfer;
use App\Models\GoodsReceipt;
use App\Models\TreatmentConsumption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductDeletionService
{
    public function deleteProduct(Product $product): void
    {
        DB::transaction(function () use ($product) {

            $productId = $product->id;

            /** @var Collection<int, int> $saleIds */
            $saleIds = DB::table('product_sale_items')
                ->where('product_id', $productId)
                ->pluck('product_sale_id')->unique()->values();

            /** @var Collection<int, int> $transferIds */
            $transferIds = DB::table('inventory_transfer_items')
                ->where('product_id', $productId)
                ->pluck('inventory_transfer_id')->unique()->values();

            /** @var Collection<int, int> $receiptIds */
            $receiptIds = DB::table('goods_receipt_items')
                ->where('product_id', $productId)
                ->pluck('goods_receipt_id')->unique()->values();

            /** @var Collection<int, int> $treatmentIds */
            $treatmentIds = DB::table('treatment_consumption_items')
                ->where('product_id', $productId)
                ->pluck('treatment_consumption_id')->unique()->values();

            $product->stockBatches()->delete();
            $product->openingQuantities()->delete();
            $product->locationSettings()->delete();

            DB::table('inventory_adjustments')->where('product_id', $productId)->delete();
            DB::table('inventory_transactions')->where('product_id', $productId)->delete();

            DB::table('product_sale_items')->where('product_id', $productId)->delete();
            DB::table('treatment_consumption_items')->where('product_id', $productId)->delete();
            DB::table('inventory_transfer_items')->where('product_id', $productId)->delete();
            DB::table('goods_receipt_items')->where('product_id', $productId)->delete();

            $product->delete();

            $this->recalcSales($saleIds);
            $this->recalcTransfers($transferIds);
            $this->recalcReceipts($receiptIds);
            $this->recalcTreatments($treatmentIds);
        });
    }

    /**
     * @param  Collection<int, int>  $saleIds
     */
    private function recalcSales(Collection $saleIds): void
    {
        foreach ($saleIds as $id) {
            /** @var ProductSale|null $sale */
            $sale = ProductSale::with('lineItems')->find($id);

            if (! $sale) {
                continue;
            }

            $items = $sale->lineItems;

            if ($items->isEmpty()) {
                $sale->update([
                    'subtotal'     => 0,
                    'total_tax'    => 0,
                    'total_amount' => 0,
                    'status'       => 'cancelled',
                ]);
                continue;
            }

            $subtotal = (float) $items->sum('line_total');
            $tax      = (float) $items->sum('total_tax');

            $sale->update([
                'subtotal'     => $subtotal,
                'total_tax'    => $tax,
                'total_amount' => $subtotal + $tax,
            ]);
        }
    }

    /**
     * @param  Collection<int, int>  $transferIds
     */
    private function recalcTransfers(Collection $transferIds): void
    {
        foreach ($transferIds as $id) {
            /** @var InventoryTransfer|null $transfer */
            $transfer = InventoryTransfer::with('lineItems')->find($id);

            if (! $transfer) {
                continue;
            }

            if ($transfer->lineItems->isEmpty()
                && ! in_array($transfer->status, ['received', 'cancelled'], true)) {
                $transfer->update(['status' => 'cancelled']);
            }
        }
    }

    /**
     * @param  Collection<int, int>  $receiptIds
     */
    private function recalcReceipts(Collection $receiptIds): void
    {
        foreach ($receiptIds as $id) {
            /** @var GoodsReceipt|null $receipt */
            $receipt = GoodsReceipt::with('lineItems')->find($id);

            if (! $receipt) {
                continue;
            }

            if ($receipt->lineItems->isEmpty() && $receipt->status !== 'cancelled') {
                $receipt->update(['status' => 'cancelled']);
            }
        }
    }

    /**
     * @param  Collection<int, int>  $treatmentIds
     */
    private function recalcTreatments(Collection $treatmentIds): void
    {
        foreach ($treatmentIds as $id) {
            /** @var TreatmentConsumption|null $treatment */
            $treatment = TreatmentConsumption::with('lineItems')->find($id);

            if (! $treatment) {
                continue;
            }

            if ($treatment->lineItems->isEmpty() && $treatment->status !== 'cancelled') {
                $treatment->update(['status' => 'cancelled']);
            }
        }
    }
}
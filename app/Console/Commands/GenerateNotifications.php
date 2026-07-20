<?php

namespace App\Console\Commands;

use App\Models\StockBatch;
use App\Models\Notification;
use App\Models\User;
use App\Models\ProductLocationSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateNotifications extends Command
{
    protected $signature = 'notifications:generate';
    protected $description = 'Generate expiry and low stock notifications for stock batches';

    public function handle()
    {
        $today = Carbon::today();
        $created = 0;

        // ====== EXPIRY NOTIFICATIONS ======
        $batches = StockBatch::with(['product', 'location'])
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->get();

        foreach ($batches as $batch) {
            $daysRemaining = $today->diffInDays($batch->expiry_date, false);
            if ($daysRemaining > 90) continue;

            $type = $daysRemaining < 0 ? 'expired' :
                    ($daysRemaining <= 30 ? 'urgent' :
                    ($daysRemaining <= 60 ? 'soon' : 'ok'));

            $existing = Notification::where('product_id', $batch->product_id)
                ->where('location_id', $batch->location_id)
                ->where('type', $type)
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->exists();

            if ($existing) continue;

            $productName = $batch->product->name ?? 'Unknown product';
            $locationName = $batch->location->name ?? 'Unknown location';
            $quantity = $batch->quantity;

            if ($daysRemaining < 0) {
                $msg = "{$productName} ({$locationName}) expired on {$batch->expiry_date->format('Y-m-d')}. Quantity: {$quantity}";
            } else {
                $msg = "{$productName} ({$locationName}) expires in {$daysRemaining} days ({$batch->expiry_date->format('Y-m-d')}). Quantity: {$quantity}";
            }

            $notification = Notification::create([
                'type' => $type,
                'message' => $msg,
                'reference' => "Product: {$productName}, Location: {$locationName}",
                'product_id' => $batch->product_id,
                'location_id' => $batch->location_id,
            ]);

            $this->attachToAllUsers($notification);
            $created++;
        }

        // ====== LOW STOCK NOTIFICATIONS ======
        $settings = ProductLocationSetting::with(['product', 'location'])->get();

        foreach ($settings as $setting) {
            $currentStock = StockBatch::where('product_id', $setting->product_id)
                ->where('location_id', $setting->location_id)
                ->sum('quantity');

            if ($currentStock <= $setting->reorder_quantity) {
                $existing = Notification::where('product_id', $setting->product_id)
                    ->where('location_id', $setting->location_id)
                    ->where('type', 'low_stock')
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->exists();

                if ($existing) continue;

                $product = $setting->product;
                $locationName = $setting->location->name ?? 'Unknown location';
                $productName = $product->name ?? 'Unknown product';

                $packagingType = $product->packaging_type ?? 'unit';
                $packSize = $product->default_pack_size ?? 1;
                $unit = $product->unit ?? 'unit';

                if ($packagingType === 'pack' && $packSize > 0) {
                    // Current stock breakdown
                    $currentPacks = floor($currentStock / $packSize);
                    $currentExtra = $currentStock % $packSize;
                    $currentDisplay = $currentPacks . ' pack' . ($currentPacks != 1 ? 's' : '');
                    if ($currentExtra > 0) {
                        $currentDisplay .= ' + ' . $currentExtra . ' ' . $unit;
                    }

                    // Reorder level breakdown
                    $reorderPacks = floor($setting->reorder_quantity / $packSize);
                    $reorderExtra = $setting->reorder_quantity % $packSize;
                    $reorderDisplay = $reorderPacks . ' pack' . ($reorderPacks != 1 ? 's' : '');
                    if ($reorderExtra > 0) {
                        $reorderDisplay .= ' + ' . $reorderExtra . ' ' . $unit;
                    }
                } else {
                    $currentDisplay = $currentStock . ' ' . $unit;
                    $reorderDisplay = $setting->reorder_quantity . ' ' . $unit;
                }

                $status = $currentStock == 0 ? 'Out of Stock' : 'Low Stock';
                $msg = "{$productName} ({$locationName}) is {$status}. Current: {$currentDisplay}, Reorder Level: {$reorderDisplay}";

                $notification = Notification::create([
                    'type' => 'low_stock',
                    'message' => $msg,
                    'reference' => "Product: {$productName}, Location: {$locationName}",
                    'product_id' => $setting->product_id,
                    'location_id' => $setting->location_id,
                ]);

                $this->attachToAllUsers($notification);
                $created++;
            }
        }

        $this->info("Created {$created} new notification(s).");
    }

    private function attachToAllUsers($notification)
    {
        $users = User::all();
        foreach ($users as $user) {
            $notification->users()->attach($user->id, ['read_at' => null]);
        }
    }
}
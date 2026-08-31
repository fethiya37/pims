<?php

namespace Database\Seeders;

use App\Models\StockBatch;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StockBatchSeeder extends Seeder
{
    public function run()
    {
        // Get all products and locations
        $products = Product::all();
        $locations = Location::all();

        if ($products->isEmpty() || $locations->isEmpty()) {
            $this->command->info('Please seed products and locations first.');
            return;
        }

        $this->command->info('Seeding 1000 stock batches...');
        $bar = $this->command->getOutput()->createProgressBar(1000);
        $bar->start();

        $stockBatches = [];

        for ($i = 0; $i < 1000; $i++) {
            $product = $products->random();
            $location = $locations->random();

            // Generate random quantity between 1 and 1000
            $quantity = rand(1, 1000);

            // Generate random lot number
            $lotNumber = 'LOT-' . strtoupper(substr(uniqid(), -8)) . '-' . rand(1000, 9999);

            // Generate random expiry date between 30 days ago and 365 days from now
            $expiryDate = Carbon::now()->addDays(rand(-30, 365));

            $stockBatches[] = [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'lot_number' => $lotNumber,
                'expiry_date' => $expiryDate,
                'quantity' => $quantity,
                'package' => rand(1, 10),
                'unit' => $product->unit ?? 'unit',
                'created_at' => Carbon::now()->subDays(rand(0, 365)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ];

            // Insert in chunks of 100 to avoid memory issues
            if (count($stockBatches) >= 100) {
                StockBatch::insert($stockBatches);
                $stockBatches = [];
                $bar->advance(100);
            }
        }

        // Insert remaining records
        if (!empty($stockBatches)) {
            StockBatch::insert($stockBatches);
            $bar->advance(count($stockBatches));
        }

        $bar->finish();
        $this->command->info("\n1000 stock batches seeded successfully!");
    }
}
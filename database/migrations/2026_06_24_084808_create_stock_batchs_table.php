<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('lot_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('package')->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'location_id', 'expiry_date', 'lot_number'], 'stock_batch_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
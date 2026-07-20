<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name')->index();
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->string('unit')->nullable();
            $table->enum('packaging_type', ['unit', 'pack'])->default('unit');
            $table->integer('default_pack_size')->default(1);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');

            $table->string('superadmin')->default('off');

            $table->string('manage_user')->default('off');
            $table->string('manage_categories')->default('off');
            $table->string('manage_products')->default('off');
            $table->string('manage_locations')->default('off');
            $table->string('manage_supplier')->default('off');

            $table->string('manage_opening_quantity')->default('off');
            $table->string('manage_goods_receipt')->default('off');
            $table->string('manage_inventory_transfer')->default('off');
            $table->string('manage_inventory_adjustment')->default('off');

            $table->string('manage_patients')->default('off');
            $table->string('manage_treatment_consumption')->default('off');

            $table->string('manage_product_sales')->default('off');

            $table->string('view_reports')->default('off');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->after('location_id');
            $table->unsignedBigInteger('requested_by')->nullable()->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('requested_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        Schema::create('inventory_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_adjustment_id');
            $table->unsignedBigInteger('product_id');
            $table->string('lot_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('adjustment_type', 10)->default('IN');
            $table->string('package')->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();

            $table->index('inventory_adjustment_id');
            $table->index('product_id');
        });

        $existing = DB::table('inventory_adjustments')->get();

        foreach ($existing as $row) {
            DB::table('inventory_adjustment_items')->insert([
                'inventory_adjustment_id' => $row->id,
                'product_id'              => $row->product_id,
                'lot_number'              => $row->lot_number,
                'expiry_date'             => $row->expiry_date,
                'quantity'                => $row->quantity,
                'adjustment_type'         => $row->adjustment_type,
                'package'                 => $row->package,
                'unit'                    => $row->unit,
                'created_at'              => $row->created_at,
                'updated_at'              => $row->updated_at,
            ]);

            DB::table('inventory_adjustments')
                ->where('id', $row->id)
                ->update([
                    'status'       => 'approved',
                    'requested_by' => $row->user_id,
                    'approved_by'  => $row->user_id,
                    'approved_at'  => $row->created_at,
                ]);
        }

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropColumn([
                'product_id',
                'lot_number',
                'expiry_date',
                'quantity',
                'adjustment_type',
                'package',
                'unit',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('adjustment_type', 10)->default('IN');
            $table->string('package')->nullable();
            $table->string('unit')->nullable();
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropColumn(['status', 'requested_by', 'approved_by', 'approved_at']);
        });

        Schema::dropIfExists('inventory_adjustment_items');
    }
};
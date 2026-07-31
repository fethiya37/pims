<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_sequences', function (Blueprint $table) {
            $table->id();
            $table->integer('last_number')->default(0);
            $table->timestamps();
        });

        DB::table('product_sequences')->insert(['last_number' => 0]);
    }

    public function down()
    {
        Schema::dropIfExists('product_sequences');
    }
};
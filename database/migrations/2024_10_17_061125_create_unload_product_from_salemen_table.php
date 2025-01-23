<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unload_product_from_salemen', function (Blueprint $table) {
            $table->id()->primary();
            $table->integer('salesman_id');
            $table->integer('product_id');
            $table->integer('quantity');
            $table->integer('total_quantity');
            $table->text('products');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unload_product_from_salemen');
    }
};

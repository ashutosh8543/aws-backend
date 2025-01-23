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
        // Schema::create('product_inventories', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('distributor_id')->nullable();
        //     $table->unsignedBigInteger('warehouse_id')->nullable();
        //     $table->unsignedBigInteger('product_id')->nullable();
        //     $table->integer('distributor_quantities')->nullable();
        //     $table->integer('warehouse_quantities')->nullable();
        //     $table->unsignedBigInteger('user_id');
        //     $table->string('country')->nullable();
        //     $table->timestamps();
        //     $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
        //     $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        //     $table->foreign('product_id')->references('main_product_id')->on('products')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_inventories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('gift_inventories', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('distributor_id')->nullable();
    //         $table->unsignedBigInteger('warehouse_id')->nullable();
    //         $table->unsignedBigInteger('gift_id')->nullable();
    //         $table->integer('distributor_quantities')->nullable();
    //         $table->integer('warehouse_quantities')->nullable();
    //         $table->unsignedBigInteger('user_id');
    //         $table->string('country')->nullable();
    //         $table->timestamps();
    //         $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
    //         $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('gift_id')->references('id')->on('gifts')->onDelete('cascade');
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_inventories');
    }
};

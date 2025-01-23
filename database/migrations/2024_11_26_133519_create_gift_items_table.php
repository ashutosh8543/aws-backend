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
    //     Schema::create('gift_items', function (Blueprint $table) {
    //         $table->id();
    //         $table->integer('sales_man_id');
    //         $table->integer('gift_id');
    //         $table->integer('customer_id');
    //         $table->integer('quantity');
    //         $table->text('gift_details');
    //         $table->softDeletes();
    //         $table->timestamps();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_items');
    }
};

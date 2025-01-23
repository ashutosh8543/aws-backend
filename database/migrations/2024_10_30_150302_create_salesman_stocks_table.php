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
        Schema::create('salesman_stocks', function (Blueprint $table) {
            $table->id()->primary;
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->integer('salesman_id');
            $table->integer('product_id');
            $table->integer('quantity');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesman_stocks');
    }
};

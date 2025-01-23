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
        Schema::create('credit_limits', function (Blueprint $table) {
            $table->id()->primary;
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->double('amount');
            $table->foreignId('order_id')->constrained('sales')->onDelete('cascade');
            $table->integer('salesman_id');
            $table->string('order_type');
            $table->string('type');
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_limits');
    }
};

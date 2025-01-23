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
    //     Schema::create('assign_customers_list', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignId('assign_customer_id')->constrained('assign_customers')->onDelete('cascade');
    //         $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
    //         $table->timestamp('assigned_date')->nullable();
    //         $table->softDeletes();
    //         $table->timestamps();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::dropIfExists('assign_customers_list');
    // }
};

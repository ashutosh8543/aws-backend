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
    //     Schema::table('assign_customers', function (Blueprint $table) {
    //         $table->unsignedBigInteger('distributor_id')->nullable()->after('assign_by');
    //         $table->unsignedBigInteger('warehouse_id')->nullable()->after('distributor_id');
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::table('assign_customers', function (Blueprint $table) {
    //         $table->dropColumn('distributor_id');
    //         $table->dropColumn('warehouse_id');
    //     });
    // }
};

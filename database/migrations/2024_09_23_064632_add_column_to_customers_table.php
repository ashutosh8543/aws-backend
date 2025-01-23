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
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('added_by')->nullabel();
            $table->integer('user_id')->nullabel();
            $table->integer('chanel_id')->nullabel();
            $table->integer("warehouse_id")->nullabel();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { 
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('added_by');
            $table->integer('user_id');
            $table->integer('chanel_id');
            $table->integer("warehouse_id");
            $table->softDeletes();
        });
    }
};

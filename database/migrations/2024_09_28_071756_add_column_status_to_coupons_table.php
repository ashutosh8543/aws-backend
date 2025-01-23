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
        Schema::table('coupon_codes', function (Blueprint $table) {
            $table->string('status');
            $table->string('cn_name');
            $table->string('bn_name');
            $table->string('description');
            $table->string('cn_description');
            $table->string('bn_description');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupon_codes', function (Blueprint $table){
            $table->string('status');
            $table->string('cn_name');
            $table->string('bn_name');
            $table->string('description');
            $table->string('cn_description');
            $table->string('bn_description');
            $table->softDeletes();
        });
    }
};

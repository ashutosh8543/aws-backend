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
        Schema::table('products', function (Blueprint $table) {
           $table->string("cn_name");
           $table->string("bn_name");
           $table->string("cn_description");
           $table->string("bn_description");
           $table->string("user_id");
           $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
           $table->string("cn_name");
           $table->string("bn_name");
           $table->string("cn_description");
           $table->string("bn_description");
           $table->string("user_id");
           $table->softDeletes();
        });
    }
};

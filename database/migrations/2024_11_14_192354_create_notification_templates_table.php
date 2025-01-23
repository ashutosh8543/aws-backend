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
    //     Schema::create('notification_templates', function (Blueprint $table){
    //         $table->id();
    //         $table->text('title')->nullable();
    //         $table->text('cn_title')->nullable();
    //         $table->text('bn_title')->nullable();
    //         $table->text('content')->nullable();
    //         $table->text('cn_content')->nullable();
    //         $table->text('bn_content')->nullable();
    //         $table->timestamps();
    //         $table->softDeletes();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};

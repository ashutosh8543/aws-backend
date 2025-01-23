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
        Schema::create('sub_areas', function (Blueprint $table) {
            $table->id();
            $table->string('sub_area_name');

            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');

            $table->softDeletes();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_areas');
    }
};

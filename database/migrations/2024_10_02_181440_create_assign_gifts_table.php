<!-- <?php

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
        Schema::create('assign_gifts', function (Blueprint $table) {
            $table->id();
            $table->integer('salesman_id');
            $table->integer('quantity');
            $table->integer('gift_id');
            $table->text('gifts')->array();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_gifts');
    }
};

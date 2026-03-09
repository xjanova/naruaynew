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
        Schema::create('level_commission_by_package', function (Blueprint $table) {
            $table->id();
            $table->integer('level');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('commission_reg_pck', 8, 4)->default(0);
            $table->decimal('commission_member_pck', 8, 4)->default(0);
            $table->timestamps();

            $table->unique(['level', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_commission_by_package');
    }
};

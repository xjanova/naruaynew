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
        Schema::create('sales_commission_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('level');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('commission', 16, 2)->default(0);
            $table->timestamps();

            $table->unique(['level', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_commission_configs');
    }
};

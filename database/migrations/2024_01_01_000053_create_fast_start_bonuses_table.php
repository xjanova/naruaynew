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
        Schema::create('fast_start_bonuses', function (Blueprint $table) {
            $table->id();
            $table->integer('referral_count')->default(0);
            $table->integer('days_count')->default(30);
            $table->decimal('bonus_amount', 16, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fast_start_bonuses');
    }
};

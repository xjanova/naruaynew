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
        Schema::create('binary_bonus_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_leg')->default(0);
            $table->integer('left_leg')->default(0);
            $table->integer('right_leg')->default(0);
            $table->string('calculation_type')->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('binary_bonus_histories');
    }
};

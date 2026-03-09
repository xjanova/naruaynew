<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Binary leg tracking for each user.
     * Maintains running totals and carry-forward values for left/right legs.
     */
    public function up(): void
    {
        Schema::create('leg_details', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->integer('total_left_count')->default(0);
            $table->integer('total_right_count')->default(0);
            $table->decimal('total_left_carry', 16, 2)->default(0);
            $table->decimal('total_right_carry', 16, 2)->default(0);
            $table->integer('total_active')->default(0);
            $table->integer('total_inactive')->default(0);
            $table->decimal('left_carry_forward', 16, 2)->default(0);
            $table->decimal('right_carry_forward', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leg_details');
    }
};

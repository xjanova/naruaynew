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
        Schema::create('binary_bonus_configs', function (Blueprint $table) {
            $table->id();
            $table->string('calculation_criteria')->default('pair_matching');
            $table->string('calculation_period')->default('daily');
            $table->string('commission_type')->default('percentage');
            $table->decimal('pair_commission', 16, 2)->default(0);
            $table->string('pair_type')->default('1:1');
            $table->decimal('pair_value', 16, 2)->default(0);
            $table->decimal('point_value', 16, 2)->default(0);
            $table->enum('carry_forward', ['yes', 'no'])->default('yes');
            $table->enum('flush_out', ['yes', 'no'])->default('no');
            $table->integer('flush_out_limit')->default(0);
            $table->string('flush_out_period')->nullable();
            $table->integer('locking_period')->default(0);
            $table->decimal('block_binary_pv', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('binary_bonus_configs');
    }
};

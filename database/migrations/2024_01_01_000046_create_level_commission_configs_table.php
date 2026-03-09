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
        Schema::create('level_commission_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('level');
            $table->decimal('percentage', 8, 4)->default(0);
            $table->decimal('donation_1', 8, 4)->default(0);
            $table->decimal('donation_2', 8, 4)->default(0);
            $table->decimal('donation_3', 8, 4)->default(0);
            $table->decimal('donation_4', 8, 4)->default(0);
            $table->timestamps();

            $table->unique('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_commission_configs');
    }
};

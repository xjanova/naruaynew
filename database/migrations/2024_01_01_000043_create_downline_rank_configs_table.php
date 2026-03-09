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
        Schema::create('downline_rank_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_id')->constrained('ranks')->cascadeOnDelete();
            $table->foreignId('required_rank_id')->constrained('ranks')->cascadeOnDelete();
            $table->integer('required_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downline_rank_configs');
    }
};

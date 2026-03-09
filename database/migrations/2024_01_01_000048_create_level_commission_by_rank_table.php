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
        Schema::create('level_commission_by_rank', function (Blueprint $table) {
            $table->id();
            $table->integer('level');
            $table->foreignId('rank_id')->constrained('ranks')->cascadeOnDelete();
            $table->decimal('commission', 8, 4)->default(0);
            $table->timestamps();

            $table->unique(['level', 'rank_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_commission_by_rank');
    }
};

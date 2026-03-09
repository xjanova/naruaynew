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
        Schema::create('rank_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_id')->constrained('ranks')->cascadeOnDelete();
            $table->string('config_key');
            $table->text('config_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rank_configs');
    }
};

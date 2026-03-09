<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Binary placement tree using the Closure Table pattern.
     * Stores all ancestor-descendant relationships for efficient tree queries.
     */
    public function up(): void
    {
        Schema::create('tree_paths', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor');
            $table->unsignedBigInteger('descendant');

            $table->primary(['ancestor', 'descendant']);
            $table->index('descendant');

            $table->foreign('ancestor')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('descendant')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_paths');
    }
};

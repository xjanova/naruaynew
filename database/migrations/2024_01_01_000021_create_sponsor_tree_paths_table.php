<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Sponsor upline tree using the Closure Table pattern.
     * Tracks the referral/sponsor hierarchy separately from the binary placement tree.
     */
    public function up(): void
    {
        Schema::create('sponsor_tree_paths', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor');
            $table->unsignedBigInteger('descendant');
            $table->unsignedInteger('depth')->default(0);

            $table->primary(['ancestor', 'descendant']);
            $table->index('descendant');
            $table->index('depth');

            $table->foreign('ancestor')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('descendant')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsor_tree_paths');
    }
};

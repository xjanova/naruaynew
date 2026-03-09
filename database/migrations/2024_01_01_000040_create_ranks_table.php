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
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();
            $table->integer('referral_count')->default(0);
            $table->decimal('personal_pv', 16, 2)->default(0);
            $table->decimal('group_pv', 16, 2)->default(0);
            $table->integer('downline_count')->default(0);
            $table->integer('team_member_count')->default(0);
            $table->decimal('rank_bonus', 16, 2)->default(0);
            $table->decimal('party_commission', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};

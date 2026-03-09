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
        Schema::create('epins', function (Blueprint $table) {
            $table->id();
            $table->string('pin_number')->unique();
            $table->decimal('amount', 16, 2);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owned_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['available', 'used', 'expired', 'blocked'])->default('available');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'owned_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epins');
    }
};

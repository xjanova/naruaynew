<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Orders for registration, repurchase, and upgrade transactions.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->index();
            $table->foreignId('user_id')->constrained();
            $table->string('order_number')->unique();
            $table->enum('type', ['registration', 'repurchase', 'upgrade']);
            $table->decimal('total_amount', 16, 2);
            $table->decimal('total_pv', 16, 2)->default(0);
            $table->decimal('total_bv', 16, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('order_status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

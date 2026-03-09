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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ewallet_type')->nullable();
            $table->decimal('amount', 16, 8);
            $table->decimal('purchase_wallet', 16, 8)->default(0);
            $table->string('amount_type');
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('transaction_fee', 16, 8)->default(0);
            $table->string('transaction_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('pending_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('amount_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('amount_type');
            $table->decimal('amount', 16, 8);
            $table->decimal('tds', 16, 8)->default(0);
            $table->decimal('service_charge', 16, 8)->default(0);
            $table->decimal('amount_payable', 16, 8);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('level')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'amount_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};

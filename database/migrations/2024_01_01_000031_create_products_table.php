<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * MLM packages and products with commission structures, ROI settings,
     * and subscription/validity configurations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->index();
            $table->string('name');
            $table->string('sku')->unique();
            $table->enum('type', ['registration', 'repurchase']);
            $table->decimal('price', 16, 2);
            $table->decimal('pv_value', 16, 2)->default(0)->comment('Pair value');
            $table->decimal('bv_value', 16, 2)->default(0)->comment('Business volume');
            $table->decimal('referral_commission', 16, 2)->default(0);
            $table->decimal('pair_price', 16, 2)->default(0);
            $table->decimal('roi_percent', 5, 2)->default(0);
            $table->integer('roi_days')->default(0);
            $table->integer('subscription_period')->default(0);
            $table->integer('product_validity_days')->default(0);
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('tree_icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

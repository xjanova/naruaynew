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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Address fields
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();

            // Banking fields (account_number and pan_number should be encrypted at application level)
            $table->string('bank_name')->nullable();
            $table->text('account_number')->nullable(); // Encrypted
            $table->string('account_holder_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->text('pan_number')->nullable(); // Encrypted

            // Payout preference
            $table->enum('payout_type', ['bank_transfer', 'ewallet'])->default('bank_transfer');

            // Social links
            $table->string('facebook')->nullable();
            $table->string('line_token')->nullable();
            $table->string('line_userid')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

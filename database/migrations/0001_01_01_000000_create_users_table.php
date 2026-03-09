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
        Schema::create('users', function (Blueprint $table) {
            // Primary key & legacy reference
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();

            // Authentication fields
            $table->string('username')->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('transaction_password')->nullable();
            $table->rememberToken();

            // Personal details
            $table->string('phone')->nullable();
            $table->string('id_card', 15)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('photo')->default('nophoto.jpg');

            // MLM tree relationships
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->unsignedBigInteger('placement_id')->nullable();
            $table->enum('position', ['L', 'R'])->nullable();
            $table->tinyInteger('leg_position')->nullable();

            // MLM data
            $table->unsignedInteger('product_id')->nullable();
            $table->date('product_validity')->nullable();
            $table->decimal('personal_pv', 16, 2)->default(0);
            $table->decimal('group_pv', 16, 2)->default(0);
            $table->unsignedInteger('rank_id')->default(1);
            $table->integer('user_level')->default(0);
            $table->integer('sponsor_level')->default(0);
            $table->enum('binary_leg', ['any', 'left', 'right'])->default('any');

            // Status fields
            $table->enum('active_status', ['active', 'inactive', 'blocked'])->default('active');
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('google_auth_enabled')->default(false);
            $table->string('google_auth_secret')->nullable();

            // Dates & meta
            $table->timestamp('join_date')->nullable();
            $table->string('register_by_using')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys (self-referencing)
            $table->foreign('sponsor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('placement_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

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
        Schema::create('import_id_maps', function (Blueprint $table) {
            $table->string('table_name');
            $table->unsignedBigInteger('legacy_id');
            $table->unsignedBigInteger('new_id');

            $table->primary(['table_name', 'legacy_id']);
            $table->index('new_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_id_maps');
    }
};

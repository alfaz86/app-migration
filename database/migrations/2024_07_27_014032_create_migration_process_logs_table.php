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
        Schema::create('migration_process_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('migration_process_id');
            $table->decimal('start_time', 15, 5);
            $table->decimal('end_time', 15, 5)->nullable();
            $table->unsignedBigInteger('total_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_process_logs');
    }
};

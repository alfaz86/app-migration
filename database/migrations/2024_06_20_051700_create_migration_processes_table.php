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
        Schema::create('migration_processes', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('http_method');
            $table->string('result_data');
            $table->string('database');
            $table->json('setup_connection');
            $table->string('schema');
            $table->string('scheduler');
            $table->time('time')->nullable();
            $table->string('duration');
            $table->string('status')->default('progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_processes');
    }
};

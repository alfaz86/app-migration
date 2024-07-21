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
        Schema::table('migration_processes', function (Blueprint $table) {
            $table->integer('duration_day_of_week')->nullable();
            $table->integer('duration_day_of_month')->nullable();
            $table->integer('duration_month')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('migration_processes', function (Blueprint $table) {
            $table->dropColumn('duration_day_of_week');
            $table->dropColumn('duration_day_of_month');
            $table->dropColumn('duration_month');
        });
    }
};

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
            $table->integer('loop')->default(0);
            $table->integer('total_page')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('migration_processes', function (Blueprint $table) {
            $table->dropColumn('loop');
            $table->dropColumn('total_page');
        });
    }
};

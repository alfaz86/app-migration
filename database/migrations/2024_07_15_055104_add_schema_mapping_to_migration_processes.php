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
            $table->json('schema_mapping')->nullable()->after('auth_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('migration_processes', function (Blueprint $table) {
            $table->dropColumn('schema_mapping');
        });
    }
};

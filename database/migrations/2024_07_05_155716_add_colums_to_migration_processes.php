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
            $table->string('schema')->nullable()->change();
            $table->string('table')->nullable()->after('schema');
            $table->string('collections')->after('table')->nullable();
            $table->string('auth_type')->default('none')->after('status');
            $table->json('auth_data')->after('auth_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('migration_processes', function (Blueprint $table) {
            $table->string('schema')->nullable(false)->change();
            $table->dropColumn('table');
            $table->dropColumn('collections');
            $table->dropColumn('auth_type');
            $table->dropColumn('auth_data');
        });
    }
};

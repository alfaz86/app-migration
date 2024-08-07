<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MigrationController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Main Features Router
Route::redirect('/', '/migration/list');
Route::prefix('/migration')->group(function () {
    Route::get('/', [MigrationController::class, 'index']);
    Route::post('/create', [MigrationController::class, 'createMigration'])->name('migration.create');
    Route::post('/process', [MigrationController::class, 'callMigrationProcess'])->name('migration.process');

    // migration list
    Route::get('/list', [MigrationController::class, 'listMigration'])->name('migration.list');
});

Route::prefix('/database')->group(function () {
    Route::get('/check-connection', [DatabaseController::class, 'checkConnection'])
        ->name('database.checkConnection');
    Route::get('/get-data', [DatabaseController::class, 'getData'])
        ->name('database.getData');
        
});

Route::middleware(['check.api.type'])->group(function () {
    Route::get('/api/checking', [APIController::class, 'checkingAPI'])
        ->name('api.checking');
    Route::get('/api/collection-key', [APIController::class, 'collectionKeyOfObjectData'])
        ->name('api.collectionKey');
});

// Minor Features Router
Route::prefix('/shortkey')->group(function () {
    Route::get('/artisan-config-cache', function () {
        Artisan::call('config:cache');
        return 'Artisan config:cache executed.';
    })->name('artisan.config.cache');
    Route::get('/artisan-config-clear', function () {
        Artisan::call('config:clear');
        return 'Artisan config:clear executed.';
    })->name('artisan.config.clear');
        
});

Route::get('/table/{table}', function ($table) {
    $columns = DB::select('SHOW COLUMNS FROM ' . $table);

    foreach ($columns as $column) {
        // The column name will be a property of the stdClass object
        $db = "Field";
        echo $column->$db . "\n";
    }
});
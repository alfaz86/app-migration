<?php

use App\Http\Controllers\HomeController;
use App\Models\DefaultModel;
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

Route::get('/', [HomeController::class, 'index']);

Route::get('/table/{table}', function ($table) {
    $columns = DB::select('SHOW COLUMNS FROM ' . $table);

    foreach ($columns as $column) {
        // The column name will be a property of the stdClass object
        $db = "Field";
        echo $column->$db . "\n";
    }
});
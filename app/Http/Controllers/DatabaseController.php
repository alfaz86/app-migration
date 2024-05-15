<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class DatabaseController extends Controller
{
    protected $request;

    public function __construct(DatabaseRequest $request)
    {
        Artisan::call('config:cache');
        $this->request = $request;
        $this->setDatabaseConfig();
    }

    protected function setDatabaseConfig()
    {
        // Ambil input dari request
        $config = $this->request;

        // Set konfigurasi database secara dinamis
        Config::set('database.connections.dynamic_mysql', [
            'driver' => $config['driver'],
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
    }

    public function getData()
    {
        try {
            // Mendapatkan data dari database dinamis
            $data = DB::connection('dynamic_mysql')->table('users')->get();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Could not retrieve data.',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

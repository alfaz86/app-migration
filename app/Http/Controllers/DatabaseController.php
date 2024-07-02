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
        // Artisan::call('config:cache');
        $this->request = $request;
        $this->setDatabaseConfig();
    }

    protected function setDatabaseConfig()
    {
        // Ambil input dari request
        $config = $this->request;

        // set db connection
        if ($config['driver'] == 'pgsql') {
            $dynamic_db = 'dynamic_pgsql';
        } elseif ($config['driver'] == 'mongodb') {
            $dynamic_db = 'dynamic_mongodb';
        } else {
            $dynamic_db = 'dynamic_mysql';
        }

        // Set konfigurasi database secara dinamis
        Config::set("database.connections.$dynamic_db", [
            'driver' => $config['driver'],
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password']
        ]);

        // // Set konfigurasi database secara dinamis
        // Config::set("database.connections.dynamic_mysql", $config);

        // // Reload konfigurasi untuk memastikan koneksi diterapkan
        // Artisan::call('config:cache');

        // // Mengembalikan response dengan konfigurasi
        // return response()->json(Config::get("database.connections.dynamic_mysql"));
    }

    public function checkConnection(Request $request)
    {
        try {
            // Mencoba koneksi ke database dinamis
            // set db connection
            if ($request['driver'] == 'pgsql') {
                $dynamic_db = 'dynamic_pgsql';
            } elseif ($request['driver'] == 'mongodb') {
                $dynamic_db = 'dynamic_mongodb';
            } else {
                $dynamic_db = 'dynamic_mysql';
            }
            DB::connection($dynamic_db)->getPdo();
            return response()->json([
                'message' => 'Connection to database is successful.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Could not connect to database.',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getData()
    {
        try {
            // Mendapatkan data dari database dinamis
            $data = DB::connection('dynamic_mysql')->table('users')->get();
            // Menampilkan data dari database dinamis
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Could not retrieve data.',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

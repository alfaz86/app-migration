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
        $dynamic_db = 'dynamic_' . $config['driver'];

        // Set konfigurasi database secara dinamis
        Config::set("database.connections.$dynamic_db", [
            'driver' => $config['driver'],
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'options'  => [
                'database' => $config['authSourceDatabase'] ?? '',
            ],
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
            $dynamic_db = 'dynamic_' . $request['driver'];
            $connection = DB::connection($dynamic_db);
            $collections = [];

            if ($request['driver'] === 'mysql') {
                // Untuk MySQL
                $tables = $connection->select('SHOW TABLES');
                foreach ($tables as $table) {
                    $collections[] = array_values((array)$table)[0];
                }
            } elseif ($request['driver'] === 'pgsql') {
                // Untuk PostgreSQL
                $tables = $connection->select("SELECT tablename FROM pg_tables WHERE schemaname='public'");
                foreach ($tables as $table) {
                    $collections[] = $table->tablename;
                }
            } elseif ($request['driver'] === 'mongodb') {
                $list = $connection->listCollections();
                foreach ($list as $collection) {
                    $collections[] = $collection->getName();
                }
            } else {
                return response()->json([
                    'message' => 'Unsupported database driver.',
                    'data' => []
                ], 400); // Bad Request
            }

            return response()->json([
                'message' => 'Connection to database is successful.',
                'data' => $collections
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

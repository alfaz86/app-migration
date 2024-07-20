<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatabaseRequest;
use App\Services\DatabaseService;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    protected $databaseService;

    public function __construct()
    {
        $this->databaseService = new DatabaseService();
    }

    protected function setDatabaseConfig(DatabaseRequest $databaseRequest)
    {
        $this->databaseService->setDatabaseConfig($databaseRequest);
    }

    public function checkConnection(DatabaseRequest $databaseRequest)
    {
        try {
            $this->setDatabaseConfig($databaseRequest);
            $data = $this->databaseService->checkConnection($databaseRequest);

            return response()->json([
                'message' => 'Connection to database is successful.',
                'data' => $data
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

<?php

namespace App\Http\Controllers;

use App\Models\MigrationProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    public function index()
    {
        return view('migration');
    }

    public function listMigration()
    {
        $migrations = MigrationProcess::paginate(25);
        return view('migration-list', compact('migrations'));
    }

    public function createMigration(Request $request)
    {
        $settings = $request->all();
        // save settings to database or session
        // dd($settings);
        try {
            // create setup json for variable $setup_connection
            // data from reqeust: driver, host, port, database, username, password
            // and save to field setup_connection
            $setup_connection = [
                'driver' => $settings['driver'],
                'host' => $settings['host'],
                'port' => $settings['port'],
                'database' => $settings['database'],
                'username' => $settings['username'],
                'password' => $settings['password'],
                'options'  => [
                    'database' => $settings['authSourceDatabase'] ?? '',
                ],
            ];

            DB::transaction(function () use ($settings, $setup_connection, &$migration) {
                // Save settings to database
                $migration = new MigrationProcess();
                $migration->url = $settings['url'];
                $migration->http_method = $settings['http_method'];
                $migration->result_data = $settings['result_data'];
                $migration->database = $settings['database'];
                $migration->setup_connection = json_encode($setup_connection);
                $migration->schema = $settings['schema'];
                $migration->table = $settings['table'];
                $migration->collections = $settings['collections'];
                $migration->scheduler = $settings['scheduler'];
                $migration->time = $settings['time'];
                $migration->duration = $settings['duration'];
                $migration->status = 'progress';
                $migration->auth_type = $settings['auth_type'];
                $migration->auth_data = $settings['auth_data'];
                $migration->save();
            });
        } catch (\Throwable $th) {
            throw $th;
        }

        return response()->json([
            'message' => 'Migration initiated successfully',
            'id' => $migration->id
        ]);
    }

    public function updateProcess(MigrationProcess $migrationProcess, string $status)
    {
        // Update migration process
        $migrationProcess->status = $status;
        $migrationProcess->save();
        // Return response
        return response()->json(['message' => 'Migration process updated successfully']);
    }

    public function callMigrationProcess(Request $request)
    {
        try {
            Artisan::call('app:migrate-process', ['migrationProcessID' => $request->id]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}

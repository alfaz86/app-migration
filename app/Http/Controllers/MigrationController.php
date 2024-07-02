<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMigration;
use App\Models\MigrationProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function setMigration(Request $request)
    {
        // Validate and save migration settings
        $settings = $request->all();
        // Save settings to database or session
        // Return response
        return response()->json(['message' => 'Settings saved successfully']);
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
                $migration->scheduler = $settings['scheduler'];
                $migration->time = $settings['time'];
                $migration->duration = $settings['duration'];
                $migration->status = 'progress';
                $migration->save();
            });
        } catch (\Throwable $th) {
            throw $th;
        }

        return response()->json(['message' => 'Migration initiated successfully']);
    }

}

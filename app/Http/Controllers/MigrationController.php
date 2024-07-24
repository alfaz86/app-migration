<?php

namespace App\Http\Controllers;

use App\Models\MigrationProcess;
use App\Models\NonRelationalModel;
use App\Models\RelationalModel;
use App\Services\SchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    protected $schemaService;

    public function __construct()
    {
        $this->schemaService = new SchemaService();
    }
    
    public function index()
    {
        $dataTypes = [
            'mysql' => RelationalModel::MYSQL_DATA_TYPES,
            'pgsql' => RelationalModel::POSTGRESQL_DATA_TYPES,
            'mongodb' => NonRelationalModel::MONGODB_DATA_TYPES
        ];
        return view('migration', compact('dataTypes'));
    }

    public function listMigration()
    {
        $migrations = MigrationProcess::orderBy('created_at', 'desc')->paginate(25);
        return view('migration-list', compact('migrations'));
    }

    public function createMigration(Request $request)
    {
        $settings = $request->all();
        $settings['schema'] = $this->schemaService->generateSchema($request->all());
        $settings['schema_mapping'] = $this->schemaService->generateSchemaMapping($request->all());
        // save settings to database or session
        // dd($settings['schema_mapping']);
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
                $migration->schema = $settings['schema'] ?? null;
                $migration->schema_mapping = $settings['schema_mapping'] ?? null;
                $migration->table = $settings['table'];
                $migration->collections = $settings['collections'];
                $migration->scheduler = $settings['scheduler'];
                $migration->time = $settings['time'];
                $migration->duration = $settings['duration'];
                $migration->status = $settings['scheduler'] == 'off' ? 'progress' : 'waiting for schedule';
                $migration->auth_type = $settings['auth_type'];
                $migration->auth_data = $settings['auth_data'];
                $migration->duration_day_of_week = $settings['duration_day_of_week'] ?? null;
                $migration->duration_day_of_month = $settings['duration_day_of_month'] ?? null;
                $migration->duration_month = $settings['duration_month'] ?? null;
                $migration->loop = $settings['loop'];
                $migration->total_page = $settings['total_page'];
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
            $migrationProcess = MigrationProcess::find($request->id);
            if ($migrationProcess->scheduler === 'off') {
                Artisan::call('app:migrate-process', ['migrationProcessID' => $request->id]);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\MigrationProcess;
use App\Services\APIService;
use App\Services\DatabaseService;
use App\Traits\CollectionEnpoint;
use App\Traits\LogProcessMigration;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMigration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CollectionEnpoint, LogProcessMigration;

    protected $migration;
    protected $url;
    protected $totalRequest;
    protected $migrationProcessID;

    public function __construct(MigrationProcess $migration, string $url, int $totalRequest)
    {
        $this->migration = $migration;
        $this->url = $url;
        $this->totalRequest = $totalRequest;
        $this->migrationProcessID = $migration->id;
    }

    public function handle(): void
    {
        $apiService = new APIService();

        $databaseRequest = json_decode($this->migration['setup_connection'], true);
        $driver = $databaseRequest['driver'];
        if ($driver === 'mongodb') {
            $databaseRequest['authSourceDatabase'] = $databaseRequest['options']['database'] ?? '';
        }
        $databaseService = new DatabaseService();
        $databaseService->setDatabaseConfig($databaseRequest);

        $startTime = microtime(true);

        $response = $apiService->sendAPI($this->migration, $this->url);

        if ($response->successful()) {
            $dynamic_db = 'dynamic_' . $driver;
            $connection = DB::connection($dynamic_db);

            // Buat tabel jika belum ada
            if ($driver == 'mongodb') {
                $connection->createCollection($this->migration['collections']);
            } else {
                $connection->statement($this->migration['schema']);
            }

            $data = $response->json();
            $data = $this->setObjectData($this->migration['result_data'], $data);
            $count = 0;

            foreach ($data as $item) {
                $fields = json_decode($this->migration['schema_mapping'], true);
                $identityTable = [];
                $record = [];
                foreach ($fields as $key => $value) {
                    if ($key === 'id' || preg_match('/_id$/', $key)) {
                        $identityTable[$key] = $this->getValueByPath($item, $value);
                    }
                    $getValueByPath = $this->getValueByPath($item, $value);
                    if (is_array($getValueByPath)) {
                        if ($driver == 'mongodb') {
                            $record[$key] = (object) $getValueByPath;
                        } else {
                            $record[$key] = json_encode($getValueByPath);
                        }
                    } else {
                        $record[$key] = $this->getValueByPath($item, $value);
                    }
                }

                // Check driver and insert data accordingly
                if ($driver == 'mongodb') {
                    DB::transaction(function () use ($connection, $record, &$count) {
                        $success = $connection->collection($this->migration['collections'])->insert($record);

                        if ($success) {
                            $count += 1;
                        }
                    });
                } else {
                    DB::transaction(function () use ($connection, $identityTable, $record, &$count) {
                        if (count($identityTable) > 0) {
                            $success = $connection->table($this->migration['table'])->updateOrInsert($identityTable, $record);
                        } else {
                            $success = $connection->table($this->migration['table'])->insert($record);
                        }

                        if ($success) {
                            $count += 1;
                        }
                    });
                }
            }

            $endTime = microtime(true);

            $this->logSuccessProcessMigration($driver, $startTime, $endTime, $count);
        } else {
            Log::error('Error: ' . $response->status() . ' ' . $response->body());
        }
        
        dispatch(new CheckAllJobsDone($this->migrationProcessID, $this->totalRequest));
    }

    public function failed(Exception $exception)
    {
        $migrationProcess = MigrationProcess::find($this->migrationProcessID);
        $migrationProcess->status = 'failed';
        $migrationProcess->save();
    }
}

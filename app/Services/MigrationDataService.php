<?php

namespace App\Services;

use App\Models\MigrationProcess;
use App\Traits\CollectionEnpoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationDataService
{
    use CollectionEnpoint;
    
    public function migrateData(MigrationProcess $migration, $url, $totalRequest): int
    {
        $apiService = new APIService();

        $databaseRequest = json_decode($migration['setup_connection'], true);
        $driver = $databaseRequest['driver'];
        if ($driver === 'mongodb') {
            $databaseRequest['authSourceDatabase'] = $databaseRequest['options']['database'] ?? '';
        }
        $databaseService = new DatabaseService();
        $databaseService->setDatabaseConfig($databaseRequest);

        $response = $apiService->sendAPI($migration, $url);

        if ($response->successful()) {
            $dynamic_db = 'dynamic_' . $driver;
            $connection = DB::connection($dynamic_db);

            // Buat tabel jika belum ada
            if ($driver == 'mongodb') {
                $connection->createCollection($migration['collections']);
            } else {
                $connection->statement($migration['schema']);
            }

            $data = $response->json();
            $data = $this->setObjectData($migration['result_data'], $data);
            $count = 0;

            foreach ($data as $item) {
                $fields = json_decode($migration['schema_mapping'], true);
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
                    DB::transaction(function () use ($migration, $connection, $record, &$count) {
                        $success = $connection->collection($migration['collections'])->insert($record);

                        if ($success) {
                            $count += 1;
                        }
                    });
                } else {
                    DB::transaction(function () use ($migration, $connection, $identityTable, $record, &$count) {
                        if (count($identityTable) > 0) {
                            $success = $connection->table($migration['table'])->updateOrInsert($identityTable, $record);
                        } else {
                            $success = $connection->table($migration['table'])->insert($record);
                        }

                        if ($success) {
                            $count += 1;
                        }
                    });
                }
            }
            return $count;
        } else {
            Log::error('Error: ' . $response->status() . ' ' . $response->body());
            return 0;
        }
    }
}

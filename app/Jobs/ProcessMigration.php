<?php

namespace App\Jobs;

use App\Models\MigrationProcess;
use App\Traits\CollectionEnpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessMigration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CollectionEnpoint;

    protected $migration;
    protected $driver;
    protected $url;

    public function __construct(MigrationProcess $migration, string $driver, string $url)
    {
        $this->migration = $migration;
        $this->driver = $driver;
        $this->url = $url;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $httpMethod = $this->migration['http_method'];
        $resultData = $this->migration['result_data'];
        $authType = $this->migration['auth_type'];
        $authData = json_decode($this->migration['auth_data'], true);

        // Mengatur klien HTTP dengan opsi autentikasi
        $client = Http::withOptions(['verify' => false]);

        switch ($authType) {
            case 'basic':
                $client = $client->withBasicAuth($authData['username'], $authData['password']);
                break;
            case 'bearer':
                $client = $client->withToken($authData['token']);
                break;
            case 'apikey':
                $client = $client->withHeaders([$authData['key'] => $authData['value']]);
                break;
            case 'oauth2':
                // Handle OAuth 2.0 specific logic here
                break;
            case 'none':
            default:
                // No additional authentication
                break;
        }

        $response = $client->send($httpMethod, $this->url);

        if ($response->successful()) {
            $dynamic_db = 'dynamic_' . $this->driver;
            $connection = DB::connection($dynamic_db);

            // Buat tabel jika belum ada
            if ($this->driver == 'mongodb') {
                $connection->createCollection($this->migration['collections']);
            } else {
                $connection->statement($this->migration['schema']);
            }

            $data = $response->json();
            $data = $this->setObjectData($resultData, $data);
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
                        if ($this->driver == 'mongodb') {
                            $record[$key] = (object)$getValueByPath;
                        } else {
                            $record[$key] = json_encode($getValueByPath);
                        }
                    } else {
                        $record[$key] = $this->getValueByPath($item, $value);
                    }
                }

                // Check driver and insert data accordingly
                if ($this->driver == 'mongodb') {
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
            $executionTime = $endTime - $startTime;
            
            $startDateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', $startTime));
            $startDateTimeString = $startDateTime->format('Y-m-d H:i:s.u');
            $endDateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', $endTime));
            $endDateTimeString = $endDateTime->format('Y-m-d H:i:s.u');

            Log::info('Driver: ' . $this->driver . PHP_EOL .
                'Migrasi ke database: ' . $this->migration['database'] . '.' . ($this->migration['table'] ?? $this->migration['collections']) . PHP_EOL .
                'Start: ' . $startDateTimeString . PHP_EOL .
                'End: ' . $endDateTimeString . PHP_EOL .
                'Waktu eksekusi migrasi: ' . $executionTime . ' detik' . PHP_EOL .
                'Jumlah data yang berhasil diinput: ' . $count);
        } else {
            Log::error('Error: ' . $response->status() . ' ' . $response->body());
        }
    }
}

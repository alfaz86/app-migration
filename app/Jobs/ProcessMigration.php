<?php

namespace App\Jobs;

use App\Models\MigrationProcess;
use GuzzleHttp\Exception\InvalidArgumentException;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $migration;
    protected $driver;

    public function __construct(MigrationProcess $migration, $driver)
    {
        $this->migration = $migration;
        $this->driver = $driver;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $url = $this->migration['url'];
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

        $response = $client->send($httpMethod, $url);

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
            $data = $resultData === 'current' ? $data : $data[$resultData];
            $count = 0;

            foreach ($data as $item) {
                $record = [];
                foreach ($item as $key => $value) {
                    if (in_array($key, ['attributes', 'links'])) {
                        $record[$key] = json_encode($value);
                    } else {
                        $record[$key] = $value;
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
                    DB::transaction(function () use ($connection, $record, &$count) {
                        $success = $connection->table($this->migration['table'])->updateOrInsert([
                            'id' => $record['id']
                        ], $record);

                        if ($success) {
                            $count += 1;
                        }
                    });
                }
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            Log::info('Migrasi ke databse: ' . $this->driver . PHP_EOL .
                'Waktu eksekusi migrasi: ' . $executionTime . ' detik' . PHP_EOL .
                'Jumlah data yang berhasil diinput: ' . $count);
        } else {
            Log::error('Error: ' . $response->status() . ' ' . $response->body());
        }
    }
}

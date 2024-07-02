<?php

namespace App\Jobs;

use App\Http\Controllers\DatabaseController;
use App\Http\Requests\DatabaseRequest;
use App\Models\MigrationProcess;
use GuzzleHttp\Exception\InvalidArgumentException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessMigration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected MigrationProcess $migration
    )
    {
        $this->migration = $migration;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $url = $this->migration['url'];
        $httpMethod = $this->migration['http_method'];
        $resultData = $this->migration['result_data'];

        switch ($httpMethod) {
            case 'GET':
                $response = Http::get($url);
                break;
            case 'POST':
                $response = Http::post($url, []);
                break;
            case 'PUT':
                $response = Http::put($url, []);
                break;
            case `DELETE`:
                $response = Http::delete($url);
                break;
            default:
                throw new InvalidArgumentException("Invalid HTTP method: $httpMethod");
        }

        if ($response->successful()) {
            $databaseRequest = new DatabaseRequest(json_decode($this->migration['setup_connection'], true));

            // set db connection
            $dynamic_db = 'dynamic_' . $databaseRequest['driver'];

            // Set konfigurasi database secara dinamis
            Config::set("database.connections.$dynamic_db", [
                'driver' => $databaseRequest['driver'],
                'host' => $databaseRequest['host'],
                'port' => $databaseRequest['port'],
                'database' => $databaseRequest['database'],
                'username' => $databaseRequest['username'],
                'password' => $databaseRequest['password']
            ]);

            $connection = DB::connection($dynamic_db);
            
            // Test the connection
            try {
                $connection->getPdo();
            } catch (\Exception $e) {
                // Handle connection errors
                Log::error('Failed to connect to database: ' . $e->getMessage());
                return;
            }

            $data = $response->json();
            $data = $resultData === 'current' ? $data : $data[$resultData];

            foreach ($data as $item) {
                $record = [];
                foreach ($item as $key => $value) {
                    $record[$key] = $value;
                }

                // Check driver and insert data accordingly
                if ($databaseRequest['driver'] == 'mongodb') {
                    DB::connection($dynamic_db)->collection('posts')->updateOrInsert([
                        'userId' => $record['userId'],
                        'id' => $record['id']
                    ], $record);
                } else {
                    DB::connection($dynamic_db)->table('posts')->updateOrInsert([
                        'userId' => $record['userId'],
                        'id' => $record['id']
                    ], $record);
                }
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        Log::info('Waktu eksekusi migrasi: ' . $executionTime . ' detik');
    }
}

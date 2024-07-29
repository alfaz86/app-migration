<?php

namespace App\Console\Commands;

use App\Models\MigrationProcess;
use App\Services\MigrationDataService;
use App\Traits\LogProcessMigration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExecuteMigrationCommand extends Command
{
    use LogProcessMigration;
    
    protected $signature = 'migrate:execute {migrationProcessID}';
    protected $description = 'Execute migration process with given ID';


    public function handle()
    {
        try {
            $id = (int) $this->argument('migrationProcessID');
            $migration = MigrationProcess::find($id);

            if (!$migration) {
                $this->error("Migration process with ID $id not found.");
                return;
            }

            $loop = $migration->loop;
            $total_page = $migration->total_page;
            $total_request = $loop ? $total_page : 1;
            $databaseRequest = json_decode($migration['setup_connection'], true);
            $driver = $databaseRequest['driver'];

            $startTime = microtime(true);
            $count = 0;

            for ($key = 1; $key <= $total_request; $key++) {
                $paging = '';
                if (strpos($migration->url, '?') !== false) {
                    $paging = '&page=' . $key;
                } else {
                    $paging = '?page=' . $key;
                }
                $url = $migration->url . $paging;
                $migrationDataService = new MigrationDataService();
                try {
                    $totalMigrated = $migrationDataService->migrateData($migration, $url, $total_request);
                    $count += $totalMigrated;
                } catch (\Exception $e) {
                    Log::error("Error on request #{$key} with URL {$url}: " . $e->getMessage());
                    continue;
                }
            }

            $endTime = microtime(true);

            $this->logSuccessProcessMigration($migration, $driver, $startTime, $endTime, $count);
            MigrationProcess::where('id', $migration->id)->update(['status' => 'completed']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

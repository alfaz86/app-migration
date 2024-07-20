<?php

namespace App\Console\Commands;

use App\Http\Controllers\MigrationController;
use App\Jobs\ProcessMigration;
use App\Models\MigrationProcess;
use App\Services\DatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use PDO;

class MigrateProcess extends Command
{
    protected $signature = 'app:migrate-process {migrationProcessID}';
    protected $description = 'Migrate process';

    public function handle()
    {
        try {
            $id = (int) $this->argument('migrationProcessID');
            $migration = MigrationProcess::find($id);
            $loop = false;
            $totalLoop = 10;
            $total_request = $loop ? $totalLoop : 1;

            if (!$migration) {
                $this->error("Migration process with ID $id not found.");
                return;
            }

            $databaseRequest = json_decode($migration['setup_connection'], true);
            $driver = $databaseRequest['driver'];
            if ($driver === 'mongodb') {
                $databaseRequest['authSourceDatabase'] = $databaseRequest['options']['database'] ?? '';
            }
            $databaseService = new DatabaseService();
            $databaseService->setDatabaseConfig($databaseRequest);
            
            // if ($driver === 'pgsql') {
            //     $pdo = new PDO("pgsql:host=$databaseRequest[host];dbname=$databaseRequest[database];port=$databaseRequest[port]", "$databaseRequest[username]", "$databaseRequest[password]");
            //     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // }

            // Dispatch job dengan konfigurasi yang sudah diterapkan
            for ($key = 1; $key <= $total_request; $key++) {
                $paging = '';
                if ($loop) {
                    $paging = '?page=' . $key;
                }
                $url = $migration->url . $paging;
                dispatch(new ProcessMigration($migration, $driver, $url));
            }

            // update status of migration process
            $migrationContoller = new MigrationController();
            $migrationContoller->updateProcess($migration, 'completed');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

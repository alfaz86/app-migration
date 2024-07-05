<?php

namespace App\Console\Commands;

use App\Http\Controllers\MigrationController;
use App\Jobs\ProcessMigration;
use App\Models\MigrationProcess;
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

            if (!$migration) {
                $this->error("Migration process with ID $id not found.");
                return;
            }

            $databaseRequest = json_decode($migration['setup_connection'], true);
            $driver = $databaseRequest['driver'];
            $dynamic_db = 'dynamic_' . $driver;

            // Set konfigurasi database secara dinamis
            Config::set("database.connections.$dynamic_db", [
                'driver' => $databaseRequest['driver'],
                'host' => $databaseRequest['host'],
                'port' => $databaseRequest['port'],
                'database' => $databaseRequest['database'],
                'username' => $databaseRequest['username'],
                'password' => $databaseRequest['password'],
                'options' => [
                    'database' => $databaseRequest['database']
                ],
            ]);

            // Memastikan konfigurasi terbaru diterapkan
            // Artisan::call('config:cache');
            
            if ($driver === 'pgsql') {
                $pdo = new PDO("pgsql:host=$databaseRequest[host];dbname=$databaseRequest[database];port=$databaseRequest[port]", "$databaseRequest[username]", "$databaseRequest[password]");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            // Dispatch job dengan konfigurasi yang sudah diterapkan
            dispatch(new ProcessMigration($migration, $driver));

            // update status of migration process
            $migrationContoller = new MigrationController();
            $migrationContoller->updateProcess($migration, 'completed');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

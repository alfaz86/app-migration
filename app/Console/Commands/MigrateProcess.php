<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMigration;
use App\Models\MigrationProcess;
use Illuminate\Console\Command;

class MigrateProcess extends Command
{
    protected $signature = 'app:migrate-process {migrationProcessID}';

    protected $description = 'Migrate process';

    public function handle()
    {
        try {
            $id = (int) $this->argument('migrationProcessID');
            $migration = MigrationProcess::find($id);
            ProcessMigration::dispatch($migration);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMigration;
use App\Models\MigrationProcess;
use Illuminate\Console\Command;

class DispatchMigrationJobsCommand extends Command
{
    protected $signature = 'migrate:dispatch {migrationProcessID}';
    protected $description = 'Dispatch migration jobs for process with given ID';

    public function handle()
    {
        try {
            $id = (int) $this->argument('migrationProcessID');
            $migration = MigrationProcess::find($id);
            $loop = $migration->loop;
            $total_page = $migration->total_page;
            $total_request = $loop ? $total_page : 1;

            if (!$migration) {
                $this->error("Migration process with ID $id not found.");
                return;
            }

            // Dispatch job dengan konfigurasi yang sudah diterapkan
            for ($key = 1; $key <= $total_request; $key++) {
                $paging = '';
                if ($total_page > 1) {
                    $paging = '?page=' . $key;
                }
                $url = $migration->url . $paging;
                ProcessMigration::dispatch($migration, $url, $total_request);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

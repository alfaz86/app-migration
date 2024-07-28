<?php

namespace App\Jobs;

use App\Models\MigrationProcess;
use App\Models\MigrationProcessLog;
use App\Traits\LogProcessMigration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAllJobsDone implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogProcessMigration;

    protected $migrationProcessID;
    protected $totalRequest;

    public function __construct($migrationProcessID, $totalRequest)
    {
        $this->migrationProcessID = $migrationProcessID;
        $this->totalRequest = $totalRequest;
    }

    public function handle()
    {
        $totalJobs = $this->totalRequest;
        $completedJobs = $this->countCompletedJobs();

        if ($completedJobs >= $totalJobs) {
            $migration = MigrationProcess::where('id', $this->migrationProcessID)->first();
            $setupConnection = json_decode($migration['setup_connection'], true);
            $driver = $setupConnection['driver'];
            $process = $this->getMigrationProcessTimeInterval($this->migrationProcessID);

            $this->logSuccessProcessMigration(
                $migration,
                $driver,
                $process['earliest_start_time'],
                $process['latest_end_time'],
                $process['total_data']
            );

            $migration->update(['status' => 'completed']);
        }
    }

    protected function countCompletedJobs()
    {
        $migrationProcessLog = MigrationProcessLog::where('migration_process_id', $this->migrationProcessID);
        return $migrationProcessLog->count();
    }

    /**
     * Menghitung waktu paling awal, paling akhir, dan interval waktu.
     *
     * @param int $migrationProcessId
     * @return array
     */
    protected function getMigrationProcessTimeInterval(int $migrationProcessId)
    {
        // Mengambil data dari model MigrationProcessLog
        $logs = MigrationProcessLog::where('migration_process_id', $migrationProcessId)->get(['start_time', 'end_time', 'total_data']);

        if ($logs->isEmpty()) {
            return [
                'error' => 'No logs found for the given migration process ID.'
            ];
        }

        // Menginisialisasi variabel untuk menyimpan waktu paling awal dan paling akhir
        $earliestStartTime = null;
        $latestEndTime = null;

        // Loop melalui setiap log untuk menentukan waktu paling awal dan paling akhir
        foreach ($logs as $log) {
            $startTime = (float) $log->start_time;
            $endTime = (float) $log->end_time;

            if (is_null($earliestStartTime) || $startTime < $earliestStartTime) {
                $earliestStartTime = $startTime;
            }

            if (is_null($latestEndTime) || $endTime > $latestEndTime) {
                $latestEndTime = $endTime;
            }
        }

        // Menghitung interval waktu
        $interval = $latestEndTime - $earliestStartTime;

        // Mengembalikan hasil dalam format array
        return [
            'earliest_start_time' => $earliestStartTime,
            'latest_end_time' => $latestEndTime,
            'interval' => $interval,
            'total_data' => $logs->sum('total_data'),
        ];
    }
}

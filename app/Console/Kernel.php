<?php

namespace App\Console;

use App\Models\MigrationProcess;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $migrationProcesses = MigrationProcess::where('scheduler', 'on')
            ->where('status', '!=', 'completed')
            ->get();

        if (count($migrationProcesses) > 0) {
            foreach ($migrationProcesses as $migrationProcess) {
                $timeString = substr($migrationProcess->time, 0, 5);
                $command = "migrate:dispatch {$migrationProcess->id}";

                if ($migrationProcess->duration == 'minute') {
                    $schedule->command($command)
                        ->everyMinute()
                        ->timezone('Asia/Jakarta');

                } elseif ($migrationProcess->duration == 'hour') {
                    $schedule->command($command)
                        ->hourly()
                        ->timezone('Asia/Jakarta');

                } elseif ($migrationProcess->duration == 'day') {
                    $schedule->command($command)
                        ->dailyAt($timeString)
                        ->timezone('Asia/Jakarta');

                } elseif ($migrationProcess->duration == 'week') {
                    $dayOfWeek = $migrationProcess->duration_day_of_week;
                    $schedule->command($command)
                        ->weeklyOn($dayOfWeek, $timeString)
                        ->timezone('Asia/Jakarta');

                } elseif ($migrationProcess->duration == 'month') {
                    $dayOfMonth = $migrationProcess->duration_day_of_month;
                    $schedule->command($command)
                        ->monthlyOn($dayOfMonth, $timeString)
                        ->timezone('Asia/Jakarta');

                } elseif ($migrationProcess->duration == 'year') {
                    $dayOfMonth = $migrationProcess->duration_day_of_month;
                    $month = $migrationProcess->duration_month;
                    $schedule->command($command)
                        ->yearlyOn($month, $dayOfMonth, $timeString)
                        ->timezone('Asia/Jakarta');
                }
            }
        }
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

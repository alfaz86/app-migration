<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogProcessMigration
{
    public function logSuccessProcessMigration($migration, $driver, $startTime, $endTime, $count)
    {
        $autoMigrationProcess = $migration['auto_migration_process'] ? 'Auto' : 'Manual';
        $executionTime = $endTime - $startTime;

        $timezone = new \DateTimeZone('Asia/Jakarta');
        $startDateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', $startTime), $timezone);
        $startDateTimeString = $startDateTime->format('Y-m-d H:i:s.u');
        $endDateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', $endTime), $timezone);
        $endDateTimeString = $endDateTime->format('Y-m-d H:i:s.u');

        Log::info('Migration: ' . $autoMigrationProcess . PHP_EOL .
            'Driver: ' . $driver . PHP_EOL .
            'Migrasi ke database: ' . $migration['database'] . '.' . ($migration['table'] ?? $migration['collections']) . PHP_EOL .
            'Start: ' . $startDateTimeString . PHP_EOL .
            'End: ' . $endDateTimeString . PHP_EOL .
            'Waktu eksekusi migrasi: ' . $executionTime . ' detik' . PHP_EOL .
            'Jumlah data yang berhasil diinput: ' . $count);
    }
}

<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogProcessMigration
{
    public function logSuccessProcessMigration($driver, $startTime, $endTime, $count)
    {
        $executionTime = $endTime - $startTime;

        $startDateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', $startTime));
        $startDateTimeString = $startDateTime->format('Y-m-d H:i:s.u');
        $endDateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', $endTime));
        $endDateTimeString = $endDateTime->format('Y-m-d H:i:s.u');

        Log::info('Driver: ' . $driver . PHP_EOL .
            'Migrasi ke database: ' . $this->migration['database'] . '.' . ($this->migration['table'] ?? $this->migration['collections']) . PHP_EOL .
            'Start: ' . $startDateTimeString . PHP_EOL .
            'End: ' . $endDateTimeString . PHP_EOL .
            'Waktu eksekusi migrasi: ' . $executionTime . ' detik' . PHP_EOL .
            'Jumlah data yang berhasil diinput: ' . $count);
    }
}

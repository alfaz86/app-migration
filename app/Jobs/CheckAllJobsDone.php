<?php

namespace App\Jobs;

use App\Models\MigrationProcess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAllJobsDone implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $migrationProcessID;
    protected $totalRequest;

    public function __construct($migrationProcessID, $totalRequest)
    {
        $this->migrationProcessID = $migrationProcessID;
        $this->totalRequest = $totalRequest;
    }

    public function handle()
    {
        // Get the count of all jobs related to this migrationProcessID
        $totalJobs = $this->totalRequest; // Total number of jobs you expect to be dispatched
        $completedJobs = $this->countCompletedJobs();

        if ($completedJobs >= $totalJobs) {
            MigrationProcess::where('id', $this->migrationProcessID)->update(['status' => 'completed']);
        }
    }

    protected function countCompletedJobs()
    {
        // Implement a way to count completed jobs for the given migrationProcessID
        // This can be done by tracking job completion status in a database table or cache
        return $this->totalRequest; // Placeholder
    }
}

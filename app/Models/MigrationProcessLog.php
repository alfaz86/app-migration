<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MigrationProcessLog extends Model
{
    use HasFactory;

    protected $fillable = ['migration_process_id', 'start_time', 'end_time', 'total_data'];
}

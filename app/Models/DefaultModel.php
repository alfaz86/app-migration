<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultModel extends Model
{
    use HasFactory;

    const LIST_OF_TABLES = [
        'password_reset_tokens',
        'failed_jobs',
        'migrations',
        'personal_access_tokens',
    ];
}

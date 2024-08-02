<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MigrationProcess extends Model
{
    use HasFactory;

    protected $fillable = ['status'];

    public function getAutoMigrationProcessAttribute()
    {
        $value = $this->attributes['auto_migration_process'];
        return $value 
            ? '<a class="badge badge-secondary" href="/migration/list?tag=1"> Experimen </a>' 
            : '<a class="badge badge-secondary" href="/migration/list?tag=0"> Kontrol </a>';
    }
}

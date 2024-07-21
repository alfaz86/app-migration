<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonRelationalModel extends Model
{
    use HasFactory;

    const MONGODB_DATA_TYPES = [
        'double' => 'double', // Bilangan floating point dengan presisi ganda
        'string' => 'string', // String
        'object' => 'object', // Objek umum
        'array' => 'array', // Array
        'binary' => 'binData', // Data biner
        'undefined' => 'undefined', // Undefined (deprecated)
        'objectId' => 'objectId', // Objek ID
        'boolean' => 'bool', // Boolean
        'date' => 'date', // Tanggal
        'null' => 'null', // Null
        'regex' => 'regex', // Ekspresi reguler
        'javascript' => 'javascript', // JavaScript
        'symbol' => 'symbol', // Symbol
        'javascriptWithScope' => 'javascriptWithScope', // JavaScript dengan skop
        'int32' => 'int', // Bilangan bulat 32-bit
        'timestamp' => 'timestamp', // Stempel waktu
        'int64' => 'long', // Bilangan bulat 64-bit
        'decimal128' => 'decimal', // Decimal 128-bit
        'minKey' => 'minKey', // Min Key
        'maxKey' => 'maxKey' // Max Key
    ];
}

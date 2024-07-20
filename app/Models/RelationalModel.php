<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationalModel extends Model
{
    use HasFactory;

    const DATA_TYPES = [
        'integer' => 'INTEGER',
        'smallint' => 'SMALLINT',
        'tinyint' => 'TINYINT',
        'bigint' => 'BIGINT',
        'float' => 'FLOAT',
        'double' => 'DOUBLE',
        'real' => 'REAL',
        'decimal' => 'DECIMAL',
        'numeric' => 'NUMERIC',
        'char' => 'CHAR',
        'varchar' => 'VARCHAR',
        'text' => 'TEXT',
        'tinytext' => 'TINYTEXT',
        'mediumtext' => 'MEDIUMTEXT',
        'longtext' => 'LONGTEXT',
        'binary' => 'BINARY',
        'varbinary' => 'VARBINARY',
        'blob' => 'BLOB',
        'tinyblob' => 'TINYBLOB',
        'mediumblob' => 'MEDIUMBLOB',
        'longblob' => 'LONGBLOB',
        'date' => 'DATE',
        'time' => 'TIME',
        'datetime' => 'DATETIME',
        'timestamp' => 'TIMESTAMP',
        'year' => 'YEAR',
        'boolean' => 'BOOLEAN',
        'enum' => 'ENUM',
        'set' => 'SET',
        'geometry' => 'GEOMETRY',
        'point' => 'POINT',
        'linestring' => 'LINESTRING',
        'polygon' => 'POLYGON',
        'json' => 'JSON',
        'xml' => 'XML',
        'uuid' => 'UUID'
    ];
}

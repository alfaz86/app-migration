<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationalModel extends Model
{
    use HasFactory;

    const MYSQL_DATA_TYPES = [
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

    const POSTGRESQL_DATA_TYPES = [
        'integer' => 'INTEGER',
        'smallint' => 'SMALLINT',
        'bigint' => 'BIGINT',
        'float' => 'REAL', // PostgreSQL uses REAL for single precision
        'double' => 'DOUBLE PRECISION',
        'real' => 'REAL',
        'decimal' => 'DECIMAL',
        'numeric' => 'NUMERIC',
        'char' => 'CHARACTER',
        'varchar' => 'VARCHAR',
        'text' => 'TEXT',
        'bytea' => 'BYTEA', // For binary data
        'date' => 'DATE',
        'time' => 'TIME',
        'timetz' => 'TIMETZ', // time with time zone
        'timestamp' => 'TIMESTAMP',
        'timestamptz' => 'TIMESTAMPTZ', // timestamp with time zone
        'boolean' => 'BOOLEAN',
        'enum' => 'ENUM', // Requires type definition
        'json' => 'JSON',
        'jsonb' => 'JSONB', // Binary JSON storage
        'uuid' => 'UUID',
        'xml' => 'XML',
        'cidr' => 'CIDR',
        'inet' => 'INET',
        'macaddr' => 'MACADDR',
        'tsvector' => 'TSVECTOR',
        'tsquery' => 'TSQUERY',
        'box' => 'BOX',
        'circle' => 'CIRCLE',
        'line' => 'LINE',
        'lseg' => 'LSEG',
        'path' => 'PATH',
        'point' => 'POINT',
        'polygon' => 'POLYGON',
        'interval' => 'INTERVAL',
        'money' => 'MONEY',
        'serial' => 'SERIAL',
        'bigserial' => 'BIGSERIAL',
        'smallserial' => 'SMALLSERIAL',
        'array' => 'ARRAY' // To define array types, e.g., INTEGER[], TEXT[]
    ];
}

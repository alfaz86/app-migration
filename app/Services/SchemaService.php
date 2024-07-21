<?php

namespace App\Services;

class SchemaService
{
    public function generateSchema($data)
    {
        $table = $data['table'];
        $columns = $data['column_name'];
        $dataTypes = $data['data_type'];
        $nullables = $data['nullable'];
        $indexes = $data['index'];
        $quationMark = $data['driver'] === 'pgsql' ? '"' : '`';

        $schema = "CREATE TABLE IF NOT EXISTS $quationMark$table$quationMark (\n";

        $primaryKeys = [];
        $uniqueKeys = [];

        for ($i = 0; $i < count($columns); $i++) {
            $column = self::getFinalKey($columns[$i]);
            $type = $dataTypes[$i];
            $nullable = $nullables[$i] === 'true' ? 'NULL' : 'NOT NULL';

            if ($type === 'VARCHAR') {
                $type .= '(255)';
            }

            $schema .= "    $quationMark$column$quationMark $type $nullable,\n";

            if ($indexes[$i] === 'primary') {
                $primaryKeys[] = "$quationMark$column$quationMark";
            } elseif ($indexes[$i] === 'unique') {
                $uniqueKeys[] = "$quationMark$column$quationMark";
            }
        }

        if (!empty($primaryKeys)) {
            $schema .= "    PRIMARY KEY (" . implode(', ', $primaryKeys) . "),\n";
        }

        foreach ($uniqueKeys as $uniqueKey) {
            $schema .= "    UNIQUE ($uniqueKey),\n";
        }

        // Remove the trailing comma and add closing parenthesis
        $schema = rtrim($schema, ",\n") . "\n);";

        return $schema;
    }


    public function generateSchemaMapping($data)
    {
        $columns = $data['column_name'];
        $schemaMapping = [];

        for ($i = 0; $i < count($columns); $i++) {
            $key = self::getFinalKey($columns[$i]);
            $schemaMapping[$key] = $columns[$i];
        }

        return json_encode($schemaMapping);
    }

    protected static function getFinalKey(string $string)
    {
        $parts = explode('.', $string);
        $finalKey = end($parts);
        if (is_numeric($finalKey)) {
            $finalKey = prev($parts);
        }

        return $finalKey;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DatabaseService
{
    const SUPPORTED_DRIVERS = ['mysql', 'pgsql', 'mongodb'];
    
    public function __construct()
    {
        // 
    }

    public function setDatabaseConfig($config)
    {
        $dynamic_db = 'dynamic_' . $config['driver'];
        Config::set("database.connections.$dynamic_db", [
            'driver' => $config['driver'],
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => $config['database'],
            'username' => $config['username'] ?? '',
            'password' => $config['password'] ?? '',
            'options' => [
                'database' => $config['authSourceDatabase'] ?? '',
            ],
        ]);
    }

    public function checkConnection($config)
    {
        $dynamic_db = 'dynamic_' . $config['driver'];
        $connection = DB::connection($dynamic_db);
        $data = [];

        if ($config['driver'] === 'mysql') {
            $tables = $connection->select('SHOW TABLES');
            foreach ($tables as $table) {
                $data[] = array_values((array) $table)[0];
            }
        } elseif ($config['driver'] === 'pgsql') {
            $tables = $connection->select("SELECT tablename FROM pg_tables WHERE schemaname='public'");
            foreach ($tables as $table) {
                $data[] = $table->tablename;
            }
        } elseif ($config['driver'] === 'mongodb') {
            $list = $connection->listCollections();
            foreach ($list as $collection) {
                $data[] = $collection->getName();
            }
        } else {
            throw new \Exception("Unsupported database driver.", 400);
        }

        return $data;
    }
}
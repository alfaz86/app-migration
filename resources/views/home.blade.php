@extends('layouts.app')

@section('content')
@php
// echo database name from config::get
$database = \Config::get('database');
echo "<h1>Database: " . $database['connections']['mysql']['database'] . "</h1>";

foreach ($tables as $table) {
    // The table name will be a property of the stdClass object
    $db = "Tables_in_" . env('DB_DATABASE');
    
    // Check if the property exists
    if (property_exists($table, $db)) {
        // Check if the table name is in the list of tables to skip
        // if (in_array($table->$db, \App\Models\DefaultModel::LIST_OF_TABLES)) {
        //     continue;
        // }
        echo "<a href='/" . $table->$db . "'>" . $table->$db . "</a><br>";
    } else {
        // Handle the case where the property does not exist
        echo "Property $db does not exist in the table object.<br>";
    }
}

@endphp
@endsection
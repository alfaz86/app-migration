@extends('layouts.app')

@section('content')
@php
// echo database name from config::get
$database = \Config::get('database');
echo "<h1>Database: " . $database['connections']['mysql']['database'] . "</h1>";

foreach ($tables as $table) {
// The table name will be a property of the stdClass object
$db = "Tables_in_" . env('DB_DATABASE');

if (in_array($table->$db, \App\Models\DefaultModel::LIST_OF_TABLES)) {
continue;
}
echo "<a href='/" . $table->$db . "'>" . $table->$db . "</a><br>";
}
@endphp
@endsection
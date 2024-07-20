@extends('layouts.app')

@section('content')
@php
// echo database name from config::get
$database = \Config::get('database');
echo "<h1>Database: " . $database['connections']['mysql']['database'] . "</h1>";



@endphp
@endsection
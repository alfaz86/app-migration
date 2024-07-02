@extends('layouts.app')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#">Migration</a></li>
<li class="breadcrumb-item"><a href="#">List</a></li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <h1>Migration List</h1>
            <div class="table-responsive">
                <table class="table table-striped">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>HTTP Method</th>
                        <th>Result Data</th>
                        <th>Database</th>
                        <th>Setup Connection</th>
                        <th>Schema</th>
                        <th>Scheduler</th>
                        <th>Time</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($migrations as $migration)
                    <tr>
                        <td>{{ $migration->url }}</td>
                        <td>{{ $migration->http_method }}</td>
                        <td>{{ $migration->result_data }}</td>
                        <td>{{ $migration->database }}</td>
                        <td>{{ $migration->setup_connection }}</td>
                        <td>{{ $migration->schema }}</td>
                        <td>{{ $migration->scheduler }}</td>
                        <td>{{ $migration->time }}</td>
                        <td>{{ $migration->duration }}</td>
                        <td>{{ $migration->status }}</td>
                        <td>{{ $migration->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
            <div class="my-3">
                {{ $migrations->links() }}
            </div>
        </div>
    </div>
@endsection
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
                        <th>Database</th>
                        <th>Scheduler</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($migrations as $migration)
                    <tr>
                        <td>{{ $migration->url }}</td>
                        <td>
                            {{ $migration->database }}
                        </td>
                        <td>
                            @if ($migration->scheduler == 'on')
                            {{ $migration->time }} -
                            {{ $migration->duration }}
                            @else
                            {{ $migration->scheduler }}
                            @endif
                        </td>
                        <td>
                            @if ($migration->status == 'completed')
                            <span class="badge badge-success">{{ $migration->status }}</span>
                            @elseif($migration->status == 'progress')
                            <span class="badge badge-warning">{{ $migration->status }}</span>
                            @else
                            <span class="badge badge-info">{{ $migration->status }}</span>
                            @endif
                        </td>
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
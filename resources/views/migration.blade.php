<!-- resources/views/database_config.blade.php -->

@extends('layouts.app')

@section('styles')
<style>
.area-display-response,
.area-display-error-message {
    max-height: 300px;
    overflow-y: auto;
    background-color: aliceblue;
    padding: 1em;
}



.loading-animation {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#">Migration</a></li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-group">
            <label for="url">Input API URL</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="dropdown">
                        <select class="form-control" aria-label="Default select example">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="PATCH">PATCH</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>
                </div>
                <input type="text" name="url" id="url" class="form-control" placeholder="Input URL">
            </div>
            <button class="btn btn-primary mt-3" onclick="fetchData()">Fetch</button>
        </div>
        <div class="form-group">
            <label for="response">Response</label>
            <div class="area-display-response">
                <pre><code id="display-response"></code></pre>
            </div>
        </div>

        <hr>

        <div class="form-group" id="form-destination-database">
            <label >Destination Database</label>
            <input type="text" name="driver" id="driver" class="form-control my-2" placeholder="driver" value="mysql">
            <input type="text" name="host" id="host" class="form-control my-2" placeholder="host" value="127.0.0.1">
            <input type="text" name="port" id="port" class="form-control my-2" placeholder="port" value="3306">
            <input type="text" name="database" id="database" class="form-control my-2" placeholder="database" value="app_migration_2">
            <input type="text" name="username" id="username" class="form-control my-2" placeholder="username" value="root">
            <input type="text" name="password" id="password" class="form-control my-2" placeholder="password" value="">
            <button class="btn btn-primary mt-3" onclick="getData()">Check</button>

            <div class="area-display-error-message my-2" style="display: none; position: relative;">
                <button id="close-button" class="btn btn-secondary btn-sm" style="position: absolute; top: 0; right: 0;">Close</button>
                <pre><code id="display-error-message"></code></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var exitButton = document.getElementById('close-button');

exitButton.addEventListener('click', function() {
    var areaDisplayErrorMessage = document.querySelector('.area-display-error-message');
    areaDisplayErrorMessage.style.display = 'none';
});

function fetchData() {
    var url = document.getElementById('url').value;
    var displayResponse = document.getElementById('display-response');
    displayResponse.innerHTML = '';
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log(data);
            displayResponse.innerHTML = JSON.stringify(data, null, 2);
        })
        .catch(error => {
            console.log(error)
            alert('kesalahan pada url yang diinputkan')
        });
}

function getData() {
    var driver = document.getElementById('driver').value;
    var host = document.getElementById('host').value;
    var port = document.getElementById('port').value;
    var database = document.getElementById('database').value;
    var username = document.getElementById('username').value;
    var password = document.getElementById('password').value;

    var loadingAnimation = document.createElement('div');
    loadingAnimation.classList.add('loading-animation');
    document.getElementById('form-destination-database').appendChild(loadingAnimation);

    // Disable check button
    var checkButton = document.querySelector('#form-destination-database button');
    checkButton.disabled = true;

    $.ajax({
        url: "{{ route('database.getData') }}",
        type: "GET",
        data: {
            _token: '{{ csrf_token() }}',
            driver: driver,
            host: host,
            port: port,
            database: database,
            username: username,
            password: password,
        },
        success: function(response) {
            console.log(response);
            // trigger click close-button
            exitButton.click();

            alert('Data berhasil diambil');
        },
        error: function(xhr) {
            console.log(xhr);
            // Tampilkan pesan kesalahan
            var areaDisplayErrorMessage = document.querySelector('.area-display-error-message');
            var displayErrorMessage = document.getElementById('display-error-message');
            areaDisplayErrorMessage.style.display = 'block';
            displayErrorMessage.innerHTML = JSON.stringify(xhr.responseJSON, null, 2);

            alert('Data gagal diambil');
        },
        complete: function() {
            // Remove loading animation
            loadingAnimation.remove();
            
            // Enable check button
            checkButton.disabled = false;
        }
    });
}
</script>
@endsection

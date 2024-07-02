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
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .custom-control-input:checked~.custom-control-label::before {
        background-color: #7c8798;
        border-color: #7c8798;
        color: white;
    }

    #form-scheduler {
        display: none;
    }
</style>
@endsection

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#">Migration</a></li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form id="migration-form">
            @csrf
            <div class="form-group" id="form-api-url">
                <h3><b>API Endpoint</b></h3>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="dropdown">
                            <select class="form-control" aria-label="Default select example" id="http-method" name="http_method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                {{-- <option value="PUT">PUT</option>
                                <option value="PATCH">PATCH</option>
                                <option value="DELETE">DELETE</option> --}}
                            </select>
                        </div>
                    </div>
                    <input type="text" name="url" id="url" class="form-control" placeholder="Input URL"
                    value="https://jsonplaceholder.typicode.com/posts">
                </div>
                <button type="button" class="btn btn-primary mt-3" onclick="fetchData()">Fetch</button>
            </div>
            <div class="form-group">
                <label for="response">Response</label>
                <div class="area-display-response">
                    <pre><code id="display-response"></code></pre>
                </div>
            </div>
            <div class="form-group">
                <label for="result-data">Result Data</label>
                <select class="form-control" aria-label="Default select example" id="result-data" name="result_data">
                    <option value="current">Current Response</option>
                </select>
            </div>

            <hr>

            <div class="form-group" id="form-destination-database">
                <h3><b>Destination Database</b></h3>
                {{-- buat select option dengan name dan id driver dengan value mysql, pgsql --}}
                <select class="form-control my-2" name="driver" id="driver" onchange="setDefaultDatabase()">
                    <optgroup label="Relational Databases">
                        <option value="mysql">MySQL</option>
                        <option value="pgsql">PostgreSQL</option>
                        {{-- <option value="sqlite">SQLite</option> --}}
                    </optgroup>
                    <optgroup label="Non-relational Databases">
                        <option value="mongodb">MongoDB</option>
                        {{-- <option value="couchdb">CouchDB</option>
                        <option value="redis">Redis</option> --}}
                    </optgroup>
                </select>
                
                {{-- <input type="text" name="driver" id="driver" class="form-control my-2" placeholder="driver" value="mysql"> --}}
                <input type="text" name="host" id="host" class="form-control my-2" placeholder="host" value="127.0.0.1">
                <input type="text" name="port" id="port" class="form-control my-2" placeholder="port" value="3306">
                <input type="text" name="database" id="database" class="form-control my-2" placeholder="database" value="destination_db">
                <input type="text" name="username" id="username" class="form-control my-2" placeholder="username" value="root">
                <input type="text" name="password" id="password" class="form-control my-2" placeholder="password" value="">
                <button type="button" class="btn btn-secondary mt-3" onclick="checkConnection()">Check</button>
                {{-- <button type="button" class="btn btn-primary mt-3" onclick="setMigration()">Set</button> --}}

                <div class="area-display-error-message my-2" style="display: none; position: relative;">
                    <button type="button" id="close-button" class="btn btn-secondary btn-sm" style="position: absolute; top: 0; right: 0;">Close</button>
                    <pre><code id="display-error-message"></code></pre>
                </div>
            </div>

            <hr>

            {{-- create input area with class schema --}}
            <div class="form-group">
                <h3><b>Schema</b></h3>
                <textarea class="form-control" name="schema" id="schema" cols="30" rows="10">
CREATE TABLE IF NOT EXISTS posts (
    userId INT,
    id INT,
    title VARCHAR(255),
    body TEXT,
    PRIMARY KEY (userId, id)
);
                </textarea>

            <hr>

            <div class="form-group">
                <h3><b>Scheduler</b></h3>
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-success">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="scheduler-on" name="scheduler" value="on" class="custom-control-input" onchange="toggleScheduler()">
                            <label class="custom-control-label" for="scheduler-on">On</label>
                        </div>
                    </label>
                    <label class="btn btn-danger">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="scheduler-off" name="scheduler" value="off" class="custom-control-input" onchange="toggleScheduler()" checked>
                            <label class="custom-control-label" for="scheduler-off">Off</label>
                        </div>
                    </label>
                </div>

                <br>

                <div id="form-scheduler">
                    <label for="time">Time</label>
                    <input type="time" name="time" id="time" class="form-control mb-1" placeholder="Input Time">

                    <label for="duration">Duration</label>
                    <select class="form-control" name="duration" id="duration">
                        <option value="minute">Menit</option>
                        <option value="hour">Jam</option>
                        <option value="day">Hari</option>
                        <option value="week">Minggu</option>
                        <option value="month">Bulan</option>
                        <option value="year">Tahun</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('footer')
<div class="submit-container">
    <div class="area-btn-submit w-100 bg-white p-4 text-right shadow-lg">
        <button class="btn btn-primary btn-submit" onclick="submitForm()">Create Migration</button>
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
        var httpMethod = document.getElementById('http-method').value;
        var displayResponse = document.getElementById('display-response');
        displayResponse.innerHTML = '';

        fetch(url, {
                method: httpMethod,
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                displayResponse.innerHTML = JSON.stringify(data, null, 2);
                if (!Array.isArray(data)) {
                    // insert the field into select result_data 
                    // fisrt reset the field without current value dan set other field
                    var resultData = document.getElementById('result-data');
                    // remove select input value
                    for (var i = resultData.options.length - 1; i >= 0; i--) {
                        if (resultData.options[i].value !== 'current') {
                            resultData.remove(i);
                        }
                    }
                    Object.keys(data).forEach(field => {
                        var option = document.createElement('option');
                        option.value = field;
                        option.text = field;
                        resultData.appendChild(option);
                        
                    })
                }
            })
            .catch(error => {
                console.log(error)
                alert('kesalahan pada url yang diinputkan')
            });
    }

    function checkConnection() {
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
            url: "{{ route('database.checkConnection') }}",
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
                exitButton.click();
                alert('Database berhasil terkoneksi');
            },
            error: function(xhr) {
                console.log(xhr);
                var areaDisplayErrorMessage = document.querySelector('.area-display-error-message');
                var displayErrorMessage = document.getElementById('display-error-message');
                areaDisplayErrorMessage.style.display = 'block';
                displayErrorMessage.innerHTML = JSON.stringify(xhr.responseJSON, null, 2);
                alert('Database gagal terkoneksi');
            },
            complete: function() {
                loadingAnimation.remove();
                checkButton.disabled = false;
            }
        });
    }

    function setMigration() {
        var form = document.getElementById('migration-form');
        var formData = new FormData(form);

        $.ajax({
            url: "{{ route('migration.set') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                alert('Pengaturan migrasi berhasil disimpan');
            },
            error: function(xhr) {
                console.log(xhr);
                alert('Pengaturan migrasi gagal disimpan');
            }
        });
    }

    function toggleScheduler() {
        var schedulerOn = document.getElementById('scheduler-on');
        var formScheduler = document.getElementById('form-scheduler');

        if (schedulerOn.checked) {
            formScheduler.style.display = 'block';
        } else {
            formScheduler.style.display = 'none';
        }
    }

    function filterResultData(dataResponse) {
        // check if array
        if (Array.isArray(dataResponse)) {
            return dataResponse[0];
        }
        return dataResponse;
    }

    function setDefaultDatabase(database) {
        var driver = document.getElementById('driver').value;
        var host = document.getElementById('host');
        var port = document.getElementById('port');
        var database = document.getElementById('database');
        var username = document.getElementById('username');
        var password = document.getElementById('password');

        if (driver === 'mysql') {
            host.value = '127.0.0.1';
            port.value = '3306';
            database.value = 'destination_db';
            username.value = 'root';
            password.value = '';
        } else if (driver === 'pgsql') {
            host.value = '127.0.0.1';
            port.value = '5432';
            database.value = 'destination_db';
            username.value = 'postgres';
            password.value = '';
        } else if (driver === 'mongodb') {
            host.value = '127.0.0.1';
            port.value = '27017';
            database.value = 'destination_db';
            username.value = 'admin';
            password.value = '';
        }
    }

    function submitForm() {
        var form = document.getElementById('migration-form');
        var formData = new FormData(form);

        $.ajax({
            url: "{{ route('migration.create') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                alert('Migrasi berhasil dibuat');
            },
            error: function(xhr) {
                console.log(xhr);
                alert('Migrasi gagal dibuat');
            }
        });
    }
</script>
@endsection

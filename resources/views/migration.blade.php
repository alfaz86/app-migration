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

    th, td {
        padding: 0.5em !important;
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
            <div id="form-api-endpoint" class="position-relative">
                <div class="form-group" id="form-api-url">
                    <h3><b>API Endpoint</b></h3>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="dropdown">
                                <select class="form-control" aria-label="Default select example" id="http-method" name="http_method">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <!-- <option value="PUT">PUT</option>
                                    <option value="PATCH">PATCH</option>
                                    <option value="DELETE">DELETE</option> -->
                                </select>
                            </div>
                        </div>
                        <input type="text" name="url" id="url" class="form-control" placeholder="Input URL"
                        value="https://jsonplaceholder.typicode.com/posts">
                        <div class="input-group-prepend">
                            <button type="button" id="send-api-button" class="btn btn-primary" onclick="fetchData()">Kirim</button>
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label for="auth-type">Authentication Type</label>
                        <select class="form-control" id="auth-type" name="auth_type">
                            <option value="none">None</option>
                            <option value="basic">Basic Auth</option>
                            <option value="bearer">Bearer Token</option>
                            {{-- <option value="oauth2">OAuth 2.0</option> --}}
                            <option value="apikey">API Key</option>
                        </select>
                    </div>
                    <div class="form-group" id="auth-params">
                        <!-- Fields for authentication parameters will be dynamically added here -->
                    </div>
                </div>
                <div class="form-group">
                    <label for="response">Response</label>
                    <div class="area-display-response">
                        <pre><code id="display-response"></code></pre>
                    </div>
                </div>
                <div class="form-group">
                    <label for="result-data">Result Data</label>
                    <select class="form-control" aria-label="Default select example" id="result-data" name="result_data" required>
                        {{-- <option value="current">Current Response</option> --}}
                    </select>
                </div>
            </div>

            <hr>

            <div class="form-group position-relative" id="form-destination-database">
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
                <input type="text" name="database" id="database" class="form-control my-2" placeholder="database" value="app_migration_2">
                <input type="text" name="username" id="username" class="form-control my-2" placeholder="username" value="root">
                <input type="password" name="password" id="password" class="form-control my-2" placeholder="password" value="">
                <input type="text" name="authSourceDatabase" id="authSourceDatabase" class="form-control my-2" placeholder="authSourceDatabase" value="" style="display: none">
                <button type="button" id="check-connection-button" class="btn btn-secondary mt-3" onclick="checkConnection()">Check Connection</button>
                {{-- <button type="button" class="btn btn-primary mt-3" onclick="setMigration()">Set</button> --}}

                <div class="area-display-error-message my-2" style="display: none; position: relative;">
                    <button type="button" id="close-button" class="btn btn-secondary btn-sm" style="position: absolute; top: 0; right: 0;">Close</button>
                    <pre><code id="display-error-message"></code></pre>
                </div>
            </div>

            <hr>

            <div class="form-group" id="table-content" style="display: block">
                <label for="table">Table</label>
                <input type="text" name="table" id="table" class="form-control" placeholder="table">
            </div>
            <div class="form-group" id="collections-content" style="display: none">
                <h3><b>Collections</b></h3>
                <input type="text" name="collections" id="collections" class="form-control" placeholder="collections">
            </div>
            {{-- create input area with class schema --}}
            <div class="form-group" id="field-content">
                <label>Column</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type Of Data</th>
                            <th>Nullable</th>
                            <th>Indeks</th>
                            <th width="1">#</th>
                        </tr>
                    </thead>
                    <tbody id="fields-body">
                        
                    </tbody>
                    <tfoot id="field-footer">

                    </tfoot>
                </table>
            </div>

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
{{-- initial variable --}}
<script>
    let responseAPI = {};
    let objectData = {};
    let connectionDB = {};
    let fieldCount = 1;
    let fieldKey = [];
    const exitButton = document.getElementById('close-button');
    const selectAuthenticationType = document.getElementById('auth-type');
    const selectResultData = document.getElementById('result-data');
    const selectDriver = document.getElementById('driver');
    const transformTable = document.getElementById('field-content');
</script>

{{-- event of element --}}
<script>
    exitButton.addEventListener('click', function() {
        var areaDisplayErrorMessage = document.querySelector('.area-display-error-message');
        areaDisplayErrorMessage.style.display = 'none';
    });

    selectAuthenticationType.addEventListener('change', function() {
        var authType = this.value;
        var authParamsDiv = document.getElementById('auth-params');
        var inputURL = document.getElementById('url');
        authParamsDiv.innerHTML = '';

        if (authType === 'basic') {
            authParamsDiv.innerHTML = `
                <div class="form-group">
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                </div>`;
        } else if (authType === 'bearer') {
            inputURL.value = 'http://firefly-lar10.test/api/v1/accounts/67/transactions';
            authParamsDiv.innerHTML = `
                <div class="form-group">
                    <input type="text" id="token" name="token" class="form-control" placeholder="Token"
                    value="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiNjViMWNjZGJmNGFhZTNjMGQ3MmFkMzIzZDhiMzc1MzhkN2IyODE1MmFkNTgzYzYxNzYxYTdiNDAwM2Q1Mzc1M2QyYzBmZjJlODg5MmQyZjQiLCJpYXQiOjE3MjAxNjAxMTAuMzc3MTM1LCJuYmYiOjE3MjAxNjAxMTAuMzc3MTM4LCJleHAiOjE3NTE2OTYxMTAuMDQ4NzAzLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.Xvk4pwPpsahhRFrs88ry7sQcAbLAaRLrwlBqXEzUh4vdHvfioeaNKbS6jn2IQoL5m30FfP2WBcEK3-47ktDzLyB9wxWlDyotnEXjD0N7KHvtmT_43i0nlK2J7XCAk0rHlQYZsWxgKPYlRUbNX1s8J-a5vxYpCvej_AkMZRg-4Ry3NDuQDnU9KcGNkrMFSfAhu1qsFm1Pr8vbR8lalAo5tFy5Bo_tol8s1NzZ3zvxYoVSAbkO3FvI3J5djJz365a4lKUPvpgHm6ne94XDXpLR0UzBc3omNs2UOIo7tQ5ytqYL9i5ad1EcB2L4a4R8xQRc7z65jj0Z0sVV0k9BcxUF8ICGAQ0Y7qx4JyHcZDt2q0g3LdEuh-FQUidrNKxqyDsoqImXpAfNYH_HUA4O-4ULOzYDp6Pm9BUit4quX1iRgMfPjMyf74K9nU9VJVgJStb66p6IRPXGGKcfoA-mDHmwtlIjRApvH-h81isbA4edZ72sfhLHNIOAu_AWYTsEHosV-B23zWzP77xKsu1nUMGXIu7jP2wjT0eg0AypIlChmYT9fOGF8jlkEzCDTxWpyysRMexveOAuUEtEXNig_w1RmSALeYLHWnvNmYpmneebYJGGyUW8xu1lfSK6u5CQPkqZEEL5uXHxvXj-z50sBJ1HDDQUzsj-aFe0R8qZOfJnvE0">
                </div>`;
        } else if (authType === 'apikey') {
            inputURL.value = 'https://api.rajaongkir.com/starter/province';
            authParamsDiv.innerHTML = `
                <div class="form-group">
                    <input type="text" id="apikey" name="apikey" class="form-control" placeholder="API Key"
                    value="key">
                    <input type="text" id="apivalue" name="apivalue" class="form-control" placeholder="API Value"
                    value="0e54283c64a3ae525f5868b95ec8c39b">
                </div>`;
        }
        // Add more fields for OAuth 2.0 if needed
    });

    selectResultData.addEventListener('change', function() {
        var resultData = this.value;
        setObjectData(resultData);
        setFieldData();
    });

    selectDriver.addEventListener('change', function() {
        setTransformTable();
    });
</script>

{{-- function that call to the backend --}}
<script>
    // fungsi untuk request API Endpoint
    function fetchData() {
        var url = document.getElementById('url').value;
        var httpMethod = document.getElementById('http-method').value;
        var authType = document.getElementById('auth-type').value;
        var displayResponse = document.getElementById('display-response');
        var loading = loadingAnimation('form-api-endpoint', 'send-api-button');
        displayResponse.innerHTML = '';

        var authData = {};

        if (authType === 'basic') {
            authData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value
            };
        } else if (authType === 'bearer') {
            authData = {
                token: document.getElementById('token').value
            };
        } else if (authType === 'apikey') {
            authData = {
                key: document.getElementById('apikey').value,
                value: document.getElementById('apivalue').value
            };
        }

        $.ajax({
            url: "{{ route('api.checking') }}",
            type: "GET",
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                url: url,
                http_method: httpMethod,
                auth_type: authType,
                auth_data: authData
            },
            success: function(response) {
                responseAPI = response.data;
                displayResponse.innerHTML = JSON.stringify(response.data, null, 2);
                var result_data = setResultData(response.data);
                if (result_data.length >= 1) {
                    setObjectData(result_data[0]);
                    setFieldData();
                }
            },
            error: function(xhr) {
                alert('terjadi kesalahan dari API Endpoint');
                displayResponse.innerHTML = JSON.stringify(xhr.responseJSON.data, null, 2);
            },
            complete: function() {
                loading.animation.remove();
                loading.btn.disabled = false;
            }
        });
    }

    // fungsi untuk check koneksi database
    function checkConnection() {
        var driver = document.getElementById('driver').value;
        var host = document.getElementById('host').value;
        var port = document.getElementById('port').value;
        var database = document.getElementById('database').value;
        var username = document.getElementById('username').value;
        var password = document.getElementById('password').value;
        var authSourceDatabase = document.getElementById('authSourceDatabase').value;
        var loading = loadingAnimation('form-destination-database', 'check-connection-button');

        $.ajax({
            url: "{{ route('database.checkConnection') }}",
            type: "GET",
            data: {
                _token: '{{ csrf_token() }}',
                driver,
                host,
                port,
                database,
                username,
                password,
                authSourceDatabase
            },
            success: function(response) {
                exitButton.click();
                alert('Database berhasil terkoneksi');
                connectionDB = {
                    driver,
                    host,
                    port,
                    database,
                    username,
                    password,
                    authSourceDatabase
                };
                setFieldData();
            },
            error: function(xhr) {
                var areaDisplayErrorMessage = document.querySelector('.area-display-error-message');
                var displayErrorMessage = document.getElementById('display-error-message');
                areaDisplayErrorMessage.style.display = 'block';
                displayErrorMessage.innerHTML = JSON.stringify(xhr.responseJSON, null, 2);
                alert('Database gagal terkoneksi');
            },
            complete: function() {
                loading.animation.remove();
                loading.btn.disabled = false;
            }
        });
    }

    // untuk Field input
    function setFieldData(object_data=objectData)
    {
        if (JSON.stringify(connectionDB) === '{}') {
            console.log('Database belum terkoneksi');
            return;
        }
        $.ajax({
            url: "{{ route('api.collectionKey') }}",
            type: "GET",
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                object_data
            },
            success: function(response) {
                if (response.data.length > 0) {
                    fieldKey = response.data;
                    addField(true);
                }
            },
            error: function(xhr) {
                // Handle the error response
            }
        });
    }

    // run migration after successful adding setup
    function processMigration(id) {
        $.ajax({
            url: "{{ route('migration.process') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                id
            },
            success: function(response) {
                console.log('Migrasi berhasil dijalankan');
            },
            error: function(xhr) {
                console.log('Migrasi gagal dijalankan');
            }
        });
    }
</script>

{{-- helper function --}}
<script>
    function loadingAnimation(idElement, clickButton) {
        // Ensure the parent element has relative position
        var parentElement = document.getElementById(idElement);
        if (!parentElement.classList.contains('relative-position')) {
            parentElement.classList.add('relative-position');
        }

        // Create the loading animation
        var loadingAnimation = document.createElement('div');
        loadingAnimation.classList.add('loading-animation');
        parentElement.appendChild(loadingAnimation);

        // Disable check button
        var btn = document.getElementById(clickButton);
        btn.disabled = true;

        return {
            animation: loadingAnimation,
            btn: btn
        };
    }

    function setObjectData(resultData)
    {
        if (resultData == 'current') {
            objectData = responseAPI[0];
        } else if(resultData.indexOf('.') > 0) {
            const keys = resultData.split('.');
            let specificObject = responseAPI;
            for (let key of keys) {
                if (specificObject[key] !== undefined) {
                    specificObject = specificObject[key];
                } else {
                    specificObject = null;
                    break;
                }
            }
            objectData = specificObject[0] ?? null;
        } else {
            objectData = responseAPI[resultData][0];
        }
    }

    function setResultData(jsonData) {
        let keysWithArray = [];
        if (!Array.isArray(jsonData)) {
            keysWithArray = findArrayKeys(jsonData);
        } else {
            keysWithArray.push('current');
        }
        var resultData = document.getElementById('result-data');
        // remove select input value
        for (var i = resultData.options.length - 1; i >= 0; i--) {
            resultData.remove(i);
        }
        keysWithArray.forEach(field => {
            var option = document.createElement('option');
            option.value = field;
            option.text = field == 'current' ? 'Current Response' : field;
            resultData.appendChild(option);
        })

        return keysWithArray;
    }

    function findArrayKeys(data) {
        const arrayKeys = [];

        function findArrayKeysRecursive(data, parentKey = '') {
            for (const key in data) {
                if (data.hasOwnProperty(key)) {
                    const currentKey = parentKey ? `${parentKey}.${key}` : key;
                    if (Array.isArray(data[key])) {
                        arrayKeys.push(currentKey);
                    }
                    if (typeof data[key] === 'object' && data[key] !== null) {
                        findArrayKeysRecursive(data[key], currentKey);
                    }
                }
            }
        }

        findArrayKeysRecursive(data);
        return arrayKeys;
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

    function setDefaultDatabase(database) {
        var driver = document.getElementById('driver').value;
        var host = document.getElementById('host');
        var port = document.getElementById('port');
        var database = document.getElementById('database');
        var username = document.getElementById('username');
        var password = document.getElementById('password');
        var authSourceDatabase = document.getElementById('authSourceDatabase');
        var table = document.getElementById('table-content');
        var collections = document.getElementById('collections-content');

        if (driver === 'mysql') {
            host.value = '127.0.0.1';
            port.value = '3306';
            database.value = 'destination_db';
            username.value = 'root';
            password.value = '';
            authSourceDatabase.style.display = 'none';
            authSourceDatabase.value = '';
            table.style.display = 'block';
            collections.style.display = 'none';
        } else if (driver === 'pgsql') {
            host.value = '127.0.0.1';
            port.value = '5432';
            database.value = 'destination_db';
            username.value = 'postgres';
            password.value = '';
            authSourceDatabase.style.display = 'none';
            authSourceDatabase.value = '';
            table.style.display = 'block';
            collections.style.display = 'none';
        } else if (driver === 'mongodb') {
            host.value = '127.0.0.1';
            port.value = '27017';
            database.value = 'destination_db';
            username.value = 'admin';
            password.value = '';
            authSourceDatabase.style.display = 'block';
            authSourceDatabase.value = 'authSourceDatabase';
            table.style.display = 'none';
            collections.style.display = 'block';
        }
    }

    function setTransformTable()
    {
        var tableRelational = `
            <label>Column</label>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type Of Data</th>
                        <th>Nullable</th>
                        <th>Indeks</th>
                        <th width="1">#</th>
                    </tr>
                </thead>
                <tbody id="fields-body">
                    
                </tbody>
                <tfoot id="field-footer">

                </tfoot>
            </table>
        `;
        var tableNonRelational = `
            <label>Field</label>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type Of Data</th>
                        <th width="1">#</th>
                    </tr>
                </thead>
                <tbody id="fields-body">
                    
                </tbody>
                <tfoot id="field-footer">

                </tfoot>
            </table>
        `;
        transformTable.innerHTML = selectDriver.value === 'mongodb' ? tableNonRelational : tableRelational;
    }

    function addField(resetrow = false) {
        var driver = document.getElementById('driver').value;
        var attributes = fieldKey.map(attribute => `<option value="${attribute}">${attribute}</option>`).join('');
        var display = driver === 'mongodb' ? 'display: none;' : '';
        fieldCount++;
        const row = `
            <tr>
                <td style="max-width:200px;">
                    <select class="form-control" name="column_name[]" id="column-name-1">
                        ${attributes}
                    </select>
                </td>
                <td style="max-width:200px;">
                    <select class="form-control" name="data_type[]" id="data-type-1">
                        @foreach ($relationalDataTypes as $item)
                            <option value="{{ $item }}">{{ $item }}</option>
                        @endforeach
                    </select>
                </td>
                <td style="max-width:200px; ${display}">
                    <select class="form-control" name="nullable[]" id="nullable-1">
                        <option value="true">True</option>
                        <option value="false" selected>False</option>
                    </select>
                </td>
                <td style="max-width:200px; ${display}">
                    <select class="form-control" name="index[]" id="index-1">
                        <option value="">---</option>
                        <option value="primary">Primary</option>
                        <option value="unique">Unique</option>
                        <option value="index">Index</option>
                        <option value="spatial">Spatial</option>
                        <option value="fulltext">Fulltext</option>
                    </select>
                </td>
                <td class="align-middle">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeField(this)">x</button>
                </td>
            </tr>`;
        const footer = `
            <tr>
                <td colspan="5" class="text-center">
                    <button type="button" class="btn btn-sm btn-primary px-5" onclick="addField()">+</button>
                </td>
            </tr>`;
        if (resetrow) {
            $('#fields-body').html(row);
            $('#field-footer').html(footer);
            return;
        }
        $('#fields-body').append(row);
        $('#field-footer').html(footer);
    }

    function removeField(button) {
        $(button).closest('tr').remove();
    }

</script>

{{-- submit form --}}
<script>
    function validateForm() {
        var url = document.getElementById('url').value;
        var resultData = document.getElementById('result-data').value;
        var driver = document.getElementById('driver').value;
        var host = document.getElementById('host').value;
        var port = document.getElementById('port').value;
        var database = document.getElementById('database').value;
        var username = document.getElementById('username').value;
        var password = document.getElementById('password').value;
        var authSourceDatabase = document.getElementById('authSourceDatabase').value;
        var table = document.getElementById('table').value;
        var collections = document.getElementById('collections').value;

        if (!url || !resultData || !driver || !host || !port || !database || !username) {
            if (driver === 'mongodb') {
                if (!authSourceDatabase || !collections) {
                    alert('Pastikan semua input terisi');
                    return false;
                }
            } else {
                if (!table) {
                    alert('Pastikan semua input terisi');
                    return false;
                }
            }
        }
    }

    function submitForm() {
        var form = document.getElementById('migration-form');
        var formData = new FormData(form);
        var authType = document.getElementById('auth-type').value;
        var authData = {};
    
        if (authType === 'basic') {
            authData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value
            };
        } else if (authType === 'bearer') {
            authData = {
                token: document.getElementById('token').value
            };
        } else if (authType === 'apikey') {
            authData = {
                key: document.getElementById('apikey').value,
                value: document.getElementById('apivalue').value
            };
        }
        
        // Menambahkan auth_data ke FormData
        formData.append('auth_data', JSON.stringify(authData));

        $.ajax({
            url: "{{ route('migration.create') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                processMigration(response.id);
                alert('Migrasi berhasil dibuat');
            },
            error: function(xhr) {
                alert('Migrasi gagal dibuat');
            }
        });
    }
</script>
@endsection

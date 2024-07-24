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
            <!-- Step Bar Navigation -->
            <ul class="nav nav-tabs" id="step-bar">
                <li class="nav-item">
                    <a class="nav-link active" id="step1-tab" data-toggle="tab" href="#step1">API Endpoint</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="step2-tab" data-toggle="tab" href="#step2">Destination Database</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="step3-tab" data-toggle="tab" href="#step3">Scheduler</a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
                    <div id="form-api-endpoint" class="position-relative">
                        <div class="form-group" id="form-api-url">
                            <h3><b>API Endpoint</b></h3>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="dropdown">
                                        <select class="form-control" aria-label="Default select example" id="http-method" name="http_method">
                                            <option value="GET">GET</option>
                                            <option value="POST">POST</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="text" name="url" id="url" class="form-control" placeholder="Input URL" value="" autocomplete="url">
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
                        <div class="form-group">
                            <label for="loop">API Paging</label>
                            <br>
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-success">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="loop-on" name="loop" value="1" class="custom-control-input" onchange="toggleLoop()">
                                        <label class="custom-control-label" for="loop-on">On</label>
                                    </div>
                                </label>
                                <label class="btn btn-danger active">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="loop-off" name="loop" value="0" class="custom-control-input" onchange="toggleLoop()" checked>
                                        <label class="custom-control-label" for="loop-off">Off</label>
                                    </div>
                                </label>
                            </div>
                            <div id="total-page" style="display: none;">
                                <label for="total_page">Total</label>
                                <input type="number" name="total_page" id="total-page" class="form-control mb-1" min="1" value="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
                    <div class="form-group position-relative" id="form-destination-database">
                        <h3><b>Destination Database</b></h3>
                        <select class="form-control my-2" name="driver" id="driver" onchange="setDefaultDatabase()">
                            <optgroup label="Relational Databases">
                                <option value="mysql">MySQL</option>
                                <option value="pgsql">PostgreSQL</option>
                            </optgroup>
                            <optgroup label="Non-relational Databases">
                                <option value="mongodb">MongoDB</option>
                            </optgroup>
                        </select>
                        <input type="text" name="host" id="host" class="form-control my-2" placeholder="host" value="127.0.0.1">
                        <input type="text" name="port" id="port" class="form-control my-2" placeholder="port" value="3306">
                        <input type="text" name="database" id="database" class="form-control my-2" placeholder="database" value="destination_db">
                        <input type="text" name="username" id="username" class="form-control my-2" placeholder="username" value="root">
                        <input type="password" name="password" id="password" class="form-control my-2" placeholder="password" value="">
                        <input type="text" name="authSourceDatabase" id="authSourceDatabase" class="form-control my-2" placeholder="authSource" value="" style="display: none">
                        <button type="button" id="check-connection-button" class="btn btn-secondary mt-3" onclick="checkConnection()">Check Connection</button>
                        <div class="area-display-error-message my-2" style="display: none; position: relative;">
                            <button type="button" id="close-button" class="btn btn-secondary btn-sm" style="position: absolute; top: 0; right: 0;">Close</button>
                            <pre><code id="display-error-message"></code></pre>
                        </div>
                    </div>
                    <div class="form-group" id="table-content" style="display: block">
                        <label for="table">Table</label>
                        <input type="text" name="table" id="table" class="form-control" placeholder="table">
                    </div>
                    <div class="form-group" id="collections-content" style="display: none">
                        <h3><b>Collections</b></h3>
                        <input type="text" name="collections" id="collections" class="form-control" placeholder="collections">
                    </div>
                    <div class="form-group" id="field-content">
                        <label>Column</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type Of Data</th>
                                    <th>Nullable</th>
                                    <th>Index</th>
                                    <th width="1">#</th>
                                </tr>
                            </thead>
                            <tbody id="fields-body">
                            </tbody>
                            <tfoot id="field-footer">
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">
                    <div class="form-group">
                        <h3><b>Scheduler</b></h3>
                        <div class="btn-group" data-toggle="buttons">
                            <label class="btn btn-success">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="scheduler-on" name="scheduler" value="on" class="custom-control-input" onchange="toggleScheduler()">
                                    <label class="custom-control-label" for="scheduler-on">On</label>
                                </div>
                            </label>
                            <label class="btn btn-danger active">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="scheduler-off" name="scheduler" value="off" class="custom-control-input" onchange="toggleScheduler()" checked>
                                    <label class="custom-control-label" for="scheduler-off">Off</label>
                                </div>
                            </label>
                        </div>
                        <br>
                        <div id="form-scheduler">
                            <label for="duration">Duration</label>
                            <select class="form-control mb-1" name="duration" id="duration" onchange="toggleSchedulerFields()">
                                <option value="minute">Menit</option>
                                <option value="hour">Jam</option>
                                <option value="day">Hari</option>
                                <option value="week">Minggu</option>
                                <option value="month">Bulan</option>
                                <option value="year">Tahun</option>
                            </select>
                            <label for="time">Time</label>
                            <input type="time" name="time" id="time" class="form-control mb-1" placeholder="Input Time">

                            <div id="scheduler-options" style="display: none;">
                                <div id="div-duration-day-of-week" style="display: none;">
                                    <label for="duration_day_of_week">Day of the Week</label>
                                    <select class="form-control mb-1" name="duration_day_of_week" id="duration_day_of_week">
                                        <option value="" selected disabled>-</option>
                                        <option value="1">Senin</option>
                                        <option value="2">Selasa</option>
                                        <option value="3">Rabu</option>
                                        <option value="4">Kamis</option>
                                        <option value="5">Jumat</option>
                                        <option value="6">Sabtu</option>
                                        <option value="7">Minggu</option>
                                    </select>
                                </div>
                                <div id="div-duration-day-of-month" style="display: none;">
                                    <label for="duration_day_of_month">Day of the Month</label>
                                    <input type="number" name="duration_day_of_month" id="duration_day_of_month" class="form-control mb-1" min="1" max="31">
                                </div>
                                <div id="div-duration-month" style="display: none;">
                                    <label for="duration_month">Month</label>
                                    <input type="number" name="duration_month" id="duration_month" class="form-control mb-1" min="1" max="12">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('footer')
<div class="submit-container">
    <div class="area-btn-submit w-100 bg-white p-4 text-right shadow-lg">
        <button class="btn btn-primary btn-submit" id="submit-button" disabled onclick="submitForm()">Create Migration</button>
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
    const dataTypes = @json($dataTypes);
    const exitButton = document.getElementById('close-button');
    const selectAuthenticationType = document.getElementById('auth-type');
    const selectResultData = document.getElementById('result-data');
    const selectDriver = document.getElementById('driver');
    const transformTable = document.getElementById('field-content');
</script>

{{-- step bar navigation --}}
<script>
    // Variables to keep track of form completion
    const stepsCompleted = { step1: false, step2: false, step3: false };

    // Function to check if all steps are completed
    function checkFormCompletion() {
        const allStepsCompleted = Object.values(stepsCompleted).every(Boolean);
        document.getElementById('submit-button').disabled = !allStepsCompleted;
    }

    // Function to validate Step 1
    function validateStep1() {
        const url = document.getElementById('url').value;
        const resultData = document.getElementById('result-data').value;
        stepsCompleted.step1 = !!(resultData && url);
        checkFormCompletion();
    }

    // Function to validate Step 2
    function validateStep2() {
        const driver = document.getElementById('driver').value;
        const host = document.getElementById('host').value;
        const port = document.getElementById('port').value;
        const database = document.getElementById('database').value;
        const table = document.getElementById('table').value;
        const collections = document.getElementById('collections').value;
        const fieldsCount = document.querySelectorAll('#fields-body tr').length;
        let tableOrCollection = table;
        if (driver === 'mongodb') {
            tableOrCollection = collections;
        }
        stepsCompleted.step2 = !!(driver && host && port && database && tableOrCollection && fieldsCount);
        checkFormCompletion();
    }

    // Function to validate Step 3
    function validateStep3() {
        const schedulerOn = document.getElementById('scheduler-on');
        const time = document.getElementById('time').value;
        const duration = document.getElementById('duration').value;
        stepsCompleted.step3 = schedulerOn.checked ? !!(time && duration) : true;
        checkFormCompletion();
    }

    // Event listeners for validation
    document.getElementById('url').addEventListener('input', validateStep1);
    document.getElementById('result-data').addEventListener('change', validateStep1);
    document.getElementById('driver').addEventListener('change', validateStep2);
    document.getElementById('host').addEventListener('input', validateStep2);
    document.getElementById('port').addEventListener('input', validateStep2);
    document.getElementById('database').addEventListener('input', validateStep2);
    document.getElementById('username').addEventListener('input', validateStep2);
    document.getElementById('password').addEventListener('input', validateStep2);
    document.getElementById('table').addEventListener('input', validateStep2);
    document.getElementById('collections').addEventListener('input', validateStep2);
    document.getElementById('time').addEventListener('input', validateStep3);
    document.getElementById('duration').addEventListener('change', validateStep3);
    validateStep3();
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
        authParamsDiv.innerHTML = '';

        if (authType === 'basic') {
            authParamsDiv.innerHTML = `
                <div class="form-group">
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                </div>`;
        } else if (authType === 'bearer') {
            authParamsDiv.innerHTML = `
                <div class="form-group">
                    <input type="text" id="token" name="token" class="form-control" placeholder="Token" 
                    value="">
                </div>`;
        } else if (authType === 'apikey') {
            authParamsDiv.innerHTML = `
                <div class="form-group">
                    <input type="text" id="apikey" name="apikey" class="form-control" placeholder="API Key"
                    value="">
                    <input type="text" id="apivalue" name="apivalue" class="form-control" placeholder="API Value"
                    value="">
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
                validateStep1()
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
        validateStep3();
    }

    function setDefaultDatabase() {
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
            authSourceDatabase.value = '';
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

        var dataTypeOptions = Object.entries(dataTypes[driver]).map(([key, value]) => {
            return `<option value="${value}">${key}</option>`;
        }).join('');
        
        const row = `
            <tr>
                <td style="max-width:200px;">
                    <select class="form-control" name="column_name[]" id="column-name-${fieldCount}">
                        ${attributes}
                    </select>
                </td>
                <td style="max-width:200px;">
                    <select class="form-control" name="data_type[]" id="data-type-${fieldCount}">
                        ${dataTypeOptions}
                    </select>
                </td>
                <td style="max-width:200px; ${display}">
                    <select class="form-control" name="nullable[]" id="nullable-${fieldCount}">
                        <option value="true">True</option>
                        <option value="false" selected>False</option>
                    </select>
                </td>
                <td style="max-width:200px; ${display}">
                    <select class="form-control" name="index[]" id="index-${fieldCount}">
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
        validateStep2();
    }

    function removeField(button) {
        $(button).closest('tr').remove();
    }

    function toggleLoop() {
        var loopOn = document.getElementById('loop-on');
        var loopOff = document.getElementById('loop-off');
        var totalPage = document.getElementById('total-page');

        if (loopOn.checked) {
            totalPage.style.display = 'block';
        } else {
            totalPage.style.display = 'none';
        }
    }

    function toggleSchedulerFields() {
        var duration = document.getElementById('duration').value;
        var schedulerOptions = document.getElementById('scheduler-options');
        var dayOfWeek = document.getElementById('div-duration-day-of-week');
        var dayOfMonth = document.getElementById('div-duration-day-of-month');
        var month = document.getElementById('div-duration-month');

        schedulerOptions.style.display = 'none';
        dayOfWeek.style.display = 'none';
        dayOfMonth.style.display = 'none';
        month.style.display = 'none';

        if (duration === 'week') {
            schedulerOptions.style.display = 'block';
            dayOfWeek.style.display = 'block';
        } else if (duration === 'month') {
            schedulerOptions.style.display = 'block';
            dayOfMonth.style.display = 'block';
        } else if (duration === 'year') {
            schedulerOptions.style.display = 'block';
            dayOfMonth.style.display = 'block';
            month.style.display = 'block';
        }
    }
</script>

{{-- submit form --}}
<script>
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
                window.location.href = "{{ route('migration.list') }}";
            },
            error: function(xhr) {
                console.log('Error: ' + xhr.responseText);
                alert('Migrasi gagal dibuat');
            }
        });
    }
</script>
@endsection

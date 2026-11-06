@extends('layouts.main')

@section('content')
    <div class="app-content-header">
        <div class="row justify-content-center">

            <div class="col-md-6">

                {{-- ALERT --}}
                <div id="alert-area"></div>

                <div class="card shadow-sm">

                    <div class="card-header">
                        <h5 class="mb-0">
                            Import Excel Balance Order NB
                        </h5>
                    </div>

                    <div class="card-body">

                        <form id="importForm" action="{{ route('balance-order.import') }}" method="POST"
                            enctype="multipart/form-data">

                            @csrf

                            {{-- FILE --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    Upload File Excel
                                </label>

                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>

                                <small class="text-muted">
                                    Sheet yang dibaca : NB
                                </small>

                            </div>

                            {{-- BUTTON --}}
                            <button type="submit" id="btnImport" class="btn btn-primary w-100">

                                <span id="btnText">
                                    Import Excel
                                </span>

                                <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none">
                                </span>

                            </button>

                        </form>

                        {{-- PROGRESS --}}
                        <div class="mt-4 d-none" id="progressWrapper">

                            <label class="form-label">
                                Progress Import
                            </label>

                            <div class="progress" style="height: 30px;">

                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar" style="width:0%">

                                    0%

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

    <div id="pageMessages"></div>
    <script>
        document.getElementById('importForm')
            .addEventListener('submit', function(e) {

                e.preventDefault();

                let form = this;

                let formData = new FormData(form);

                /*
                |--------------------------------------------------------------------------
                | UI START
                |--------------------------------------------------------------------------
                */

                document.getElementById('btnImport')
                    .disabled = true;

                document.getElementById('progressWrapper')
                    .classList.remove('d-none');

                document.getElementById('loadingSpinner')
                    .classList.remove('d-none');

                document.getElementById('btnText')
                    .innerHTML = 'Importing...';

                /*
                |--------------------------------------------------------------------------
                | START CHECK PROGRESS
                |--------------------------------------------------------------------------
                */

                checkProgress();

                /*
                |--------------------------------------------------------------------------
                | IMPORT
                |--------------------------------------------------------------------------
                */

                fetch(form.action, {

                        method: 'POST',

                        body: formData,

                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }

                    })
                    .then(res => res.json())
                    .then(data => {

                        console.log(data);

                    })
                    .catch(err => {

                        console.log(err);

                        alert('Import gagal');

                    });

            });

        function checkProgress() {
            let interval = setInterval(() => {

                fetch("{{ route('balance-order.progress') }}")

                    .then(response => response.json())

                    .then(data => {

                        let progress = data.progress ?? 0;

                        let progressBar =
                            document.getElementById('progressBar');

                        progressBar.style.width =
                            progress + '%';

                        progressBar.innerHTML =
                            progress + '%';

                        /*
                        |--------------------------------------------------------------------------
                        | FINISH
                        |--------------------------------------------------------------------------
                        */

                        if (progress >= 100) {

                            clearInterval(interval);

                            progressBar.classList.remove(
                                'progress-bar-animated'
                            );

                            document.getElementById('alert-area')
                                .innerHTML = `
                    <div class="alert alert-success mt-3">
                        Import berhasil selesai
                    </div>
                `;

                            document.getElementById('btnImport')
                                .disabled = false;

                            document.getElementById('btnText')
                                .innerHTML = 'Import Excel';

                            document.getElementById('loadingSpinner')
                                .classList.add('d-none');

                        }

                    });

            }, 1000);
        }
    </script>
@endsection

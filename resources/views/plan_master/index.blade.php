@extends('layouts.main')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <!-- Alert Notification -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- FORM IMPORT EXCEL DI KIRI -->
                <div class="col-md-5 pl-1 pr-1 mb-3">
                    <div class="card p-3 h-100 shadow-sm">
                        <h4 class="mb-3">Import Plan Master</h4>
                        <form action="{{ route('plan_master.import') }}" method="POST" id="import_plan_master"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="file" class="form-label font-weight-bold">Upload File Excel (.xlsx /
                                    .xls)</label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror"
                                    id="file" name="file" accept=".xlsx, .xls, .csv" required>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted mt-2">
                                    Format file sesuai template <code>Source System QCO.xlsx</code>.
                                </small>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary pt-2">
                                    <i class="fas fa-file-import me-1"></i> Import Excel Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATABASE DI KANAN -->
                <div class="col-md-7 pl-1 pr-1 mb-3">
                    <div class="card p-3 h-100 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="m-0">Plan Master Database</h4>
                        </div>
                        <div class="overflow-y-auto" style="max-height: 450px">
                            <table class="table table-bordered table-small-font table-hover text-center align-middle"
                                cellspacing="0" id="planMasterTable">
                                <thead class="thead-dark sticky-top">
                                    <tr>
                                        <th>No</th>
                                        <th>Cell</th>
                                        <th>Model</th>
                                        <th>Style No</th>
                                        <th>Qty</th>
                                        <th>Assembly Start</th>
                                        <th>Stitching Start</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($plans as $index => $plan)
                                        <tr>
                                            <td>{{ $plans->firstItem() + $index }}</td>
                                            <td><span class="badge bg-secondary">{{ $plan->cell }}</span></td>
                                            <td>{{ $plan->model }}</td>
                                            <td class="font-weight-bold">{{ $plan->style_no }}</td>
                                            <td>{{ number_format($plan->qty) }}</td>
                                            <td>{{ $plan->assembly_start_date ? $plan->assembly_start_date->format('Y-m-d') : '-' }}
                                            </td>
                                            <td>{{ $plan->stitching_start_date ? $plan->stitching_start_date->format('Y-m-d') : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Data Plan Master belum
                                                tersedia. Silakan lakukan import data.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if (method_exists($plans, 'links'))
                            <div class="mt-3 d-flex justify-content-end">
                                {{ $plans->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

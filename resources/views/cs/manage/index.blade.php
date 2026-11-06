@extends('layouts.main')
@section('content')
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container py-4">

            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-hdd-network fs-1 text-primary me-3"></i>

                <div>
                    <h2 class="mb-0">
                        Monitoring & Management System
                    </h2>
                    <small class="text-muted">
                        Computer Stitching Centralize
                    </small>
                </div>
            </div>

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs" id="stitchingTab" role="tablist">

                <!-- Running -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="running-tab" data-bs-toggle="tab" data-bs-target="#running" type="button"
                        role="tab">

                        <i class="bi-diagram-3 me-2"></i>
                        Model Running
                    </button>
                </li>

                <!-- Process -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="line-tab" data-bs-toggle="tab" data-bs-target="#line" type="button"
                        role="tab">

                        <i class="bi bi-geo-alt me-2"></i>
                        Line Location
                    </button>
                </li>

                <!-- Operator -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="operator-tab" data-bs-toggle="tab" data-bs-target="#operator"
                        type="button" role="tab">

                        <i class="bi bi-people me-2"></i>
                        Operator
                    </button>
                </li>

                <!-- Cell -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="cell-tab" data-bs-toggle="tab" data-bs-target="#cell"
                        type="button" role="tab">

                        <i class="bi bi-grid-3x3-gap-fill me-2"></i>
                        Cell
                    </button>
                </li>

            </ul>

            <!-- Tab Content -->
            <div class="tab-content border border-top-0 bg-white p-4 shadow-sm rounded-bottom" id="stitchingTabContent">

                <!-- Running Content -->
                <div class="tab-pane fade" id="running" role="tabpanel">

                    <h4 class="mb-3">
                        <i class="bi-diagram-3 text-success me-2"></i>
                        Model Running
                    </h4>

                    <p class="text-muted">
                        Manage Model Running by Process
                    </p>

                    <div class="card-header bg-white">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                            <!-- Title -->
                            <div>
                                <h5 class="mb-0">
                                    <i class="bi bi-activity text-success me-2"></i>
                                    Data Manage
                                </h5>
                                <small class="text-muted">
                                    Process Baru
                                </small>

                                <input type="text" class="form-control form-control-sm" mt-2" id="new-process"
                                    placeholder="Input Process Baru">
                            </div>

                            <!-- Search Form -->
                            <form class="d-flex gap-2">

                                <input type="text" class="form-control" onkeyup="main_model_running(this.value)"
                                    placeholder="Search Process">

                                <button class="btn btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>

                            </form>

                        </div>

                    </div>

                    <!-- Card Body -->
                    <div class="card-body">

                        <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">

                            <table class="table table-hover align-middle text-center">

                                <thead class="table-light">

                                    <tr>
                                        <th>No</th>
                                        <th>Process</th>
                                        <th>Target</th>
                                        <th>Model</th>
                                        <th class="bg-secondary text-light" style="width: 10%">Action</th>
                                    </tr>

                                </thead>

                                <tbody id="tbody_model_running">
                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- Process Content -->
                <div class="tab-pane fade" id="line" role="tabpanel">

                    <h4 class="mb-3">
                        <i class="bi bi-geo-alt"></i>
                        Line
                    </h4>

                    <p class="text-muted">
                        Manage Location by Line
                    </p>

                    <div class="row g-3">

                        <!-- LINE MANAGE -->
                        <div class="col-12 col-xl-6">

                            <div class="card h-100 border-0 shadow-sm">

                                <!-- Header -->
                                <div class="card-header bg-white border-0 pb-0">

                                    <!-- Title -->
                                    <div class="d-flex align-items-center mb-3">

                                        <h5 class="mb-0">
                                            <i class="bi bi-diagram-3 text-success me-2"></i>
                                            Line Manage
                                        </h5>

                                    </div>

                                    <!-- Filter -->
                                    <div class="row g-3 align-items-end">

                                        <!-- New Line -->
                                        <div class="col-md-6">

                                            <label class="form-label small text-muted mb-1">
                                                Line Baru
                                            </label>

                                            <input type="text" class="form-control form-control-sm" id="new-line"
                                                placeholder="Input new line">

                                        </div>

                                        <!-- Search -->
                                        <div class="col-md-6">

                                            <label class="form-label small text-muted mb-1">
                                                Search Line
                                            </label>

                                            <div class="input-group input-group-sm">

                                                <input type="text" class="form-control"
                                                    onkeyup="main_line(this.value)" placeholder="Search Line">

                                                <button class="btn btn-primary">

                                                    <i class="bi bi-search"></i>

                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <!-- Body -->
                                <div class="card-body">

                                    <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">
                                        <table class="table table-hover align-middle text-center mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:5%">
                                                        No
                                                    </th>

                                                    <th>
                                                        Line
                                                    </th>

                                                    <th style="width:15%" class="bg-secondary text-light">

                                                        Action

                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_line">
                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- AREA MANAGE -->
                        <div class="col-12 col-xl-6">

                            <div class="card h-100 border-0 shadow-sm">

                                <!-- Header -->
                                <div class="card-header bg-white border-0 pb-0">

                                    <!-- Title -->
                                    <div class="d-flex align-items-center mb-3">

                                        <h5 class="mb-0">
                                            <i class="bi bi-geo-alt text-success me-2"></i>
                                            Area Manage
                                        </h5>

                                    </div>

                                    <!-- Filter -->
                                    <div class="row g-3 align-items-end">

                                        <!-- Select Line -->
                                        <div class="col-md-4">

                                            <label class="form-label small text-muted mb-1">
                                                Search Line First
                                            </label>

                                            <select class="form-select form-select-sm select2" id="select-line"
                                                style="width:100%">

                                            </select>

                                        </div>

                                        <!-- New Area -->
                                        <div class="col-md-4">

                                            <label class="form-label small text-muted mb-1">
                                                Area Baru
                                            </label>

                                            <input type="text" class="form-control form-control-sm" autocomplete="off"
                                                id="new-area" placeholder="Input area Baru">

                                        </div>

                                        <!-- Search -->
                                        <div class="col-md-4">

                                            <label class="form-label small text-muted mb-1">
                                                Search Area
                                            </label>

                                            <div class="input-group input-group-sm">

                                                <input type="text" class="form-control"
                                                    onkeyup="main_area(this.value)" placeholder="Search Area">

                                                <button class="btn btn-primary">

                                                    <i class="bi bi-search"></i>

                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <!-- Body -->
                                <div class="card-body">

                                    <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">

                                        <table class="table table-hover align-middle text-center mb-0">

                                            <thead class="table-light">

                                                <tr>

                                                    <th style="width:5%">
                                                        No
                                                    </th>

                                                    <th>Line</th>
                                                    <th>Area</th>

                                                    <th style="width:15%" class="bg-secondary text-light">

                                                        Action

                                                    </th>

                                                </tr>

                                            </thead>

                                            <tbody id="tbody_area">
                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Operator Content -->
                <div class="tab-pane fade" id="operator" role="tabpanel">

                    <h4 class="mb-3">
                        <i class="bi bi-people text-primary me-2"></i>
                        Operator
                    </h4>

                    <p class="text-muted">
                        Data operator dan aktivitas produksi.
                    </p>

                    <div class="row g-4">

                        <!-- LEFT COLUMN -->
                        <div class="col-lg-4">

                            <div class="card shadow-sm">
                                <div class="card-body p-4">
                                    <form id="formOperator">
                                        <h5>
                                            <i class="bi bi-person-plus text-primary me-2"></i>
                                            Form Operator Baru
                                        </h5>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                NIK
                                            </label>

                                            <input type="text" class="form-control" name="nik"
                                                placeholder="Masukkan NIK">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Nama
                                            </label>

                                            <input type="text" class="form-control" name="nama"
                                                placeholder="Masukkan Nama">
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">
                                                Remark
                                            </label>

                                            <textarea class="form-control" rows="4" name="remark" placeholder="Masukkan Remark"></textarea>
                                        </div>

                                        <div class="d-grid">
                                            <button class="btn btn-primary btn-lg">
                                                Save Data
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>

                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-lg-8">

                            <div class="card shadow-sm">
                                <div class="card-body p-4">

                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5>
                                            <i class="bi bi-list-ul text-primary me-2"></i>
                                            List Operator
                                        </h5>

                                        <input type="text" class="form-control w-25"
                                            onkeyup="main_operator(this.value)" placeholder="Search Operator">
                                    </div>

                                    <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">

                                        <table class="table table-hover align-middle text-center">

                                            <thead>
                                                <tr>
                                                    <th width="70">No</th>
                                                    <th>NIK</th>
                                                    <th>Nama</th>
                                                    <th>Remark</th>
                                                    <th style="width:15%" class="bg-secondary text-light">
                                                        Action
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody id="tbody_operator">
                                            </tbody>

                                        </table>

                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>

                </div>
                <!-- Operator Content -->
                <div class="tab-pane fade show active" id="cell" role="tabpanel">

                    <h4 class="mb-3">
                        <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>
                        Cell
                    </h4>

                    <p class="text-muted">
                        Tempat setting target Computer Stitching Centralize per Hari
                    </p>

                    <div class="row g-4">

                        <!-- LEFT COLUMN -->
                        <div class="col-lg-4">

                            <div class="card shadow-sm">
                                <div class="card-body p-4">
                                    <form id="formCell">
                                        <h5>
                                            <i class="bi bi-person-plus text-primary me-2"></i>
                                            Form input cell baru
                                        </h5>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Nama Cell
                                            </label>

                                            <input type="text" class="form-control" name="cell"
                                                placeholder="Masukan Nama Cell Baru">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Target Cell
                                            </label>
                                            <input type="number" min="1" step="1" class="form-control"
                                                class="form-control" name="target" placeholder="Masukkan Nama">
                                        </div>

                                        <div class="d-grid">
                                            <button class="btn btn-primary btn-lg">
                                                Save Data
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>

                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-lg-8">

                            <div class="card shadow-sm">
                                <div class="card-body p-4">

                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5>
                                            <i class="bi bi-list-ul text-primary me-2"></i>
                                            List Operator
                                        </h5>

                                        <input type="text" class="form-control w-25" onkeyup="mainCell(this.value)"
                                            placeholder="Search Cell">
                                    </div>

                                    <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">

                                        <table class="table table-hover align-middle text-center">

                                            <thead>
                                                <tr>
                                                    <th width="70">No</th>
                                                    <th>Nama Cell</th>
                                                    <th>Target</th>
                                                    <th style="width:15%" class="bg-secondary text-light">
                                                        Action
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody id="tbody_cell">
                                            </tbody>

                                        </table>

                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
            <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <div id="alertMessage" class="alert alert-dismissible fade show" style="display: none;">

            <span id="alertText"></span>

            <button type="button" class="btn-close" data-bs-dismiss="alert">
            </button>

        </div>
        <div class="modal fade" id="myModal" tabindex="-1">

            <div class="modal-dialog  modal-lg">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Modal Title
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="button" class="btn btn-primary" onclick="saveChangeModal()">
                            Save Changes
                        </button>
                    </div>
                </div>

            </div>

        </div>
        @include('scripts.cs.manage')
    @endsection

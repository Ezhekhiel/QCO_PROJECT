@extends('layouts.main')

@section('content')
    <br>
    <style>
        .form-control {
            text-align: center;
        }

        .size-chart-table th,
        .size-chart-table td {
            min-width: 55px;
            font-weight: 600;
        }

        .size-chart-table th,
        .size-chart-table td {
            vertical-align: middle !important;
        }

        .mark-cell {
            border: 4px solid #6c757d !important;
            font-weight: bold;
        }


        .active-cell {
            border: 2px solid #0d6efd !important;
            background: #eaf3ff !important;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, .25);
        }

        .legend-box {
            width: 22px;
            height: 22px;
            border-radius: 4px;
            display: inline-block;
            border: 1px solid rgba(0, 0, 0, .1);
        }

        .bg-secondary-light {
            background-color: #b8c2cc;
            color: #fff;
        }
    </style>
    <div class="container-fluid">
        <div class="row justify-content-center r-font p-4">

            <ul class="nav nav-tabs" id="formTab" role="tablist">
                @auth
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="input-tab" data-bs-toggle="tab" data-bs-target="#input" type="button"
                            onclick="resetForm('input')">
                            <i class="bi bi-box-arrow-in-right me-2"></i> FORM INPUT
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="output-tab" data-bs-toggle="tab" data-bs-target="#output" type="button"
                            onclick="resetForm('output')">
                            <i class="bi bi-box-arrow-right me-2"></i> FORM OUTPUT
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage" type="button"
                            onclick="main_manage()">
                            <i class="bi bi-kanban me-2"></i> Manage
                        </button>
                    </li>
                @endauth

                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ auth()->guest() ? 'active' : '' }}" id="wip-tab" data-bs-toggle="tab"
                        data-bs-target="#wip" type="button" onclick="main_wip()">
                        <i class="bi bi-list-task me-2"></i> WIP LIST
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="db-tab" data-bs-toggle="tab" data-bs-target="#db" type="button"
                        onclick="main_db()">
                        <i class="bi bi-list-task me-2"></i> Daily Balance
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="io-tab" data-bs-toggle="tab" data-bs-target="#io" type="button"
                        onclick="io_db()">
                        <i class="bi bi-list-task me-2"></i> Balance Input
                    </button>
                </li>
            </ul>

            <div class="tab-content border border-top-0 bg-white p-4 shadow-sm rounded-bottom" id="stitchingTabContent">
                @auth
                    @include('cs.daily_produksi.tabs.input')
                    @include('cs.daily_produksi.tabs.output')
                    @include('cs.daily_produksi.tabs.manage')
                @endauth
                @include('cs.daily_produksi.tabs.wip')
                @include('cs.daily_produksi.tabs.daily_balance')
                @include('cs.daily_produksi.tabs.io')
            </div>
        </div>
    </div>

    <div id="alertMessage" class="alert alert-dismissible fade show" style="display: none;">

        <span id="alertText"></span>

        <button type="button" class="btn-close" data-bs-dismiss="alert">
        </button>

    </div>
    <!-- LOADING -->
    <div id="table-loading" class="d-none position-fixed top-0 start-0 w-100 h-100"
        style="
        background: rgba(255,255,255,0.7);
        z-index: 9999;
        backdrop-filter: blur(2px);
    ">

        <div class="d-flex justify-content-center align-items-center h-100">

            <div class="text-center">

                <div class="spinner-border text-primary" style="width:4rem;height:4rem;">
                </div>

                <div class="mt-3 fw-bold">
                    Mohon Tunggu ...
                </div>

            </div>

        </div>

    </div>
    @include('scripts.cs.produksi')
@endsection

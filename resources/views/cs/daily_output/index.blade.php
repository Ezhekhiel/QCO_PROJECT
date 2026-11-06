@extends('layouts.main')
@section('content')
    <style>
        body {
            background: #f4f6f9;
            font-size: 12px;
        }

        .shift-card {
            border-radius: 20px;
            border: none;
            overflow: hidden;
        }

        .shift-icon {
            width: 65px;
            height: 65px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
        }

        .table-custom {
            width: 100%;
            white-space: nowrap;
        }

        .table-custom th,
        .table-custom td {
            padding: 2px !important;
            font-size: 11px;
            vertical-align: middle;
        }

        .table-custom thead th {
            background: #001f63;
            color: white;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #26427f;
        }

        .table-custom thead tr:first-child th {
            position: sticky;
            top: 0;
            z-index: 101;
        }

        .table-custom thead tr:nth-child(2) th {
            position: sticky;
            top: 22px;
            /* sesuaikan */
            z-index: 100;
        }

        .table-custom tbody td {
            text-align: center;
            font-size: 13px;
        }

        .bg-target {
            background: yellow !important;
            color: black !important;
        }

        .bg-downtime {
            background: red !important;
            color: white !important;
        }

        .achieve-good {
            background: #00c853;
            color: white;
            font-weight: bold;
        }

        .achieve-warning {
            background: #ffd600;
            color: black;
            font-weight: bold;
        }

        .achieve-danger {
            background: #ff1744;
            color: white;
            font-weight: bold;
        }

        .mini-text {
            font-size: 9px;
        }

        .shift-button {
            cursor: pointer;
            transition: 0.3s;
            border: 3px solid transparent;
        }

        .shift-button:hover {
            transform: translateY(-3px);
        }

        .active-shift {
            border: 3px solid #0d6efd;
            box-shadow: 0 0 15px rgba(13, 110, 253, 0.3);
        }

        .custom-date {
            border-radius: 14px;
            border: 1px solid #dfe6e9;
            padding: 10px 14px;
            transition: 0.3s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .custom-date:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .15);
        }

        .mytr {
            position: relative;
        }

        .table-custom td {
            text-align: center;
            vertical-align: middle;
        }

        /* Buat select2 mengikuti lebar pembungkusnya */
        .select2-container {
            width: 100% !important;
        }

        .select2-selection--single {
            height: 28px !important;
            font-size: 11px !important;
        }

        .select2-selection__rendered {
            line-height: 26px !important;
        }

        .select2-selection__arrow {
            height: 28px !important;
        }

        /* --- ATUR LEBAR KOLOM TABEL --- */
        .kolom-operator {
            width: 200px;
        }

        .kolom-model {
            width: 400px;
        }

        /* Potong lebar Cell dan Model menjadi setengah dari 250px (masing-masing 125px) */
        .kolom-setengah {
            width: 125px;
        }

        .table-scroll {
            max-height: 800px;
            overflow-y: auto;
        }

        .target-input {
            width: 80px !important;
            min-width: 80px !important;
            margin: auto !important;
            text-align: center;
        }

        /* Chrome, Edge, Safari */
        .target-input::-webkit-outer-spin-button,
        .target-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        .target-input[type=number] {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        table td.kolom-input-padat {
            padding: 0 !important;
            vertical-align: middle;
        }

        /* Buat input memenuhi seluruh area TD tanpa ada border ganda atau sisa jarak */
        .kolom-input-padat .target-input {
            width: 100% !important;
            height: 100% !important;
            min-height: 35px;
            /* Sesuaikan tinggi yang Anda inginkan */
            border: none;
            /* Menghilangkan border input bawaan agar menyatu dengan border TD */
            border-radius: 0 !important;
            /* Menghilangkan lengkungan sudut biar kotak sempurna */
            margin: 0 !important;
            padding: 6px;
            /* Padding di dalam input saja agar teks angka tidak terlalu mepet ke garis */
            text-align: center;
            /* Opsional: membuat angka '0' berada di tengah */
        }

        /* Efek opsional: memberi warna border saat input sedang di-klik/fokus */
        .kolom-input-padat .target-input:focus {
            box-shadow: inset 0 0 0 2px #007bff;
            /* Efek highlight biru di dalam kotak */
            outline: none;
        }

        .table td.kolom-input-padat {
            padding: 0 !important;
            vertical-align: middle;

            /* Tambahkan ini untuk mengecilkan lebar kolom */
            width: 100px !important;
            /* Silakan ubah angkanya (misal: 40px, 50px, atau 60px) */
            max-width: 100px !important;
            /* Mengunci agar kolom tidak melar */
        }

        .td-cal {
            width: 100px !important;
            max-width: 100px !important;
            min-width: 100px !important;
            /* Tambahkan ini agar tidak bisa menyusut di tablet */

            /* Tambahan pengaman agar teks tidak merusak layout */
            white-space: nowrap;
            /* Mencegah teks turun ke bawah secara berantakan */
            overflow: hidden;
            /* Sembunyikan teks yang meluber jika terlalu panjang */
            text-overflow: ellipsis;
            /* Berikan efek titik-titik (...) jika teks terpotong */
            text-align: center;
            /* Opsional: teks rata tengah agar rapi */
        }
    </style>
    <div class="app-content-header">
        <div class="container-fluid py-3">
            <button type="button" class="btn btn-primary mb-2" id="btnFilter">
                <i class="bi bi-chevron-down"></i>
            </button>

            <div id="filterArea" style="display:none;" class="mb-2">
                <div class="card card-body">

                    <!-- SHIFT CARD -->
                    <div class="row g-3 mb-3">

                        <!-- SHIFT 1 -->
                        <div class="col-lg-6">
                            <div class="card shift-card shadow-sm shift-button active-shift" data-shift="shift1"
                                id="btnShift1">

                                <div class="card-body">

                                    <div class="row align-items-center">

                                        <div class="col-auto">
                                            <div class="shift-icon bg-primary">
                                                <i class="bi bi-sunrise"></i>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <h3 class="fw-bold text-primary mb-1">
                                                SHIFT (1) PAGI
                                            </h3>

                                            <div class="text-muted fs-5">
                                                07.00 - 18.00
                                            </div>

                                            <div class="mt-1 text-secondary">
                                                <i class="bi bi-clock me-1"></i>
                                                Durasi : 10 Jam
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- SHIFT 2 -->
                        <div class="col-lg-6">

                            <div class="card shift-card shadow-sm shift-button" data-shift="shift2" id="btnShift2">

                                <div class="card-body">

                                    <div class="row align-items-center">

                                        <div class="col-auto">
                                            <div class="shift-icon bg-success">
                                                <i class="bi bi-moon-stars"></i>
                                            </div>
                                        </div>

                                        <div class="col">

                                            <h3 class="fw-bold text-success mb-1">
                                                SHIFT (2) MALAM
                                            </h3>

                                            <div class="text-muted fs-5">
                                                20.00 - 07.00
                                            </div>

                                            <div class="mt-1 text-secondary">
                                                <i class="bi bi-clock me-1"></i>
                                                Durasi : 10 Jam
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="my-3 card p-2">

                        <div class="card-header">
                            <h5>Pilih Tanggal</h5>
                        </div>

                        <div class="mt-2 input-group shadow-sm">
                            <span class="input-group-text bg-primary text-white border-0">
                                <i class="bi bi-calendar-event"></i>
                            </span>

                            <input type="date" name="date" id="search_date" style="font-size:200%;"
                                class="form-control custom-date" value="{{ now()->format('Y-m-d') }}">
                        </div>

                    </div>

                </div>
            </div>

            <!-- TABLE SHIFT 1 -->
            <div id="tableShift1">

                <div class="card shadow-sm border-0 mb-3">

                    <div class="card-header bg-primary text-white fw-bold">
                        SHIFT 1
                    </div>

                    <div class="card-body">

                        <!-- TABLE -->


                        <div class="table-responsive table-scroll">

                            <table class="table table-bordered table-hover table-custom align-middle">

                                <thead>

                                    <tr>

                                        <th rowspan="2">LINE CCS</th>
                                        <th rowspan="2">MESIN</th>

                                        <th rowspan="2">IDENTITAS</th>

                                        <th rowspan="2">CELL<br>PRODUKSI</th>
                                        <th rowspan="2">MODEL | PROSES</th>

                                        <th rowspan="2" class="bg-target">
                                            TARGET/<br>JAM
                                        </th>

                                        <th colspan="10">
                                            OUTPUT / JAM
                                        </th>

                                        <th rowspan="2">TOTAL</th>
                                        <th rowspan="2">RATA-RATA</th>
                                        <th rowspan="2">ACHIEVE(%)</th>

                                        <th colspan="3" class="bg-downtime">
                                            DOWNTIME (Minutes)
                                        </th>

                                    </tr>

                                    <tr>
                                        <th>
                                            1
                                            <div class="mini-text">
                                                07.00-08.00
                                            </div>
                                        </th>

                                        <th>
                                            2
                                            <div class="mini-text">
                                                08.00-09.00
                                            </div>
                                        </th>

                                        <th>
                                            3
                                            <div class="mini-text">
                                                09.00-10.00
                                            </div>
                                        </th>

                                        <th>
                                            4
                                            <div class="mini-text">
                                                10.00-11.00
                                            </div>
                                        </th>

                                        <th>
                                            5
                                            <div class="mini-text">
                                                11.00-12.00
                                            </div>
                                        </th>

                                        <th>
                                            6
                                            <div class="mini-text">
                                                13.00-14.00
                                            </div>
                                        </th>

                                        <th>
                                            7
                                            <div class="mini-text">
                                                14.00-15.00
                                            </div>
                                        </th>

                                        <th>
                                            8
                                            <div class="mini-text">
                                                15.00-16.00
                                            </div>
                                        </th>

                                        <th>
                                            9
                                            <div class="mini-text">
                                                16.00-17.00
                                            </div>
                                        </th>

                                        <th>
                                            10
                                            <div class="mini-text">
                                                17.00-18.00
                                            </div>
                                        </th>

                                        <th class="bg-downtime">
                                            Quality
                                        </th>

                                        <th class="bg-downtime">
                                            Mesin
                                        </th>
                                        <th class="bg-downtime">
                                            Material
                                        </th>

                                    </tr>

                                </thead>

                                <tbody id="tbody-shift-1">
                                </tbody>

                            </table>

                        </div>



                    </div>

                </div>

            </div>

            <!-- TABLE SHIFT 2 -->
            <div id="tableShift2" style="display:none;">

                <div class="card shadow-sm border-0">

                    <div class="card-header bg-success text-white fw-bold">
                        SHIFT 2
                    </div>

                    <div class="card-body">
                        <div class="table-responsive table-scroll">

                            <table class="table table-bordered table-hover table-custom align-middle">

                                <thead>

                                    <tr>

                                        <th rowspan="2">LINE CCS</th>
                                        <th rowspan="2">MESIN</th>

                                        <th rowspan="2">IDENTITAS</th>

                                        <th rowspan="2">CELL<br>PRODUKSI</th>
                                        <th rowspan="2">MODEL | PROSES</th>

                                        <th rowspan="2" class="bg-target">
                                            TARGET/<br>JAM
                                        </th>

                                        <th colspan="10">
                                            OUTPUT / JAM
                                        </th>

                                        <th rowspan="2">TOTAL</th>
                                        <th rowspan="2">RATA-RATA</th>
                                        <th rowspan="2">ACHIEVE(%)</th>

                                        <th colspan="3" class="bg-downtime">
                                            DOWNTIME (Minutes)
                                        </th>

                                    </tr>

                                    <tr>


                                        <th>
                                            1
                                            <div class="mini-text">
                                                20.00-21.00
                                            </div>
                                        </th>

                                        <th>
                                            2
                                            <div class="mini-text">
                                                21.00-22.00
                                            </div>
                                        </th>

                                        <th>
                                            3
                                            <div class="mini-text">
                                                22.00-23.00
                                            </div>
                                        </th>

                                        <th>
                                            4
                                            <div class="mini-text">
                                                23.00-24.00
                                            </div>
                                        </th>

                                        <th>
                                            5
                                            <div class="mini-text">
                                                01.00-02.00
                                            </div>
                                        </th>

                                        <th>
                                            6
                                            <div class="mini-text">
                                                02.00-03.00
                                            </div>
                                        </th>

                                        <th>
                                            7
                                            <div class="mini-text">
                                                03.00-04.00
                                            </div>
                                        </th>

                                        <th>
                                            8
                                            <div class="mini-text">
                                                04.00-05.00
                                            </div>
                                        </th>

                                        <th>
                                            9
                                            <div class="mini-text">
                                                05.00-06.00
                                            </div>
                                        </th>

                                        <th>
                                            10
                                            <div class="mini-text">
                                                06.00-07.00
                                            </div>
                                        </th>

                                        <th class="bg-downtime">
                                            Quality
                                        </th>

                                        <th class="bg-downtime">
                                            Mesin
                                        </th>
                                        <th class="bg-downtime">
                                            Material
                                        </th>

                                    </tr>

                                </thead>

                                <tbody id="tbody-shift-2">
                                </tbody>

                            </table>

                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!--end::App Content Header-->
        <div id="alertMessage" class="alert alert-dismissible fade show" style="display: none;font-size:24px">

            <span id="alertText"></span>

            <button type="button" class="btn-close" data-bs-dismiss="alert">
            </button>

        </div>
        <div class="modal fade" id="myModal" tabindex="-1">

            <div class="modal-dialog">

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
        <span id="sync-status" class="badge bg-success fs-5"> Tidak Ada Proses </span>
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

        @include('scripts.cs.performa')
    @endsection

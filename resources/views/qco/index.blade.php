@extends('layouts.main')

@section('content')
    <style>
        .card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            /* Membuat sudut card melengkung halus */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            /* Bayangan halus */
        }
    </style>
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0" onclick="triggerReminder()">Change Over (QCO) Schedule</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="card shadow-sm">
                    <!-- Header Card Utama & Nav Tabs (Di luar tab-content agar tetap diam di atas) -->
                    <div
                        class="card-header bg-white d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
                        <!-- Judul di Kiri -->
                        <h3 class="card-title mb-0">Kalender Change Over</h3>

                        <!-- Month Navigation di Tengah -->
                        <div class="btn-group mx-auto">
                            <a href="?month={{ \Carbon\Carbon::parse($currentMonth ?? now())->subMonth()->format('Y-m') }}{{ request('cell') ? '&cell=' . request('cell') : '' }}"
                                class="btn btn-sm btn-outline-secondary">&laquo; Prev</a>
                            <span
                                class="btn btn-sm btn-light disabled fw-bold">{{ \Carbon\Carbon::parse($currentMonth ?? now())->translatedFormat('F Y') }}</span>
                            <a href="?month={{ \Carbon\Carbon::parse($currentMonth ?? now())->addMonth()->format('Y-m') }}{{ request('cell') ? '&cell=' . request('cell') : '' }}"
                                class="btn btn-sm btn-outline-secondary">Next &raquo;</a>
                        </div>

                        <!-- Search Select dengan Kirim Bulan & Cell Sekaligus -->
                        <div class="d-flex align-items-center gap-1">
                            <select name="cell" id="cell" class="form-select form-select-sm"
                                style="min-width: 180px;"
                                onchange="window.location.href='?month={{ \Carbon\Carbon::parse($currentMonth ?? now())->format('Y-m') }}&cell=' + this.value">
                                <option value="">-- Pilih Cell --</option>
                                @foreach ($cell as $a)
                                    <option value="{{ $a->id }}" {{ request('cell') == $a->id ? 'selected' : '' }}>
                                        {{ $a->area }}
                                    </option>
                                @endforeach
                            </select>
                            @if (request('cell'))
                                <a href="?month={{ \Carbon\Carbon::parse($currentMonth ?? now())->format('Y-m') }}"
                                    class="btn btn-sm btn-outline-danger" title="Reset Filter">&times;</a>
                            @endif
                        </div>
                    </div>

                    <!-- Nav Tabs Header -->
                    <div class="card-header bg-light p-2">
                        <ul class="nav nav-tabs card-header-tabs" id="checklistTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active font-weight-bold" id="calendar-tab" data-bs-toggle="tab"
                                    data-bs-target="#calendar-content" type="button" role="tab"
                                    aria-controls="calendar-content" aria-selected="true">
                                    Calendar View
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link font-weight-bold" id="detail-tab" data-bs-toggle="tab"
                                    data-bs-target="#detail-content" type="button" role="tab"
                                    aria-controls="detail-content" aria-selected="false">
                                    Detail Content
                                </button>
                            </li>
                            @auth
                                @if (Auth::user()->department && Auth::user()->department->nama == 'LEAN')
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link font-weight-bold" id="master-tab" data-bs-toggle="tab"
                                            data-bs-target="#master-content" type="button" role="tab"
                                            aria-controls="master-content" aria-selected="false">
                                            Plan Master
                                        </button>
                                    </li>
                                @endif
                            @endauth
                        </ul>
                    </div>

                    <!-- CARD BODY UTAMA YANG MEMBUNGKUS TAB CONTENT -->
                    <div class="card-body">
                        <div class="tab-content" id="checklistTabContent">

                            <!-- TAB 1: CALENDAR VIEW -->
                            <div class="tab-pane fade show active" id="calendar-content" role="tabpanel"
                                aria-labelledby="master-tab">
                                <style>
                                    @import url('https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap');

                                    .calendar-grid {
                                        display: grid;
                                        grid-template-columns: repeat(7, minmax(0, 1fr));
                                        gap: 8px;
                                    }

                                    .calendar-day-header {
                                        text-align: center;
                                        font-weight: 600;
                                        color: #6c757d;
                                        padding-bottom: 8px;
                                        border-bottom: 2px solid #dee2e6;
                                    }

                                    .calendar-cell {
                                        height: 120px;
                                        min-height: 120px;
                                        max-height: 120px;
                                        background-color: #f8f9fa;
                                        border: 1px solid #e9ecef;
                                        border-radius: 6px;
                                        padding: 6px;
                                        position: relative;
                                        overflow-y: auto;
                                        display: flex;
                                        flex-direction: column;
                                    }

                                    .calendar-cell.other-month {
                                        background-color: #ffffff;
                                        opacity: 0.4;
                                    }

                                    .calendar-cell.today {
                                        background-color: #fff3cd;
                                        border-color: #ffeeba;
                                    }

                                    .calendar-cell::-webkit-scrollbar {
                                        width: 4px;
                                    }

                                    .calendar-cell::-webkit-scrollbar-track {
                                        background: transparent;
                                    }

                                    .calendar-cell::-webkit-scrollbar-thumb {
                                        background: #ced4da;
                                        border-radius: 4px;
                                    }

                                    .calendar-cell::-webkit-scrollbar-thumb:hover {
                                        background: #adb5bd;
                                    }

                                    .day-number {
                                        font-size: 0.85rem;
                                        font-weight: bold;
                                        color: #495057;
                                    }

                                    .handwritten-badge {
                                        font-family: 'Caveat', cursive;
                                        font-size: 1.25rem;
                                        line-height: 1.1;
                                        color: #0d6efd;
                                        transform: rotate(-3deg);
                                        display: inline-block;
                                        background: rgba(13, 110, 253, 0.05);
                                        padding: 2px 6px;
                                        border-radius: 4px;
                                        border: 1px dashed rgba(13, 110, 253, 0.3);
                                        margin-top: 2px;
                                        cursor: pointer;
                                    }
                                </style>

                                <div class="calendar-grid mb-2">
                                    <div class="calendar-day-header">Min</div>
                                    <div class="calendar-day-header">Sen</div>
                                    <div class="calendar-day-header">Sel</div>
                                    <div class="calendar-day-header">Rab</div>
                                    <div class="calendar-day-header">Kam</div>
                                    <div class="calendar-day-header">Jum</div>
                                    <div class="calendar-day-header">Sab</div>
                                </div>

                                <div class="calendar-grid">
                                    @php
                                        use Carbon\Carbon;
                                        $targetDate = isset($currentMonth)
                                            ? Carbon::parse($currentMonth)
                                            : Carbon::now();
                                        $startOfMonth = $targetDate->copy()->startOfMonth();
                                        $endOfMonth = $targetDate->copy()->endOfMonth();

                                        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
                                        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

                                        $qcoByDate = isset($qcoList)
                                            ? $qcoList->groupBy(function ($item) {
                                                return Carbon::parse($item->change_over_date)->format('Y-m-d');
                                            })
                                            : collect();
                                    @endphp

                                    @while ($startDate <= $endDate)
                                        @php
                                            $dateString = $startDate->format('Y-m-d');
                                            $isToday = $startDate->isToday();
                                            $isCurrentMonth = $startDate->month === $targetDate->month;
                                            $dayEvents = $qcoByDate->get($dateString, collect());
                                        @endphp

                                        <div
                                            class="calendar-cell {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                                            <div class="day-number">{{ $startDate->format('j') }}</div>

                                            @foreach ($dayEvents as $qco)
                                                @php
                                                    if ($qco->completion == 100) {
                                                        $color_status = 'bg-success text-white';
                                                    } elseif ($qco->completion >= 50) {
                                                        $color_status = 'bg-warning text-dark';
                                                    } else {
                                                        $color_status = 'bg-danger text-white';
                                                    }
                                                @endphp
                                                <div title="Model: {{ $qco->model }} | Cell: {{ $qco->cell }}"
                                                    onclick="openModalQCO('{{ $qco->id }}')"
                                                    class="handwritten-badge shadow-sm {{ $color_status }}">
                                                    {{ $qco->model }}
                                                </div>
                                            @endforeach
                                        </div>

                                        @php $startDate->addDay(); @endphp
                                    @endwhile
                                </div>
                            </div>

                            <!-- TAB 2: DETAIL CONTENT -->
                            <div class="tab-pane fade" id="detail-content" role="tabpanel" aria-labelledby="detail-tab">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped text-center align-middle">
                                        <thead>
                                            <!-- Baris Header 1: Kolom Utama & Pengelompokan Departemen -->
                                            <tr>
                                                <th rowspan="2" class="align-middle">No</th>
                                                <th rowspan="2" class="align-middle">Change Over Date</th>
                                                <th rowspan="2" class="align-middle">Actual Change Over</th>
                                                <th rowspan="2" class="align-middle">Model</th>
                                                <th rowspan="2" class="align-middle">Cell / Area</th>
                                                <th rowspan="2" class="align-middle">Stage</th>
                                                <th rowspan="2" class="align-middle">Status QCO</th>

                                                @foreach ($list->groupBy('department_id') as $deptId => $deptItems)
                                                    @php
                                                        $deptName =
                                                            $deptItems->first()->department->nama ??
                                                            'DEPT ID: ' . $deptId;
                                                    @endphp
                                                    <!-- Border kiri & kanan tebal untuk group department -->
                                                    <th colspan="{{ $deptItems->count() }}"
                                                        style="font-size: 12px; border-left: 2px solid #b8daff; border-right: 2px solid #b8daff;"
                                                        class="text-center bg-light">
                                                        {{ $deptName }}
                                                    </th>
                                                @endforeach
                                            </tr>

                                            <!-- Baris Header 2: Nama-nama List Persiapan Per Departemen -->
                                            <tr>
                                                @foreach ($list as $prepMaster)
                                                    @php
                                                        // Cek apakah item ini adalah awal atau akhir dari sebuah departemen untuk mempertebal garis pembatas antar departemen
                                                        // (Opsional agar pembatas antar departemen ke bawah ikut terlihat jelas)
                                                    @endphp
                                                    <th style="font-size: 11px;" class="text-center">
                                                        {{ $prepMaster->list }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($qcoList as $index => $qco)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $qco->change_over_date }}</td>
                                                    <td>{{ $qco->qco_status?->actual_change_over ?? 'NOT YET' }}</td>
                                                    <td><strong>{{ $qco->model }}</strong></td>
                                                    <td>
                                                        {{ $qco->cell_info->building ?? '-' }} <br>
                                                        <span
                                                            class="badge bg-secondary">{{ $qco->cell_info->area ?? 'Cell ' . $qco->cell }}</span>
                                                    </td>
                                                    <td>{{ $qco->stage }}</td>
                                                    @php
                                                        if ($qco->status == 'CANCEL') {
                                                            $colorBadge = 'danger';
                                                        } elseif ($qco->status == 'DONE') {
                                                            $colorBadge = 'success';
                                                        } else {
                                                            $colorBadge = 'warning';
                                                        }
                                                    @endphp
                                                    <td>
                                                        <span class="badge bg-{{ $colorBadge }}">
                                                            {{ $qco->status }}
                                                        </span>
                                                    </td>

                                                    @foreach ($list as $prepMaster)
                                                        @php
                                                            $matchedPrep = $qco->preparations->firstWhere(
                                                                'id_list',
                                                                $prepMaster->id,
                                                            );
                                                            $hasFile =
                                                                $matchedPrep &&
                                                                $matchedPrep->preparation_file->count() > 0;
                                                        @endphp
                                                        <td>
                                                            @if ($matchedPrep)
                                                                <span class="text-success"
                                                                    title="Ada File / Selesai">✔️</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ 6 + count($list) }}" class="text-center">
                                                        Tidak ada data Change Over pada periode ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 2: PLAN MASTER CONTENT -->
                            @auth
                                @if (Auth::user()->department && Auth::user()->department->nama == 'LEAN')
                                    <div class="tab-pane fade" id="master-content" role="tabpanel"
                                        aria-labelledby="master-tab">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-center align-middle mb-0"
                                                id="planMasterTable" style="font-size: 12px;">
                                                <thead class="table-primary" id="planMasterHeader">
                                                    <!-- Header tanggal akan di-generate via JS -->
                                                </thead>
                                                <tbody id="planMasterBody">
                                                    <tr>
                                                        <td colspan="32" class="text-center py-4 text-muted">Memuat data...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endauth

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalQCO" tabindex="-1" role="dialog" aria-labelledby="modalCustomWidthLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 90%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCustomWidthLabel">Form Preparation List</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Baris Kontrol Atas (Filter & Info Model) -->
                    <div class="row mb-3 align-items-center">
                        <input type="hidden" id="id_qco">
                        <div class="col-md-12">
                            <div class="card bg-light border-0 p-3">
                                <div class="row align-items-center">
                                    <!-- Bagian Kiri: Model Name, Stage, Status (Lebar 9 Kolom) -->
                                    <div class="col-lg-9">
                                        <div class="row align-items-center">
                                            <div class="col-3 border-right">
                                                <span class="small text-muted d-block">MODEL NAME</span>
                                                <strong id="info_model" class="text-dark">U9060</strong>
                                            </div>
                                            <div class="col-3 border-right">
                                                <span class="small text-muted d-block">STAGE</span>
                                                <strong id="info_stage" class="text-dark">Production Trial</strong>
                                            </div>
                                            <div class="col-3 text-center">
                                                <span class="small text-muted d-block">STATUS</span>
                                                <!-- Elemen Badge Status Dinamis -->
                                                <span id="info_status" onclick="updateStatusCancel()"
                                                    class="badge bg-success px-2 py-1 text-white">DONE</span>
                                            </div>
                                            <div class="col-3 border-right">
                                                <span class="small text-muted d-block">Change Over Date</span>
                                                <strong id="change_over_date" class="text-dark"></strong>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $isProduction = false;
                                        $isPlanner = false;
                                        if (
                                            Auth::check() &&
                                            Auth::user()->department &&
                                            Auth::user()->department->nama == 'PRODUCTION'
                                        ) {
                                            $isProduction = true;
                                        }
                                        if (
                                            Auth::check() &&
                                            Auth::user()->department &&
                                            Auth::user()->department->nama == 'PLANNER'
                                        ) {
                                            $isPlanner = true;
                                        }
                                    @endphp

                                    <!-- Bagian Kanan / Kotak Merah: Actual Change Over (Lebar 3 Kolom) -->
                                    @if ($isProduction)
                                        <div class="col-lg-3 mt-3 mt-lg-0 border-left">
                                            <label for="act_co_date" class="small text-muted d-block mb-1">ACTUAL CHANGE
                                                OVER</label>
                                            <div class="input-group input-group-sm" id="act_change_over">

                                                <input type="date" id="act_co_date" class="form-control" />
                                                <div class="input-group-append">
                                                    <button id="btn_save_act_co" class="btn btn-primary btn-sm"
                                                        type="button">
                                                        Save
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($isPlanner)
                                        <div class="col-lg-3 mt-3 mt-lg-0 border-left" id="update_or_cancel">
                                            <label for="act_co_date" class="small text-muted d-block mb-1">UBAH ATAU
                                                BATALKAN</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <button onclick="updateQCO()" class="btn btn-primary btn-sm w-100"
                                                        type="button">UBAH</button>
                                                </div>
                                                <div class="col-6">
                                                    <button onclick="cancelQCO()" class="btn btn-danger btn-sm w-100"
                                                        type="button">BATALKAN</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Dashboard Konten di Bawah Search Cell -->
                    <div class="row">
                        <!-- Kolom Kiri: Tabel Master Checklist Item (Department & Checklist) -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-primary text-white py-2 text-center font-weight-bold">
                                    CHECKLIST ITEM
                                </div>
                                <div class="card-body p-0" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                                    <table class="table table-bordered table-sm mb-0 text-center"
                                        style="font-size: 0.85rem;">
                                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>
                                                <th style="width: 35%;">DEPARTEMENT</th>
                                                <th style="width: 45%;">CHECKLIST</th>
                                                <th style="width: 20%;">File</th>
                                            </tr>
                                        </thead>
                                        <tbody id="master_checklist_tbody">
                                            <tr>
                                                <td rowspan="2" class="align-middle font-weight-bold bg-white">PLANNER
                                                </td>
                                                <td>Schedule</td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td>Material Readiness</td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td rowspan="5" class="align-middle font-weight-bold bg-white">PPIC
                                                    TOOLING</td>
                                                <td>Bottom Tools</td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td>Upper Tools</td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td>Pallet Comp Stitching</td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td>Laste</td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td>Cutting Dies</td>
                                                <td>-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Tengah: QCO Preparation Summary & Detail Per Departemen -->
                        <div class="col-md-4">
                            <!-- Ringkasan Persentase -->
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-secondary text-white py-2 text-center font-weight-bold">
                                    QCO Preparation Summary
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-striped table-bordered table-sm mb-0 text-center"
                                        style="font-size: 0.85rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>DEPARTEMENT</th>
                                                <th>PREPARE PERCENTAGE</th>
                                            </tr>
                                        </thead>
                                        <tbody id="summary_preparation_tbody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Quick Statistics Card -->
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light font-weight-bold text-secondary text-center py-2">
                                    QUICK STATISTICS
                                </div>
                                <div class="card-body p-3">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="p-2 border rounded bg-white shadow-sm">
                                                <span class="small text-muted d-block font-weight-bold">TOTAL ITEMS</span>
                                                <h4 class="font-weight-bold mb-0 text-dark" id="stat_total">0</h4>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="p-2 border rounded bg-white shadow-sm">
                                                <span class="small text-muted d-block font-weight-bold">COMPLETED</span>
                                                <h4 class="font-weight-bold mb-0 text-success" id="stat_completed">0</h4>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 border rounded bg-white shadow-sm">
                                                <span class="small text-muted d-block font-weight-bold">REMAINING</span>
                                                <h4 class="font-weight-bold mb-0 text-danger" id="stat_remaining">0</h4>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 border rounded bg-white shadow-sm">
                                                <span class="small text-muted d-block font-weight-bold">TARGET DATE</span>
                                                <h6 class="font-weight-bold mb-0 text-primary mt-2" id="stat_date">-</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Chart Area (Placeholder untuk Grafik Donut & Bar Chart) -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light text-center font-weight-bold text-secondary">
                                    Overall Changeover Readiness
                                </div>
                                <div class="card-body text-center py-4">
                                    <!-- Ubah <canvas> menjadi <div> -->
                                    <div id="qcoDonutChart" style="min-height: 180px;"></div>
                                </div>
                            </div>

                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light text-center font-weight-bold text-secondary">
                                    QCO Readiness by Department
                                </div>
                                <div class="card-body">
                                    <!-- Tempat ApexCharts Bar Chart -->
                                    <div id="qcoBarChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ubah QCO -->
    <div class="modal fade" id="modalUbahQCO" tabindex="-1" aria-labelledby="modalUbahQCOLimit" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formUbahQCO">
                    <input type="hidden" name="id_qco_update" id="id_qco_update">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUbahQCOLimit">Ubah Data QCO</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Token CSRF -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <!-- Hidden ID untuk menyimpan ID QCO yang sedang diedit -->
                        <div class="mb-3">
                            <label for="edit_nama_field" class="form-label">Model</label>
                            <input type="text" class="form-control" id="edit_model" name="edit_model" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_completion" class="form-label">Change Over Date</label>
                            <input type="date" class="form-control" id="edit_date" name="edit_date" min="0"
                                max="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateQCO">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('script.qco.index')
@endsection

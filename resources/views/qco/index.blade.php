@extends('layouts.main')

@section('content')
    <style>
        .qco-row {
            cursor: pointer;
            transition: background-color 0.2s ease, opacity 0.2s ease, filter 0.2s ease;
        }

        /* Baris yang sedang difokuskan / diklik (kuning) */
        .qco-row.is-focused td {
            background-color: #ffe69c !important;
            border-top: 2px solid #e0a800;
            border-bottom: 2px solid #e0a800;
            color: #111827;
        }

        /* Baris lain yang berada dalam satu rantai parent_id / reschedule (biru muda) */
        .qco-row.is-related td {
            background-color: #cfe2ff !important;
            border-top: 1px dashed #3b82f6;
            border-bottom: 1px dashed #3b82f6;
            color: #111827;
        }

        /* Baris yang TIDAK terpilih: abu-abu sedang (tidak terlalu gelap) */
        tbody.has-selection .qco-row:not(.is-focused):not(.is-related) td {
            background-color: #f4f4f4 !important;
            /* abu-abu sedang */
            color: #cfcfcf !important;
            /* teks abu tua agar tetap terbaca */
            border-color: #c8c8c8 !important;
        }

        /* Sedikit redamkan badge & ikon di baris abu-abu agar tidak mencolok */
        tbody.has-selection .qco-row:not(.is-focused):not(.is-related) .badge,
        tbody.has-selection .qco-row:not(.is-focused):not(.is-related) span,
        tbody.has-selection .qco-row:not(.is-focused):not(.is-related) small {
            opacity: 0.65;
        }

        .chain-badge {
            font-size: 0.72rem;
            display: inline-block;
            margin-top: 3px;
        }
    </style>
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Change Over (QCO) Schedule</h3>
                </div>
            </div>
        </div>
    </div>
    @php
        $isProduction = false;
        $isPlanner = false;
        if (Auth::check() && Auth::user()->department && Auth::user()->department->nama == 'PRODUCTION') {
            $isProduction = true;
        }
        if (Auth::check() && Auth::user()->department && Auth::user()->department->nama == 'PLANNER') {
            $isPlanner = true;
        }
    @endphp

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="card shadow-sm">
                    <!-- Header Card Utama & Nav Tabs (Di luar tab-content agar tetap diam di atas) -->
                    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
                        <!-- Judul di Kiri -->
                        <h3 class="card-title mb-0">Kalender Change Over</h3>

                        <!-- Month Navigation di Tengah -->
                        <div class="btn-group mx-auto">
                            <a href="{{ request()->fullUrlWithQuery(['month' => \Carbon\Carbon::parse($currentMonth ?? now())->subMonth()->format('Y-m')]) }}"
                                class="btn btn-sm btn-outline-secondary">&laquo; Prev</a>
                            <span
                                class="btn btn-sm btn-light disabled fw-bold">{{ \Carbon\Carbon::parse($currentMonth ?? now())->translatedFormat('F Y') }}</span>
                            <a href="{{ request()->fullUrlWithQuery(['month' => \Carbon\Carbon::parse($currentMonth ?? now())->addMonth()->format('Y-m')]) }}"
                                class="btn btn-sm btn-outline-secondary">Next &raquo;</a>
                        </div>

                        <!-- Search Select dengan Kirim Bulan & Cell Sekaligus -->
                        <form action="" method="GET" class="d-flex align-items-center gap-2">
                            <!-- Pertahankan parameter month -->
                            <input type="hidden" name="month"
                                value="{{ \Carbon\Carbon::parse($currentMonth ?? now())->format('Y-m') }}">

                            <!-- Dropdown Container -->
                            <div class="dropdown">
                                @php
                                    $selectedCells = (array) request('cell', []);
                                    $countSelected = count($selectedCells);
                                @endphp

                                <!-- Tombol Toggle Dropdown -->
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="cellDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    <i class="fas fa-filter"></i> Pilih Cell
                                    @if ($countSelected > 0)
                                        <span class="badge bg-primary rounded-pill">{{ $countSelected }}</span>
                                    @endif
                                </button>

                                <!-- Menu Dropdown berisi Checkbox -->
                                <div class="dropdown-menu p-3 shadow" aria-labelledby="cellDropdown"
                                    style="min-width: 220px; max-height: 250px; overflow-y: auto;">
                                    <div class="fw-bold small text-muted mb-2">Daftar Cell</div>

                                    @foreach ($cell as $a)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="cell[]"
                                                value="{{ $a->id }}" id="cell_{{ $a->id }}"
                                                {{ in_array($a->id, $selectedCells) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="cell_{{ $a->id }}">
                                                {{ $a->area }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Tombol Search untuk mengeksekusi filter -->
                            <button type="submit" class="btn btn-sm btn-primary">
                                Cari
                            </button>

                            <!-- Tombol Reset Filter jika ada yang terpilih -->
                            @if ($countSelected > 0)
                                <a href="?month={{ \Carbon\Carbon::parse($currentMonth ?? now())->format('Y-m') }}"
                                    class="btn btn-sm btn-outline-danger" title="Reset Filter">&times;</a>
                            @endif
                        </form>
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

                                            <!-- Container pembungkus untuk membatasi tinggi maksimal 2 komponen dan mengaktifkan scroll -->
                                            <div class="event-list-container"
                                                style="max-height: 85px; overflow-y: auto; overflow-x: hidden;">
                                                @foreach ($dayEvents as $qco)
                                                    @php
                                                        // Ambil nama area cell jika relasinya ada (asumsi relasi cell_info ada)
                                                        $cellName = $qco->cell_info->area ?? 'Cell ' . $qco->cell;

                                                        if ($qco->status === 'CANCEL') {
                                                            // Badge untuk jadwal lama yang digeser/dibatalkan
                                                            $color_status =
                                                                'bg-light text-muted border border-secondary text-decoration-line-through';
                                                            // Tampilkan info jelas model & cell-nya, ditambah teks pindah
                                                            $badgeContent = "<small class='d-block' style='font-size: 0.7rem;'>[{$cellName}]</small> {$qco->model} ➔ Direschedule";
                                                        } else {
                                                            // Warna normal berdasarkan completion
                                                            if ($qco->completion == 100) {
                                                                $color_status = 'bg-success text-white';
                                                            } elseif ($qco->completion >= 50) {
                                                                $color_status = 'bg-warning text-dark';
                                                            } else {
                                                                $color_status = 'bg-danger text-white';
                                                            }

                                                            $badgeContent = "<small class='d-block' style='font-size: 0.7rem;'>[{$cellName}]</small> {$qco->model}";

                                                            // Jika ini data hasil reschedule, tambahkan indikator
                                                            if ($qco->status_plan === 'rescheduled') {
                                                                $badgeContent .= ' 🔄';
                                                            }
                                                        }
                                                    @endphp

                                                    <div title="Model: {{ $qco->model }} | Cell: {{ $cellName }} | Status: {{ $qco->status }}"
                                                        onclick="openModalQCO('{{ $qco->id }}')"
                                                        class="p-1 mb-1 rounded shadow-sm cursor-pointer {{ $color_status }}"
                                                        style="font-size: 0.85rem; line-height: 1.2; text-align: left;">
                                                        {!! $badgeContent !!}
                                                    </div>
                                                @endforeach
                                            </div>
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
                                        <div class="mb-2 d-flex justify-content-between align-items-center"
                                            id="qco-chain-toolbar" style="display: none !important;">
                                            <div id="qco-chain-info" class="small text-primary fw-semibold"></div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                id="clear-qco-selection">
                                                Reset Pilihan
                                            </button>
                                        </div>

                                        <tbody>
                                            @php
                                                // Peta cepat untuk mengecek apakah parent_id ada di $qcoList yang tampil sekarang
                                                $qcoById = $qcoList->keyBy('id');
                                                // Cari ID mana saja yang menjadi parent dari baris lain
                                                $parentIdsSet = $qcoList->pluck('parent_id')->filter()->flip();
                                            @endphp

                                            @forelse($qcoList as $index => $qco)
                                                @php
                                                    $hasParent = !is_null($qco->parent_id);
                                                    $isParentOfOthers = $parentIdsSet->has($qco->id);
                                                    $parentExistsInView = $hasParent && $qcoById->has($qco->parent_id);
                                                @endphp

                                                <tr class="qco-row" data-id="{{ $qco->id }}"
                                                    data-parent-id="{{ $qco->parent_id ?? '' }}"
                                                    data-model="{{ $qco->model }}"
                                                    data-date="{{ $qco->change_over_date }}"
                                                    data-status="{{ $qco->status }}">

                                                    <td>
                                                        <div>{{ $index + 1 }}</div>
                                                    </td>

                                                    <td>
                                                        <div>{{ $qco->change_over_date }}</div>
                                                        @if ($hasParent)
                                                            <span class="badge bg-info text-dark chain-badge"
                                                                title="{{ $parentExistsInView ? 'Terhubung ke QCO #' . $qco->parent_id . ' (' . $qcoById[$qco->parent_id]->change_over_date . ')' : 'Parent QCO #' . $qco->parent_id . ' (di luar filter/bulan ini)' }}">
                                                                ↳ Dari
                                                                @if ($parentExistsInView)
                                                                    ({{ $qcoById[$qco->parent_id]->change_over_date }})
                                                                @endif
                                                            </span>
                                                        @elseif ($isParentOfOthers)
                                                            <span class="badge bg-light text-dark border chain-badge">
                                                                Awal Jadwal
                                                            </span>
                                                        @endif
                                                    </td>

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
                                                        @if (!empty($qco->status_plan) && $qco->status_plan !== 'plan')
                                                            <div><small
                                                                    class="text-muted fst-italic">{{ $qco->status_plan }}</small>
                                                            </div>
                                                        @endif
                                                    </td>

                                                    @foreach ($list as $prepMaster)
                                                        @php
                                                            $matchedPrep = $qco->preparations->firstWhere(
                                                                'id_list',
                                                                $prepMaster->id,
                                                            );
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
                                                    <td colspan="{{ 7 + count($list) }}" class="text-center">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                            <label for="act_co_date" class="small text-muted d-block mb-1">Update
                                                QCO</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <button onclick="updateQCO()" class="btn btn-primary btn-sm w-100"
                                                        type="button">Re-Schedule</button>
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
                                                <th>DEPARTEMENT</th>
                                                <th>CHECKLIST</th>
                                                <th>File</th>
                                                <th>Validation</th>
                                            </tr>
                                        </thead>
                                        <tbody id="master_checklist_tbody">

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
                <div class="modal-footer justify-content-between">
                    <!-- Teks panjang di pojok kiri -->
                    <div class="text-start" id="description-qco" style="font-size: 1.2 rem; max-width: 70%;">
                    </div>

                    <!-- Tombol di pojok kanan -->
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
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
                        <h5 class="modal-title" id="modalUbahQCOLimit">Re-Schedule Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Token CSRF -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <!-- Hidden ID untuk menyimpan ID QCO yang sedang diedit -->
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

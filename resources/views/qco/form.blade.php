@extends('layouts.main')

@section('content')
    <style>
        /* Default untuk Mobile */
        .modal-custom-size {
            max-width: 100% !important;
            width: 95% !important;
            margin: 10px auto;
        }

        /* Untuk Tablet ke atas / PC */
        @media (min-width: 768px) {
            .modal-custom-size {
                max-width: 700px !important;
                width: 100% !important;
            }
        }

        .hover-effect:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .25) !important;
            filter: brightness(95%);
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

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                @php
                    $isPlanner = false;
                    if (Auth::check() && Auth::user()->department && Auth::user()->department->nama == 'PLANNER') {
                        $isPlanner = true;
                    }
                    $col = $isPlanner ? '8' : '12';
                @endphp

                {{-- Kolom Form Input (Hanya untuk PLANNER) --}}
                @if ($isPlanner)
                    <div class="col-lg-4 mb-4">
                        <div class="card card-primary card-outline shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">Form Input QCO</h3>
                            </div>
                            <form id="input_cso" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="nik" class="form-label">User</label>
                                        <input type="hidden" name="nik" value="{{ Auth::user()->nik }}" required>
                                        <input type="text" class="form-control @error('nik') is-invalid @enderror"
                                            id="nama" name="nama"
                                            value="{{ Auth::user()->name }} ({{ Auth::user()->nik }})" required readonly>
                                        @error('nik')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="change_over_date" class="form-label">Plan CO</label>
                                        <select name="plan" id="plan_id"
                                            class="form-control  @error('change_over_date') @enderror" required>
                                            <option value="plan">PLAN</option>
                                            <option value="unplan">UNPLAN</option>
                                        </select>
                                        @error('change_over_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="change_over_date" class="form-label">Change Over Date</label>
                                        <input type="date"
                                            class="form-control @error('change_over_date') is-invalid @enderror"
                                            id="change_over_date" name="change_over_date"
                                            value="{{ old('change_over_date') }}" required>
                                        @error('change_over_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" class="form-control @error('model') is-invalid @enderror"
                                            id="model" name="model" value="{{ old('model') }}" required
                                            placeholder="Nama Model">
                                        @error('model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="cell" class="form-label">Cell</label>
                                        <select name="cell" id="cell"
                                            class="form-control @error('cell') is-invalid @enderror">
                                            <option value="">Select Cell</option>
                                            @foreach ($cell as $a)
                                                <option value="{{ $a->id }}">{{ $a->area }}</option>
                                            @endforeach
                                        </select>
                                        @error('cell')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="stage" class="form-label">Stage</label>
                                        <select name="stage" id="stage"
                                            class="form-control @error('stage') is-invalid @enderror">
                                            <option value="">Select Stage</option>
                                            <option value="Production Trial">Production Trial</option>
                                            <option value="Mass Production">Mass Production</option>
                                        </select>
                                        @error('stage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                            rows="2" placeholder="Harus input Description">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input @error('no_preparation') is-invalid @enderror"
                                                type="checkbox" id="no_preparation" name="no_preparation" value="1"
                                                {{ old('no_preparation') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="no_preparation">
                                                Tidak perlu preparation
                                            </label>
                                            @error('no_preparation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary w-100">Simpan Data QCO</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Kolom Tampilan Kalender --}}
                <div class="col-lg-{{ $col }}">
                    <div class="card shadow-sm">
                        <div
                            class="card-header bg-white d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
                            <!-- Judul di Kiri -->
                            <h3 class="card-title mb-0">Kalender Change Over</h3>

                            <!-- Month Navigation di Tengah -->
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

                        <div class="card-body">
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
                                    min-height: 100px;
                                    background-color: #f8f9fa;
                                    border: 1px solid #e9ecef;
                                    border-radius: 6px;
                                    padding: 6px;
                                    position: relative;
                                    overflow: hidden;
                                }

                                .calendar-cell.other-month {
                                    background-color: #ffffff;
                                    opacity: 0.4;
                                }

                                .calendar-cell.today {
                                    background-color: #fff3cd;
                                    border-color: #ffeeba;
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
                                    $targetDate = isset($currentMonth) ? Carbon::parse($currentMonth) : Carbon::now();
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

                                        <!-- Bungkus event dengan container untuk membatasi tinggi dan mengaktifkan scroll -->
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
                                                        $badgeContent = "<small class='d-block' style='font-size: 0.7rem;'>[{$cellName}]</small> {$qco->model} ➔ Direschedule";
                                                    } else {
                                                        // Warna normal berdasarkan completion
                                                        $completionValue = $qco->percent_checklist ?? 0;

                                                        if ($completionValue == 100) {
                                                            $color_status = 'bg-success text-white';
                                                        } elseif ($completionValue >= 50) {
                                                            $color_status = 'bg-warning text-dark';
                                                        } else {
                                                            $color_status = 'bg-danger text-white';
                                                        }

                                                        $badgeContent = "<small class='d-block' style='font-size: 0.7rem;'>[{$cellName}]</small> {$qco->model}";

                                                        // Jika ini data hasil reschedule, tambahkan indikator
                                                        if (
                                                            isset($qco->status_plan) &&
                                                            $qco->status_plan === 'rescheduled'
                                                        ) {
                                                            $badgeContent .= ' 🔄';
                                                        }
                                                    }
                                                @endphp

                                                <div title="Model: {{ $qco->model }} | Cell: {{ $cellName }} | Status: {{ $qco->status ?? 'Normal' }}"
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
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalQCO" tabindex="-1" role="dialog" aria-labelledby="modalCustomWidthLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-custom-size" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCustomWidthLabel">Form Preparation List</h5>
                    <!-- Ubah data-dismiss menjadi data-bs-dismiss -->
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formQCO" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id_qco" id="id_qco" value="">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold mb-2" id="label_modal_checkbox">Pilih Preparation List & Upload
                                File:</label>
                            <div id="preparation_list" class="d-flex flex-column gap-3">
                                <!-- Render via jQuery -->
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3" id="btnSubmit">Simpan Data</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <!-- Ubah data-dismiss menjadi data-bs-dismiss -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @include('script.qco.form')
@endsection

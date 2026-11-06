@extends('layouts.main')
@section('content')
    <style>
        .size-group {
            margin-bottom: 20px;
        }

        .size-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 4px;
        }

        .cells {
            flex: 1;
            /* ambil semua sisa ruang */
            display: flex;
            gap: 2px;
        }

        .cell {
            flex: 1;
            /* semua kotak sama lebar */
            height: clamp(60px, 4vh, 60px);

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;
            color: white;
        }

        .size-no {
            width: 40px;
            text-align: center;
            font-weight: 600;
            margin-left: 5px;
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
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-2 my-3">
                        <div class="card p-2">
                            <label class="mb-2 fw-semibold">Pilih Tanggal</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-primary text-white border-0">
                                    <i class="bi bi-calendar-event"></i>
                                </span>

                                <input type="date" name="date" id="search_date" class="form-control custom-date"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-2 my-3">
                        <div class="card p-2">
                            <label class="mb-2 fw-semibold">Pilih Shift</label>

                            <div class="btn-group w-100">

                                <input type="radio" class="btn-check" name="shift" id="shift1" value="shift-1"
                                    checked>
                                <label class="btn btn-outline-primary shift1" for="shift1">
                                    Shift 1
                                </label>

                                <input type="radio" class="btn-check" name="shift" id="shift2" value="shift-2">
                                <label class="btn btn-outline-success shift2" for="shift2">
                                    Shift 2
                                </label>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-4">
                    <div class="row d-none" id="viewCCS">
                        <!-- LEFT SIDE -->
                        <div class="col-md-6">
                            @php
                                $no = 21;
                            @endphp
                            @for ($i = 0; $i < 5; $i++)
                                <div class="size-group">
                                    @for ($j = $i; $j < $i + 2; $j++)
                                        @php
                                            $no_urut = $no - $j - $i;
                                        @endphp
                                        <div class="size-row">
                                            <div class="cells">
                                                <div id="{{ $no_urut . '-J' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-J' }}</div>
                                                <div id="{{ $no_urut . '-I' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-I' }}</div>
                                                <div id="{{ $no_urut . '-H' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-H' }}</div>
                                                <div id="{{ $no_urut . '-G' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-G' }}</div>
                                                <div id="{{ $no_urut . '-F' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-F' }}</div>
                                                <div id="{{ $no_urut . '-E' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-E' }}</div>
                                                <div id="{{ $no_urut . '-D' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-D' }}</div>
                                                <div id="{{ $no_urut . '-C' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-C' }}</div>
                                                <div id="{{ $no_urut . '-B' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-B' }}</div>
                                                <div id="{{ $no_urut . '-A' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-A' }}</div>
                                            </div>
                                            <div class="size-no">{{ $no_urut }}</div>
                                        </div>
                                    @endfor
                                </div>
                            @endfor
                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-md-6">

                            <div class="size-group">
                                <div class="size-row">
                                    <div class="cells">
                                        <div id="11-J"data-bs-toggle="tooltip" data-bs-placement="top" class="cell">
                                            11-J
                                        </div>
                                        <div id="11-I"data-bs-toggle="tooltip" data-bs-placement="top" class="cell">
                                            11-I
                                        </div>
                                        <div id="11-H"data-bs-toggle="tooltip" data-bs-placement="top" class="cell">
                                            11-H
                                        </div>
                                        <div id="11-G"data-bs-toggle="tooltip" data-bs-placement="top" class="cell">
                                            11-G
                                        </div>
                                        <div id="11-F"data-bs-toggle="tooltip" data-bs-placement="top" class="cell">
                                            11-F
                                        </div>
                                        <div id="11-E"data-bs-toggle="tooltip" data-bs-placement="top"
                                            class="cell">11-E
                                        </div>
                                        <div id="11-D"data-bs-toggle="tooltip" data-bs-placement="top"
                                            class="cell">11-D
                                        </div>
                                        <div id="11-C"data-bs-toggle="tooltip" data-bs-placement="top"
                                            class="cell">11-C
                                        </div>
                                        <div id="11-B"data-bs-toggle="tooltip" data-bs-placement="top"
                                            class="cell">11-B
                                        </div>
                                        <div id="11-A"data-bs-toggle="tooltip" data-bs-placement="top"
                                            class="cell">11-A
                                        </div>
                                    </div>
                                    <div class="size-no">11</div>
                                </div>
                            </div>
                            @php
                                $no = 10;
                            @endphp
                            @for ($i = 0; $i < 5; $i++)
                                <div class="size-group">
                                    @for ($j = $i; $j < $i + 2; $j++)
                                        @php
                                            $no_urut = $no - $j - $i;
                                            if ($no_urut < 10) {
                                                $no_urut = '0' . $no_urut;
                                            }
                                        @endphp
                                        <div class="size-row">
                                            <div class="cells">
                                                <div id="{{ $no_urut . '-J' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-J' }}</div>
                                                <div id="{{ $no_urut . '-I' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-I' }}</div>
                                                <div id="{{ $no_urut . '-H' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-H' }}</div>
                                                <div id="{{ $no_urut . '-G' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-G' }}</div>
                                                <div id="{{ $no_urut . '-F' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-F' }}</div>
                                                <div id="{{ $no_urut . '-E' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-E' }}</div>
                                                <div id="{{ $no_urut . '-D' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-D' }}</div>
                                                <div id="{{ $no_urut . '-C' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-C' }}</div>
                                                <div id="{{ $no_urut . '-B' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-B' }}</div>
                                                <div id="{{ $no_urut . '-A' }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" class="cell">{{ $no_urut . '-A' }}</div>
                                            </div>
                                            <div class="size-no">{{ $no_urut }}</div>
                                        </div>
                                    @endfor
                                </div>
                            @endfor

                        </div>

                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body py-3">

                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Deskripsi Warna
                                </h6>

                                <div class="d-flex flex-wrap gap-4">

                                    <div class="d-flex align-items-center">
                                        <span class="legend-box bg-success"></span>
                                        <span class="ms-2">100% Production</span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <span class="legend-box bg-warning"></span>
                                        <span class="ms-2">50% < production> 100%</span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <span class="legend-box bg-danger"></span>
                                        <span class="ms-2">production <= 50%</span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <span class="legend-box bg-secondary-light"></span>
                                        <span class="ms-2">Mesin tidak di pakai</span>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Container-->
    </div>
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header" id="header_detail_modal">
                    <h5 class="modal-title">Detail Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body" id="modalContent">
                </div>

            </div>
        </div>
    </div>
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
    <!--end::App Content Header-->
    @include('scripts.cs.dashboard_ccs')
@endsection

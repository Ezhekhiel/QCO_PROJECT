@extends('layouts.main')

@section('content')
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Custom card styling untuk shadow yang lembut dan border-radius */
        .dashboard-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
        }

        /* Styling untuk lingkaran ikon */
        .icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        /* Custom badge untuk persentase naik/turun */
        .trend-badge {
            font-size: 15px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        .trend-label {
            font-size: 15px;
            color: #8c98a5;
        }

        .app-content {
            height: calc(100vh - tinggi_header - tinggi_breadcrumb - tinggi_footer);
        }

        .dp-left,
        .dp-right {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 260px);
            /* header + breadcrumb + sedikit margin */
        }

        .dp-table {
            flex: 7;
            min-height: 0;
            margin-bottom: 12px;
        }

        .dp-model {
            flex: 3;
            min-height: 0;
        }

        .dp-chart1 {
            flex: 1;
            min-height: 0;
            margin-bottom: 12px;
        }

        .dp-chart2 {
            flex: 1;
            min-height: 0;
        }

        .dp-table>.card,
        .dp-model>.card,
        .dp-chart1>.card,
        .dp-chart2>.card {
            height: 100%;
        }

        .dp-table .card-body,
        .dp-model .card-body,
        .dp-chart1 .card-body,
        .dp-chart2 .card-body {
            height: 100%;
            overflow: hidden;
        }

        .dp-table .table-responsive,
        .dp-model .table-responsive {
            height: 100%;
            overflow: auto;
        }

        .chart-box {
            width: 100%;
            height: 100%;
        }

        table {
            font-size: clamp(11px, .75vw, 14px);
            white-space: nowrap;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        @media(max-width:991.98px) {

            .dp-table {
                height: 500px;
            }

            .dp-model {
                height: 250px;
                margin-bottom: 12px;
            }

            .dp-chart1,
            .dp-chart2 {
                height: 350px;
                margin-bottom: 12px;
            }
        }

        tr.double-row td {
            border-bottom: 4px double #000 !important;
        }

        .trend-up {
            background: #dcfce7;
            color: #16a34a;
        }

        .trend-down {
            background: #fee2e2;
            color: #dc2626;
        }

        .trend-flat {
            background: #f3f4f6;
            color: #6b7280;
        }
    </style>

    <div class="app-content">

        <div class="container-fluid">
            <div class="row">
                <div class="col-2 my-3">
                    <div class="input-group mb-0 rounded-3 overflow-hidden border">
                        <input type="date" name="date" id="search_date"
                            class="form-control border-0 border-end-0 py-1 fs-5" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-6 g-3">
                <!-- 4. TOTAL INPUT -->
                <div class="col">
                    <div class="card h-100 dashboard-card p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-circle" style="background-color: #06b6d4;">
                                <i class="bi bi-arrow-down-circle"></i>
                            </div>
                            <div>
                                <div class="text-uppercase fw-bold text-info"
                                    style="font-size: 11px; letter-spacing: 0.5px;">Total Input</div>
                                <h3 class="fw-bold m-0 text-dark" style="font-size: 24px;" id="total_input">10,024</h3>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2">
                            <span class="trend-badge trend-flat" id="trend_input_badge">
                                <i class="bi" id="trend_input_icon"></i>
                                <span id="trend_input"></span>
                            </span>
                            <span class="trend-label">vs kemarin</span>
                        </div>
                    </div>
                </div>
                <!-- 1. TOTAL OUTPUT -->
                <div class="col">
                    <div class="card h-100 dashboard-card p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-circle" style="background-color: #10b981;">
                                <i class="bi bi-box"></i>
                            </div>
                            <div>
                                <div class="text-uppercase fw-bold text-success"
                                    style="font-size: 11px; letter-spacing: 0.5px;">Total Output</div>
                                <h3 class="fw-bold m-0 text-dark" style="font-size: 24px;" id="total_output">9,820</h3>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2">
                            <span class="trend-badge trend-flat" id="trend_output_badge">
                                <i class="bi" id="trend_output_icon"></i>
                                <span id="trend_output"></span>
                            </span>
                            <span class="trend-label">vs kemarin</span>
                        </div>
                    </div>
                </div>

                <!-- 2. TOTAL TARGET -->
                <div class="col">
                    <div class="card h-100 dashboard-card p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-circle" style="background-color: #0d6efd;">
                                <i class="bi bi-flag"></i>
                            </div>
                            <div>
                                <div class="text-uppercase fw-bold text-primary"
                                    style="font-size: 11px; letter-spacing: 0.5px;">Total Target</div>
                                <h3 class="fw-bold m-0 text-dark" style="font-size: 24px;" id="total_target">10,500</h3>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2">
                            {{-- <span class="trend-badge" style="background-color: #e6f0ff; color: #0d6efd;">
                                <i class="bi bi-arrow-up"></i>
                                <span id="trend_target">6.7%</span>
                            </span>
                            <span class="trend-label">vs kemarin</span> --}}
                        </div>
                    </div>
                </div>

                <!-- 3. ACHIEVEMENT -->
                <div class="col">
                    <div class="card h-100 dashboard-card p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-circle" style="background-color: #f59e0b;">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div>
                                <div class="text-uppercase fw-bold"
                                    style="color: #f59e0b; font-size: 11px; letter-spacing: 0.5px;">Achievement</div>
                                <h3 class="fw-bold m-0 text-dark" style="font-size: 24px;" id="achievement"></h3>
                                <!-- Jaga alignment -->
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2">
                            <span class="trend-badge trend-flat" id="trend_achieve_badge">
                                <i class="bi" id="trend_achieve_icon"></i>
                                <span id="trend_achieve"></span>
                            </span>
                            <span class="trend-label">vs kemarin</span>
                        </div>
                    </div>
                </div>

                <!-- 5. TOTAL WIP -->
                <div class="col">
                    <div class="card h-100 dashboard-card p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-circle" style="background-color: #8b5cf6;">
                                <i class="bi bi-layers"></i>
                            </div>
                            <div>
                                <div class="text-uppercase fw-bold"
                                    style="color: #8b5cf6; font-size: 11px; letter-spacing: 0.5px;">Total WIP</div>
                                <h3 class="fw-bold m-0 text-dark" style="font-size: 24px;" id="total_wip">32,100</h3>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2">
                            <span class="trend-badge trend-flat" id="trend_wip_badge">
                                <i class="bi" id="trend_wip_icon"></i>
                                <span id="trend_wip">6.7%</span>
                            </span>
                            <span class="trend-label">vs kemarin</span>
                        </div>
                    </div>
                </div>

                <!-- 6. ACTIVE CELL -->
                <div class="col">
                    <div class="card h-100 dashboard-card p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-circle" id="icon_wip" style="background-color: #d71919;">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <div class="text-uppercase fw-bold text-secondary"
                                    style="font-size: 11px; letter-spacing: 0.5px;">WIP Target</div>
                                <h3 class="fw-bold m-0 text-dark" style="font-size: 24px;" id="total_cell_wip"></h3>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2">
                            <span class="trend-label" id="total_cell_wip_all"></span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-2">

                <!-- LEFT -->
                <div class="col-12 col-xl-5 mb-3 mb-xl-0 dp-left">

                    <!-- TABLE CELL -->
                    <div class="dp-table">

                        <div class="card shadow-sm">

                            <div class="card-body p-1">

                                <div class="table-responsive">

                                    <table class="table table-bordered table-sm text-center align-middle">

                                        <thead class="table-primary">
                                            <tr>
                                                <th>Cell</th>
                                                <th>Model</th>
                                                <th>Target / Hrs</th>
                                                <th>WH</th>
                                                <th>DailyTarget</th>

                                                <th class="text-nowrap"
                                                    style="width: 85px; min-width: 85px; max-width: 85px; vertical-align: middle;">
                                                    WIP</th>
                                                <th class="text-nowrap"
                                                    style="width: 85px; min-width: 85px; max-width: 85px; vertical-align: middle;">
                                                    Input</th>
                                                <th class="text-nowrap"
                                                    style="width: 85px; min-width: 85px; max-width: 85px; vertical-align: middle;">
                                                    Output<br>(Shift 1)</th>
                                                <th class="text-nowrap"
                                                    style="width: 85px; min-width: 85px; max-width: 85px; vertical-align: middle;">
                                                    Output<br>(Shift 2)</th>
                                                <th class="text-nowrap"
                                                    style="width: 85px; min-width: 85px; max-width: 85px; vertical-align: middle;">
                                                    Total<br>Output<br>S1+S2</th>

                                                <th class="text-nowrap"
                                                    style="width: 85px; min-width: 85px; max-width: 85px; vertical-align: middle;">
                                                    WIP Update</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>

                                        <tbody id="summaryTable">

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- TABLE MODEL -->
                    <div class="dp-model">

                        <div class="card shadow-sm">

                            <div class="card-body p-1">

                                <div class="table-responsive">

                                    <table class="table table-bordered table-sm text-center align-middle">

                                        <thead class="table-primary">
                                            <tr>
                                                <th>Model</th>
                                                <th>Total Cell</th>
                                                <th>Target</th>
                                                <th>Output</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>

                                        <tbody id="summaryTableModel">
                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- RIGHT -->
                <div class="col-12 col-xl-7 dp-right">

                    <div class="dp-chart1">

                        <div class="card shadow-sm">
                            <div class="card-header border-0 text-white"
                                style="background:linear-gradient(90deg,#2563eb,#1d4ed8);">

                                <div class="d-flex align-items-center">

                                    <i class="bi-arrow-repeat fs-3 me-3"></i>

                                    <div>
                                        <div class="fw-bold fs-5">
                                            Input - Output Computer Centralized
                                        </div>

                                        <small class="text-white-50">
                                            Production Monitoring Dashboard
                                        </small>
                                    </div>

                                </div>

                            </div>

                            <div class="card-body">

                                <div id="chartInputOutput" class="chart-box"></div>

                            </div>

                        </div>

                    </div>

                    <div class="dp-chart2">

                        <div class="card shadow-sm">
                            <div class="card-header border-0 text-white"
                                style="background:linear-gradient(90deg,#156082,#0F4C66);">

                                <div class="d-flex align-items-center">

                                    <i class="bi bi-box-seam-fill fs-3 me-3"></i>

                                    <div>
                                        <div class="fw-bold fs-5">
                                            WIP
                                        </div>

                                        <small class="text-white-50">
                                            Work In Progress Monitoring Dashboard
                                        </small>
                                    </div>

                                </div>

                            </div>

                            <div class="card-body">

                                <div id="chartWIP" class="chart-box"></div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @include('scripts.cs.dashboard_production')
@endsection

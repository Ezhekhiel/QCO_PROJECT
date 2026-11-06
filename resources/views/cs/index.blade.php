@extends('layouts.main')
@section('content')
    <style>
        .size-chart-table th,
        .size-chart-table td {
            min-width: 55px;
            font-weight: 600;
        }

        .size-chart-table th,
        .size-chart-table td {
            vertical-align: middle !important;
        }
    </style>
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row align-items-end mb-3">

                <div class="col-md-3">
                    <label for="date_id_input" class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control form-input" name="date" id="date_id_input"
                        value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Template By</label>

                    <div class="card">
                        <div class="card-body py-2">
                            <div class="d-flex gap-4">

                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="template_by" id="template_po"
                                        value="po" checked>

                                    <label class="form-check-label" for="template_po">
                                        PO
                                    </label>
                                </div>

                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="template_by" id="template_model"
                                        value="model">

                                    <label class="form-check-label" for="template_model">
                                        Model
                                    </label>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="p-3">
                <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">

                    <table class="table table-sm table-hover table-bordered text-center align-middle size-chart-table mb-0">

                        <thead class="table-secondary text-center sticky-top">

                            <tr>
                                <th rowspan="3">Cell</th>
                                <th rowspan="3" id="option">PO</th>
                                <th rowspan="3" id="wide_option">Wide</th>
                                <th rowspan="3">Input</th>
                                <th rowspan="2" colspan="2">Output</th>
                                <th rowspan="3">WIP</th>
                                <th class="bg-success text-light">M/W/U</th>
                                <th class="bg-success text-light">3</th>
                                <th class="bg-success text-light">3T</th>
                                <th class="bg-success text-light">4</th>
                                <th class="bg-success text-light">4T</th>
                                <th class="bg-success text-light">5</th>
                                <th class="bg-success text-light">5T</th>
                                <th class="bg-success text-light">6</th>
                                <th class="bg-success text-light">6T</th>
                                <th class="bg-success text-light">7</th>
                                <th class="bg-success text-light">7T</th>
                                <th class="bg-success text-light">8</th>
                                <th class="bg-success text-light">8T</th>
                                <th class="bg-success text-light">9</th>
                                <th class="bg-success text-light">9T</th>
                                <th class="bg-success text-light">10</th>
                                <th class="bg-success text-light">10T</th>
                                <th class="bg-success text-light">11</th>
                                <th class="bg-success text-light">11T</th>
                                <th class="bg-success text-light">12</th>
                                <th class="bg-success text-light">12T</th>
                                <th class="bg-success text-light">13</th>
                                <th class="bg-success text-light">13T</th>
                                <th class="bg-success text-light">14</th>
                                <th class="bg-success text-light">15</th>
                                <th class="bg-success text-light">16</th>
                                <th class="bg-success text-light">17</th>
                                <th class="bg-success text-light">18</th>
                                <th class="bg-success text-light">19</th>
                                <th class="bg-success text-light">20</th>
                            </tr>
                            <tr>
                                <th class="bg-warning">KID'S/J</th>
                                <th class="bg-warning"></th>
                                <th class="bg-warning"></th>
                                <th class="bg-warning"></th>
                                <th class="bg-warning"></th>
                                <th class="bg-warning">10</th>
                                <th class="bg-warning">10T</th>
                                <th class="bg-warning">11</th>
                                <th class="bg-warning">11T</th>
                                <th class="bg-warning">12</th>
                                <th class="bg-warning">12T</th>
                                <th class="bg-warning">13</th>
                                <th class="bg-warning">13T</th>
                                <th class="bg-warning">1</th>
                                <th class="bg-warning">1T</th>
                                <th class="bg-warning">2</th>
                                <th class="bg-warning">2T</th>
                                <th class="bg-warning">3</th>
                                <th class="bg-warning">3T</th>
                                <th class="bg-warning">4</th>
                                <th class="bg-warning">4T</th>
                                <th class="bg-warning">5</th>
                                <th class="bg-warning">5T</th>
                                <th class="bg-warning">6</th>
                                <th class="bg-warning">6T</th>
                                <th class="bg-warning">7</th>
                                <th class="bg-warning">7T</th>
                                <th class="bg-warning">8</th>
                                <th class="bg-warning">8T</th>
                                <th class="bg-warning">9</th>
                            </tr>
                            <tr>
                                <th>Shift 1</th>
                                <th>Shift 2</th>
                                <th>INF/C</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-danger">-</th>
                                <th class="text-danger">1</th>
                                <th class="text-danger">1T</th>
                                <th>2</th>
                                <th>2T</th>
                                <th>3</th>
                                <th>3T</th>
                                <th>4</th>
                                <th>4T</th>
                                <th>5</th>
                                <th>5T</th>
                                <th>6</th>
                                <th>6T</th>
                                <th>7</th>
                                <th>7T</th>
                                <th>8</th>
                                <th>8T</th>
                                <th>9</th>
                                <th>9T</th>
                                <th>10</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>

                        </thead>

                        <tbody id="tbody_dashboard">
                        </tbody>

                    </table>

                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->
    @include('scripts.cs.dashboard')
@endsection

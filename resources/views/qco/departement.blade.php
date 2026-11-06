@extends('layouts.main')
@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="p-3">
                        <div class="row">
                            <div class="col-2 pl-1 pr-1">
                                <div class="card p-3 h-100">
                                    <h4>Department</h4>
                                    <form action="#" method="POST" id="import_location" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group mb-4">
                                            <label for="department_id">Department</label>
                                            <input type="text" class="form-control" id="department_id" name="department"
                                                aria-describedby="departmentHelp">
                                        </div>
                                        <button type="submit" class="btn btn-primary pt-2">Save</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-3 pl-1 pr-1">
                                <div class="card p-3 h-100">
                                    <h4>Department Database</h4>
                                    <div class="overflow-y-auto" style="max-height: 400px">
                                        <table class="table table-bordered table-small-font table-hover text-center"
                                            cellspacing="0" id="departmentTable">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Department</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 pl-1 pr-1">
                                <div class="card p-3 h-100">
                                    <h4>Area</h4>
                                    <form action="#" method="POST" id="import_area" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group mb-4">
                                            <label for="test">Location</label>
                                            <select class="form-control select_location" name="location"></select>
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="test">Area</label>
                                            <input type="text" class="form-control" id="area_id" name="area"
                                                placeholder="Input area">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-5 pl-1 pr-1">
                                <div class="card p-3 h-100">
                                    <h4>Area Database</h4>
                                    <div class="m-2">
                                        <div class="overflow-y-auto" style="max-height: 400px">
                                            <table class="table table-striped text-sm text-center">
                                                <thead class="thead-dark ">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Location</th>
                                                        <th>ID Area</th>
                                                        <th>Area</th>
                                                        <th>Delete</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="table_area"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('script.qco.dashboard')
@endsection

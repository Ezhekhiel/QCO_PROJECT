@extends('layouts.main')
@section('content')
    <div class="app-content-header py-3">
        <div class="container">
            <div class="row">
                <div class="col-5 pl-1 pr-1">
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
                <div class="col-7 pl-1 pr-1">
                    <div class="card p-3 h-100">
                        <h4>Department Database</h4>
                        <div class="overflow-y-auto" style="max-height: 400px">
                            <table class="table table-bordered table-small-font table-hover text-center" cellspacing="0"
                                id="departmentTable">
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
            </div>
        </div>
    </div>
    <!-- Modal Edit Department -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditDepartment">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_department_id" name="department_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editDepartmentModalLabel">Edit Departemen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="edit_nama_department" class="form-label">Nama Departemen</label>
                            <input type="text" class="form-control" id="edit_nama_department" name="nama" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateDepartment">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('script.auth.department')
@endsection

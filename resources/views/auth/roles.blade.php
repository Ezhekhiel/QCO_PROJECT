@extends('layouts.main')
@section('content')
    <div class="app-content-header py-3">
        <div class="container">
            <div class="row">
                <div class="col-5 pl-1 pr-1">
                    <div class="card p-3 h-100">
                        <h4>Manage Role</h4>
                        <form action="#" method="POST" id="import_role" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-4">
                                <label for="department_id">Role Name</label>
                                <div class="mb-3">
                                    <label for="pilihRole" class="form-label">Pilih Hak Akses (Role)</label>
                                    <select class="form-select" id="pilihRole" name="role">
                                        <option selected disabled value="">-- Pilih Role --</option>
                                        <option value="Director">Director</option>
                                        <option value="Manager">Manager</option>
                                        <option value="Assistant Manager">Assistant Manager</option>
                                        <option value="Chiev">Chiev</option>
                                        <option value="Supervison">Supervison</option>
                                        <option value="Staff">Staff</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label for="department_id">Department</label>
                                <select class="form-select" id="pilihDepartment" name="department_id">
                                    <option selected disabled value="">-- Pilih Department --</option>
                                    @foreach ($department as $a)
                                        <option value="{{ $a->id }}">{{ $a->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary pt-2">Save</button>
                        </form>
                    </div>
                </div>
                <div class="col-7 pl-1 pr-1">
                    <div class="card p-3 h-100">
                        <h4>department Database</h4>
                        <div class="overflow-y-auto" style="max-height: 400px">
                            <table class="table table-bordered table-small-font table-hover text-center" cellspacing="0"
                                id="roleTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Role Name</th>
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
    <!-- Modal Edit Role -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditRole">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_role_id" name="role_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Pilihan Role -->
                        <div class="form-group mb-3">
                            <label for="edit_pilihRole" class="form-label">Pilih Hak Akses (Role)</label>
                            <select class="form-select" id="edit_pilihRole" name="role">
                                <option selected disabled value="">-- Pilih Role --</option>
                                <option value="Manager">Manager</option>
                                <option value="Chief">Chief</option>
                                <option value="Supervisor">Supervisor</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>

                        <!-- Pilihan Department -->
                        <div class="form-group mb-3">
                            <label for="edit_department_id" class="form-label">Departemen</label>
                            <select class="form-select" id="edit_department_id" name="department_id">
                                <option selected disabled value="">-- Pilih Department --</option>
                                @foreach ($department as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateRole">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('script.auth.roles')
@endsection

@extends('layouts.main')
@section('content')
    <div class="app-content-header py-3">
        <div class="container">
            <div class="row">
                <div class="col-5 pl-1 pr-1">
                    <div class="card p-3 h-100">
                        <h4>QCO List</h4>
                        <form action="#" method="POST" id="importList" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group mb-4">
                                <label for="list_id">Department</label>
                                <select class="form-select" id="pilihDepartment" name="department_id">
                                    <option selected disabled value="">-- Pilih Department --</option>
                                    @foreach ($department as $a)
                                        <option value="{{ $a->id }}">{{ $a->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-4">
                                <label for="list">List List</label>
                                <input type="text" class="form-control" id="list" name="list"
                                    aria-describedby="listHelp">
                            </div>
                            <button type="submit" class="btn btn-primary pt-2">Save</button>
                        </form>
                    </div>
                </div>
                <div class="col-7 pl-1 pr-1">
                    <div class="card p-3 h-100">
                        <h4>Preparation List</h4>

                        <!-- TAMBAHKAN INPUT SEARCH DI SINI -->
                        <div class="mb-3">
                            <input type="text" id="searchTable" class="form-control"
                                placeholder="Cari berdasarkan department atau list...">
                        </div>

                        <div class="overflow-y-auto" style="max-height: 600px">
                            <table class="table table-bordered table-small-font table-hover text-center" cellspacing="0"
                                id="listTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Department</th>
                                        <th>List</th>
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
    <div class="modal fade" id="editListModal" tabindex="-1" aria-labelledby="editListModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditList">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_list_id" name="list_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editlistModalLabel">Edit List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="edit_department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="edit_department_id" name="department_id"
                                readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_nama_list" class="form-label">List</label>
                            <input type="text" class="form-control" id="edit_nama_list" name="list" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateList">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('script.qco.manage.preparation_list')
@endsection

@extends('layouts.main')

@section('content')
    <div class="app-content-header py-3">
        <div class="container">
            <div class="row">
                <!-- FORM INPUT DI KIRI -->
                <div class="col-5 pl-1 pr-1">
                    <div class="card p-3 h-100">
                        <h4>Tambah Cell Info</h4>
                        <form action="#" method="POST" id="import_location" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="building">Building</label>
                                <input type="text" class="form-control" id="building" name="building" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="area">Area</label>
                                <input type="text" class="form-control" id="area" name="area" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="eol">EOL</label>
                                <input type="number" class="form-control" id="eol" name="eol" value="0">
                            </div>
                            <div class="form-group mb-4">
                                <label for="mp">MP</label>
                                <input type="number" class="form-control" id="mp" name="mp" value="0">
                            </div>
                            <button type="submit" class="btn btn-primary pt-2">Save</button>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATABASE DI KANAN -->
                <div class="col-7 pl-1 pr-1">
                    <div class="card p-3 h-100">
                        <h4>Cell Info Database</h4>
                        <div class="overflow-y-auto" style="max-height: 400px">
                            <table class="table table-bordered table-small-font table-hover text-center align-middle"
                                cellspacing="0" id="cellTable"> <!-- ID tabel tetap menyesuaikan referensi Anda -->
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Building</th>
                                        <th>Area</th>
                                        <th>EOL</th>
                                        <th>MP</th>
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

    <!-- Modal Edit Cell Info -->
    <div class="modal fade" id="editCellModal" tabindex="-1" aria-labelledby="editCellModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditCell">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_cell_id" name="cell_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editcellModalLabel">Edit Cell Info</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="edit_building" class="form-label">Building</label>
                            <input type="text" class="form-control" id="edit_building" name="building" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="edit_area" name="area" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_eol" class="form-label">EOL</label>
                            <input type="number" class="form-control" id="edit_eol" name="eol">
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_mp" class="form-label">MP</label>
                            <input type="number" class="form-control" id="edit_mp" name="mp">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateCell">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Memanggil script AJAX terpisah sesuai struktur Anda -->
    @include('script.qco.manage.cell')
@endsection

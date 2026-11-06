@extends('layouts.main')
@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- KOLOM KIRI: FORM INPUT USER -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah User Baru</h5>
                    </div>
                    <div class="card-body">
                        <form id="formAddUser">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="department_id" class="form-label">Departemen</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option selected disabled value="">-- Pilih Departemen --</option>
                                    @foreach ($department as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="role_id" class="form-label">Pilih Role</label>
                                <select name="role_id" id="role_id" class="form-control" required disabled>
                                    <option value="" disabled selected>-- Pilih Departemen Terlebih Dahulu --</option>
                                </select>
                            </div>

                            <button type="submit" class="backend-btn btn btn-primary w-100" id="btnSaveUser">Simpan
                                User</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: LIST USER -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Daftar User</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle" id="tableUsers">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>NIK</th>
                                        <th>Email</th>
                                        <th>Departemen</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data akan dimuat secara dinamis via AJAX / jQuery -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('script.auth.register')
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- apexcharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        main();
        $('#import_role').on('submit', function(e) {
            event.preventDefault();
            $.ajax({
                url: "{{ route('roles.store') }}",
                method: "POST",
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
                    $("#loading-spinner").show();
                },
                success: function(response) {
                    if (response.success) {
                        $('#editDepartmentModal').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        let errorMsg = response.message;
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', errorMsg, 'error');
                }
            });
        });
    });

    function main() {
        $.ajax({
            url: "{{ route('roles.main') }}",
            method: "get",
            dataType: 'JSON',
            success: function(response) {

                if (response.success) {
                    let rows = '';
                    let no = 1;

                    // Looping data dari JSON response
                    $.each(response.data, function(index, item) {
                        rows += `
                                    <tr>
                                        <td>${no++}</td>
                                        <td>${item.name}</td>
                                        <td>${item.department.nama}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="roleEdit(${item.id})">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="roleDelete(${item.id})">Hapus</button>
                                        </td>
                                    </tr>
                                `;
                    });

                    // Masukkan baris HTML ke dalam tbody tabel
                    console.log(rows);
                    $('#roleTable tbody').html(rows);
                }
            }
        })
        // 2. Fungsi saat form edit disubmit (Update data via AJAX)
        $('#formEditRole').on('submit', function(e) {
            e.preventDefault();

            let id = $('#edit_role_id').val();
            let formData = $(this).serialize();

            $.ajax({
                url: '/roles/' + id,
                type: 'POST', // Menggunakan POST karena membawa _method PUT
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Tutup modal
                        $('#editRoleModal').modal('hide');

                        // Tampilkan pesan sukses dengan SweetAlert
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location
                                .reload(); // Reload halaman untuk memperbarui data tabel
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', errorMsg, 'error');
                }
            });
        });
    }

    function roleDelete(id) {
        // Konfirmasi menggunakan SweetAlert2
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data role yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Eksekusi AJAX
                $.ajax({
                    url: '/roles/' + id,
                    type: 'POST', // Menggunakan POST dengan _method DELETE
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr(
                            'content') // Pastikan meta CSRF token sudah terpasang di <head>
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Terhapus!',
                                response.message,
                                'success'
                            ).then(() => {
                                // Reload halaman atau reload datatable agar data terbaru tampil
                                location.reload();
                                // Jika pakai DataTable, bisa gunakan: $('#tabelRole').DataTable().ajax.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Gagal!',
                            'Terjadi kesalahan pada server.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function roleEdit(id) {
        $.ajax({
            url: '/roles/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let data = response.data;

                    // Masukkan data ke dalam form modal
                    $('#edit_role_id').val(data.id);
                    $('#edit_pilihRole').val(data.name);
                    $('#edit_department_id').val(data.department_id);

                    // Tampilkan modal Bootstrap 5
                    var myModal = new bootstrap.Modal(document.getElementById('editRoleModal'));
                    myModal.show();
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal!', 'Gagal mengambil data role.', 'error');
            }
        });
    }
</script>

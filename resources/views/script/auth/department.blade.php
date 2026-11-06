<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- apexcharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#department_id').focus();

        main();
        $('#import_location').on('submit', function(e) {
            event.preventDefault();
            $.ajax({
                url: "{{ route('departments.import') }}",
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
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Ambil pesan error validasi pertama
                        let errors = xhr.responseJSON.errors;
                        errorMsg = Object.values(errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', errorMsg, 'error');
                }
            });
        });
    });

    function main() {
        $.ajax({
            url: "{{ route('departments.main') }}",
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
                                        <td>${item.nama}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="departmentEdit(${item.id})">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="departmentDelete(${item.id})">Hapus</button>
                                        </td>
                                    </tr>
                                `;
                    });

                    // Masukkan baris HTML ke dalam tbody tabel
                    $('#departmentTable tbody').html(rows);
                }
            }
        });
        // 2. Event Submit Form Update via AJAX
        $('#formEditDepartment').on('submit', function(e) {
            e.preventDefault();

            let id = $('#edit_department_id').val();
            let formData = $(this).serialize();

            $.ajax({
                url: '/departments/' + id,
                type: 'POST', // Menggunakan POST dengan _method PUT
                data: formData,
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
    // 1. Fungsi Edit: Ambil data & tampilkan modal
    function departmentEdit(id) {
        $.ajax({
            url: '/departments/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let data = response.data;

                    // Masukkan data ke input modal
                    $('#edit_department_id').val(data.id);
                    $('#edit_nama_department').val(data.nama);

                    // Tampilkan modal Bootstrap 5
                    var myModal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
                    myModal.show();
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal!', 'Gagal mengambil data departemen.', 'error');
            }
        });
    }
    // 3. Fungsi Delete: Konfirmasi & Hapus via AJAX
    function departmentDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data departemen yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/departments/' + id,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Terhapus!',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Gagal!',
                            'Terjadi kesalahan saat menghapus data.',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>

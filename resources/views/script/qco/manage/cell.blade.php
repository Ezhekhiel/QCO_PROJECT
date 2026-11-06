<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- apexcharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#building').focus();

        main();

        // 1. Event Submit Form Tambah (Store)
        $('#import_location').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('cell-info.store') }}",
                method: "POST",
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#import_location')[0].reset();
                        $('#building').focus();
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            main(); // Refresh tabel tanpa reload halaman
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        errorMsg = Object.values(errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', errorMsg, 'error');
                }
            });
        });

        // 2. Event Submit Form Update via AJAX
        $('#formEditCell').on('submit', function(e) {
            e.preventDefault();

            let id = $('#edit_cell_id').val();
            let formData = $(this).serialize();

            $.ajax({
                url: '/cell-info/' + id,
                type: 'POST', // Menggunakan POST dengan _method PUT
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editCellModal').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            main(); // Refresh tabel tanpa reload halaman
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
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

    // Fungsi Load Data Tabel (Main)
    function main() {
        $.ajax({
            url: "{{ route('cell-info.main') }}",
            method: "GET",
            dataType: 'JSON',
            success: function(response) {
                if (response.success) {
                    let rows = '';
                    let no = 1;

                    $.each(response.data, function(index, item) {
                        rows += `
                            <tr>
                                <td>${no++}</td>
                                <td>${item.building}</td>
                                <td>${item.area}</td>
                                <td>${item.eol}</td>
                                <td>${item.mp}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="cellEdit(${item.id})">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="cellDelete(${item.id})">Hapus</button>
                                </td>
                            </tr>
                        `;
                    });

                    $('#cellTable tbody').html(rows);
                }
            }
        });
    }

    // Fungsi Edit: Ambil data & tampilkan modal
    function cellEdit(id) {
        $.ajax({
            url: '/cell-info/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let data = response.data;

                    // Masukkan data ke input modal
                    $('#edit_cell_id').val(data.id);
                    $('#edit_building').val(data.building);
                    $('#edit_area').val(data.area);
                    $('#edit_eol').val(data.eol);
                    $('#edit_mp').val(data.mp);

                    // Tampilkan modal Bootstrap
                    var myModal = new bootstrap.Modal(document.getElementById('editCellModal'));
                    myModal.show();
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal!', 'Gagal mengambil data cell info.', 'error');
            }
        });
    }

    // Fungsi Delete: Konfirmasi & Hapus via AJAX
    function cellDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data cell info yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/cell-info/' + id,
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
                                main(); // Refresh tabel setelah hapus
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

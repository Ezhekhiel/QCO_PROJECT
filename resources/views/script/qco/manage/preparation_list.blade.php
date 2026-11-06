<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- apexcharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#building').focus();

        main();

        // 1. Event Submit Form Tambah (Store)
        $('#importList').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('QCO.import_list') }}",
                method: "POST",
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#importList')[0].reset();
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
        $('#formEditList').on('submit', function(e) {
            e.preventDefault();

            let id = $('#edit_list_id').val();
            let formData = $(this).serialize();

            $.ajax({
                url: '/QCO/' + id,
                type: 'POST', // Menggunakan POST dengan _method PUT
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editListModal').modal('hide');
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
        $('#searchTable').on('keyup', function() {
            let value = $(this).val().toLowerCase();

            // Filter setiap baris (tr) di dalam tbody tabel #listTable
            $('#listTable tbody tr').filter(function() {
                // Toggle baris: sembunyikan jika teks tidak cocok, tampilkan jika cocok
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });

    // Fungsi Load Data Tabel (Main)
    function main() {
        $.ajax({
            url: "{{ route('QCO.list.main') }}",
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
                                <td>${item.department.nama}</td>
                                <td>${item.list}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="listEdit(${item.id})">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="listDelete(${item.id})">Hapus</button>
                                </td>
                            </tr>
                        `;
                    });

                    $('#listTable tbody').html(rows);
                }
            }
        });
    }

    // Fungsi Edit: Ambil data & tampilkan modal
    function listEdit(id) {
        $.ajax({
            url: '/QCO/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let data = response.data;

                    // Masukkan data ke input modal
                    $('#edit_list_id').val(data.id);
                    $('#edit_department_id').val(data.department_id);
                    $('#edit_nama_list').val(data.list);

                    // Tampilkan modal Bootstrap
                    var myModal = new bootstrap.Modal(document.getElementById('editListModal'));
                    myModal.show();
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal!', 'Gagal mengambil data list info.', 'error');
            }
        });
    }

    // Fungsi Delete: Konfirmasi & Hapus via AJAX
    function listDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data list info yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/QCO/' + id,
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

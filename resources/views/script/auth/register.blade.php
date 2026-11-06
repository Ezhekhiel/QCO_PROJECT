<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#name').focus();
        // 1. Load Data User saat halaman pertama dibuka
        loadUsers();
        document.getElementById('department_id').addEventListener('change', function() {
            let departmentId = this.value;
            let roleSelect = document.getElementById('role_id');

            // Reset dan disable dropdown role sementara loading
            roleSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';
            roleSelect.disabled = true;

            if (departmentId) {
                fetch(`/get-roles-by-department?department_id=${departmentId}`)
                    .then(response => response.json())
                    .then(data => {
                        roleSelect.innerHTML =
                            '<option value="" disabled selected>-- Pilih Role --</option>';

                        if (data.data.length > 0) {
                            data.data.forEach(role => {
                                let option = document.createElement('option');
                                option.value = role.id;
                                option.textContent = role.name;
                                roleSelect.appendChild(option);
                            });
                            roleSelect.disabled = false; // Aktifkan kembali dropdown role
                        } else {
                            roleSelect.innerHTML =
                                '<option value="" disabled selected>Tidak ada role di departemen ini</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        roleSelect.innerHTML =
                            '<option value="" disabled selected>Gagal memuat data</option>';
                    });
            } else {
                roleSelect.innerHTML =
                    '<option value="" disabled selected>-- Pilih Departemen Terlebih Dahulu --</option>';
                roleSelect.disabled = true;
            }
        });

        function loadUsers() {
            $.ajax({
                url: "{{ route('register.main') }}",
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        let rows = '';
                        let no = 1;
                        response.data.forEach(function(item) {
                            rows += `
                                <tr>
                                    <td>${no++}</td>
                                    <td>${item.name}</td>
                                    <td>${item.nik}</td>
                                    <td>${item.email}</td>
                                    <td>${item.department ? item.department.nama : '-'}</td>
                                    <td>${item.role ? item.role.name : '-'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser(${item.id})">Hapus</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#tableUsers tbody').html(rows);
                    }
                }
            });
        }

        // 2. Submit Form Tambah User via AJAX
        $('#formAddUser').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('register') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Reset Form & Select Option ke Index 0
                        $('#formAddUser')[0].reset();
                        $('#formAddUser select').prop('selectedIndex', 0);

                        // Reload data list user di sebelah kanan
                        loadUsers();
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

    // 3. Fungsi Delete User
    function deleteUser(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data user yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/users/' + id,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Terhapus!', response.message, 'success');
                            location.reload(); // atau panggil ulang fungsi loadUsers()
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus user.', 'error');
                    }
                });
            }
        });
    }
</script>

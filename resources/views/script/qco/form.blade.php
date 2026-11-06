<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- apexcharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<script>
    window.globalData = {
        qcoList: @json($qcoList),
        listPreparation: @json($list),
    };
    let rowFilesMap = {};

    $(document).ready(function() {
        $('#input_cso').on('submit', function(e) {
            e.preventDefault(); // Memperbaiki penulisan event.preventDefault()

            $.ajax({
                url: "{{ route('qco.form.store') }}",
                method: "POST",
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
                    // Jika Anda punya element loading spinner, bisa di-show di sini
                    // $("#loading-spinner").show();

                    // Atau disable tombol submit agar tidak double-click
                    $('button[type="submit"]').prop('disabled', true).text('Menyimpan...');
                },
                success: function(response) {
                    $('button[type="submit"]').prop('disabled', false).text(
                        'Simpan Data QCO');

                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location
                                .reload(); // Refresh halaman untuk memperbarui kalender
                        });
                    } else {
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.',
                            'error');
                    }
                },
                error: function(xhr) {
                    $('button[type="submit"]').prop('disabled', false).text(
                        'Simpan Data QCO');

                    let errorMsg = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Ambil pesan error validasi pertama dari Laravel
                        let errors = xhr.responseJSON.errors;
                        errorMsg = Object.values(errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', errorMsg, 'error');
                }
            });
        });
        $('#formQCO').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData();

            // Masukkan id_qco
            formData.append('id_qco', $('#id_qco').val());

            // Masukkan _token CSRF
            formData.append('_token', $('input[name="_token"]').val());

            // Loop data dari rowFilesMap dan pastikan checkbox-nya tercentang
            console.log(rowFilesMap);

            $.each(rowFilesMap, function(index, files) {
                let checkbox = $(`#list-${window.globalData.listPreparation[index].id}`);

                if (checkbox.is(':checked') && !checkbox.is(':disabled')) {
                    let idList = checkbox.val();
                    formData.append(`items[${index}][id_list]`, idList);

                    // Masukkan setiap file ke FormData secara biner
                    files.forEach(file => {
                        formData.append(`items[${index}][files][]`, file);
                    });
                }
            });

            $.ajax({
                url: "{{ route('QCO.preparation.store') }}",
                method: "POST",
                data: formData,
                contentType: false, // Wajib false untuk upload file
                processData: false, // Wajib false untuk upload file
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location
                                .reload(); // Refresh halaman untuk memperbarui kalender
                        });
                    } else {
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.',
                            'error');
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan pada server.');
                    console.log(xhr.responseText);
                }
            });
        });
    });

    // Object penampung file per baris (Key = index baris, Value = array file)

    function openModalQCO(id) {
        $('#id_qco').val(id);
        rowFilesMap = {}; // Reset data file saat modal dibuka
        var listData = '';
        var status_checkbox = '';
        // 1. Ambil array preparations berdasarkan id QCO yang sedang dibuka
        let preparationsArray = window.globalData.qcoList.find(item => item.id == id)?.preparations || [];
        let dataSelection = window.globalData.qcoList.find(item => item.id == id) || [];

        // 2. Ambil kumpulan id_list yang sudah pernah di-submit sebelumnya
        let submittedListIds = preparationsArray.map(prep => prep.id_list);

        // Cek hasil log
        console.log(dataSelection.status);
        if (dataSelection.status == 'CANCEL') {
            $('#label_modal_checkbox').hide();
            listData += `
                <div class="card p-4 mb-3 shadow-lg bg-danger text-white border-0 rounded-4 transition-all hover-effect"
                    onclick="updateStatusList(7,4)"
                    style="cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="row align-items-center justify-content-center text-center m-0">
                        <div class="col-12">
                            <h5 class="fw-bold mb-0 text-uppercase tracking-wider">
                                <i class="fas fa-ban me-2"></i> QCO DI BATALKAN
                            </h5>
                        </div>
                    </div>
                </div>
            `;
        } else {
            $('#label_modal_checkbox').show();
            $.each(window.globalData.listPreparation, function(index, value) {
                rowFilesMap[index] = [];
                let isAlreadySubmitted = submittedListIds.includes(value.id);

                let nameAttr = isAlreadySubmitted ? 'udah' : `items[${index}][id_list]`;
                let disabledAttr = isAlreadySubmitted ? 'disabled checked' : '';
                let fileDisabledAttr = isAlreadySubmitted ? 'disabled' : '';
                let badgeText = isAlreadySubmitted ?
                    '<span class="badge badge-light text-success float-right px-2 py-1">Sudah Disimpan</span>' :
                    '';

                // Tambahkan bg-success dan text-white di sini agar seluruh card berwarna hijau & teksnya putih
                let cardClass = isAlreadySubmitted ? 'bg-success text-white border-success' : 'border';
                let textColor = isAlreadySubmitted ? 'text-white' : '';
                let onClick = isAlreadySubmitted ?
                    `onclick="updateStatusList(${value.id},${id})" style="cursor: pointer;"` : '';


                listData += `
                    <div class="card p-3 mb-2 shadow-sm ${cardClass}" ${ onClick }}>
                        <div class="row align-items-center">
                            <!-- Checkbox & Label -->
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input checkbox-item"
                                        name="${nameAttr}" value="${value.id}" id="list-${value.id}" data-index="${index}" ${disabledAttr}>
                                    <label class="custom-control-label font-weight-bold ${textColor}" for="list-${value.id}">
                                        ${value.list}
                                    </label>
                                </div>
                                ${badgeText ? '<div class="mt-2">' + badgeText + '</div>' : ''}
                            </div>

                            <!-- Input File per List -->
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <input type="file" class="form-control-file file-input-item"
                                        multiple
                                        accept=".jpg, .jpeg, .png, .pdf, .xls, .xlsx"
                                        data-index="${index}" ${fileDisabledAttr}>

                                    <small class="${textColor} d-block mt-1">
                                        ${isAlreadySubmitted ? '<i class="fas fa-check-circle"></i> File untuk item ini sudah di-upload dan dikunci.' : 'Format: Gambar, PDF, Excel (Bisa pilih banyak file sekaligus)'}
                                    </small>

                                    <!-- Preview List File -->
                                    <ul class="list-group mt-2 preview-list-${index}">
                                        <li class="list-group-item ${textColor} py-1 small bg-transparent border">
                                            ${isAlreadySubmitted ? '<i class="fas fa-database"></i> Tersimpan di database' : 'Belum ada file dipilih'}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
        }


        $("#preparation_list").html(listData);
        $('#modalQCO').modal('show');
    }

    // 1. Handle saat user memilih file (Akumulasi & Auto-Check)
    $(document).on('change', '.file-input-item', function(e) {
        let index = $(this).data('index');
        let newFiles = Array.from(e.target.files);

        if (newFiles.length > 0) {
            // Tambahkan file baru ke array yang sudah ada (akumulasi dari folder berbeda)
            newFiles.forEach(file => {
                rowFilesMap[index].push(file);
            });

            // OTOMATIS CHECKLIST JIKA USER MEMILIH FILE
            $(`#list-${window.globalData.listPreparation[index].id}`).prop('checked', true);

            // Render ulang tampilan preview file
            renderFileList(index);
        }

        // Reset value input file agar bisa memilih file dengan nama yang sama lagi jika diperlukan
        $(this).val('');
    });

    // 2. Fungsi Render Daftar File & Tombol Hapus (X)
    function renderFileList(index) {
        let $previewList = $(`.preview-list-${index}`);
        $previewList.empty();

        let files = rowFilesMap[index];

        if (files.length === 0) {
            $previewList.append('<li class="list-group-item text-muted py-1 small">Belum ada file dipilih</li>');
            return;
        }

        files.forEach((file, fileIndex) => {
            let ext = file.name.split('.').pop().toLowerCase();
            let icon = '<i class="fas fa-file text-secondary mr-1"></i>';
            if (['jpg', 'jpeg', 'png'].includes(ext)) icon =
                '<i class="fas fa-file-image text-success mr-1"></i>';
            else if (ext === 'pdf') icon = '<i class="fas fa-file-pdf text-danger mr-1"></i>';
            else if (['xls', 'xlsx'].includes(ext)) icon =
                '<i class="fas fa-file-excel text-primary mr-1"></i>';

            $previewList.append(`
            <li class="list-group-item py-1 small d-flex justify-content-between align-items-center">
                <span>${icon} ${file.name}</span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-file-item"
                        data-row="${index}" data-fileindex="${fileIndex}" title="Hapus file">
                    &times;
                </button>
            </li>
        `);
        });
    }

    function updateStatusList(id_list, id_qco) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data Checklist ini akan di reset!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/QCO/Form/reset/' + id_qco + '/' + id_list,
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

    // 3. Handle Tombol Hapus (X) per File
    $(document).on('click', '.remove-file-item', function() {
        let rowIndex = $(this).data('row');
        let fileIndex = $(this).data('fileindex');

        // Hapus file dari array berdasarkan index-nya
        rowFilesMap[rowIndex].splice(fileIndex, 1);

        // Render ulang preview
        renderFileList(rowIndex);

        // (Opsional) Jika file habis dihapus, apakah checkbox mau di-uncheck otomatis?
        // Biarkan tetap tercheck atau uncheck sesuai kebutuhan.
    });
</script>

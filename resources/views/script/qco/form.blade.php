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

    let previewUrlsMap = {};

    const preparationFileBaseUrl = @json(url('/QCO/preparation-file'));

    $(document).ready(function() {
        $('#input_cso').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('qco.form.store') }}",
                method: "POST",
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
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
                            location.reload();
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
            formData.append('id_qco', $('#id_qco').val());
            formData.append('_token', $('input[name="_token"]').val());

            let hasError = false;
            let errorMessage = '';
            let newItemCount = 0;

            $.each(rowFilesMap, function(index, files) {
                let checkbox = $(`#list-${window.globalData.listPreparation[index].id}`);

                if (checkbox.is(':disabled')) {
                    return true;
                }

                if (checkbox.is(':checked')) {
                    newItemCount++;
                    let idList = checkbox.val();

                    let listName = checkbox.closest('.card, .row, div').find('label, span')
                        .first().text().trim() || 'Item Checklist';

                    if (!files || files.length === 0) {
                        hasError = true;
                        errorMessage = `Item "${listName}" wajib melampirkan file!`;
                        return false;
                    }

                    formData.append(`items[${index}][id_list]`, idList);

                    files.forEach(file => {
                        formData.append(`items[${index}][files][]`, file);
                    });
                }
            });

            if (hasError) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: errorMessage,
                    icon: 'warning'
                });
                return;
            }

            if (newItemCount === 0) {
                Swal.fire({
                    title: 'Info',
                    text: 'Pilih minimal satu item baru dan lampirkan filenya.',
                    icon: 'info'
                });
                return;
            }

            $.ajax({
                url: "{{ route('QCO.preparation.store') }}",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
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
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.',
                            'error');
                    }
                },
                error: function(xhr) {
                    let errMessage = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', errMessage, 'error');
                    console.log(xhr.responseText);
                }
            });
        });
    });

    function openModalQCO(id) {
        $('#id_qco').val(id);
        rowFilesMap = {};
        var listData = '';

        let preparationsArray = window.globalData.qcoList.find(item => item.id == id)?.preparations || [];
        let dataSelection = window.globalData.qcoList.find(item => item.id == id) || {};
        let submittedListIds = preparationsArray.map(prep => prep.id_list);

        if (dataSelection.status == 'CANCEL') {
            $('#label_modal_checkbox').hide();
            listData += `
                <div class="card p-4 mb-3 shadow-lg bg-danger text-white border-0 rounded-4 transition-all hover-effect"
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
                const matchedPrep = preparationsArray.find(prep => prep.id_list == value.id);

                const savedFiles = matchedPrep?.preparation_file || [];

                let savedFilesHtml = '';

                if (savedFiles.length > 0) {
                    savedFilesHtml = savedFiles.map(file => {
                        const extension = (file.file || '').split('.').pop().toLowerCase();
                        const isOfficeFile = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension);
                        const viewButton = isOfficeFile
                            ? ''
                            : `<a href="${preparationFileBaseUrl}/${file.id}/view" target="_blank" rel="noopener"
                                    class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation()">
                                    <i class="fas fa-eye"></i> View
                               </a>`;

                        return `
                            <li class="list-group-item py-2 d-flex justify-content-between align-items-center flex-wrap">
                                <span class="small text-break">
                                    <i class="fas fa-file-alt text-primary mr-1"></i>
                                    ${$('<div>').text(file.file).html()}
                                </span>

                                <span class="mt-1">
                                    ${viewButton}
                                    <a href="${preparationFileBaseUrl}/${file.id}/download"
                                    class="btn btn-sm btn-outline-secondary"
                                    onclick="event.stopPropagation()">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </span>
                            </li>
                        `;
                    }).join('');
                } else if (isAlreadySubmitted) {
                    savedFilesHtml = `
                        <li class="list-group-item py-1 small">
                            <i class="fas fa-exclamation-circle"></i>
                            File tidak ditemukan.
                        </li>
                    `;
                } else {
                    savedFilesHtml = `
                        <li class="list-group-item py-1 small">
                            Belum ada file dipilih
                        </li>
                    `;
                }

                const userName = matchedPrep?.user?.[0]?.name;
                const labelText = userName ? `Di update oleh ${userName}` : 'Sudah Disimpan';

                let nameAttr = isAlreadySubmitted ? 'udah' : `items[${index}][id_list]`;
                let disabledAttr = isAlreadySubmitted ? 'disabled checked' : '';
                let fileDisabledAttr = isAlreadySubmitted ? 'disabled' : '';
                let badgeText = isAlreadySubmitted ?
                    `<span class="badge bg-light text-dark d-inline-block text-wrap text-start px-2 py-1 mt-2 lh-sm" style="max-width: 100%;">${labelText}</span>` :
                    '';

                let cardClass = isAlreadySubmitted ? 'bg-success text-white border-success' : 'border';
                let textColor = isAlreadySubmitted ? 'text-white' : '';
                let onClick = isAlreadySubmitted ?
                    `onclick="updateStatusList(${value.id},${id})" style="cursor: pointer;"` : '';

                listData += `
                    <div class="card p-3 mb-2 shadow-sm ${cardClass}" ${onClick}>
                        <div class="row align-items-center">
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

                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <input type="file" class="form-control-file file-input-item"
                                        multiple
                                        accept=".jpg, .jpeg, .png, .pdf, .xls, .xlsx"
                                        data-index="${index}" ${fileDisabledAttr}>

                                    <small class="${textColor} d-block mt-1">
                                        ${isAlreadySubmitted ? '<i class="fas fa-check-circle"></i> File untuk item ini sudah di-upload dan dikunci.' : 'Format: Gambar, PDF, Excel (Bisa pilih banyak file sekaligus)'}
                                    </small>

                                    <ul class="list-group mt-2 preview-list-${index}">
                                        ${savedFilesHtml}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
        }

        $("#preparation_list").html(listData);

        const modalElement = document.getElementById('modalQCO');
        if (modalElement && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        } else {
            console.error('Bootstrap Modal belum tersedia.');
        }
    }

    $(document).on('change', '.file-input-item', function(e) {
        let index = $(this).data('index');
        let newFiles = Array.from(e.target.files);

        if (newFiles.length > 0) {
            newFiles.forEach(file => {
                rowFilesMap[index].push(file);
            });

            $(`#list-${window.globalData.listPreparation[index].id}`).prop('checked', true);
            renderFileList(index);
        }

        $(this).val('');
    });

    
    function renderFileList(index) {
        let $previewList = $(`.preview-list-${index}`);
        $previewList.empty();

        // Bersihkan URL preview sebelumnya agar tidak menumpuk.
        if (previewUrlsMap[index]) {
            previewUrlsMap[index].forEach(url => URL.revokeObjectURL(url));
        }
        previewUrlsMap[index] = [];

        let files = rowFilesMap[index];
        if (!files || files.length === 0) {
            $previewList.append(
                '<li class="list-group-item text-muted py-1 small">Belum ada file dipilih</li>'
            );
            return;
        }

        files.forEach((file, fileIndex) => {
            let ext = file.name.split('.').pop().toLowerCase();
            let icon = '<i class="fas fa-file text-secondary mr-1"></i>';
            let previewButton = '';

            if (['jpg', 'jpeg', 'png'].includes(ext)) {
                icon = '<i class="fas fa-file-image text-success mr-1"></i>';
            } else if (ext === 'pdf') {
                icon = '<i class="fas fa-file-pdf text-danger mr-1"></i>';
            } else if (['xls', 'xlsx', 'csv'].includes(ext)) {
                icon = '<i class="fas fa-file-excel text-success mr-1"></i>';
            } else if (['doc', 'docx'].includes(ext)) {
                icon = '<i class="fas fa-file-word text-primary mr-1"></i>';
            } else if (['ppt', 'pptx'].includes(ext)) {
                icon = '<i class="fas fa-file-powerpoint text-danger mr-1"></i>';
            }

            // PDF dan gambar dibuka langsung menggunakan browser.
            if (['pdf', 'jpg', 'jpeg', 'png'].includes(ext)) {
                const previewUrl = URL.createObjectURL(file);
                previewUrlsMap[index].push(previewUrl);
                previewButton = `
                    <button type="button"
                            class="btn btn-sm btn-outline-primary py-0 px-2 preview-file-item"
                            data-preview-url="${previewUrl}"
                            data-file-name="${$('<div>').text(file.name).html()}">
                        <i class="fas fa-eye"></i> View
                    </button>
                `;
            } else if (['doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx'].includes(ext)) {
                const downloadUrl = URL.createObjectURL(file);
                previewUrlsMap[index].push(downloadUrl);
                previewButton = `
                    <a href="${downloadUrl}"
                       download="${$('<div>').text(file.name).html()}"
                       class="btn btn-sm btn-outline-secondary py-0 px-2">
                        <i class="fas fa-download"></i> Download
                    </a>
                `;
            }

            let safeFileName = $('<div>').text(file.name).html();
            $previewList.append(`
                <li class="list-group-item py-2 small d-flex justify-content-between align-items-center flex-wrap">
                    <span class="text-break mr-2">${icon} ${safeFileName}</span>
                    <span class="mt-1">
                        ${previewButton}
                        <button type="button"
                                class="btn btn-sm btn-outline-danger py-0 px-2 remove-file-item"
                                data-row="${index}"
                                data-fileindex="${fileIndex}"
                                title="Hapus file">&times;</button>
                    </span>
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

    $(document).on('click', '.remove-file-item', function() {
        let rowIndex = $(this).data('row');
        let fileIndex = $(this).data('fileindex');

        rowFilesMap[rowIndex].splice(fileIndex, 1);
        renderFileList(rowIndex);
    });

    // Preview PDF dan gambar: buka file asli di tab baru.
    $(document).on('click', '#preparation_list .preview-file-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const url = $(this).attr('data-preview-url');
        if (url) {
            window.open(url, '_blank', 'noopener');
        }
    });


</script>

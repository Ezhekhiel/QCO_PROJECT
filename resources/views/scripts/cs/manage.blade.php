<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- SELECT2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- SELECT2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let chartMaxInstance = null;
    let chartCountInstance = null;

    $(document).ready(function() {
        main_model_running('');
        main_line('');
        main_area('');
        main_operator('');
        main_cell('');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#new-process').on('keydown', function(e) {

            if (e.key === 'Enter') {
                saveDataProcess();
            }

        });
        $('#new-line').on('keydown', function(e) {

            if (e.key === 'Enter') {
                saveDataLine();
            }

        });
        $('#new-area').on('keydown', function(e) {

            if (e.key === 'Enter') {
                saveDataArea();
            }
        });
        $('#formOperator').submit(function(e) {

            e.preventDefault();

            let nik = $('input[name=nik]').val();
            let nama = $('input[name=nama]').val();
            let remark = $('textarea[name=remark]').val();

            // AJAX
            $.ajax({
                url: "{{ route('cs.manage.save_operator') }}",
                type: 'POST',
                data: {
                    nik: nik,
                    nama: nama,
                    remark: remark
                },

                success: function(res) {

                    showAlert(
                        res.alert, res.message
                    );
                    main_operator();

                    $('#formOperator')[0].reset();

                },

                error: function(res) {

                    showAlert(
                        res.alert, res.message
                    );

                }

            });

        });
        $('#formCell').submit(function(e) {

            e.preventDefault();

            let cell = $('input[name=cell]').val();
            let target = $('input[name=target]').val();

            // AJAX
            $.ajax({
                url: "{{ route('cs.manage.save_cell') }}",
                type: 'POST',
                data: {
                    cell: cell,
                    target: target
                },

                success: function(res) {

                    showAlert(
                        res.alert, res.message
                    );
                    main_cell();

                    $('#formOperator')[0].reset();

                },

                error: function(res) {

                    showAlert(
                        res.alert, res.message
                    );

                }

            });

        });
        $('#new-process').on('change', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('cs.manage.save_process') }}",
                method: "POST",
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
                    $("#loading-spinner").show();
                },
                success: function(data) {
                    createAlert('', data.alert, data.text, data.color, true, true,
                        'pageMessages');
                    if (data.alert != 'Failed!') {
                        resetForm();
                        main_model_running('');
                    }
                },
                complete: function() {
                    $("#loading-spinner").hide();
                }
            })
        });
        $('.target-input').each(function() {

            // simpan value awal
            $(this).attr('data-old', $(this).val());

        });

        // saat focus
        $(document).on('focus', '.target-input', function() {

            $(this).attr('data-old', $(this).val());

            $(this).val('');

        });

        // saat keluar focus
        $(document).on('blur', '.target-input', function() {

            // kalau kosong / tidak diubah
            if ($(this).val() == '') {

                $(this).val($(this).attr('data-old'));

            }

        });

    });

    function saveDataProcess() {

        $.ajax({

            url: "{{ route('cs.manage.save_process') }}",
            type: "POST",

            data: {
                process_name: $('#new-process').val()
            },
            dataType: 'JSON',

            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                $('#new-process').val('')
                main_model_running('');
            },

            error: function(xhr) {
                showAlert(
                    res.alert, res.message
                );
            }

        });

    }

    function saveDataLine() {

        $.ajax({

            url: "{{ route('cs.manage.save_line') }}",
            type: "POST",

            data: {
                line: $('#new-line').val()
            },
            dataType: 'JSON',

            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                main_line('');
            },

            error: function(xhr) {
                showAlert(
                    res.alert, res.message
                );
            }

        });

    }

    function saveChangeModal() {
        var type = $('#type_modal').val();
        var id = $('#id_modal').val();
        var new_value = $('#new_value_modal').val();
        var date_update = $('#date_update').val();
        var wh_id = $('#wh_id').val();
        if (!isNaN(wh_id) && (wh_id < 8 || wh_id > 10)) {
            alert('Working hour tidak boleh kurang dari 8 dan lebih dari 10 jam kerja');
            return false;
        }
        $.ajax({

            url: "{{ route('cs.manage.saveChangeModal') }}",
            type: "POST",

            data: {
                type: type,
                id: id,
                new_value: new_value,
                date_update: date_update,
                wh_id: wh_id,
            },
            dataType: 'JSON',

            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                if (type == 'process') {
                    main_model_running();
                } else if (type == 'line') {
                    main_line('');
                    main_area('');
                } else if (type == 'area') {
                    main_area('');
                } else if (type == 'operator') {
                    main_operator();
                } else if (type == 'target') {
                    main_cell();
                }
                $('#myModal').modal('hide');

            },

            error: function(xhr) {
                showAlert(
                    res.alert, res.message
                );
            }

        });
    }

    function edit(type, id, value) {
        $('#myModal').modal('show');
        var html_body = '';
        title = 'Update ' + type
        if (type == "target") {
            html_body = `
                <div class="row g-4">
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">
                            Current Database Value
                        </label>
                        <div class="border rounded-3 bg-light px-3 py-2">
                            <span class="fw-semibold text-dark">
                                ${value}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">
                            Update Value Baru
                        </label>
                        <input type="text" id="id_modal" class="d-none" value="${id}">
                        <input type="text" id="type_modal" class="d-none" value="${type}">
                        <input type="text" id="new_value_modal" class="form-control rounded-3" placeholder="Input new value"
                            autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">
                            Tanggal perubahan
                        </label>
                        <input type="date" id="date_update" class="form-control rounded-3"
                            autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">
                            Working Hour
                        </label>
                        <input type="number" class="form-control" id="wh_id" name="wh" min="8" max="10" step="0.01" placeholder="8.00 - 10.00" required>
                    </div>
                </div>
            `;
        } else {
            html_body = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">
                            Database Value Baru
                        </label>
                        <div class="border rounded-3 bg-light px-3 py-2">
                            <span class="fw-semibold text-dark">
                                ${value}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">
                            Update Value Baru
                        </label>
                        <input type="text" id="id_modal" class="d-none" value="${id}">
                        <input type="text" id="type_modal" class="d-none" value="${type}">
                        <input type="text" id="new_value_modal" class="form-control rounded-3" placeholder="Input new value"
                            autocomplete="off">
                    </div>
                </div>
            `;
        }

        $('.modal-title').html(title);
        $('.modal-body').html(html_body);
    }

    function deleteData(type, id, id_process) {
        if (confirm("Are you sure you want to delete this data?")) {
            $.ajax({

                url: "{{ route('cs.manage.deleteDataModal') }}",
                type: "POST",

                data: {
                    type: type,
                    id: id,
                    id_process: id_process
                },
                dataType: 'JSON',

                success: function(res) {
                    showAlert(
                        res.alert, res.message
                    );
                    if (type == 'process') {
                        main_model_running();
                    } else if (type == 'line') {
                        main_line('');
                        main_area('');
                    } else if (type == 'area') {
                        main_area('');
                    } else if (type == 'operator') {
                        main_operator();
                    } else if (type == 'target') {
                        main_cell();
                    }
                },

                error: function(xhr) {
                    showAlert(
                        res.alert, res.message
                    );
                }
            });

        } else {

            // NO
            alert("Delete Failled!");

        }
    }

    function saveDataArea() {

        $.ajax({

            url: "{{ route('cs.manage.save_area') }}",
            type: "POST",

            data: {
                id_line: $('#select-line').val(),
                area: $('#new-area').val()
            },
            dataType: 'JSON',

            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                main_area('');
            },

            error: function(xhr) {
                showAlert(
                    res.alert, res.message
                );
            }

        });

    }


    function main_model_running(process) {
        $.ajax({
            url: "{{ route('cs.manage.main_model_running') }}",
            data: {
                process: process
            },
            method: "GET",
            success: function(res) {
                $('#tbody_model_running').html(res.tbody);
                $('.select2').select2();

            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function main_area(area) {
        $.ajax({
            url: "{{ route('cs.manage.main_area') }}",
            data: {
                area: area
            },
            method: "GET",
            success: function(res) {
                $('#tbody_area').html(res.tbody);
                $('.select2').select2();
            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function main_line(line) {
        $.ajax({
            url: "{{ route('cs.manage.main_line') }}",
            data: {
                line: line
            },
            method: "GET",
            success: function(res) {
                $('#tbody_line').html(res.tbody);
                $('#select-line').html(res.option);
                $('.select2').select2();
            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function main_operator(where) {
        $.ajax({
            url: "{{ route('cs.manage.main_operator') }}",
            data: {
                where: where
            },
            method: "GET",
            success: function(res) {
                $('#tbody_operator').html(res.tbody);
            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function main_cell(where) {
        $.ajax({
            url: "{{ route('cs.manage.main_cell') }}",
            data: {
                where: where
            },
            method: "GET",
            success: function(res) {
                $('#tbody_cell').html(res.tbody);
            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function editTarget(id, target) {
        $.ajax({
            url: "{{ route('cs.manage.edit_target') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: id,
                target: target
            },
            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                main_model_running('');
            },
            error: function(res) {
                showAlert(
                    res.alert, res.message
                );
            }
        });
    }

    function editModel(id_process, model) {
        $.ajax({
            url: "{{ route('cs.manage.edit_model_running') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id_process: id_process,
                model: model
            },
            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                main_model_running('');
            },
            error: function(res) {
                showAlert(
                    res.alert, res.message
                );
            }
        });
    }

    function showAlert(type, message_) {
        let message = '';
        if (type == 'success') {
            message = '<i class="bi bi-check-circle me-2"></i>' + message_;
        } else {
            message = '<i class="bi bi-x-circle me-2"></i>' + message_;
        }

        let alertBox = $('#alertMessage');


        // reset class
        alertBox.removeClass(
            'alert-success alert-danger alert-warning alert-info'
        );

        // set type
        alertBox.addClass('alert-' + type);

        // set text
        $('#alertText').html(message_);

        // show
        alertBox.fadeIn();

        // auto hide 3 detik
        setTimeout(function() {

            alertBox.fadeOut();

        }, 3000);

    }
</script>

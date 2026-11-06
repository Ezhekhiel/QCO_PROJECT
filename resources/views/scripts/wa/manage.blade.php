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
        main_schedule();
        let data_schedule = [];
        $('#list_group').select2({
            dropdownParent: $('#scheduleModal')
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#new-area').on('keydown', function(e) {

            if (e.key === 'Enter') {
                saveDataArea();
            }
        });
        $('#formSchedule').submit(function(e) {

            e.preventDefault();

            $.ajax({
                url: "{{ route('wa.store_schedule') }}",
                method: 'POST',
                data: $(this).serialize(),

                success: function(res) {
                    if (res.alert == 'success') {
                        main_schedule();
                    }
                    showAlert(
                        res.alert,
                        res.message
                    );

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan'
                    });
                }
            });

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
    let xhrMain = null;

    function manageDelete(id) {
        if (!confirm("Apakah anda yakin ingin menghapus data ini?")) {
            alert('Delete di batalkan!');
            return false;
        }
        if (xhrMain) {

            xhrMain.abort();

        }

        xhrMain = $.ajax({

            url: "{{ route('wa.delete') }}",
            type: 'POST',
            data: {
                id: id,

            },
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                if (res.alert == 'success') {
                    main();
                }
                showAlert(
                    res.alert,
                    res.message
                );
            },

            complete: function() {

                $('#table-loading')
                    .addClass('d-none');

                xhrChange = null;

            },

            error: function(xhr, status) {

                if (status !== 'abort') {
                    console.error(xhr);
                    showAlert(
                        'danger',
                        'Update failed'
                    );
                }

            }

        });
    }

    function renderWhatsAppNew(data) {
        let tbody = '';
        let no = 0;
        data.forEach(function(item) {
            let addBtn = /*html*/ `
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                        onclick="addDataGroup('${item.groupId}','${item.name}')">
                    <i class="bi-plus me-1"></i>
                    Add
                </button>
            `;

            tbody += /*html*/ `
                <tr>
                    <td>${++no}</td>
                    <td>${item.name}</td>
                    <td>${addBtn}</td>
                </tr>
            `;

        });
        $('#tbody_group_new').html(tbody);
    }

    function formatDateTime(datetime) {
        if (!datetime) {
            return '';
        }

        return datetime.substring(0, 16).replace('T', ' ');
    }

    function renderSchedule(data) {
        let tbody = '';
        let no = 0;
        data.forEach(function(item) {
            let addBtn = /*html*/ `
                <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                        onclick="deleteSchedule('${item.id}')">
                    <i class="bi bi-trash me-1"></i>
                    Delete
                </button>
            `;
            let send_time = item.send_time.substring(0, 5);
            let next_run_at = formatDateTime(item.next_run_at);
            let last_run_at = formatDateTime(item.last_run_at);

            tbody += /*html*/ `
                <tr>
                    <td>${++no}</td>
                    <td>${item.group.name}</td>
                    <td>${item.name}</td>
                    <td>${send_time}</td>
                    <td>${next_run_at}</td>
                    <td>${last_run_at}</td>
                    <td onclick="UpdateStatusSchedule(${item.id},'${item.is_active}')">${item.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Tidak Aktive</span>'}</td>
                    <td>${addBtn}</td>
                </tr>
            `;

        });
        $('#tbody_schedule').html(tbody);
    }

    function renderOption(data, id_list) {
        let option = '';
        data.forEach(function(item) {
            option += /*html*/ `
              <option value="${item.id}">${item.name}</option>
            `;

        });
        $('#' + id_list).html(option);
    }

    function deleteSchedule(id) {
        if (!confirm("Apakah anda yakin ingin menghapus data ini?")) {
            alert('Delete di batalkan!');
            return false;
        }
        $.ajax({
            url: "{{ route('wa.delete_schedule') }}",
            data: {
                id: id,
            },
            method: "POST",
            success: function(res) {
                if (res.alert == 'success') {
                    main_schedule();
                }
                showAlert(
                    res.alert,
                    res.message
                );

            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function UpdateStatusSchedule(id, status) {
        if (!confirm("Apakah anda yakin ingin merubah status ini?")) {
            alert('Update di batalkan!');
            return false;
        }
        $.ajax({
            url: "{{ route('wa.update_status') }}",
            data: {
                id: id,
                status: status,
            },
            method: "POST",
            success: function(res) {
                if (res.alert == 'success') {
                    main_schedule();
                }
                showAlert(
                    res.alert,
                    res.message
                );

            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function main_schedule(area) {
        var search_schedule = $('#search_schedule').val();
        $.ajax({
            url: "{{ route('wa.main_schedule') }}",
            data: {
                search_schedule: search_schedule,
            },
            method: "GET",
            success: function(data) {
                renderSchedule(data);
                data_schedule = data;
            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function open_modal_tambah_jadwal() {
        $.ajax({
            url: "{{ route('wa.open_modal_jadwal') }}",
            method: "GET",
            success: function(data) {
                renderOption(data, 'list_group');
            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
            }
        });
    }

    function addDataGroup(group_id, name) {
        if (!confirm(`Apakah anda yakin ingin menambahkan group: ${name} ke database?`)) {
            alert('Delete di batalkan!');
            return false;
        }
        if (xhrMain) {

            xhrMain.abort();

        }

        xhrMain = $.ajax({

            url: "{{ route('wa.add_data_group') }}",
            type: 'POST',
            data: {
                group_id: group_id,
                name: name
            },
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                if (res.alert == 'success') {
                    main();
                }
                showAlert(
                    res.alert,
                    res.message
                );
            },

            complete: function() {

                $('#table-loading')
                    .addClass('d-none');

                xhrChange = null;

            },

            error: function(xhr, status) {

                if (status !== 'abort') {
                    console.error(xhr);
                    showAlert(
                        'danger',
                        'Update failed'
                    );
                }

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

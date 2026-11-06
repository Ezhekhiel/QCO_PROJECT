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
        //========local network
        let storedQueue =
            localStorage.getItem(
                'performa_save_queue'
            );

        if (storedQueue) {

            saveQueue =
                JSON.parse(storedQueue);

            let total =
                Object.keys(saveQueue).length;

            if (total > 0) {

                console.log(
                    'Restore queue:',
                    total
                );

                updateSyncStatus();
                processQueue();

            }

        }
        updateSyncStatus();
        window.addEventListener('online', function() {

            updateSyncStatus();

            processQueue();

        });
        window.addEventListener('offline', function() {

            updateSyncStatus();

        });
        //========/local network
        main();
        $('#btnFilter').on('click', function() {


            if ($(this).html() == '<i class="bi bi-chevron-up"></i>') {

                $(this).html('<i class="bi bi-chevron-down"></i>');
            } else {
                $(this).html('<i class="bi bi-chevron-up"></i>');
            }
            $('#filterArea').slideToggle(100);
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#search_date').on('change', function(e) {
            e.preventDefault();
            main();
        })
        $('.shift-button').click(function() {

            let shift = $(this).data('shift');

            // remove active
            $('.shift-button').removeClass('active-shift');

            // add active
            $(this).addClass('active-shift');

            // hide all table
            $('#tableShift1').hide();
            $('#tableShift2').hide();

            // show selected
            if (shift == 'shift1') {

                $('#tableShift1').show();

            } else {

                $('#tableShift2').show();

            }

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
        // focus input ================////
        $('.target-input').each(function() {

            // simpan value awal
            $(this).attr('data-old', $(this).val());

        });

        $(document).on({

            mouseenter: function() {

                $(this)
                    .find('.hover-menu')
                    .stop(true, true)
                    .fadeIn(100);

            },

            mouseleave: function() {

                $(this)
                    .find('.hover-menu')
                    .stop(true, true)
                    .fadeOut(100);

            }

        }, '.mytr');
        $(document).on({

            mouseenter: function() {

                $(this)
                    .find('.hover-menu')
                    .stop(true, true)
                    .fadeIn(100);

            },

            mouseleave: function() {

                $(this)
                    .find('.hover-menu')
                    .stop(true, true)
                    .fadeOut(100);

            }

        }, '.mytr');
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
        // --------focus input ================////

    });
    let oldSelectValue = {};
    let rollbackSelect = false;

    $(document).on('mousedown', '.select2-selection', function() {

        let selectId = $(this)
            .closest('.select2')
            .prev('select')
            .attr('id');

        oldSelectValue[selectId] = $('#' + selectId).val();
    });

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

            error: function(res) {
                showAlert(
                    res.alert, res.message
                );
            }

        });

    }

    let xhrMain = null;

    function main() {

        const date = $('#search_date').val();
        if (xhrMain) {

            xhrMain.abort();

        }
        xhrMain = $.ajax({

            url: "{{ route('cs.performa.main') }}",

            method: "GET",

            data: {
                date: date
            },
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                document.getElementById('tbody-shift-1').innerHTML = res.tbody_1;

                document.getElementById('tbody-shift-2').innerHTML = res.tbody_2;
                $('#tbody-shift-1 .select2').select2({
                    width: '100%'
                });

                $('#tbody-shift-2 .select2').select2({
                    width: '100%'
                });
                $('.select-cell').select2({
                    width: '100px'
                });


            },
            complete: function() {

                $('#table-loading')
                    .addClass('d-none');
                xhrMain = null;

            },

            error: function(xhr, status) {
                if (status !== 'abort') {

                    console.error(
                        "Gagal memuat data",
                        xhr
                    );

                }
            }

        });

    }


    let saveQueue = {};
    let isSaving = false;

    function change_data(
        option,
        id,
        value,
        col
    ) {
        let el = document.getElementById(option + '-' + id);
        if (rollbackSelect) {
            rollbackSelect = false;
            return false;
        }
        if (value === '') {

            if (!confirm('Yakin mau kosongkan ini?')) {

                rollbackSelect = true;

                alert('Data perubahan di batalkan');

                $(el)
                    .val(oldSelectValue[el.id])
                    .trigger('change');

                return false;
            }
        }

        let key =
            id + '_' + col;

        saveQueue[key] = {

            option: option,
            id: id,
            value: value,
            col: col,
            created_at: Date.now()

        };

        saveQueueToStorage();
        updateSyncStatus();

        processQueue();

    }

    function updateSyncStatus() {

        let total =
            Object.keys(saveQueue).length;

        if (!navigator.onLine) {

            $('#sync-status')
                .removeClass('bg-success bg-warning')
                .addClass('bg-danger')
                .text('Tidak ada Koneksi (' + total + ')');

            return;
        }

        if (total > 0) {

            $('#sync-status')
                .removeClass('bg-success bg-danger')
                .addClass('bg-warning')
                .text('Sedang Proses Menyimpan (' + total + ')');

        } else {

            $('#sync-status')
                .removeClass('bg-warning bg-danger')
                .addClass('bg-success')
                .text('Data Tersimpan');

        }

    }

    function processQueue() {

        if (isSaving) {
            return;
        }

        let keys =
            Object.keys(saveQueue);

        if (keys.length === 0) {
            updateSyncStatus();
            return;
        }

        isSaving = true;

        let firstKey = keys[0];

        let item =
            saveQueue[firstKey];

        $.ajax({

            url: "{{ route('cs.performa.change_data') }}",

            type: 'POST',
            timeout: 10000,


            data: {

                option: item.option,

                id: item.id,

                value: item.value,

                col: item.col,

                date: $('#search_date').val()

            },

            success: function(res) {

                if (res.alert == 'danger') {

                    showAlert(
                        res.alert,
                        res.message
                    );

                    delete saveQueue[firstKey];

                    saveQueueToStorage();
                    updateSyncStatus();

                    return;
                }

                updateUI(item, res);

                if (
                    saveQueue[firstKey] &&
                    saveQueue[firstKey].created_at === item.created_at
                ) {
                    delete saveQueue[firstKey];
                }

                saveQueueToStorage();
                updateSyncStatus();

            },

            error: function(xhr) {

                console.error(xhr);

                showAlert(
                    'warning',
                    'Connection problem. Retrying...'
                );

            },

            complete: function() {

                isSaving = false;

                setTimeout(
                    processQueue,
                    1000
                );

            }

        });

    }

    function updateUI(item, res) {

        let option = item.option;
        let id = item.id;
        let value = item.value;
        let col = item.col;

        showAlert(
            res.alert,
            res.message
        );

        if (option == "operator" || option == "cell" || option == "model") {
            $('#' + option + "-" + id).html("");
            $('#' + option + "-" + id).html(res.option);
        } else {

            if (res.alert == 'danger') {

                $('#' + col + "-" + id).val(0);

                $('#' + col + "-" + id).trigger('blur');

                return false;

            }

            $('#' + col + "-" + id).val(value);

            var target = $('#target-' + id).html();

            var lastIndex = 0;

            var total = 0;

            for (let i = 10; i >= 1; i--) {

                var value_i =
                    parseFloat($('#O_' + i + "-" + id).val()) || 0;

                total += value_i;

                if (value_i != 0 && lastIndex == 0) {
                    lastIndex = i;
                }

            }

            var avg =
                lastIndex > 0 ?
                (total / lastIndex).toFixed(2) :
                0;

            var achive =
                target > 0 ?
                parseFloat(
                    ((avg / target) * 100).toFixed(2)
                ) :
                0;

            if (achive < 50) {

                $('#achieve-' + id)
                    .removeClass()
                    .addClass('bg-danger text-light');

            } else if (achive < 100) {

                $('#achieve-' + id)
                    .removeClass()
                    .addClass('bg-warning');

            } else {

                $('#achieve-' + id)
                    .removeClass()
                    .addClass('bg-success text-light');

            }

            $('#total-' + id).html(total);

            $('#avg-' + id).html(Math.ceil(avg));

            $('#achieve-' + id).html(Math.ceil(achive) + " %");

        }

    }

    function saveQueueToStorage() {

        if (Object.keys(saveQueue).length === 0) {

            localStorage.removeItem('performa_save_queue');

        } else {

            localStorage.setItem(
                'performa_save_queue',
                JSON.stringify(saveQueue)
            );

        }

    }

    function addData(option, shift) {
        var date = $('#search_date').val();
        $.ajax({
            url: "{{ route('cs.performa.addData') }}",
            data: {
                option: option,
                date: date,
                shift: shift
            },
            method: "GET",
            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                main()
                $('.select2').select2();
            },
            error: function(xhr) {
                console.error("Gagal memuat data chart awal", xhr);
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

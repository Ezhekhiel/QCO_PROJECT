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
            @auth
            main();
        @endauth
        @guest main_wip();
    @endguest
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('.form-control').on('focus', function() {
    // Menghapus class is-invalid dan is-valid saat input mulai diklik/diisi
    $(this).removeClass('is-invalid is-valid');
    });
    });

    let xhrMain = null;

    function main() {

        if (xhrMain) {

            xhrMain.abort();

        }
        xhrMain = $.ajax({

            url: "{{ route('cs.produksi.main') }}",

            method: "GET",
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                $('#po_list').html(res.option);
                $('#po_list_output').html(res.input_option);
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

    function renderHtmlManage(data) {
        let tbody_input = '';
        let tbody_output = '';

        let no_input = 0;
        let no_output = 0;

        data.forEach(function(item) {


            if (item.option === 'input') {
                let deleteBtn = /*html*/ `
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                            onclick="manageDelete(${item.id})">
                        <i class="bi bi-trash me-1"></i>
                        Delete
                    </button>
                `;
                tbody_input += /*html*/ `
                    <tr>
                        <td>${++no_input}</td>
                        <td>${item.po}</td>
                        <td>${item.wide}</td>
                        <td>${item.size_database}</td>
                        <td>${item.qty}</td>
                        <td>${deleteBtn}</td>
                    </tr>
                `;
            } else {
                let deleteBtn = /*html*/ `
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                            onclick="manageDelete(${item.id},'output')">
                        <i class="bi bi-trash me-1"></i>
                        Delete
                    </button>
                `;
                tbody_output += /*html*/ `
                    <tr>
                        <td>${++no_input}</td>
                        <td>${item.po}</td>
                        <td>${item.wide}</td>
                        <td>${item.remark}</td>
                        <td>${item.size_database}</td>
                        <td>${item.qty}</td>
                        <td>${deleteBtn}</td>
                    </tr>
                `;
            }
        });
        $('#tbody_input').html(tbody_input);
        $('#tbody_output').html(tbody_output);
    }

    function renderHtmlWip(data) {
        let html = '';

        $.each(data, function(cell, genders) {

            // Hitung rowspan cell
            let cellRowspan = 0;

            $.each(genders, function(g, items) {
                cellRowspan += items.length;
            });

            let firstCell = true;

            $.each(genders, function(g, items) {

                let genderRowspan = items.length;
                let firstGender = true;

                $.each(items, function(index, item) {

                    html += '<tr>';

                    // CELL
                    if (firstCell) {
                        html += `
                    <td rowspan="${cellRowspan}" class="align-middle fw-bold">
                        ${cell}
                    </td>
                `;
                        firstCell = false;
                    }

                    // DATA
                    html += `
                <td>${item.po}</td>
                <td>${item.wide}</td>
            `;
                    // GENDER
                    var color_gender = '';
                    if (firstGender) {
                        if (g.trim() === 'M' || g.trim() === 'W' || g.trim() === 'U') {
                            color_gender = 'bg-success text-light';
                        } else if (g.trim() === "K" || g.trim() === "J") {
                            color_gender = 'bg-warning';
                        } else {
                            color_gender = '';
                        }
                        html += `
                    <td rowspan="${genderRowspan}" class="align-middle ${color_gender}">
                        ${g.trim()}
                    </td>
                `;
                        firstGender = false;
                    }

                    // SIZE 1-29
                    var color_border_size = '';
                    var total_wip = 0;
                    for (let i = 1; i <= 29; i++) {
                        var value = item['size_' + i] ?? 0;
                        total_wip = parseInt(total_wip) + parseInt(value);
                        if (value > 0) {
                            color_border_size = 'mark-cell';
                        } else {
                            color_border_size = '';
                        }
                        html += `
                            <td class="${color_border_size}">
                                ${value}
                            </td>
                        `;
                    }
                    html += `
                            <td>
                                ${total_wip}
                            </td>
                        `;

                    html += '</tr>';
                });

            });

        });

        $('#tbody_wip').html(html);
    }

    function renderHtmlDb(data) {
        let html = '';

        $.each(data, function(cell, genders) {

            // Hitung rowspan cell
            let cellRowspan = 0;

            $.each(genders, function(g, items) {
                cellRowspan += items.length;
            });

            let firstCell = true;

            $.each(genders, function(g, items) {

                let genderRowspan = items.length;
                let firstGender = true;

                $.each(items, function(index, item) {

                    html += '<tr>';

                    // CELL
                    if (firstCell) {
                        html += `
                            <td rowspan="${cellRowspan}" class="align-middle fw-bold">
                                ${cell}
                            </td>
                        `;
                        firstCell = false;
                    }

                    // DATA
                    html += `
                        <td>${item.po}</td>
                        <td>${item.wide}</td>
                        <td>${item.qty_order}</td>
                        <td>${parseInt(item.qty_order)-parseInt(item.total_output)}</td>
                    `;
                    // GENDER
                    var color_gender = '';
                    if (firstGender) {
                        if (g.trim() === 'M' || g.trim() === 'W' || g.trim() === 'U') {
                            color_gender = 'bg-success text-light';
                        } else if (g.trim() === "K" || g.trim() === "J") {
                            color_gender = 'bg-warning';
                        } else {
                            color_gender = '';
                        }
                        html += `
                                <td rowspan="${genderRowspan}" class="align-middle ${color_gender}">
                                    ${g.trim()}
                                </td>
                            `;
                        firstGender = false;
                    }
                    // SIZE 1-29
                    var color_border_size = '';
                    var total_output = 0;
                    for (let i = 1; i <= 29; i++) {
                        var value = item.qty['size_' + i] ?? 0;
                        var order = item.size_order['size_' + i] ?? 0;
                        total_output = parseInt(total_output) + parseInt(value);
                        let balance = parseInt(order) - parseInt(value);
                        if (balance == 0 && value > 0) {
                            color_border_size = 'border border-4 border-success fw-bold';
                        } else if (balance > 0 || order > 0) {
                            color_border_size =
                                'text-danger fw-bold';
                        } else {
                            color_border_size = '';
                        }
                        if (order == 0) {
                            html += `
                                <td>
                                </td>
                            `;
                        } else {
                            html += `
                                <td class="${color_border_size}">
                                    ${balance}/${order}
                                </td>
                            `;
                        }
                    }
                    html += `
                            <td>
                                ${total_output}
                            </td>
                        `;
                    html += '</tr>';
                });

            });

        });

        $('#tbody_db').html(html);
    }

    function renderHtmlIo(data) {
        let html = '';

        $.each(data, function(cell, genders) {

            // Hitung rowspan cell
            let cellRowspan = 0;

            $.each(genders, function(g, items) {
                cellRowspan += items.length;
            });

            let firstCell = true;

            $.each(genders, function(g, items) {

                let genderRowspan = items.length;
                let firstGender = true;

                $.each(items, function(index, item) {

                    html += '<tr>';

                    // CELL
                    if (firstCell) {
                        html += `
                            <td rowspan="${cellRowspan}" class="align-middle fw-bold">
                                ${cell}
                            </td>
                        `;
                        firstCell = false;
                    }

                    // DATA
                    html += `
                        <td>${item.po}</td>
                        <td>${item.wide}</td>
                        <td>${item.qty_order}</td>
                        <td>${parseInt(item.qty_order)-parseInt(item.total_input)}</td>
                    `;
                    // GENDER
                    var color_gender = '';
                    if (firstGender) {
                        if (g.trim() === 'M' || g.trim() === 'W' || g.trim() === 'U') {
                            color_gender = 'bg-success text-light';
                        } else if (g.trim() === "K" || g.trim() === "J") {
                            color_gender = 'bg-warning';
                        } else {
                            color_gender = '';
                        }
                        html += `
                                <td rowspan="${genderRowspan}" class="align-middle ${color_gender}">
                                    ${g.trim()}
                                </td>
                            `;
                        firstGender = false;
                    }
                    // SIZE 1-29
                    var color_border_size = '';
                    var total_order = 0;
                    var total_input = 0;
                    for (let i = 1; i <= 29; i++) {
                        var order = item.data_order['size_' + i] ?? 0;
                        var input = item.qty_input['size_' + i] ?? 0;
                        total_order = parseInt(total_order) + parseInt(order);
                        total_input = parseInt(total_input) + parseInt(input);
                        let balance = parseInt(order) - parseInt(input);
                        if (balance == 0 && order > 0) {
                            color_border_size = 'border border-4 border-success fw-bold';
                        } else if (balance > 0 || order > 0) {
                            color_border_size =
                                'text-danger fw-bold';
                        } else {
                            color_border_size = '';
                        }
                        if (order == 0) {
                            html += `
                                <td>
                                </td>
                            `;
                        } else {
                            html += `
                                <td class="${color_border_size}">
                                    ${order}/${input}
                                </td>
                            `;
                        }
                    }
                    html += `
                     <td>
                                ${total_input}
                            </td>
                        `;
                    html += '</tr>';
                });

            });

        });

        $('#tbody_io').html(html);
    }

    function main_manage() {

        if (xhrMain) {

            xhrMain.abort();

        }
        var date = $('#search_date_manage').val();

        xhrMain = $.ajax({

            url: "{{ route('cs.produksi.main_manage') }}",
            data: {
                date: date,
            },
            method: "GET",
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(data) {
                renderHtmlManage(data)
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

    function main_wip() {

        if (xhrMain) {

            xhrMain.abort();

        }

        xhrMain = $.ajax({

            url: "{{ route('cs.produksi.main_wip') }}",
            method: "GET",
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(data) {
                renderHtmlWip(data)
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

    function main_db() {

        if (xhrMain) {

            xhrMain.abort();

        }

        xhrMain = $.ajax({

            url: "{{ route('cs.produksi.main_db') }}",
            method: "GET",
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(data) {
                renderHtmlDb(data)
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

    function io_db() {

        if (xhrMain) {

            xhrMain.abort();

        }

        xhrMain = $.ajax({

            url: "{{ route('cs.produksi.main_io') }}",
            method: "GET",
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(data) {
                renderHtmlIo(data)
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


    function manageDelete(id) {
        if (!confirm("Apakah anda yakin ingin menghapus data ini?")) {
            alert('Delete di batalkan!');
            return false;
        }
        if (xhrMain) {

            xhrMain.abort();

        }

        xhrMain = $.ajax({

            url: "{{ route('cs.produksi.manageDelete') }}",
            type: 'POST',
            data: {
                id: id,
            },
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                main_manage();
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

    function resetForm(option) {
        main();

        $('.form-control:not([type="date"])').val("");

        $('#wide_id_' + option).prop('readonly', true);

        $('.form-control').css('background-color', '');

        $('.form-control').prop('disabled', false);
    }

    var data_limit = genderData = [];

    function change_data(option) {
        var po = $('#po_id_' + option).val();
        var wide = $('#wide_id_' + option).val();

        if (xhrMain) {

            xhrMain.abort();

        }

        xhrMain = $.ajax({

            url: "{{ url('cs/produksi') }}/change_po_" + option,
            type: 'POST',
            data: {
                po: po,
                wide: wide,
                option: option,

            },
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                $('.validation_balance').val('');
                $('.form-control').css('background-color', '');
                if (res.count > 1) {
                    $('#wide_id_' + option).prop('readonly', false);
                    $('#wide_id_' + option).prop('placeholder', "Pilih Wide");
                    $('#wide_list_' + option).html(res.option);
                } else {
                    $('#wide_id_' + option).val(res.dataBalance.wide);
                    $('#cell_id_' + option).val(res.dataBalance.cell);
                    $('#style_id_' + option).val(res.dataBalance.article);
                    $('#qty_po_id_' + option).val(res.dataBalance.qty);
                    $('#gender_id_' + option).val(res.dataBalance.g);
                    data_limit = [];
                    genderData = res.genderData;
                    for (let i = 1; i < 30; i++) {
                        data_limit.push(res.dataBalance['qty_' + i]);

                        $('.label_' + option + '_' + i).html(res.genderData['size_' + i]);
                        if (res.dataBalance['size_' + i] == "0 of 0") {
                            $('#visual_' + option + '_' + i).prop('disabled', true);
                            $('#size_' + option + '_' + i).prop('disabled', true);
                        } else {
                            $('#visual_' + option + '_' + i).prop('disabled', false);
                            $('#size_' + option + '_' + i).prop('disabled', false);
                            const [current, total] = res.dataBalance['size_' + i]
                                .split(' of ')
                                .map(Number);

                            const percent = total ? (current / total) * 100 : 0;

                            let color = '';

                            if (percent > 100) {
                                color = '#f8d7da'; // merah
                            } else if (percent === 100) {
                                color = '#d1e7dd'; // hijau
                            } else if (percent > 0) {
                                color = '#fff3cd'; // kuning
                            }

                            $('#visual_' + option + '_' + i).css('background-color', color);

                            $('#visual_' + option + '_' + i).val(res.dataBalance['size_' + i]);
                        }
                    }
                }

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
{{-- update keyup --}}
<script>
    // ini function untuk keyup data
    function saveData(el, value) {
        var option = el.data('type');

        var form = getAllDataFormControl(option);

        var apakahSemuaKosong = form[`qty_${option}`].every(function(item) {
            return item === "";
        });
        if (apakahSemuaKosong) {
            alert('Form incoming belum ada yang di input!');
            return false;
        }
        if (form.logistik == "") {
            alert('Logistik harus di isi!');
            return false;
        }

        if (option == "output" && form.shift == "") {
            alert('Shift harus di pilih di form output!');
            return false;
        }
        var qtyData = form[`qty_${option}`];


        for (let i = 0; i < data_limit.length; i++) {
            // Pastikan nilai input di-trim untuk menghindari spasi tak terlihat, dan cek jika tidak kosong
            if (qtyData[i] !== undefined && qtyData[i].toString().trim() !== "") {

                // KONVERSI KE ANGKA (Gunakan fallback 0 jika gagal/NaN)
                var limitVal = Number(data_limit[i]) || 0;
                var qtyVal = Number(qtyData[i]) || 0;

                var cek = limitVal - qtyVal;

                if (isNaN(cek) || cek < 0) {
                    var i_ = i + 1;

                    alert('Qty tidak boleh lebih dari Order di size ' + genderData['size_' + i_]);

                    $('#size_' + option + '_' + i_)
                        .addClass('is-invalid')
                        .removeClass('is-valid');

                    return false;
                }
            }
        }

        if (xhrMain) {

            xhrMain.abort();

        }

        xhrMain = $.ajax({

            url: "{{ route('cs.produksi.save') }}",
            type: 'POST',
            data: {
                form: form,
                option: option
            },
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                showAlert(
                    res.alert, res.message
                );
                change_data(option)
                // resetForm();
                el.val('');

                formulaBuffer = '';

                overwriteMode = true;
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

    $(function() {

        const inputs = $('.excel-cell');

        let currentIndex = 0;
        let overwriteMode = true;
        let formulaBuffer = '';

        // =====================================
        // Helper
        // =====================================

        function isValidInput(index) {

            if (index < 0 || index >= inputs.length) {
                return false;
            }

            const el = $(inputs[index]);

            return !el.prop('disabled') &&
                !el.prop('readonly') &&
                el.is(':visible');
        }

        function selectCell(index) {

            if (!isValidInput(index)) {
                return;
            }

            inputs.removeClass('active-cell');

            currentIndex = index;

            overwriteMode = true;

            formulaBuffer = '';

            $(inputs[index]).addClass('active-cell');
        }

        function findNext(start, step) {

            let idx = start;

            while (
                idx >= 0 &&
                idx < inputs.length
            ) {

                if (isValidInput(idx)) {
                    return idx;
                }

                idx += step;
            }

            return currentIndex;
        }

        function calculateFormula(text) {
            return text
                .split('+')
                .reduce((sum, item) => {
                    return sum + (parseInt(item) || 0);
                }, 0);
        }

        // =====================================
        // Cari cell pertama yang aktif
        // =====================================

        for (let i = 0; i < inputs.length; i++) {

            if (isValidInput(i)) {

                selectCell(i);

                break;
            }
        }

        // =====================================
        // Klik untuk memilih cell
        // =====================================

        inputs.on('click', function() {

            currentIndex = inputs.index(this);

            selectCell(currentIndex);
        });

        // =====================================
        // Keyboard Navigation
        // =====================================

        $(document).on('keydown', function(e) {
            const target = $(e.target);

            // Jika sedang mengetik di input selain excel-cell
            if (
                target.is('input, textarea, select') &&
                !target.hasClass('excel-cell')
            ) {
                return;
            }


            const currentInput = $(inputs[currentIndex]);

            // =====================================
            // Angka 0 - 9
            // =====================================

            if (/^[0-9]$/.test(e.key)) {
                e.preventDefault();

                formulaBuffer += e.key;

                currentInput.val(formulaBuffer);

                overwriteMode = false;

                return;
            }

            // =====================================
            // Numpad
            // =====================================

            if (/^Numpad[0-9]$/.test(e.code)) {

                e.preventDefault();

                const number = e.code.replace('Numpad', '');

                if (overwriteMode) {

                    currentInput.val(number);

                    overwriteMode = false;

                } else {

                    currentInput.val(
                        currentInput.val() + number
                    );
                }

                currentInput.trigger('change');

                return;
            }

            // =====================================
            // support tombol +
            // =====================================

            if (e.key === '+') {

                e.preventDefault();

                if (
                    formulaBuffer.length > 0 &&
                    !formulaBuffer.endsWith('+')
                ) {

                    formulaBuffer += '+';

                    currentInput.val(formulaBuffer);
                }

                return;
            }

            // =====================================
            // Backspace
            // =====================================

            if (e.key === 'Backspace') {

                e.preventDefault();

                currentInput.val('');

                formulaBuffer = '';

                overwriteMode = true;

                currentInput.trigger('change');

                return;
            }

            // =====================================
            // Delete
            // =====================================

            if (e.key === 'Delete') {

                e.preventDefault();

                currentInput.val('');

                formulaBuffer = '';

                overwriteMode = true;

                currentInput.trigger('change');

                return;
            }

            if (e.key === 'Enter') {

                e.preventDefault();

                if (formulaBuffer !== '') {

                    const result = calculateFormula(formulaBuffer);

                    currentInput.val(result);

                    formulaBuffer = '';

                    overwriteMode = true;

                    currentInput.trigger('change');

                    // AJAX SAVE
                    saveData(currentInput, result);
                }

                return;
            }

            let nextIndex = currentIndex;

            switch (e.key) {

                // Layout Anda:
                //
                // 3   5T   8   10T  13  17
                // 3T  6    8T  11   13T 18
                // 4   6T   9   11T  14  19
                // 4T  7    9T  12   15  20
                // 5   7T   10  12T  16
                //
                // Atas/Bawah = 1 langkah
                // Kiri/Kanan = 5 langkah

                case 'ArrowDown':

                    e.preventDefault();

                    nextIndex = findNext(
                        currentIndex + 1,
                        1
                    );

                    break;

                case 'ArrowUp':

                    e.preventDefault();

                    nextIndex = findNext(
                        currentIndex - 1,
                        -1
                    );

                    break;

                case 'ArrowRight':

                    e.preventDefault();

                    nextIndex = findNext(
                        currentIndex + 5,
                        5
                    );

                    break;

                case 'ArrowLeft':

                    e.preventDefault();

                    nextIndex = findNext(
                        currentIndex - 5,
                        -5
                    );

                    break;

                default:
                    return;
            }

            selectCell(nextIndex);
        });

    });
</script>

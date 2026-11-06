<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- SELECT2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- SELECT2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let chartMaxInstance = null;
    let chartCountInstance = null;
    let machineData = {};
    $(document).ready(function() {
        main();

        setInterval(() => {
            if (!window.loadingData) {
                main();
            }
        }, 300000);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('input[name="shift"]').on('click', function() {
            main();
        });
    });

    let xhrMain = null;

    function renderMap(data) {
        if (data.length > 0) {
            $('#viewCCS').removeClass('d-none').addClass('d-flex');
        } else {
            $('#viewCCS').removeClass('d-flex').addClass('d-none');
        }
        $.each(data, function(i, item) {
            let id_area = item.area.area;
            let operator = item?.operator ?
                `${item.operator.nik} - ${item.operator.nama}` :
                '-';
            let target = item?.model_running?.process?.target ?? 0;
            if (target === 0) {
                $('#' + item.area.area).attr('class', 'cell bg-secondary-light').attr('title', `
                    MESIN TIDAK DI PAKAI
                `);
                return true;
            }
            let outputs = [
                Number(item.O_1),
                Number(item.O_2),
                Number(item.O_3),
                Number(item.O_4),
                Number(item.O_5),
                Number(item.O_6),
                Number(item.O_7),
                Number(item.O_8),
                Number(item.O_9),
                Number(item.O_10)
            ];

            let total = outputs.reduce((sum, val) => sum + val, 0);

            let lastIndex = outputs.findLastIndex(val => val != 0) + 1;

            let avg = lastIndex > 0 ?
                (total / lastIndex).toFixed(2) :
                '0.00';

            let achive = (
                target ?
                (avg / target) * 100 :
                0
            ).toFixed(2);

            if (achive < 50) {
                var color = 'bg-danger text-light';
            } else if (achive < 100) {
                var color = 'bg-warning';
            } else {
                var color = 'bg-success text-light';
            }

            machineData[item.id] = item;
            machineData[item.id]['avg'] = avg;
            machineData[item.id]['achive'] = achive;
            machineData[item.id]['color'] = color;
            machineData[item.id]['total'] = total;

            $("#" + item.area.area)
                .attr('data-id', item.id)
                .attr('data-bs-toggle', 'tooltip')
                .attr('class', 'cell ' + color)
                .attr(
                    'title',
                    `Operator : ${operator}
                Target : ${target}
                Total : ${total}
                Avg : ${avg}
                Achive : ${achive}`
                )
                .css('cursor', 'pointer')
                .off('click')
                .on('click', function() {
                    openModal($(this).data('id'));
                });


        });
    }

    function openModal(id) {

        let item = machineData[id];

        if (!item) {
            alert('Data tidak ditemukan');
            return;
        }
        $("#header_detail_modal").attr('class', 'modal-header ' + item.color)

        let html = `
        <table class="table table-bordered">
            <tr>
                <th>Area</th>
                <td>${item.area.area}</td>
            </tr>
            <tr>
                <th>Waktu</th>
                <td>${item.shift} | ${item.date}</td>
            </tr>
            <tr>
                <th>Cell</th>
                <td>${item.cell}</td>
            </tr>
            <tr>
                <th>Operator</th>
                <td>${item.operator?.nik ?? '-'} -
                    ${item.operator?.nama ?? '-'}</td>
            </tr>
            <tr>
                <th>Model</th>
                <td>${item.model_running.model_name}</td>
            </tr>
            <tr>
                <th>Process</th>
                <td>${item.model_running.process.process}</td>
            </tr>
            <tr>
                <th>Target / Jam</th>
                <td>${item.model_running.process.target}</td>
            </tr>
            <tr>
                <th>Total</th>
                <td>${item.total}</td>
            </tr>
            <tr>
                <th>AVG</th>
                <td>${item.avg}</td>
            </tr>
            <tr>
                <th>Achive</th>
                <td class="${item.color}">${item.achive}%</td>
            </tr>
        </table>
    `;

        $('#modalContent').html(html);

        new bootstrap.Modal(
            document.getElementById('detailModal')
        ).show();
    }

    function main() {
        if (xhrMain) {

            xhrMain.abort();

        }
        window.loadingData = true;
        var date = $('#search_date').val();
        let shift = $('input[name="shift"]:checked').val();
        xhrMain = $.ajax({

            url: "{{ route('dashboard.CCS.main') }}",
            data: {
                date: date,
                shift: shift
            },
            method: "GET",
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                renderMap(res);
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- apexcharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<script>
    window.globalData = {
        qcoList: @json($qcoList),
        listPreparation: @json($list),
        planMasterData: @json($planMaster),
        qcoListData: @json($qcoList)
    };
    let isTableLoaded = false;
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

            $.each(rowFilesMap, function(index, files) {
                let checkbox = $(`#list-${window.globalData.listPreparation[index].id}`);

                // Hanya proses jika checkbox dicentang
                if (checkbox.is(':checked')) {
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
        $('#formUbahQCO').on('submit', function(e) {
            e.preventDefault(); // Mencegah form reload halaman secara default

            // Ambil data form (termasuk _token, edit_model, edit_date, dan id_qco jika ada)
            var formData = $(this).serialize();

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Pastikan data Change Over QCO sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('QCO.update') }}", // Sesuaikan dengan route update di Laravel Anda
                        type: "POST",
                        data: formData,
                        dataType: "json",
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                // Tutup modal Bootstrap
                                $('#modalUbahQCO').modal('hide');

                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location
                                        .reload(); // Refresh halaman setelah sukses
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = "Terjadi kesalahan pada server.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Gagal!', errorMsg, 'error');
                        }
                    });
                }
            });
        });
        $('#modalQCO').on('hidden.bs.modal', function() {
            location.reload();
        });
        $('#master-tab').on('shown.bs.tab', function(e) {
            // Jika tabel belum pernah dirender, baru render sekarang
            if (!isTableLoaded) {
                renderPlanMasterTable();
                isTableLoaded = true;
            }
        });
        // Gunakan $(document).on agar bisa mendeteksi elemen yang dibuat secara dinamis
        $(document).on('click', '#btn_save_act_co', function(e) {
            e.preventDefault();

            var id_qco = $('#id_qco').val();
            var act_co_date_raw = $('#act_co_date').val();

            // Validasi 1: Cek jika input kosong
            if (!act_co_date_raw) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Silakan pilih tanggal dan waktu Actual Change Over terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Ambil hanya bagian tanggalnya saja (YYYY-MM-DD) jika input mengandung jam/waktu
            var act_co_date = act_co_date_raw.split('T')[0].split(' ')[0];

            // Ambil tanggal hari ini (YYYY-MM-DD)
            var today = new Date().toISOString().split('T')[0];

            // Validasi 2: Actual CO tidak boleh hari ini atau masa depan (harus sudah lewat)
            // Artinya jika tanggal input >= hari ini, maka diblokir
            if (act_co_date > today) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Belum bisa input ya, pastikan input actual change over hari ini atau sudah terlewat.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false; // Menghentikan eksekusi, AJAX tidak akan jalan
            }

            // Validasi 3: Cek status QCO
            // var info_status = $('#info_status').html().trim();
            // if (info_status == "NOT YET") {
            //     Swal.fire({
            //         title: 'Gagal!',
            //         text: 'QCO Belum lengkap!',
            //         icon: 'error',
            //         confirmButtonText: 'OK'
            //     });
            //     return false;
            // }

            // Lanjutkan proses AJAX jika lolos semua validasi
            $.ajax({
                url: '/QCO/update-actual-co',
                type: 'POST',
                dataType: 'json',
                data: {
                    act_co: act_co_date_raw,
                    id_qco: id_qco,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#btn_save_act_co').prop('disabled', true).text('Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message ||
                                'Data Actual Change Over berhasil disimpan.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.message ||
                                'Terjadi kesalahan saat menyimpan data.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan pada server. Silakan coba lagi.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    $('#btn_save_act_co').prop('disabled', false).text('Save');
                }
            });
        });
    });

    function renderPlanMasterTable() {
        const tbody = $('#planMasterBody');
        const thead = $('#planMasterHeader');

        // 1. Generate Header Tanggal (1 sampai 30 November 2026)
        let headerHtml =
            `<tr><th scope="col" style="width: 80px;">Cell</th><th scope="col" style="width: 140px;">OPTION</th>`;

        let dates = [];
        for (let i = 1; i <= 30; i++) {
            let dayStr = String(i).padStart(2, '0');
            let formattedDate = `2026-11-${dayStr}`;
            dates.push(formattedDate);

            // Format tampilan tanggal (misal: 01-Nov)
            let dateObj = new Date(formattedDate);
            let monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
            let label = `${dayStr}-${monthNames[dateObj.getMonth()]}`;
            headerHtml += `<th scope="col">${label}</th>`;
        }
        headerHtml += `</tr>`;
        thead.html(headerHtml);

        // 2. Ambil Unique Cells dari window.globalData.planMasterData
        let uniqueCells = [...new Set(window.globalData.planMasterData.map(item => item.cell))];

        if (uniqueCells.length === 0) {
            tbody.html(`<tr><td colspan="32" class="text-center">Tidak ada data master plan.</td></tr>`);
            return;
        }

        let bodyHtml = '';

        // 3. Looping setiap Cell
        uniqueCells.forEach(cellName => {
            // --- ROW 1: MASTER PLAN ---
            bodyHtml += `<tr>`;
            bodyHtml +=
                `<td rowspan="3" class="fw-bold bg-light align-middle" style="border-bottom: 3px solid #b0bec5;">${cellName}</td>`;
            bodyHtml += `<td class="fw-bold bg-light">MASTER PLAN</td>`;

            dates.forEach(dateStr => {
                let matchedPlans = window.globalData.planMasterData.filter(item => {
                    let itemDate = item.stitching_start_date.substring(0, 10);
                    return item.cell === cellName && itemDate === dateStr;
                });

                let models = [...new Set(matchedPlans.map(item => item.model))];
                let cellContent = models.length > 0 ? models.join('<br>--------<br>') : '';
                bodyHtml += `<td>${cellContent}</td>`;
            });
            bodyHtml += `</tr>`;

            // --- ROW 2: PLANNER QCO ---
            bodyHtml += `<tr>`;
            bodyHtml += `<td class="fw-bold bg-light">PLAN QCO</td>`;

            dates.forEach(dateStr => {
                // Ambil master models untuk perbandingan badge merah
                let masterModels = window.globalData.planMasterData.filter(item => {
                    let itemDate = item.stitching_start_date.substring(0, 10);
                    return item.cell === cellName && itemDate === dateStr;
                }).map(item => item.model);

                let matchedQcos = window.globalData.qcoListData.filter(item => {
                    let qcoCell = item.cell_info ? item.cell_info.area : null;
                    let qcoDate = item.change_over_date ? item.change_over_date.substring(0,
                        10) : null;
                    return qcoCell === cellName && qcoDate === dateStr;
                });

                let qcoFormatted = matchedQcos.map(item => {
                    let model = item.model;
                    if (!masterModels.includes(model)) {
                        return `<span class="badge bg-danger fs-6 px-2 py-1">${model}</span>`;
                    } else {
                        return `<span class="badge bg-success fs-6 px-2 py-1">${model}</span>`;
                    }
                });
                // Unique-kan array
                qcoFormatted = [...new Set(qcoFormatted)];

                let cellContent = qcoFormatted.length > 0 ? qcoFormatted.join('<br>--------<br>') : '';
                bodyHtml += `<td>${cellContent}</td>`;
            });
            bodyHtml += `</tr>`;

            // --- ROW 3: ACTUAL CO ---
            bodyHtml += `<tr>`;
            bodyHtml += `<td class="fw-bold bg-light" style="border-bottom: 3px solid #b0bec5;">ACTUAL CO</td>`;

            dates.forEach(dateStr => {
                let masterModels = window.globalData.planMasterData.filter(item => {
                    let itemDate = item.stitching_start_date.substring(0, 10);
                    return item.cell === cellName && itemDate === dateStr;
                }).map(item => item.model);

                let matchedActuals = window.globalData.qcoListData.filter(item => {
                    let qcoCell = item.cell_info ? item.cell_info.area : null;
                    let actualDate = (item.qco_status && item.qco_status.actual_change_over) ?
                        item.qco_status.actual_change_over.substring(0, 10) :
                        null;
                    return qcoCell === cellName && actualDate === dateStr;
                });

                let actualFormatted = matchedActuals.map(item => {
                    let model = item.model;
                    // PERUBAHAN DI SINI:
                    if (!masterModels.includes(model)) {
                        return `<span class="badge bg-danger fs-6 px-2 py-1">${model}</span>`; // Tidak sama -> Merah
                    } else {
                        return `<span class="badge bg-success fs-6 px-2 py-1">${model}</span>`; // Sama -> Hijau
                    }
                });
                actualFormatted = [...new Set(actualFormatted)];

                let cellContent = actualFormatted.length > 0 ? actualFormatted.join(
                    '<br>--------<br>') : '';
                bodyHtml += `<td style="border-bottom: 3px solid #b0bec5;">${cellContent}</td>`;
            });
            bodyHtml += `</tr>`;
        });

        tbody.html(bodyHtml);
    }

    // Object penampung file per baris (Key = index baris, Value = array file)

    function openModalQCO(id) {
        getDataAjaxModal(id);
        $('#id_qco').val(id);
        $('#modalQCO').modal('show');
    }

    function updateQCO() {
        var id_qco = $('#id_qco').val();
        $('#id_qco_update').val(id_qco);
        $('#modalUbahQCO').modal('show');
    }

    function updateStatusCancel() {
        var id_qco = $('#id_qco').val();
        // Validasi sederhana jika id_qco kosong
        if (!id_qco) {
            Swal.fire('Error!', 'ID QCO tidak ditemukan.', 'error');
            return;
        }

        Swal.fire({
            title: 'Apakah Anda yakin update status cancel?',
            text: "Data yang sudah di batalkan tidak bisa di ulang!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, batalkan!', // Disesuaikan dari 'Ya, hapus!'
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('/QCO/updateStatusCancel') }}",
                    type: "POST",
                    data: {
                        id_qco: id_qco,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Data QCO sudah dibatalkan.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Refresh halaman atau reload DataTable setelah alert sukses tertutup
                            location.reload();
                            // Atau jika pakai DataTable: $('#myTable').DataTable().ajax.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);

                        // Ambil pesan error dari Laravel jika ada (misal validasi/exception)
                        let errorMsg = "Gagal memproses permintaan ke server.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire(
                    'Dibatalkan',
                    'Data QCO Anda aman :)',
                    'info' // Diubah dari 'error' ke 'info' agar lebih natural saat dibatalkan
                );
            }
        });
    }

    function cancelQCO() {
        var id_qco = $('#id_qco').val();

        // Validasi sederhana jika id_qco kosong
        if (!id_qco) {
            Swal.fire('Error!', 'ID QCO tidak ditemukan.', 'error');
            return;
        }

        Swal.fire({
            title: 'Apakah Anda yakin batalkan QCO ini?',
            text: "Data yang sudah di batalkan tidak bisa di ulang!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, batalkan!', // Disesuaikan dari 'Ya, hapus!'
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('/QCO/cancelQCO') }}",
                    type: "POST",
                    data: {
                        id_qco: id_qco,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Data QCO sudah dibatalkan.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Refresh halaman atau reload DataTable setelah alert sukses tertutup
                            location.reload();
                            // Atau jika pakai DataTable: $('#myTable').DataTable().ajax.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);

                        // Ambil pesan error dari Laravel jika ada (misal validasi/exception)
                        let errorMsg = "Gagal memproses permintaan ke server.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire(
                    'Dibatalkan',
                    'Data QCO Anda aman :)',
                    'info' // Diubah dari 'error' ke 'info' agar lebih natural saat dibatalkan
                );
            }
        });
    }

    function getDataAjaxModal(id_qco) {

        $.ajax({
            url: "{{ url('/QCO/dataModal') }}",
            type: "POST",
            data: {
                id_qco: id_qco,
                _token: "{{ csrf_token() }}" // Wajib untuk POST di Laravel
            },
            dataType: "json",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                renderModal(response)
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                $('#loading-state').html(`
                    <p class="text-danger">Gagal memuat data dari server.</p>
                `);
            }
        });
    }

    window._qcoRenderQueue_ = Promise.resolve();

    function renderModal(response) {
        window._qcoRenderQueue_ = window._qcoRenderQueue_
            .then(async function() {
                let dataQCO = response.dataQCO || {};
                let prepList = response.preparation_list || [];
                let checklistData = dataQCO.preparations_checklist || {};

                // A. Render Header
                renderHeaderInfo(dataQCO);

                // B. Render Checklist
                let completedItemsCount = renderChecklistTables(checklistData);

                // C. Render Statistik
                renderQuickStatistics(
                    dataQCO,
                    prepList,
                    completedItemsCount
                );

                // D. Tunggu browser menyelesaikan layout
                await new Promise(resolve => {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(resolve);
                    });
                });

                // E. Render Chart
                await renderCharts(dataQCO, checklistData);

            })
            .catch(function(error) {
                console.error('QCO Render Error:', error);
            });

        return window._qcoRenderQueue_;
    }

    function renderHeaderInfo(dataQCO) {
        $('#info_model').text(dataQCO.model || '-');
        $('#info_stage').text(dataQCO.stage || '-');
        $('#change_over_date').text(dataQCO.change_over_date || '-');

        let status = dataQCO.status || 'UNKNOWN';
        let statusBadge = $('#info_status');
        statusBadge.text(status);
        if (status == 'CANCEL') {
            $("#update_or_cancel").html("");
        }

        let view_act_co = '';
        if (dataQCO.actual_change_over != null) {
            const actualDate = new Date(dataQCO.actual_change_over);
            const planDate = new Date(dataQCO.change_over_date);
            let color = (actualDate > planDate) ? 'bg-danger' : 'bg-success';

            view_act_co = `<span class="badge px-2 py-1 ${color} text-white">${dataQCO.actual_change_over}</span>`;
        } else {
            view_act_co = `
            <input type="date" id="act_co_date" class="form-control" />
            <div class="input-group-append">
                <button id="btn_save_act_co" class="btn btn-primary btn-sm" type="button">Save</button>
            </div>
        `;
        }
        $('#act_change_over').html(view_act_co);

        statusBadge.removeClass('bg-secondary bg-warning bg-success bg-danger text-white text-dark');
        if (status === 'COMPLETED' || status === 'DONE') {
            statusBadge.addClass('bg-success text-white');
        } else if (status === 'ON PROGRESS' || status === 'PROCESS') {
            statusBadge.addClass('bg-warning text-dark');
        } else {
            statusBadge.addClass('bg-secondary text-white');
        }
    }

    function renderChecklistTables(checklistData) {
        let tbody = $('#master_checklist_tbody');
        let tbodySummary = $('#summary_preparation_tbody');
        let rowsHtml = '';
        let rowsHtmlSummary = '';
        let completedItemsCount = 0;

        $.each(checklistData, function(deptName, deptInfo) {
            let items = deptInfo.items || [];
            let percentage = Number(deptInfo.percentage) || 0;
            let rowSpan = items.length > 0 ? items.length : 1;

            completedItemsCount += (deptInfo.completed !== undefined ? Number(deptInfo.completed) : items
                .length);

            if (items.length === 0) {
                rowsHtml += `
                <tr>
                    <td class="align-middle fw-bold bg-white text-center" rowspan="1">${deptName}</td>
                    <td class="text-muted fst-italic align-middle text-center">Belum ada checklist</td>
                    <td class="text-muted fst-italic align-middle text-center">Tidak ada file</td>
                </tr>
            `;
            } else {
                items.forEach(function(item, index) {
                    let data_files = item.preparation_file;
                    let fileHtml = '<span class="text-muted fst-italic">Tidak ada file</span>';

                    if (data_files && data_files.length > 0) {
                        let links = [];
                        data_files.forEach(function(fileObj, index2) {
                            let fileUrl = `/data_qco/${fileObj.file}`;
                            links.push(`
                            <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 me-1 mb-1" title="${fileObj.file}">
                                <i class="fas fa-download"></i> File - ${index2 + 1}
                            </a>
                        `);
                        });
                        fileHtml = links.join('');
                    }

                    rowsHtml += `<tr>`;
                    if (index === 0) {
                        rowsHtml +=
                            `<td class="align-middle fw-bold bg-white text-center" rowspan="${rowSpan}">${deptName}</td>`;
                    }
                    rowsHtml += `
                    <td class="align-middle">
                        <div class="form-check d-flex align-items-center justify-content-center">
                            <input class="form-check-input me-2" type="checkbox" checked disabled>
                            <label class="form-check-label mb-0">${item.list}</label>
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex flex-wrap justify-content-center">${fileHtml}</div>
                    </td>
                </tr>`;
                });
            }

            let badgeClass = percentage === 100 ? 'text-success fw-bold' : (percentage > 0 ?
                'text-primary fw-bold' : 'text-muted');
            rowsHtmlSummary += `
            <tr>
                <td class="align-middle fw-bold text-center">${deptName}</td>
                <td class="align-middle ${badgeClass} text-center">${percentage}%</td>
            </tr>
        `;
        });

        tbody.html(rowsHtml);
        tbodySummary.html(rowsHtmlSummary);

        return completedItemsCount;
    }

    function renderQuickStatistics(dataQCO, prepList, completedItemsCount) {
        let totalItemsCount = prepList && prepList.length > 0 ? prepList.length : completedItemsCount;
        let remainingItemsCount = totalItemsCount - completedItemsCount;
        if (remainingItemsCount < 0) remainingItemsCount = 0;

        $('#stat_total').text(totalItemsCount);
        $('#stat_completed').text(completedItemsCount);
        $('#stat_remaining').text(remainingItemsCount);
        $('#stat_date').text(dataQCO.change_over_date || '-');
    }

    async function renderCharts(dataQCO, checklistData) {
        await new Promise(resolve => {
            requestAnimationFrame(() => {
                requestAnimationFrame(resolve);
            });
        });

        // ==========================================
        // 1. DONUT CHART
        // ==========================================
        let chartElement = document.querySelector("#qcoDonutChart");

        if (chartElement) {
            let preparedVal = Number(dataQCO.completion);
            if (!Number.isFinite(preparedVal)) preparedVal = 0;
            preparedVal = Math.max(0, Math.min(100, preparedVal));
            let unpreparedVal = 100 - preparedVal;

            let chartOptions = {
                series: [preparedVal, unpreparedVal],
                chart: {
                    type: 'donut',
                    height: 220,
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                labels: ['Prepared', 'Unprepared'],
                colors: ['#0d6efd', '#e9ecef'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '80%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '12px',
                                    fontWeight: '600',
                                    color: '#6c757d',
                                    offsetY: -6
                                },
                                value: {
                                    show: true,
                                    fontSize: '20px',
                                    fontWeight: '700',
                                    color: '#212529',
                                    offsetY: 6,
                                    formatter: function(val) {
                                        let num = Number(val);
                                        return (Number.isFinite(num) ? Math.round(num) : 0) + '%';
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Completion',
                                    color: '#6c757d',
                                    fontSize: '12px',
                                    formatter: function() {
                                        return Math.round(preparedVal) + '%';
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '12px'
                },
                stroke: {
                    show: true,
                    colors: ['#ffffff'],
                    width: 3
                },
                noData: {
                    text: 'No Data Available'
                }
            };

            let donutChart = new ApexCharts(chartElement, chartOptions);
            window._qcoChartInstance_ = donutChart;
            await donutChart.render();
        }

        // ==========================================
        // 2. BAR CHART
        // ==========================================
        let barElement = document.querySelector("#qcoBarChart");

        if (barElement) {
            let deptCategories = [];
            let deptPercentages = [];

            $.each(checklistData || {}, function(deptName, deptInfo) {
                deptCategories.push(deptName);
                let percentageVal = Number(deptInfo.percentage);
                if (!Number.isFinite(percentageVal)) percentageVal = 0;
                percentageVal = Math.max(0, Math.min(100, percentageVal));
                deptPercentages.push(percentageVal);
            });

            let barChartOptions = {
                series: [{
                    name: 'Readiness',
                    data: deptPercentages
                }],
                chart: {
                    type: 'bar',
                    height: 280,
                    fontFamily: 'inherit',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '50%',
                        distributed: false
                    }
                },
                colors: ['#0d6efd'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        let num = Number(val);
                        if (!Number.isFinite(num) || num === 0) return "";
                        return Math.round(num) + "%";
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '10px',
                        fontWeight: '600',
                        colors: ["#ffffff"]
                    }
                },
                xaxis: {
                    categories: deptCategories,
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    max: 110,
                    tickAmount: 4,
                    labels: {
                        formatter: function(val) {
                            let num = Number(val);
                            return (Number.isFinite(num) ? Math.round(num) : 0) + '%';
                        }
                    }
                },
                grid: {
                    borderColor: '#e9ecef',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function(val) {
                            let num = Number(val);
                            return (Number.isFinite(num) ? Math.round(num) : 0) + "% Readiness";
                        }
                    }
                },
                noData: {
                    text: 'No Data Available'
                }
            };

            let barChart = new ApexCharts(barElement, barChartOptions);
            window._qcoBarChartInstance_ = barChart;
            await barChart.render();
        }
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

    function triggerReminder() {
        // Ambil CSRF token dari meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Berikan feedback visual bahwa proses sedang berjalan (opsional)
        console.log("Mengirim reminder...");

        fetch('/trigger-reminder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message); // Notifikasi sukses
                } else {
                    alert('Terjadi kesalahan!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal menghubungi server.');
            });
    }
</script>

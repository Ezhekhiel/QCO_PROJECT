<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- SELECT2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- SELECT2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- amchart --}}
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<script>
    let chartMaxInstance = null;
    let chartCountInstance = null;
    let machineData = {};

    $(document).ready(function() {
        main();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('input[name="shift"]').on('click', function() {
            main();
        });
        $('#search_date').on('change', function(event) {
            main();
        })
    });

    let xhrMain = null;

    function main() {

        if (xhrMain) {

            xhrMain.abort();

        }
        window.loadingData = true;
        var date = $('#search_date').val();
        xhrMain = $.ajax({

            url: "{{ route('dashboard.production.main') }}",
            data: {
                date: date,
            },
            method: "GET",
            beforeSend: function() {
                $('#table-loading')
                    .removeClass('d-none');
            },
            success: function(res) {
                renderMap(res.data_production, res.cell_target);
                renderSummaryModel(res.data_by_model)
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

    function renderSummaryModel(data) {
        let html = '';

        $.each(data, function(index, item) {
            let persen = item.percent;
            let bgColor = '';
            if (persen >= 95) {
                bgColor = '#00b050';
            } else if (persen > 80 && persen < 95) {
                bgColor = '#ffc000';
            } else {
                bgColor = '#ff0000';
            }
            html += '<tr>';
            html +=
                `
                            <td>${item.model}</td>
                            <td>${item.total_cell}</td>
                            <td>${item.target}</td>
                            <td>${item.output}</td>
                            <td style="min-width:28px;">
                                    <div class="position-relative">
                                        <div class="progress rounded-1" style="height:20px;">
                                            <div class="progress-bar rounded-pill"
                                                style="width:${item.percent}%; background:${bgColor};">
                                                ${item.percent}%
                                            </div>
                                        </div>

                                    </div>
                                </td>`;

            html += '</tr>';
        });
        $('#summaryTableModel').html(html);
    }

    function renderMap(data, arrTarget) {
        let html = '';
        let chartData = [];
        let input_today = 0;
        let input_yesterday = 0;
        let output_today = 0;
        let output_yesterday = 0;
        let total_daily_target = 0;
        let wip_today = 0;
        let wip_yesterday = 0;
        let total_wip = 0;
        $.each(data, function(cell, style) {

            let cellRowspan = 0;
            let cellTotalOutput = 0;
            let cellTotalInput = 0;
            let cellTotalWipBaru = 0;

            let search_target = arrTarget.find(x => x.cell === cell);
            console.log(search_target);
            if (!search_target) {}

            let daily_target = Math.floor(search_target.target *
                search_target.working_hour);

            // HITUNG TOTAL CELL DULU
            $.each(style, function(s, items) {
                cellRowspan += items.length;

                $.each(items, function(index, item) {
                    cellTotalOutput += (
                        parseInt(item.output_shift_1_today || 0) +
                        parseInt(item.output_shift_2_today || 0)
                    );
                    cellTotalInput += parseInt(item.input_today || 0);
                    cellTotalWipBaru += parseInt(item.wip_baru || 0);
                });
            });


            chartData.push({
                "cell": cell,
                "input": cellTotalInput,
                "output": cellTotalOutput,
                "wip_baru": cellTotalWipBaru,
                "target": daily_target * 1.5
            });

            let firstCell = true;

            $.each(style, function(s, items) {

                $.each(items, function(index, item) {
                    let input_lama = parseInt(item.input_lama);
                    let output_lama = parseInt(item.output_lama);

                    let output_shift_1_today = parseInt(item.output_shift_1_today);
                    let output_shift_2_today = parseInt(item.output_shift_2_today);


                    // ✅ PAKAI TOTAL CELL
                    let percent = Math.floor((cellTotalOutput / daily_target) * 100);
                    let bgColor =
                        percent >= 100 ? "#198754" : // hijau
                        percent >= 50 ? "#ffc107" : // kuning
                        "#dc3545"; // merah

                    html += '<tr>';

                    if (firstCell) {
                        html += `
                                <td rowspan="${cellRowspan}" class="align-middle fw-bold">
                                    ${cell}
                                </td>
                            `;
                    }

                    html += `<td>${item.style}</td>`;

                    if (firstCell) {
                        html += `
                                <td rowspan="${cellRowspan}">${search_target.target}</td>
                                <td rowspan="${cellRowspan}">${search_target.working_hour}</td>
                                <td rowspan="${cellRowspan}">${daily_target}</td>
                            `;
                        total_daily_target += Number(daily_target || 0);
                    }
                    input_today += Number(item.input_today || 0);
                    input_yesterday += Number(item.input_yesterday || 0);
                    output_today += Number(item.output_today || 0);
                    output_yesterday += Number(item.input_yesterday || 0);
                    total_wip += Number(item.wip_baru || 0);

                    wip_today += Number(item.wip_baru || 0);
                    wip_yesterday += Number(item.wip_lama || 0);


                    html += `
                            <td>${item.wip_lama}</td>
                            <td>${item.input_today}</td>
                            <td>${item.output_shift_1_today}</td>
                            <td>${item.output_shift_2_today}</td>
                            <td>${item.output_today}</td>
                            <td>${item.wip_baru}</td>`;
                    if (firstCell) {
                        html += `
                                <td rowspan="${cellRowspan}" style="min-width:90px;">
                                    <div class="position-relative">
                                        <div class="progress rounded-1" style="height:20px;">
                                            <div class="progress-bar rounded-pill"
                                                style="width:${percent}%; background:${bgColor};">
                                                ${percent}%
                                            </div>
                                        </div>

                                    </div>
                                </td>
                            `;
                        firstCell = false;
                    }


                    html += '</tr>';
                });
            });
        });
        let achievement = Math.floor((output_today / total_daily_target) * 100);
        let achievement_yesterday = Math.floor((output_yesterday / total_daily_target) * 100);
        const trend_input = input_yesterday > 0 ?
            Math.floor(((input_today - input_yesterday) / input_yesterday) * 100) :
            0;
        const trend_output = output_yesterday > 0 ?
            Math.floor(((output_today - output_yesterday) / output_yesterday) * 100) :
            0;
        const trend_achieve = achievement_yesterday > 0 ?
            Math.floor(((achievement - achievement_yesterday) / achievement_yesterday) * 100) :
            0;
        const trend_wip = wip_yesterday > 0 ?
            Math.floor(((wip_today - wip_yesterday) / wip_yesterday) * 100) :
            0;
        const jumlah_wip = chartData.filter(item => item.wip_baru > item.target).length;
        const jumlah_wip_semua = chartData.length;
        const percentage_wip = jumlah_wip_semua > 0 ? (jumlah_wip / jumlah_wip_semua) * 100 : 0;

        let color_wip;

        if (percentage_wip >= 100) {
            color_wip = "#198754"; // success
        } else if (percentage_wip < 50) {
            color_wip = "#dc3545"; // danger
        } else {
            color_wip = "#ffc107"; // warning
        }

        $('#total_input').html(input_today.toLocaleString("id-ID"));
        $('#trend_input').html(trend_input + "%");
        $('#total_output').html(output_today.toLocaleString("id-ID"));
        $('#trend_output').html(trend_output + "%");
        $('#total_target').html(total_daily_target.toLocaleString("id-ID"));
        $('#achievement').html(achievement + "%");
        $('#total_wip').html(total_wip.toLocaleString("id-ID"));
        $('#total_wip').html(total_wip.toLocaleString("id-ID"));
        $('#icon_wip').css("background-color", color_wip);
        $('#total_cell_wip').html(jumlah_wip + " Cell Ready");
        $('#total_cell_wip_all').html("Dari " + jumlah_wip_semua + " Cell");

        setTrend("input", trend_input);
        setTrend("output", trend_output);
        setTrend("achieve", trend_achieve);
        setTrend("wip", trend_wip);

        $('#summaryTable').html(html);

        createAmChart(chartData);
        createWipChart(chartData);
    }

    function setTrend(type, trend) {
        const badge = $(`#trend_${type}_badge`);
        const icon = $(`#trend_${type}_icon`);
        const text = $(`#trend_${type}`);

        badge.removeClass("trend-up trend-down trend-flat");

        if (trend > 0) {
            badge.addClass("trend-up");
            icon.removeClass().addClass("bi bi-arrow-up");
        } else if (trend < 0) {
            badge.addClass("trend-down");
            icon.removeClass().addClass("bi bi-arrow-down");
        } else {
            badge.addClass("trend-flat");
            icon.removeClass().addClass("bi bi-dash");
        }

        text.text(`${Math.abs(trend)}%`);
    }

    var myInputOutputChartRoot = null;

    function createAmChart(chartData) {
        // JIKA ROOT SUDAH ADA, LANGSUNG DISPOSE DI SINI
        if (myInputOutputChartRoot) {
            myInputOutputChartRoot.dispose();
            myInputOutputChartRoot = null;
        }

        // Initialize Root baru dan simpan ke variabel global
        let root = am5.Root.new("chartInputOutput");
        myInputOutputChartRoot = root;

        // Set Theme
        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        // Create Chart
        let chart = root.container.children.push(am5xy.XYChart.new(root, {
            panX: false,
            panY: false,
            wheelX: "none",
            wheelY: "none",
            layout: root.verticalLayout
        }));
        // ---- SETTING KURSOR UNTUK HOVER EFFECT ----
        // Ini membuat garis pemandu (crosshair) muncul dan menyatukan tooltip saat di-hover
        let cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
            behavior: "none"
        }));
        cursor.lineY.set("visible", false); // Sembunyikan garis horizontal cursor jika mengganggu
        cursor.lineX.set("stroke", am5.color(0xaaaaaa)); // Warna garis vertikal pemandu

        // Create Axes
        let xRenderer = am5xy.AxisRendererX.new(root, {
            minGridDistance: 30
        });
        xRenderer.labels.template.setAll({
            rotation: -45,
            centerY: am5.p50,
            centerX: am5.p100,
            paddingRight: 15
        });

        let xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
            categoryField: "cell",
            renderer: xRenderer,
            tooltip: am5.Tooltip.new(root, {})
        }));

        xAxis.data.setAll(chartData);

        let yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
            renderer: am5xy.AxisRendererY.new(root, {})
        }));

        // 1. SERIES: INPUT (Kolom Biru)
        let seriesInput = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: "Input",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "input",
            categoryXField: "cell",
            clustered: true,
            fill: am5.color(0x00a2e8), // Biru cerah sesuai gambar
            stroke: am5.color(0x00a2e8)
        }));

        seriesInput.columns.template.setAll({
            width: am5.percent(70),
            cornerRadiusTL: 4,
            cornerRadiusTR: 4,
            tooltipText: "{name}\n{categoryX}: [bold]{valueY}[/]"
        });
        seriesInput.columns.template.set("fillGradient",
            am5.LinearGradient.new(root, {
                rotation: 90,
                stops: [{
                        color: am5.color(0x29B6F6)
                    },
                    {
                        color: am5.color(0x0288D1)
                    }
                ]
            })
        );
        seriesInput.columns.template.states.create("hover", {
            scale: 1.50
        });

        // Menambahkan Label Angka di dalam Kolom Input
        seriesInput.bullets.push(function() {
            return am5.Bullet.new(root, {
                locationY: 0.5,
                child: am5.Label.new(root, {
                    text: "{valueY}",
                    fill: am5.color(0xffffff),
                    outset: false,
                    centerX: am5.p50,
                    centerY: am5.p50,
                    populateText: true,
                    rotation: -90
                })
            });
        });
        seriesInput.data.setAll(chartData);

        // 2. SERIES: OUTPUT CCS (Kolom Hijau)
        let seriesOutput = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: "Output CCS",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "output",
            categoryXField: "cell",
            clustered: true,
            fill: am5.color(0x00b050), // Hijau sesuai gambar
            stroke: am5.color(0x00b050)
        }));

        seriesOutput.columns.template.setAll({
            width: am5.percent(70),
            cornerRadiusTL: 4,
            cornerRadiusTR: 4,
            tooltipText: "{name}\n{categoryX}: [bold]{valueY}[/]"
        });
        seriesOutput.columns.template.set("fillGradient",
            am5.LinearGradient.new(root, {
                rotation: 90,
                stops: [{
                        color: am5.color(0x66BB6A)
                    },
                    {
                        color: am5.color(0x2E7D32)
                    }
                ]
            })
        );
        seriesOutput.columns.template.states.create("hover", {
            scale: 1.50
        });
        // Menambahkan Label Angka di dalam Kolom Output
        seriesOutput.bullets.push(function() {
            return am5.Bullet.new(root, {
                locationY: 0.5,
                child: am5.Label.new(root, {
                    text: "{valueY}",
                    fill: am5.color(0xffffff),
                    outset: false,
                    centerX: am5.p50,
                    centerY: am5.p50,
                    populateText: true,
                    rotation: -90
                })
            });
        });
        seriesOutput.data.setAll(chartData);


        // 3. SERIES: TARGET (Garis Merah)
        let seriesTarget = chart.series.push(am5xy.LineSeries.new(root, {
            name: "Target",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "target",
            categoryXField: "cell",
            stroke: am5.color(0xe46c0a), // Warna oranye/merah target
            strokeWidth: 3,
            // Kustomisasi Tooltip saat di-hover
            tooltip: am5.Tooltip.new(root, {
                labelText: "[bold]{name}[/]: {valueY}",
                pointerOrientation: "horizontal"
            })
        }));
        seriesTarget.strokes.template.setAll({
            strokeWidth: 3,
            strokeDasharray: [8, 4],
            strokeLinecap: "round",
            strokeLinejoin: "round"
        });

        // Membuat titik (bullet) kecil pada garis target agar area hover lebih sensitif
        seriesTarget.bullets.push(function() {
            return am5.Bullet.new(root, {
                child: am5.Circle.new(root, {
                    radius: 4,
                    fill: am5.color(0xe46c0a)
                })
            });
        });
        seriesTarget.data.setAll(chartData);

        // Menambahkan Legend di bagian bawah chart
        let legend = chart.children.push(am5.Legend.new(root, {
            centerX: am5.p50,
            x: am5.p50,
            layout: root.horizontalLayout
        }));
        legend.data.setAll(chart.series.values);
        legend.labels.template.setAll({
            fontWeight: "500",
            fontSize: 13
        });

        legend.markers.template.setAll({
            width: 14,
            height: 14
        });

        // Animasi saat chart muncul
        seriesInput.appear(1000);
        seriesOutput.appear(1000);
        chart.setAll({
            paddingTop: 15,
            paddingLeft: 0,
            paddingRight: 15
        });
        chart.appear(1000, 100);
    }

    var myWipChartRoot = null;

    function createWipChart(chartData) {
        // JIKA ROOT SUDAH ADA, LANGSUNG DISPOSE
        if (myWipChartRoot) {
            myWipChartRoot.dispose();
            myWipChartRoot = null;
        }

        // Initialize Root baru
        let root = am5.Root.new("chartWIP");
        myWipChartRoot = root;

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        let chart = root.container.children.push(am5xy.XYChart.new(root, {
            panX: false,
            panY: false,
            wheelX: "none",
            wheelY: "none",
            layout: root.verticalLayout
        }));

        // Tambahkan kursor hover
        let cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
            behavior: "none"
        }));
        cursor.lineY.set("visible", false);
        cursor.lineX.set("stroke", am5.color(0xaaaaaa));

        // X Axis
        let xRenderer = am5xy.AxisRendererX.new(root, {
            minGridDistance: 30
        });
        xRenderer.labels.template.setAll({
            rotation: -45,
            centerY: am5.p50,
            centerX: am5.p100,
            paddingRight: 15
        });

        let xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
            categoryField: "cell",
            renderer: xRenderer,
            tooltip: am5.Tooltip.new(root, {})
        }));
        xAxis.data.setAll(chartData);

        // Y Axis
        let yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
            renderer: am5xy.AxisRendererY.new(root, {})
        }));

        // 1. SERIES: WIP BARU (Kolom Biru Gelap / Teal sesuai gambar)
        let seriesWip = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: "WIP",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "wip_baru",
            categoryXField: "cell",
            fill: am5.color(0x115f82), // Warna teal gelap sesuai gambar
            stroke: am5.color(0x115f82),
            tooltip: am5.Tooltip.new(root, {
                labelText: "[bold]{name}[/]: {valueY}",
                pointerOrientation: "horizontal"
            })
        }));

        seriesWip.columns.template.setAll({
            width: am5.percent(70),
            cornerRadiusTL: 4,
            cornerRadiusTR: 4,
            tooltipText: "{name}\n{categoryX}: [bold]{valueY}[/]"
        });

        seriesWip.columns.template.set(
            "fillGradient",
            am5.LinearGradient.new(root, {
                rotation: 90,
                stops: [{
                        color: am5.color(0x2A7FA5)
                    },
                    {
                        color: am5.color(0x115F82)
                    }
                ]
            })
        );
        seriesWip.columns.template.states.create("hover", {
            scale: 1.50
        });

        // Menambahkan Label Angka Vertikal di dalam Batang WIP
        seriesWip.bullets.push(function() {
            return am5.Bullet.new(root, {
                locationY: 0.5,
                child: am5.Label.new(root, {
                    text: "{valueY}",
                    fill: am5.color(0xffffff),
                    outset: false,
                    centerX: am5.p50,
                    centerY: am5.p50,
                    populateText: true,
                    rotation: -90
                })
            });
        });
        seriesWip.data.setAll(chartData);

        // 2. SERIES: TARGET (Garis Merah/Oranye)
        let seriesTarget = chart.series.push(am5xy.LineSeries.new(root, {
            name: "Target",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "target",
            categoryXField: "cell",
            stroke: am5.color(0xe46c0a), // Warna oranye kemerahan
            strokeWidth: 3,
            tooltip: am5.Tooltip.new(root, {
                labelText: "[bold]{name}[/]: {valueY}",
                pointerOrientation: "horizontal"
            })
        }));

        seriesTarget.strokes.template.setAll({
            strokeWidth: 3,
            strokeDasharray: [8, 4],
            strokeLinecap: "round",
            strokeLinejoin: "round"
        });

        // Bullet titik pada garis target
        seriesTarget.bullets.push(function() {
            return am5.Bullet.new(root, {
                child: am5.Circle.new(root, {
                    radius: 4,
                    fill: am5.color(0xe46c0a)
                })
            });
        });
        seriesTarget.data.setAll(chartData);

        // Legend
        let legend = chart.children.push(am5.Legend.new(root, {
            centerX: am5.p50,
            x: am5.p50,
            layout: root.horizontalLayout
        }));
        legend.data.setAll(chart.series.values);
        legend.labels.template.setAll({
            fontWeight: "500",
            fontSize: 13
        });

        legend.markers.template.setAll({
            width: 14,
            height: 14
        });

        // Animasi muncul
        seriesWip.appear(1000);
        chart.setAll({
            paddingTop: 15,
            paddingLeft: 0,
            paddingRight: 15
        });
        chart.appear(1000, 100);
    }
</script>

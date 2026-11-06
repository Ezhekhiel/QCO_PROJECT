<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- apexcharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        main();
        $('#import_location').on('submit', function(e) {
            event.preventDefault();
            $.ajax({
                url: "{{ route('qco.import.department') }}",
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
                    main();
                    // createAlert('', data.alert, data.text, data.color, true, true,
                    //     'pageMessages');
                    // if (data.alert != 'Failed!') {
                    //     resetForm();
                    //     main();
                    // }
                },
                complete: function() {
                    $("#loading-spinner").hide();
                }
            });
        });
    });

    function main() {
        $.ajax({
            url: "{{ route('qco.manage.main') }}",
            method: "get",
            dataType: 'JSON',
            success: function(response) {

                if (response.success) {
                    let rows = '';
                    let no = 1;

                    // Looping data dari JSON response
                    $.each(response.data, function(index, item) {
                        rows += `
                                    <tr>
                                        <td>${no++}</td>
                                        <td>${item.nama}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="departmentEdit(${item.id})">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="departmentDelete(${item.id})">Hapus</button>
                                        </td>
                                    </tr>
                                `;
                    });

                    // Masukkan baris HTML ke dalam tbody tabel
                    console.log(rows);
                    $('#departmentTable tbody').html(rows);
                }
            }
        })
    }

    function createAlert(title, summary, details, severity, dismissible, autoDismiss, appendToId) {
        var iconMap = {
            info: "fa fa-info-circle",
            success: "fa fa-thumbs-up",
            warning: "fa fa-exclamation-triangle",
            danger: "fa ffa fa-exclamation-circle"
        };

        var iconAdded = false;

        var alertClasses = ["alert", "animated", "flipInX"];
        alertClasses.push("alert-" + severity.toLowerCase());

        if (dismissible) {
            alertClasses.push("alert-dismissible");
        }

        var msgIcon = $("<i />", {
            "class": iconMap[severity] // you need to quote "class" since it's a reserved keyword
        });

        var msg = $("<div />", {
            "class": alertClasses.join(" ") // you need to quote "class" since it's a reserved keyword
        });

        if (title) {
            var msgTitle = $("<h4 />", {
                html: title
            }).appendTo(msg);

            if (!iconAdded) {
                msgTitle.prepend(msgIcon);
                iconAdded = true;
            }
        }

        if (summary) {
            var msgSummary = $("<strong />", {
                html: summary
            }).appendTo(msg);

            if (!iconAdded) {
                msgSummary.prepend(msgIcon);
                iconAdded = true;
            }
        }

        if (details) {
            var msgDetails = $("<p />", {
                html: details
            }).appendTo(msg);

            if (!iconAdded) {
                msgDetails.prepend(msgIcon);
                iconAdded = true;
            }
        }


        if (dismissible) {
            var msgClose = $("<span />", {
                "class": "close", // you need to quote "class" since it's a reserved keyword
                "data-dismiss": "alert",
                html: "<i class='fa fa-times-circle'></i>"
            }).appendTo(msg);
        }

        $('#' + appendToId).prepend(msg);

        if (autoDismiss) {
            setTimeout(function() {
                msg.addClass("flipOutX");
                setTimeout(function() {
                    msg.remove();
                }, 1000);
            }, 5000);
        }
    }
</script>

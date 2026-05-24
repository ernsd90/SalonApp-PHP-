$(document).ready(function() {


    var get_salerecord = $('#get_salerecord').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:100,
        ordering:false,
        "ajax": {
            "url": "ajax/report_ajax.php",
            "type": "POST",
            'data': function(data) {
                // Read values
                var from_date = $('#search_fromdate').val();
                var to_date = $('#search_todate').val();
                var staff_id = $('#reportstaff_id').val();
                var refrence_by = $('#refrence_by').val();
                // Append to data
                data.fromdate = from_date;
                data.todate = to_date;
                data.staff_id = staff_id;
                data.refrence_by = refrence_by;
                data.method = "get_salerecord";
                if(staff_id > 0){
                    staff_summary_sale();
                }else{
                    summary_sale();
                }

            },
        },
        "columns": [
            { "data": "invoice_id", "title": "ID" },
            { "data": "cust_name", "title": "Cust Name" },
            { "data": "cust_mob", "title": "Mobile No" },
            { "data": "invoice_type", "title": "Invoice Type" },
            { "data": "payment_mode", "title": "Payment Method" },
            { "data": "discount", "title": "Discount" },
            { "data": "grand_total", "title": "Total" },
            { "data": "invoice_date", "title": "Invoice Date" },
            { "data": "action", "title": "Action" },
        ],
        "createdRow": function(row, data, dataIndex) {
           // console.log(data);
            if (data['delete_bill'] == 1) {
                $(row).addClass('deleted_redClass');
            }
            $(row).find('[data-toggle="tooltip"]').tooltip();

        }
    });




    var get_salerecord_old = $('#get_salerecord_old').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/report_ajax.php",
            "type": "POST",
            'data': function(data) {
                data.method = "get_salerecord_old";
            },
        },
        "columns": [
            { "data": "cust_name", "title": "Name" },
            { "data": "cust_mob", "title": "Mobile" },
            { "data": "all_staff", "title": "Staff" },
            { "data": "all_service", "title": "Service" },
            { "data": "payment_mode", "title": "Payment Mode", "orderable": false },
            { "data": "grand_total", "title": "Total Bill" },
            { "data": "invoice_date", "title": "Date", "orderable": true },
        ],
        "createdRow": function(row, data, dataIndex) {
            console.log(data);
            if (data['delete_bill'] == 1) {
                $(row).addClass('deleted_redClass');
            }
        }
    });


    var get_staffrecord = $('#get_staffrecord').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:500,
        dom: 'Bfrtip',
        buttons: [{
            extend: 'pdf',
            text: 'Download PDF',
            title: 'Staff Report',
            exportOptions:{
            }

        }],
        "ajax": {
            "url": "ajax/report_ajax.php",
            "type": "POST",
            'data': function(data) {
                // Read values
                var from_date = $('#search_fromdate').val();
                var to_date = $('#search_todate').val();
                // Append to data
                data.fromdate = from_date;
                data.todate = to_date;
                data.method = "get_staffrecord";

            },
        },
        "columns": [
            { "data": "staff_name", "title": "Emp Name" },
            { "data": "staff_salary", "title": "Salary" },
            { "data": "total_customer", "title": "Total Customer", "orderable": false  },
            { "data": "service_sale", "title": "Service Sale", "orderable": false },
            { "data": "target_sale", "title": "Target Sale(5x)", "orderable": false },
            { "data": "incentive_sale", "title": "Incentive(3%)", "orderable": false },
            { "data": "product_total", "title": "Product Sale", "orderable": false },
            { "data": "total_pkgsell" , "title": "Package Sale", "orderable": false },
            { "data": "total_pkg_service", "title": "Pakage Service", "orderable": false },
            { "data": "grand_total", "title": "Grand Total", "orderable": false },
        ],
        "createdRow": function(row, data, dataIndex) {
            if (data['delete_bill'] == 1) {
                $(row).addClass('deleted_redClass');
            }
        }
    });

    var get_servicerecord = $('#get_servicerecord').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:150,
        "ajax": {
            "url": "ajax/report_ajax.php",
            "type": "POST",
            'data': function(data) {
                // Read values
                var from_date = $('#search_fromdate').val();
                var to_date = $('#search_todate').val();
                if ($('#pkg_service').is(':checked')) {
                    var pkg_service = 1;
                }else{
                    var pkg_service = 0;
                }
                if ($('#membership_include').is(':checked')) {
                    var membership_include = 1;
                }else{
                    var membership_include = 0;
                }


                // Append to data
                data.fromdate = from_date;
                data.todate = to_date;
                data.pkg_service = pkg_service;
                data.membership_include = membership_include;
                data.method = "get_servicerecord";

            },
        },
        "columns": [
            { "data": "sevice_name", "title": "Service Name" },
            { "data": "service_price", "title": "Service Price" },
            { "data": "service_qty", "title": "Total Quantity" },
            { "data": "service_grand", "title": "Grand Total" },
        ],
        "createdRow": function(row, data, dataIndex) {
            if (data['delete_bill'] == 1) {
                $(row).addClass('deleted_redClass');
            }
        }
    });


    function summary_sale() {
        var search_fromdate = $("#search_fromdate").val();
        var search_todate = $("#search_todate").val();
        var refrence_by = $('#refrence_by').val();
        $.ajax({
            type: "POST",
            url: "ajax/report_ajax.php",
            data: "method=summary_sale&fromdate=" + search_fromdate + "&todate=" + search_todate + "&refrence_by=" + refrence_by,
            success: function(res) {
                var obj = jQuery.parseJSON(res);
                if (obj.error == 1) {

                } else {
                    $(".exp_total").text(obj.exp_total);
                    $(".exp_total_cc").text(obj.exp_total_cc);
                    $(".exp_total_cash").text(obj.exp_total_cash);

                    $(".total_cash").text(obj.total_cash);
                    $(".total_cc").text(obj.total_cc);
                    $(".service_total").text(obj.service_total);

                    $(".product_total_cash").text(obj.product_total_cash);
                    $(".product_total_cc").text(obj.product_total_cc);
                    $(".product_total").text(obj.product_total);

                    $(".total_customer").text(obj.total_customer);
                    $(".total_customer_cash").text(obj.total_customer_cash);
                    $(".total_customer_pkg").text(obj.total_customer_pkg);

                    $(".grand_cc").text(obj.grand_cc);
                    $(".grand_cash").text(obj.grand_cash);
                    $(".grand_total").text(obj.grand_total);
                }
            },
            error: function() {
                alert("Error");
            }
        });
    }


    function staff_summary_sale() {
        var search_fromdate = $("#search_fromdate").val();
        var search_todate = $("#search_todate").val();
        var staff_id = $('#reportstaff_id').val();
        var refrence_by = $('#refrence_by').val();
        $.ajax({
            type: "POST",
            url: "ajax/report_ajax.php",
            data: "method=staff_summary_sale&fromdate=" + search_fromdate + "&todate=" + search_todate + "&staff_id=" + staff_id + "&refrence_by=" + refrence_by,
            success: function(res) {
                var obj = jQuery.parseJSON(res);
                if (obj.error == 1) {

                } else {
                    $(".grand_total").text(obj.grand_total);
                    $(".exp_total").text(obj.exp_total);
                    $(".total_cash").text(obj.total_cash);
                    $(".total_cc").text(obj.total_cc);
                    $(".product_total").text(obj.product_total);
                    $(".total_customer").text(obj.total_customer);
                }
            },
            error: function() {
                alert("Error");
            }
        });
    }

    $('#search_fromdate,#att_fromdate').datepicker({ changeMonth: true, changeYear: true, autoclose: true, "format": 'dd-mm-yyyy', });
    $('#search_todate, #att_todate').datepicker({ changeMonth: true, changeYear: true, autoclose: true, "format": 'dd-mm-yyyy', });
    // Event listener to the two range filtering inputs to redraw on input
    $('#search_fromdate, #search_todate, #reportstaff_id,#refrence_by, #pkg_service, #membership_include').change(function() {
        get_salerecord.draw();
        get_staffrecord.draw();
        get_servicerecord.draw();

    });



    function summary_attendance() {
        var from_date = $('#att_fromdate').val();
        var to_date = $('#att_todate').val();
        var staff_id = $('#reportstaff_name').val();
        $.ajax({
            type: "POST",
            url: "ajax/report_ajax.php",
            data: "method=summary_attendance&fromdate=" + from_date + "&todate=" + to_date + "&staff_id=" + staff_id,
            success: function(res) {
                var obj = jQuery.parseJSON(res);
                if (obj.error == 1) {

                } else {
                    $(".total_hr").text(obj.total_hr);
                    $(".total_working").text(obj.total_working);
                    $(".total_cash").text(obj.total_cash);
                    $(".total_cc").text(obj.total_cc);
                    $(".product_total").text(obj.product_total);
                    $(".total_customer").text(obj.total_customer);
                }
            },
            error: function() {
                alert("Error");
            }
        });
    }

    var get_attendencerecord = $('#get_attendencerecord').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:50,
        "ajax": {
            "url": "ajax/report_ajax.php",
            "type": "POST",
            'data': function(data) {
                // Read values
                var from_date = $('#att_fromdate').val();
                var to_date = $('#att_todate').val();
                var staff_id = $('#reportstaff_name').val();
                // Append to data
                data.fromdate = from_date;
                data.todate = to_date;
                data.staff_id = staff_id;
                data.method = "get_attendencerecord";
                summary_attendance();
            },
        },
        "columns": [
            { "title":"Name", "data": "name" },
            { "title":"Working Hour", "data": "working_hr" },
            { "title":"In", "data": "duty_in" },
            { "title":"Out", "data": "duty_out" },
            { "title":"Date", "data": "user_date", "orderable": true },
        ]
    });


    $('#att_fromdate, #att_todate, #reportstaff_name').change(function() {
        get_attendencerecord.draw();
    });

    var get_feedback = $('#get_feedback').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:50,
        "ajax": {
            "url": "ajax/report_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_feedback"
            }
        },
        "columns": [
            { "data": "invoice_id" },
            { "data": "cust_name" },
            { "data": "cust_mob" },
            { "data": "experience" },
            { "data": "message", "width": "40px" },
            { "data": "created_date" },
            { "data": "action", "orderable": false },
        ]
    });



    $(document).on('submit', "#report_form", function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "ajax/report_ajax.php",
            data: $('#report_form').serialize(),
            success: function(res) {
                var obj = jQuery.parseJSON(res);

                if (obj.error == 1) {
                    toastr.error(obj.msg, 'Report');
                } else {
                    toastr.success(obj.msg, 'Report');
                    get_salerecord.draw();
                    $("#modalButtonCommon").modal('hide');

                }
            },
            error: function() {
                alert("Error");
            }
        });
    });



});
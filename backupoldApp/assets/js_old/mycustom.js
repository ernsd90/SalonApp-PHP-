$(document).ready(function() {


    var get_staff = $('#get_staff').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/user_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_staff"
            }
        },
        "columns": [
            { "data": "staff_id" },
            { "data": "staff_name" },
            { "data": "staff_mob" },
            { "data": "joining_date" },
            { "data": "staff_status", "orderable": false },
            { "data": "action", "orderable": false },
        ]
    });

    var get_customer = $('#get_customer').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:100,
        "ajax": {
            "url": "ajax/customer_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_customer"
            }
        },
        "order": [
            [0, "desc"]
        ],
        "columns": [
            { "data": "cust_id" },
            { "data": "cust_name", "orderable": false },
            { "data": "cust_mobile", "orderable": false },
            { "data": "cust_wallet" },
            { "data": "cust_outstanding" },
            { "data": "action", "orderable": false },
        ]
    });

    var get_serviceCat = $('#get_serviceCat').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_serviceCat"
            }
        },
        "columns": [
            { "data": "service_catid" },
            { "data": "service_catName" },
            { "data": "action", "orderable": false },
        ]
    });
    var get_expensesCat = $('#get_expensesCat').DataTable({
        "processing": true,
        "serverSide": true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_expensesCat"
            }
        },
        "columns": [
            { "data": "exp_catId" },
            { "data": "category_name" },
            { "data": "action", "orderable": false },
        ]
    });

    var get_service = $('#get_service').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_service"
            }
        },
        "columns": [
            { "data": "service_id" },
            { "data": "service_name" },
            { "data": "service_price" },
            { "data": "service_catid" },
            { "data": "service_status" },
            { "data": "action", "orderable": false },
        ]
    });


    var get_package = $('#get_package').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_package"
            }
        },
        "columns": [
            { "title": "id","data": "pkg_id" },
            { "title": "Package","data": "package_name" },
            { "title": "Validity","data": "pakage_validity" },
            { "title": "Cust Pay","data": "customer_pay" },
            { "title": "Cust Get","data": "customer_get" },
            { "title": "Status","data": "package_status" },
            { "title": "Action","data": "action", "orderable": false },
        ]
    });

    var get_expenses = $('#get_expenses').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:800,
        dom: 'Bfrtip',
        buttons: [{
            extend: 'pdf',
            text: 'Download Report',
            title: 'Expence Report',
            exportOptions:{
                columns:[2,3,4,5]
            }

        }],
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            'data': function(data) {
                // Read values
                var from_date = $('#exp_fromdate').val();
                var to_date = $('#exp_todate').val();
                var category_id = $('#reportcategory_id').val();
                var expence_type = $('#expence_type').val();
                // Append to data
                data.fromdate = from_date;
                data.todate = to_date;
                data.payment_mode = expence_type;
                data.category_id = category_id;
                data.method = "get_expenses";
                expenses_summary();
            }
        },
        "columns": [
            { "data": "exp_id" },
            { "data": "exp_catId" },
            { "data": "exp_name" },
            { "data": "exp_total" },
            { "data": "exp_note" },
            { "data": "exp_date" },
            { "data": "action", "orderable": false },
        ]
    });
    $('#exp_fromdate').datepicker({ changeMonth: true, changeYear: true, autoclose: true, "format": 'dd-mm-yyyy', });
    $('#exp_todate').datepicker({ changeMonth: true, changeYear: true, autoclose: true, "format": 'dd-mm-yyyy', });
    // Event listener to the two range filtering inputs to redraw on input
    $('#exp_fromdate, #exp_todate, #reportcategory_id').change(function() {
        get_expenses.draw();
    });

    function expenses_summary() {
        var exp_fromdate = $("#exp_fromdate").val();
        var exp_todate = $("#exp_todate").val();
        var category_id = $('#reportcategory_id').val();
        var expence_type = $('#expence_type').val();
        $.ajax({
            type: "POST",
            url: "ajax/salon_ajax.php",
            data: "method=expenses_summary&payment_mode=" + expence_type + "&fromdate=" + exp_fromdate + "&todate=" + exp_todate + "&category_id=" + category_id,
            success: function(res) {
                var obj = jQuery.parseJSON(res);
                if (obj.error == 1) {

                } else {
                    $(".exp_number").text(obj.exp_number);
                    $(".exp_total").text(obj.exp_total);
                }
            },
            error: function() {
                alert("Error");
            }
        });
    }


    var get_inventory_bill = $('#get_inventory_bill').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_inventory_bill"
            }
        },
        "columns": [
            { "title": "Bill No","data": "bill_id" },
            { "title": "Invoice Date","data": "invoice_date" },
            { "title": "Vendor","data": "vendor_name" },
            { "title": "Discount","data": "discount" },
            { "title": "Total","data": "total" },
            { "title": "Action","data": "action", "orderable": false },
        ]
    });

    var get_inventory = $('#get_inventory').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        iDisplayLength:350,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            'data': function(data) {
                // Read values
                var from_date = $('#inven_fromdate').val();
                var to_date = $('#inven_todate').val();
                var category_id = $('#reportproduct_id').val();
                // Append to data
                data.fromdate = from_date;
                data.todate = to_date;
                data.category_id = category_id;
                data.method = "get_inventory";
                inventory_summary();
            }
        },
        "columns": [
            { "title": "Product Name","data": "product_name" },
            { "title": "Product Type","data": "product_type" },
            { "title": "Total Qty","data": "ttl_qty" },
            { "title": "In Store","data": "store_qty" },
            { "title": "Action","data": "action" },
        ]
    });


    var get_vendor_credit_debit = $('#get_vendor_credit_debit').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        iDisplayLength:100,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_vendor_credit_debit"
            }
        },
        "columns": [
            { "title": "Bill No","data": "bill_id" },
            { "title": "Date","data": "created_date" },
            { "title": "Vendor","data": "vendor_name" },
            { "title": "Purchase","data": "amt_in" },
            { "title": "Payment","data": "amt_out" },
            { "title": "Payment_mode","data": "payment_mode" },
            { "title": "Balance","data": "balance" },
            { "title": "Action","data": "action", "orderable": false },
        ]
    });

    $('#inven_fromdate').datepicker({ changeMonth: true, changeYear: true, autoclose: true, "format": 'dd-mm-yyyy', });
    $('#inven_todate').datepicker({ changeMonth: true, changeYear: true, autoclose: true, "format": 'dd-mm-yyyy', });
    // Event listener to the two range filtering inputs to redraw on input
    $('#inven_fromdate, #inven_todate, #reportproduct_id').change(function() {
        get_inventory.draw();
    });

    function inventory_summary() {
        var inven_fromdate = $("#inven_fromdate").val();
        var inven_todate = $("#inven_todate").val();
        var reportproduct_id = $('#reportproduct_id').val();
        $.ajax({
            type: "POST",
            url: "ajax/salon_ajax.php",
            data: "method=inventory_summary&fromdate=" + inven_fromdate + "&todate=" + inven_todate + "&product_id=" + reportproduct_id,
            success: function(res) {
                var obj = jQuery.parseJSON(res);
                if (obj.error == 1) {

                } else {
                    $(".product_number").text(obj.product_number);
                    $(".product_total").text(obj.product_total);
                }
            },
            error: function() {
                alert("Error");
            }
        });
    }


    var get_inventory_compare = $('#get_inventory_compare').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        iDisplayLength:100,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_inventory_compare"
            }
        },
        "columns": [
            { "title": "Product Name","data": "product_name" },
            { "title": "Product Type","data": "product_type" },
            { "title": "Old Bill - New Bill","data": "bill_number" },
            { "title": "Old Price","data": "old_price" },
            { "title": "New Price","data": "new_price" },
        ]
    });


    var get_monthly_discount = $('#get_monthly_discount').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_monthly_discount"
            }
        },
        "columns": [
            { "title": "Month","data": "discount_month" },
            { "title": "Discount","data": "total_discount" },
            { "title": "Action","data": "action" },
        ]
    });


    var get_product = $('#get_product').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_product"
            }
        },
        "columns": [
            { "data": "product_id" },
            { "data": "brand_name" },
            { "data": "product_name" },
            { "data": "product_price" },
            { "data": "product_status" },
            { "data": "action", "orderable": false },
        ]
    });


    var get_product_brand = $('#get_product_brand').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_product_brand"
            }
        },
        "columns": [
            { "data": "brand_id" },
            { "data": "brand_name" },
            { "data": "brand_status" },
            { "data": "action", "orderable": false },
        ]
    });





    var get_user = $('#get_user').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:100,
        "ajax": {
            "url": "ajax/user_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_user"
            }
        },
        "columns": [
            { "data": "user_id" },
            { "data": "full_name" },
            { "data": "username" },
            { "data": "password" },
            { "data": "role_name" },
            { "data": "action" },
        ]
    });


    var get_role = $('#get_role').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/user_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_role"
            }
        },
        "columns": [
            { "data": "role_id" },
            { "data": "role_name" },
            { "data": "action" },
        ]
    });


    var get_category_domain = $('#get_category_domain').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        "ajax": {
            "url": "ajax/movie_ajax.php",
            "type": "POST",
            "data": {
                "method": "get_category_domain"
            }
        },
        "columns": [
            { "data": "cat_name" },
            { "data": "default_domain" },
            { "data": "latest_domain" }
        ]
    });



    $(document).on('click', '.modalButtonCommon', function() {
        var dataURL = $(this).attr('data-href');
        $('.modal-content').load(dataURL, function() {
            $('#modalButtonCommon').modal({ show: true });
        });
    });



    $(document).on('submit', "#user_form", function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "ajax/user_ajax.php",
            data: $('#user_form').serialize(),
            success: function(res) {
                var obj = jQuery.parseJSON(res);

                if (obj.error == 1) {
                    toastr.error(obj.msg, 'User Info');
                } else {
                    toastr.success(obj.msg, 'User Info');
                    get_staff.draw();
                    get_role.draw();
                    get_user.draw();
                    $("#modalButtonCommon").modal('hide');

                }
            },
            error: function() {
                alert("Error");
            }
        });
    });



    $(document).on('submit', "#salon_form", function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "ajax/salon_ajax.php",
            data: $('#salon_form').serialize(),
            success: function(res) {
                var obj = jQuery.parseJSON(res);

                if (obj.error == 1) {
                    toastr.error(obj.msg, 'Salon Info');
                } else {
                    toastr.success(obj.msg, 'Salon Info');
                    get_expenses.draw();
                    get_expensesCat.draw();
                    get_serviceCat.draw();
                    get_service.draw();
                    get_product.draw();
                    get_product_brand.draw();
                    get_monthly_discount.draw();
                    get_role.draw();
                    get_inventory_bill.draw();
                    get_inventory.draw();
                    get_package.draw();
                    $("#modalButtonCommon").modal('hide');

                }
            },
            error: function() {
                alert("Error");
            }
        });
    });




    $(document).on('submit', "#common_form", function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "ajax/user_ajax.php",
            data: $(this).serialize(),
            success: function(res) {
                var obj = jQuery.parseJSON(res);

                if (obj.error == 1) {
                    toastr.error(obj.msg, 'Setting');
                } else {
                    toastr.success(obj.msg, 'Setting');
                }
            },
            error: function() {
                alert("Error");
            }
        });
    });



    $(document).on('submit', "#movie_common_form", function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "ajax/salon_ajax.php",
            data: $(this).serialize(),
            success: function(res) {
                var obj = jQuery.parseJSON(res);

                if (obj.error == 1) {
                    toastr.error(obj.msg, 'Movie Settings');
                } else {
                    toastr.success(obj.msg, 'Movie Settings');
                    get_movies_domain.draw();
                    get_category_domain.draw();
                    new_domain_group.draw();
                    $("#modalButtonCommon").modal('hide');
                    if (obj.next_id > 0) {

                        $("#new_movie_id").val(obj.next_id);
                    }
                }
            },
            error: function() {
                alert("Error");
            }
        });
    });


    $(document).on('click', ".movie_imgage_del", function(e) {
        e.preventDefault();

        var img_id = $(this).attr("data-img");
        var movie_id = $(this).attr("data-movieid");
        var parent_div = $(this).parent();

        $.ajax({
            type: "POST",
            url: "ajax/movie_ajax.php",
            data: "method=movie_imgage_del&img_id=" + img_id + "&movie_id=" + movie_id,
            success: function(res) {
                var obj = jQuery.parseJSON(res);

                if (obj.error == 1) {
                    toastr.error(obj.msg, 'Movie Settings');
                } else {
                    toastr.success(obj.msg, 'Movie Settings');
                    parent_div.html('');
                }
            },
            error: function() {
                alert("Error");
            }
        });
    });


    $(document).on('submit', "#customer_form", function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "ajax/customer_ajax.php",
            data: $('#customer_form').serialize(),
            success: function(res) {
                var obj = jQuery.parseJSON(res);

                if (obj.error == 1) {
                    toastr.error(obj.msg, 'Customer');
                } else {
                    toastr.success(obj.msg, 'Customer');
                    get_customer.draw();
                    $("#modalButtonCommon").modal('hide');

                }
            },
            error: function() {
                alert("Error");
            }
        });
    });





});


$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
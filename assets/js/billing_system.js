$("input.cust_mob").typeahead({
    onSelect: function(item) {
        get_customerfrom_mob(item);
    },
    minLength: 3,
    autoSelect: false,
    ajax: {
        type: "POST",
        url: "ajax/customer_ajax.php",
        timeout: 500,
        displayField: "title",
        triggerLength: 1,
        method: "post",
        preDispatch: function(query) {
            return {
                cust_mob: query,
                method: "get_customer_from_mobile"
            }
        },
        preProcess: function(data) {
            if (data === null) {
                $(".customer_detail").hide();
                $(".cust_name").val("");
                $(".cust_id").val("");

                $("#check_wallet").val("");
                return false;
            }
            return data;
        }

    }
});





function get_customerfrom_mob(item) {

    var value = item.value;
    $.ajax({
        type: "POST",
        url: "ajax/customer_ajax.php",
        data: { cust_mob: value, method: "get_customer_from_mobile", "detail": 1 },
        success: function(res) {
            var obj = jQuery.parseJSON(res);

            if (obj.error == 1) {
                toastr.error("Getting Server error while Feaching", 'Customer Info');
            } else {
                $(".cust_name").text(obj[0]['cust_name']);
                $(".cust_name").val(obj[0]['cust_name']);
                if (obj[0]['cust_gender'] != '') {
                    $(".cust_gender").val(obj[0]['cust_gender']);
                    $(".gender_check").hide();

                }

                $(".cust_id").val(obj[0]['cust_id']);
                $(".cust_wallet").text(obj[0]['cust_wallet']);
                $("#check_wallet").val(obj[0]['cust_wallet']);
                $(".cust_outstanding").text(obj[0]['cust_outstanding']);
                $(".billing_remark").text(obj[0]['billing_remark']);
                $(".customer_detail").show();

            }
        },
        error: function() {
            alert("Error");
        }
    });
}



$(document).ready(function() {


    $('form#billingform').submit(function() {

        var counter = 0;
        var staff = 0;
        var price = 0;

        $('tr.item').each(function() {

            var sub_service = ($(this).find(".sub_service").find(':selected').val());

            if (sub_service == 0) {
                // $(this).parents("td").addClass("redClass");
                counter++;
            } else if (sub_service > 0) {
                var service_staff = ($(this).find(".service_staff").find(':selected').val());
                var service_price = ($(this).find(".service_price").val());
                if (service_staff == 0 || service_staff == undefined) {

                    staff++;
                }
                if (service_price > 0) {} else {

                    price++;
                }

            }
        });

        var discount_mode = $(".discount_mode").children("option:selected").val();
        var discount = ($(".discount").val());

        if (discount_mode == 1) {
            if(discount > 60) {
          
                $(".discount").val(60);
                toastr.error("Maximum Discount will be 60%", 'Billing Error');
                return false;
            }
        }else{
            if(discount > 2000) {
                $(".discount").val(2000);
                toastr.error("Maximum Discount will be Rs 2000 ", 'Billing Error');
                return false;
            }
        }
        var error = '';
        var cust_reffer = $(".cust_reffer").children("option:selected").val();
        if (cust_reffer == "0") {
            toastr.error("Please Select Reference", 'Billing Error');
            error = 1;
        }
        if (staff > 0) {
            toastr.error("Please select Staff Member", 'Billing Error');
            error = 1;
        }

        if (price > 0) {
            toastr.error("Please add a Price", 'Billing Error');
            error = 1;
        }
        if (counter == 9) {
            toastr.error("Please select atleast one service", 'Billing Error');
            error = 1;
        }

        if(error == 1){
            return false;
        }


        $(this).find('.save_bill,.save_bill_print').prop('disabled', true);
    });


    $('form#productform').submit(function() {

        var counter = 0;
        var staff = 0;
        var price = 0;

        $('tr.item').each(function() {

            var product_id = ($(this).find(".product_id").find(':selected').val());

            if (product_id == 0) {
                // $(this).parents("td").addClass("redClass");
                counter++;
            } else if (product_id > 0) {
                var product_staff = ($(this).find(".product_staff").find(':selected').val());
                var service_price = ($(this).find(".service_price").val());
                if (product_staff == 0 || product_staff == undefined) {
                    staff++;
                }
                if (service_price > 0) {} else {
                    price++;
                }

            }
        });

        var cust_reffer = $(".cust_reffer").children("option:selected").val();
        if (cust_reffer == "0") {
            toastr.error("Please Select Reference", 'Billing Error');
            return false;
        }

        if (staff > 0) {
            toastr.error("Please select Staff Member", 'Billing Error');
            return false;
        }
        if (price > 0) {
            toastr.error("Please add a Price", 'Billing Error');
            return false;
        }
        if (counter == 4) {
            toastr.error("Please select atleast one Product", 'Billing Error');
            return false;
        }

        $(this).find('.save_bill,.save_bill_print').prop('disabled', true);
    });


    $('form#membershipform').submit(function() {

        var counter = 0;
        var staff = 0;

        $('tr.item').each(function() {

            var package = ($(this).find(".package").find(':selected').val());

            if (package == 0) {
                // $(this).parents("td").addClass("redClass");
                counter++;
            } else if (package > 0) {
                var service_staff = ($(this).find(".service_staff").find(':selected').val());
                if (service_staff == 0 || service_staff == undefined) {
                    staff++;
                }

            }
        });

        var cust_reffer = $(".cust_reffer").children("option:selected").val();
        if (cust_reffer == "0") {
            toastr.error("Please Select Reference", 'Billing Error');
            return false;
        }
        
        if (staff > 0) {
            toastr.error("Please select Staff Member", 'Billing Error');
            return false;
        }
        if (counter == 1) {
            toastr.error("Please select a Package", 'Billing Error');
            return false;
        }

        $(this).find('.save_bill,.save_bill_print').prop('disabled', true);
    });

    $(".search_select").select2();

    $(".search_select").on("select2:select", function (evt) {
        var element = evt.params.data.element;
        var $element = $(element);

        $element.detach();
        $(this).append($element);
        $(this).trigger("change");
    });


    $(document).on("change", "#service_staff0", function() {

        var first_staff = $(this).val();
        var j = 1;
        $('tr.item .service_staff').each(function() {
            // $("#service_staff" + j).val(first_staff);
            // $("#service_staff" + j).trigger('change');
            j++;
        });

    });


    function check_serviceprice(currentdiv) {

        var service_price = parseFloat(currentdiv.find(".service_price").val());
        var service_price_org = parseFloat(currentdiv.find(".service_price_org").val());

        console.log(service_price);
        console.log(service_price_org);
        if(service_price < service_price_org){

            currentdiv.find(".service_price").val(service_price_org)
            toastr.error("You don't have a permission to decrease Service Price", 'Service');
            return false;
        }
    }

    $(document).on("change paste focusout", ".service_price", function() {
       // console.log($(this));
        check_serviceprice($(this).parent());
    });


    $(document).on("change keyup", ".service_price,.service_qty,.service_gst,.service_gst,.discount_mode,.discount,.extra_tax", function() {
        calcTotals();
    });
    $(document).on("change keyup", ".part_cc", function() {
        var mytotal = calcTotals();
        var part_cc = $(".part_cc").val();
       
        if (part_cc > mytotal) {
            toastr.error("Amount should be equal or less then total value", 'Service');
            $(".part_cc").val(mytotal);
            $(".part_cash").val('0');
        } else {
            
            var part_cash = mytotal-part_cc;
            $(".part_cash").val(part_cash);
        }
    });

    $(document).on("change keyup", ".part_cash", function() {
        var mytotal = calcTotals();
        var part_cash = $(".part_cash").val();

        if (part_cash > mytotal) {
            toastr.error("Amount should be equal or less then total value", 'Service');
            $(".part_cash").val(mytotal);
            $(".part_cc").val('0');
        } else {
            var part_cc = mytotal-part_cash;
            $(".part_cc").val(part_cc);
        }
        
    });


    $(document).on("change", ".payment_mode", function() {

       

        if ($(this).val() == "pkg" && salon_id != 15) {
            $(".service_gst").val('');
            
            var mytotal = calcTotals();
            var check_wallet = $("#check_wallet").val();
            if (check_wallet > mytotal) {} else {
                toastr.error("Wallet Amount is less than your billing amount", 'Service');
                $(".payment_mode").val("cash");
                $(".payment_mode").trigger('change');
            }
        } else {
           
            $(".part_payment").hide();
            if ($(this).val() == "split"){
                $(".part_payment").show();
                $(".part_cc").val(mytotal/2);
                $(".part_cash").val(mytotal/2);
            }
            $(".service_gst").val('18');
            calcTotals();
        }
    });

    function remove_tax(itemTotal, itemTax) {

        var check_tax = parseFloat(itemTotal - (itemTotal * (100 / (100 + itemTax))));
        return check_tax > 0 ? check_tax : 0;

    }

    function calcTotals() {
        var subTotal = 0;
        var total = 0;
        var amountDue = 0;
        var totalTax = 0;
        $('tr.item').each(function() {

            var quantity = parseFloat($(this).find(".service_qty").val());

            var price = parseFloat($(this).find(".service_price").val());

            var service_price_org = parseFloat($(this).find(".service_price_org").val());

           // check_serviceprice($(this));

            var itemTax = $(this).find(".service_gst").val();

            var itemTotal = parseFloat(quantity * price) > 0 ? parseFloat(quantity * price) : 0;

            var taxValue = parseFloat(itemTotal * itemTax / 100) > 0 ? parseFloat(itemTotal * itemTax / 100) : 0;

            subTotal += parseFloat(price * quantity) > 0 ? parseFloat(price * quantity) : 0;

            totalTax += parseFloat(taxValue) > 0 ? parseFloat(taxValue.toFixed(0)) : 0;
            itemTotal = itemTotal + taxValue;
            $(this).find(".service_total_txt").text(itemTotal.toFixed(2));
        });
        var discount_mode = parseInt($("[name='discount_mode']").val());
        var payment_mode = ($("[name='payment_mode']").children("option:selected").val());
        var extra_tax = parseInt($("[name='extra_tax']").val());
         extra_tax = parseFloat(extra_tax) > 0 ? parseFloat(extra_tax.toFixed(0)) : 0;

        if(payment_mode == 'pkg'){
            $(".discount").val(0);
        }


        var discount = parseFloat($("[name='discount']").val()) > 0 ? parseFloat($("[name='discount']").val()) : 0;
        //var paid        = parseFloat($("#paidAmount").val()) > 0 ? parseFloat($("#paidAmount").val()) : 0;

        var total_with_tax = subTotal + totalTax;

        var discount_amount = discount_mode == 1 ? subTotal * (discount / 100) : discount;
        total += parseFloat(subTotal + (totalTax - discount_amount));
        if (payment_mode != 'pkg') {
            total = Math.round((total) / 10) * 10;
        }

        total = parseFloat(total + extra_tax);
        if(total < 1){
            return false;
        }

        //amountDue += parseFloat(subTotal+totalTax-discount_amount-paid);

        $('#discount_value').text(discount_amount.toFixed(2));
        $('#subTotal').text(subTotal.toFixed(2));
        $('#taxTotal').text(totalTax.toFixed(2));
        $('#grandTotal').text(total.toFixed(2));
        $('.grandTotal').val(total.toFixed(2));
        //$( '#amountDue' ).text( amountDue.toFixed(2) );
        return total;
    }


    $(document).on("change", ".sub_service", function() {
        var sub_service = $(this);
        var current_tr = sub_service.closest('tr');
        var catid = sub_service.find(':selected').data("catid");

        var sub_service_value = $(this).val();
        $.ajax({
            type: "POST",
            url: "ajax/salon_ajax.php",
            data: "method=get_sub_service_detail&service_id=" + sub_service.val(),
            success: function(res) {
                var obj = jQuery.parseJSON(res);
                if (obj.error == 1) {
                    toastr.error("SERVER ERROR!!", 'Service');
                } else {
                    if (include_gst == 1) {
                        var tax = remove_tax(obj.service_price, 18);
                        var new_price = obj.service_price - tax;
                        current_tr.find(".service_price").val(new_price.toFixed(2));
                        current_tr.find(".service_price_org").val(new_price.toFixed(2));
                    } else {
                        current_tr.find(".service_price").val(obj.service_price);
                        current_tr.find(".service_price_org").val(obj.service_price);
                    }
                    current_tr.find(".service_catid").val(catid);
                    calcTotals();
                }
            },
            error: function() {
                alert("Error");
            }
        });
    });

    $(document).on("change", ".service_catidss", function() {

        var service_category = $(this);

        $.ajax({
            type: "POST",
            url: "ajax/salon_ajax.php",
            data: "method=get_sub_service&service_catid=" + service_category.val(),
            success: function(res) {
                var obj = jQuery.parseJSON(res);
                if (obj.error == 1) {
                    toastr.error("SERVER ERROR!!", 'Service');
                } else {

                    service_category.closest('tr').find(".sub_service").html(obj);
                    service_category.closest('tr').find(".search_select").select2();
                }
            },
            error: function() {
                alert("Error");
            }
        });
    });



});
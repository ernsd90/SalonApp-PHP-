var autocompleteOptions = {
    source: function (request, response) {
        $.ajax({
            url: "ajax/salon_ajax.php",
            dataType: "json",
            method: "post",
            data: {
                pro_name: request.term,
                method: "get_inventory_product"
            },
            success: function (data) {
                response(data);
            }
        });
    },
    minLength: 2,
    select: function (event, ui) {
    }
};

$("input.myproductinventory").autocomplete(autocompleteOptions);



$(document).ready(function(){

    $(document).on("change keyup", ".totalprice,.discount,.ttl_gst",function () {
        var sum = 0;
        $('.totalprice').each(function(){
            if(this.value > 0) {
                sum += parseFloat(this.value);
            }
        });

        var discount = $(".discount").val();
        var ttl_gst = $(".ttl_gst").val();
        if(discount > 0) {
            sum = parseFloat(sum)-parseFloat(discount);
        }
        if(ttl_gst > 0) {
            sum = parseFloat(sum)+parseFloat(ttl_gst);
        }

        $(".grand_total").val(sum);
    });

    var i=1;
    $("#add_row").click(function(){

        var td1 = "<td>"+ (i+1) +"</td>";
        var td2 = "<td><select name='protype["+i+"]' required class='form-control'><option value='store'>Store</option><option value='retail'>Retail</option></select></td>";
        var input_product = "<input name='product["+i+"]' required type='text' placeholder='Product' class='myproductinventory form-control input-md'  />";
        var td3 = "<td>"+input_product+"</td>";
        var td4 = "<td><input name='quantity["+i+"]' required type='text' value='1' placeholder='Quantity' class='form-control input-md'  /> </td>";
        var td5 = "<td><input name='mrp["+i+"]' required type='text' placeholder='MRP' class='form-control input-md'  /> </td>";
        var td6 = "<td><input name='totalprice["+i+"]' required type='text' placeholder='Total Price' class='form-control totalprice input-md'/> </td>";

        $('#addr'+i).html(td1+td2+td3+td4+td5+td6);

        $('#tab_logic').append('<tr id="addr'+(i+1)+'"></tr>');

        // for autocomplete
        $('#addr'+i+' input.myproductinventory').focus().autocomplete(autocompleteOptions);

        //for validation
        $('#addr'+i+' input').each(function () {
            $(this).rules("add", {
                required: true,
            });
        });

        i++;
    });
    $("#delete_row").click(function(){
        if(i>1){
            $("#addr"+(i-1)).html('');
            i--;
        }
    });
});


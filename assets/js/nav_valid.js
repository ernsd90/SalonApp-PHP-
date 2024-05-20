

    function inventoryform_validate() {
        $('.inventoryform').validate({
            rules: {
                invoice_date: {
                    required: true
                },
                vendor: {
                    required: true
                },
                "protype[]": {
                    required: true
                },
                "product[]": {
                    required: true
                },
                "quantity[]": {
                    required: true
                },
                "mrp[]": {
                    required: true
                },
                "totalprice[]": {
                    required: true
                },
                discount:{
                    required: true
                },
                gst:{
                    required: true
                },
                total:{
                    required: true
                }
            },
            messages: {},
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    }

$(function () {
    inventoryform_validate();
});
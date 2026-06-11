$(document).ready(function () {
    var globalFromDate = '1970-01-01'; // Default: All Time
    var globalToDate = moment().format('YYYY-MM-DD');

    var start = moment().subtract(10, 'years'); // For "All Time" fallback
    var end = moment();

    function cb(start_val, end_val, label) {
        if (label === 'All Time') {
            $('#datelabel').html('All Time');
            globalFromDate = '1970-01-01';
            globalToDate = moment().format('YYYY-MM-DD');
        } else {
            if (start_val.format('YYYY-MM-DD') == end_val.format('YYYY-MM-DD')) {
                if (start_val.format('YYYY-MM-DD') == moment().format('YYYY-MM-DD')) {
                    $('#datelabel').html('Today');
                } else if (start_val.format('YYYY-MM-DD') == moment().subtract(1, 'days').format('YYYY-MM-DD')) {
                    $('#datelabel').html('Yesterday');
                } else {
                    $('#datelabel').html(start_val.format('D MMM YYYY'));
                }
            } else {
                $('#datelabel').html(start_val.format('D MMM YYYY') + ' - ' + end_val.format('D MMM YYYY'));
            }
            globalFromDate = start_val.format('YYYY-MM-DD');
            globalToDate = end_val.format('YYYY-MM-DD');
        }
        loadOutstanding();
    }

    $('#reportdaterange').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        ranges: {
            'All Time': [moment().subtract(10, 'years'), moment()],
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    var outstandingTable;

    function loadOutstanding() {
        var apiFromDate = (globalFromDate === '1970-01-01') ? '' : moment(globalFromDate).format('DD-MM-YYYY');
        var apiToDate = (globalFromDate === '1970-01-01') ? '' : moment(globalToDate).format('DD-MM-YYYY');

        if (!outstandingTable) {
            outstandingTable = $('#outstandingTable').DataTable({
                processing: true, serverSide: true, responsive: true,
                ajax: {
                    url: "ajax/report_ajax.php", type: "POST",
                    data: function (d) {
                        d.fromdate = apiFromDate;
                        d.todate = apiToDate;
                        d.method = "get_outstanding_report";
                    }
                },
                columns: [
                    { data: "type", render: function(data) {
                        if (data === 'Invoice') return '<span class="badge bg-primary">Invoice</span>';
                        if (data === 'Membership') return '<span class="badge bg-success">Membership</span>';
                        if (data === 'Package') return '<span class="badge bg-info">Package</span>';
                        return data;
                    }},
                    { data: "invoice_id", render: function (data) { return '<span style="font-weight:600;color:var(--primary);">#' + data + '</span>'; } },
                    { data: "invoice_date" },
                    { data: "cust_name", render: function (data, type, row) { return '<span style="font-weight:600;">' + data + '</span> <br><small class="text-muted">' + (row.cust_mob || '') + '</small>'; } },
                    { data: "grand_total", render: function (data) { return '<span style="font-weight:700;">₹' + data + '</span>'; } },
                    { data: "paid_amount", render: function (data) { return '<span style="font-weight:700;color:#16a34a;">₹' + data + '</span>'; } },
                    { data: "outstanding", render: function (data) { return '<span style="font-weight:700;color:#dc2626;">₹' + data + '</span>'; } },
                    { data: "days_pending", render: function (data) { return '<span style="font-weight:600;color:#ca8a04;">' + data + ' Days</span>'; } },
                    { data: null, orderable: false, render: function(data, type, row) {
                        var mob = row.cust_mob ? row.cust_mob.replace(/\D/g, '') : '';
                        if(mob.length === 10) mob = '91' + mob;
                        
                        var msg = "Dear " + row.cust_name + ",\n\nA gentle reminder that an amount of ₹" + row.outstanding + " is pending against your " + row.type + " (Ref: #" + row.invoice_id + ") dated " + row.invoice_date + ".\n\nKindly clear the dues at your earliest convenience.\n\nThank you.";
                        var wa_url = "https://wa.me/" + mob + "?text=" + encodeURIComponent(msg);
                        
                        if(mob) {
                            var wa_btn = '<a href="'+wa_url+'" target="_blank" class="btn btn-sm btn-success" style="border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i class="ph-fill ph-whatsapp-logo"></i> Reminder</a>';
                        } else {
                            var wa_btn = '<span class="text-muted" style="font-size:12px;">No Mobile</span>';
                        }
                        var pay_btn = '';
                        if(row.type === 'Invoice') {
                            pay_btn = '<button class="btn btn-sm btn-primary btn-pay-outstanding" data-id="'+row.invoice_id+'" data-type="'+row.type+'" data-amt="'+row.outstanding+'" data-name="'+row.cust_name+'" style="border-radius:6px; display:inline-flex; align-items:center; gap:4px; margin-left: 8px;"><i class="ph-fill ph-currency-inr"></i> Pay</button>';
                        }
                        
                        return '<div style="display:flex; align-items:center;">' + wa_btn + pay_btn + '</div>';
                    }}
                ]
            });
        } else {
            outstandingTable.ajax.reload();
        }
    }

    // Initial load
    cb(start, end, 'All Time');

    // Handle Pay Button Click
    $(document).on('click', '.btn-pay-outstanding', function() {
        var id = $(this).data('id');
        var type = $(this).data('type');
        var amt = parseFloat($(this).data('amt')).toFixed(2);
        var name = $(this).data('name');

        $('#pay_invoice_id').val(id);
        $('#pay_type').val(type);
        $('#pay_client_name').text(name);
        $('#pay_ref_id').text('#' + id);
        $('#pay_pending_amt').text('₹' + amt);
        $('#pay_amount').val(amt);
        $('#pay_amount').attr('max', amt);

        $('#modalPayOutstanding').css('display', 'flex');
    });

    $(document).on('click', '.close-modal', function() {
        $('.modal-overlay').hide();
    });

    // Handle Payment Submission
    $('#pay_outstanding_form').submit(function(e) {
        e.preventDefault();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.html('<i class="ph-bold ph-spinner ph-spin"></i> Processing...').prop('disabled', true);
        
        $.ajax({
            type: "POST",
            url: "ajax/report_ajax.php",
            data: $(this).serialize(),
            success: function(res) {
                try {
                    var obj = JSON.parse(res);
                    if (obj.error == 1) {
                        alert(obj.msg);
                    } else {
                        $('#modalPayOutstanding').hide();
                        outstandingTable.ajax.reload();
                        // Open print receipt in new tab if requested
                        if(obj.print_url) {
                            window.open(obj.print_url, '_blank');
                        }
                    }
                } catch(err) {
                    alert('Error processing payment.');
                }
                submitBtn.html(originalText).prop('disabled', false);
            },
            error: function() {
                alert('Network Error.');
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

});

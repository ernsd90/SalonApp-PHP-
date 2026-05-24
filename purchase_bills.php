<?php include 'header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;color:var(--text-main);margin-bottom:4px;">Purchase Bills</h1>
    <p style="color:var(--text-muted);font-size:14px;">Track all purchase invoices, credit balances, and payment history.</p>
</div>

<!-- Summary Tiles -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;" id="bill_tiles">
    <div style="background:linear-gradient(135deg,#f8fafc,#e0e7ff);padding:20px;border-radius:16px;border:1px solid var(--primary-light);">
        <div style="color:var(--primary);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Total Bills</div>
        <div style="font-size:28px;font-weight:700;color:var(--text-main);"><span id="t_bills">—</span></div>
    </div>
    <div style="background:linear-gradient(135deg,#f8fafc,#fee2e2);padding:20px;border-radius:16px;border:1px solid #fecaca;">
        <div style="color:var(--danger);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Total Billed</div>
        <div style="font-size:28px;font-weight:700;color:var(--text-main);">₹<span id="t_amount">—</span></div>
    </div>
    <div style="background:linear-gradient(135deg,#f8fafc,#dcfce7);padding:20px;border-radius:16px;border:1px solid #bbf7d0;">
        <div style="color:#16a34a;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Total Paid</div>
        <div style="font-size:28px;font-weight:700;color:var(--text-main);">₹<span id="t_paid">—</span></div>
    </div>
    <div style="background:linear-gradient(135deg,#f8fafc,#fef9c3);padding:20px;border-radius:16px;border:1px solid #fde68a;">
        <div style="color:#ca8a04;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Outstanding</div>
        <div style="font-size:28px;font-weight:700;color:var(--text-main);">₹<span id="t_outstanding">—</span></div>
    </div>
</div>

<!-- Filter + Table -->
<div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div style="position:relative;">
                <i class="ph ph-calendar-blank" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                <input type="text" id="dateRangePicker" class="form-control" style="padding-left:36px;width:220px;background:#f8fafc;" placeholder="Filter by date">
                <input type="hidden" id="s_from" value=""><input type="hidden" id="s_to" value="">
            </div>
            <select id="s_status" class="form-control" style="width:130px;background:#f8fafc;">
                <option value="">All Status</option>
                <option value="unpaid">Unpaid</option>
                <option value="partial">Partial</option>
                <option value="paid">Paid</option>
            </select>
            <button id="btnFilter" class="btn-primary" style="width:auto;padding:10px 18px;margin:0;box-shadow:none;font-size:14px;height:48px;">
                <i class="ph ph-funnel"></i> Filter
            </button>
        </div>
        <a href="purchase_bill_create.php" class="btn-primary" style="width:auto;padding:10px 16px;margin:0;font-size:14px;display:flex;align-items:center;gap:8px;text-decoration:none;">
            <i class="ph-bold ph-plus"></i> New Purchase Bill
        </a>
    </div>

    <div style="padding:24px;">
        <div class="table-responsive">
            <table id="bills_table" class="table-modern" style="width:100%;">
                <thead>
                    <tr>
                        <th>Bill #</th>
                        <th>Invoice Date</th>
                        <th>Vendor</th>
                        <th>Payment Date</th>
                        <th>Total (₹)</th>
                        <th>Paid (₹)</th>
                        <th>Balance (₹)</th>
                        <th>Status</th>
                        <th style="width:90px;">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<style>
.table-modern{width:100%;border-collapse:separate;border-spacing:0}
.table-modern th{background:#f8fafc;color:var(--text-muted);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;padding:12px 16px;border-bottom:1px solid var(--border-color);text-align:left;white-space:nowrap}
.table-modern td{padding:14px 16px;font-size:14px;color:var(--text-main);border-bottom:1px solid var(--border-color);vertical-align:middle}
.table-modern tbody tr:hover td{background:#f8fafc}
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);z-index:100;align-items:center;justify-content:center}
.modal-overlay.active{display:flex}
.modal-dialog{background:white;border-radius:20px;width:100%;max-width:500px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.4);overflow:hidden;animation:fadeUp 0.25s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
</style>
<div class="modal-overlay" id="commonModalOverlay"><div class="modal-dialog" id="commonModalContent"></div></div>

<script>
$(document).ready(function(){
    var table = $('#bills_table').DataTable({
        processing:true, serverSide:true, responsive:true,
        order: [[1, 'desc']],
        ajax:{url:'ajax/inventory_ajax.php', type:'POST', data:function(d){
            d.method='get_bills';
            d.fromdate=$('#s_from').val();
            d.todate=$('#s_to').val();
            d.status_filter=$('#s_status').val();
        }},
        columns:[
            {data:'bill_id'},
            {data:'invoice_date', render:function(d){return '<span style="white-space:nowrap;color:var(--text-muted);font-size:13px;"><i class="ph ph-calendar-blank"></i> '+d+'</span>';}},
            {data:'vendor_name', render:function(d){return '<strong>'+d+'</strong>';}},
            {data:'payment_date', render:function(d){return d ? '<span style="white-space:nowrap;color:var(--text-muted);font-size:13px;"><i class="ph ph-money"></i> '+d+'</span>' : '<span style="color:var(--text-muted);font-size:13px;">—</span>';}},
            {data:'grand_total', render:function(d){return '<strong>₹'+d+'</strong>';}},
            {data:'amount_paid', render:function(d){return '<span style="color:#16a34a;font-weight:600;">₹'+d+'</span>';}},
            {data:'balance', render:function(d){return parseFloat(d)>0?'<span style="color:var(--danger);font-weight:600;">₹'+d+'</span>':'<span style="color:#16a34a;">₹0.00</span>';}},
            {data:'payment_status', orderable:false},
            {data:'action', orderable:false}
        ]
    });

    $('#dateRangePicker').daterangepicker({autoUpdateInput:false,locale:{format:'DD-MM-YYYY',cancelLabel:'Clear'}});
    $('#dateRangePicker').on('apply.daterangepicker',function(e,p){ $(this).val(p.startDate.format('DD-MM-YYYY')+' - '+p.endDate.format('DD-MM-YYYY')); $('#s_from').val(p.startDate.format('DD-MM-YYYY')); $('#s_to').val(p.endDate.format('DD-MM-YYYY')); });
    $('#dateRangePicker').on('cancel.daterangepicker',function(){ $(this).val(''); $('#s_from,#s_to').val(''); });

    $('#btnFilter').click(function(){ table.draw(); fetchSummary(); });
    fetchSummary();

    function fetchSummary(){
        $.post('ajax/inventory_ajax.php',{method:'get_bill_summary',fromdate:$('#s_from').val(),todate:$('#s_to').val()},function(r){
            try{ var o=JSON.parse(r); $('#t_bills').text(o.total_bills); $('#t_amount').text(o.total_amount); $('#t_paid').text(o.total_paid); $('#t_outstanding').text(o.total_outstanding); }catch(e){}
        });
    }
});

function loadModal(url){
    $('#commonModalContent').html('<div style="padding:40px;text-align:center;"><i class="ph ph-spinner ph-spin" style="font-size:32px;color:var(--primary);"></i></div>');
    $('#commonModalOverlay').addClass('active');
    $.ajax({url:url, success:function(d){ $('#commonModalContent').html(d); }});
}
$(document).on('click','.modalButtonCommon',function(e){ e.preventDefault(); loadModal($(this).attr('data-href')); });
$(document).on('click','.close-modal',function(){ $('#commonModalOverlay').removeClass('active'); });

// Mark as Paid (quick one-click)
$(document).on('click','.btn-mark-paid',function(){
    var id=$(this).data('id');
    if(!confirm('Mark bill #'+id+' as fully paid?')) return;
    $.post('ajax/inventory_ajax.php',{method:'mark_paid',bill_id:id},function(res){
        try{ var o=JSON.parse(res);
            if(o.error) alert('Error: '+o.msg);
            else { if($.fn.DataTable.isDataTable('#bills_table')) $('#bills_table').DataTable().draw(); fetchSummary(); }
        }catch(e){}
    });
});
$(document).on('submit','form.ajax-form',function(e){
    e.preventDefault();
    var form=$(this), btn=form.find('button[type="submit"]'), orig=btn.html();
    btn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled',true);
    $.ajax({type:'POST',url:form.attr('data-action-url'),data:form.serialize(),
        success:function(res){
            try{ var o=JSON.parse(res);
                if(o.error==1){ alert('Error: '+o.msg); btn.html(orig).prop('disabled',false); }
                else{ alert(o.msg); $('#commonModalOverlay').removeClass('active'); if($.fn.DataTable.isDataTable('#bills_table')) $('#bills_table').DataTable().draw(); }
            }catch(e){ alert('Server error'); btn.html(orig).prop('disabled',false); }
        }
    });
});
</script>
<?php include 'footer.php'; ?>

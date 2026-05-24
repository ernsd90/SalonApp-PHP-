<?php include 'header.php'; ?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<?php
$salon_id  = get_session_data('salon_id');
$sel_vendor = intval($_GET['vendor_id'] ?? 0);
$vendors   = select_array("SELECT id, vendor_name FROM hr_vendor WHERE (salon_id='$salon_id' OR salon_id=0) AND status=1 ORDER BY vendor_name ASC");
?>

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;color:var(--text-main);margin-bottom:4px;">Vendor Ledger</h1>
    <p style="color:var(--text-muted);font-size:14px;">Track debit (bills raised) and credit (payments made) for each vendor.</p>
</div>

<!-- Filters -->
<div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:20px 24px;margin-bottom:24px;">
    <div style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
        <div style="flex:1;min-width:220px;">
            <label style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:8px;display:block;">Vendor</label>
            <select id="sel_vendor" class="form-control" style="background:#f8fafc;">
                <option value="0">All Vendors</option>
                <?php foreach((array)$vendors as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $sel_vendor==$v['id']?'selected':'' ?>><?= htmlspecialchars($v['vendor_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex:1;min-width:220px;">
            <label style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:8px;display:block;">Date Range</label>
            <div style="position:relative;">
                <i class="ph ph-calendar-blank" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                <input type="text" id="dateRangePicker" class="form-control" style="padding-left:40px;background:#f8fafc;" placeholder="All dates">
                <input type="hidden" id="s_from"><input type="hidden" id="s_to">
            </div>
        </div>
        <button id="btnLoad" class="btn-primary" style="width:auto;padding:0 24px;margin:0;height:48px;box-shadow:none;font-size:14px;">
            <i class="ph ph-chart-bar"></i> Load Ledger
        </button>
        <a href="purchase_bills.php" class="btn-primary" style="width:auto;padding:0 24px;margin:0;height:48px;background:white;color:var(--text-main);border:1px solid var(--border-color);box-shadow:none;font-size:14px;text-decoration:none;display:flex;align-items:center;gap:8px;">
            <i class="ph ph-receipt"></i> View Bills
        </a>
    </div>
</div>

<!-- Summary Tiles -->
<div id="ledger_tiles" style="display:none;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
    <div style="background:linear-gradient(135deg,#f8fafc,#fee2e2);padding:20px;border-radius:16px;border:1px solid #fecaca;">
        <div style="color:var(--danger);font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Total Billed</div>
        <div style="color:var(--text-muted);font-size:11px;margin-bottom:8px;">Debit &mdash; <span class="period-dynamic">All Time</span></div>
        <div style="font-size:26px;font-weight:700;">₹<span id="t_billed">0</span></div>
    </div>
    <div style="background:linear-gradient(135deg,#f8fafc,#dcfce7);padding:20px;border-radius:16px;border:1px solid #bbf7d0;">
        <div style="color:#16a34a;font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Total Paid</div>
        <div style="color:var(--text-muted);font-size:11px;margin-bottom:8px;">Credit &mdash; <span class="period-dynamic">All Time</span></div>
        <div style="font-size:26px;font-weight:700;">₹<span id="t_paid">0</span></div>
    </div>
    <div style="background:linear-gradient(135deg,#f8fafc,#fef9c3);padding:20px;border-radius:16px;border:1px solid #fde68a;">
        <div style="color:#ca8a04;font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Outstanding</div>
        <div style="color:var(--text-muted);font-size:11px;margin-bottom:8px;">Current Balance</div>
        <div style="font-size:26px;font-weight:700;">₹<span id="t_outstanding">0</span></div>
    </div>
</div>

<!-- Ledger Table -->
<div id="ledger_card" style="display:none;" class="card-modern" style="background:white;"></div>

<style>
.table-modern{width:100%;border-collapse:separate;border-spacing:0}
.table-modern th{background:#f8fafc;color:var(--text-muted);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;padding:12px 16px;border-bottom:1px solid var(--border-color);text-align:left;white-space:nowrap}
.table-modern td{padding:14px 16px;font-size:14px;color:var(--text-main);border-bottom:1px solid var(--border-color);vertical-align:middle}
.table-modern tbody tr:hover td{background:#fafafa}
</style>

<script>
$(document).ready(function(){
    // No default date — show all-time summary by default
    $('#dateRangePicker').daterangepicker({
        autoUpdateInput: false,
        locale: { format: 'DD-MM-YYYY', cancelLabel: 'Clear' }
    });

    $('#dateRangePicker').on('apply.daterangepicker', function(e,p){
        $(this).val(p.startDate.format('DD-MM-YYYY') + ' - ' + p.endDate.format('DD-MM-YYYY'));
        $('#s_from').val(p.startDate.format('DD-MM-YYYY'));
        $('#s_to').val(p.endDate.format('DD-MM-YYYY'));
    });
    $('#dateRangePicker').on('cancel.daterangepicker', function(){
        $(this).val(''); $('#s_from,#s_to').val('');
    });

    loadLedger(); // Auto-load on page open (all vendors, all time)
    $('#btnLoad').click(function(){ loadLedger(); });

    function loadLedger(){
        var vid = $('#sel_vendor').val() || '0'; // 0 = all vendors
        $('#btnLoad').html('<i class="ph ph-spinner ph-spin"></i> Loading...').prop('disabled',true);
        $.post('ajax/inventory_ajax.php',{method:'get_vendor_ledger', vendor_id:vid, fromdate:$('#s_from').val(), todate:$('#s_to').val()},function(res){
            $('#btnLoad').html('<i class="ph ph-chart-bar"></i> Load Ledger').prop('disabled',false);
            try{
                var o = JSON.parse(res);
                if(o.error){ alert(o.msg); return; }
                // Tiles
                var fromD = $('#s_from').val(), toD = $('#s_to').val();
                var tileLabel = (fromD && toD) ? fromD + ' – ' + toD : 'All Time';
                $('#t_billed').text(o.total_billed);
                $('#t_paid').text(o.total_paid);
                $('#t_outstanding').text(o.outstanding);
                // Update tile sub-labels
                $('#ledger_tiles').find('.period-dynamic').text(tileLabel);
                $('#ledger_tiles').css('display','grid');
                // Table
                var vendorLabel = $('#sel_vendor option:selected').text() || 'All Vendors';
                var html = '<div style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;">';
                html += '<div style="padding:16px 24px;border-bottom:1px solid var(--border-color);font-size:15px;font-weight:600;">'+vendorLabel+' — Transaction Ledger</div>';
                html += '<div style="padding:0"><table class="table-modern">';
                html += '<thead><tr><th>Date</th><th>Description</th><th style="text-align:right;color:var(--danger);">Debit (Bill)</th><th style="text-align:right;color:#16a34a;">Credit (Paid)</th><th style="text-align:right;">Balance</th></tr></thead><tbody>';
                if(o.rows.length===0){ html += '<tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted);">No transactions found.</td></tr>'; }
                o.rows.forEach(function(r){
                    var balColor = parseFloat(r.balance_raw)>0?'color:var(--danger);':'color:#16a34a;';
                    html += '<tr><td style="white-space:nowrap;color:var(--text-muted);font-size:13px;">'+r.date+'</td><td>'+r.description+'</td>';
                    html += '<td style="text-align:right;color:var(--danger);font-weight:600;">'+(r.debit!='—'?r.debit:'<span style="color:var(--text-muted);">—</span>')+'</td>';
                    html += '<td style="text-align:right;color:#16a34a;font-weight:600;">'+(r.credit!='—'?r.credit:'<span style="color:var(--text-muted);">—</span>')+'</td>';
                    html += '<td style="text-align:right;font-weight:700;'+balColor+'">'+r.balance+'</td></tr>';
                });
                html += '</tbody></table></div></div>';
                $('#ledger_card').html(html).show();
            }catch(e){ alert('Error loading ledger.'); }
        });
    }
});
</script>
<?php include 'footer.php'; ?>

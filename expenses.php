<?php
include 'header.php'; 
?>

<!-- DataTables Required CSS/JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<!-- Date Range Picker CSS/JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Expense Tracking</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Log and monitor your cash outflows, vendor payments, and category breakdowns.</p>
</div>

<!-- Filter Section -->
<div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:18px 24px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">

        <!-- Quick presets -->
        <div style="display:flex;gap:6px;flex-wrap:wrap;" id="date-presets">
            <?php
            $presets = [
                ['today',      'Today'],
                ['yesterday',  'Yesterday'],
                ['last7',      'Last 7 Days'],
                ['last30',     'Last 30 Days'],
                ['thismonth',  'This Month'],
                ['lastmonth',  'Last Month'],
                ['thisyear',   'This Year'],
                ['custom',     'Custom Range'],
            ];
            foreach($presets as [$key,$label]):
            ?>
            <button class="preset-btn<?= $key==='thismonth'?' active':'' ?>" data-preset="<?= $key ?>" style="padding:7px 14px;border-radius:8px;border:1.5px solid <?= $key==='thismonth'?'var(--primary)':'var(--border-color)' ?>;background:<?= $key==='thismonth'?'var(--primary-light)':'#f8fafc' ?>;color:<?= $key==='thismonth'?'var(--primary)':'var(--text-muted)' ?>;font-size:13px;font-weight:600;cursor:pointer;transition:.15s;white-space:nowrap;"><?= $label ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Custom date range (hidden by default) -->
        <div id="custom-date-wrap" style="display:none;align-items:center;gap:8px;">
            <div style="position:relative;">
                <i class="ph ph-calendar-blank" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:15px;"></i>
                <input type="text" id="dateRangePicker" class="form-control" placeholder="Select date range" style="padding-left:36px;height:38px;width:230px;background:#f8fafc;font-size:13px;" />
            </div>
            <button id="btnFilter" class="btn-primary" style="margin:0;height:38px;padding:0 18px;box-shadow:none;width:auto;font-size:13px;">
                <i class="ph ph-funnel"></i> Apply
            </button>
        </div>

        <!-- Hidden date values sent to server -->
        <input type="hidden" id="search_fromdate" value="<?= date('01-m-Y') ?>">
        <input type="hidden" id="search_todate"   value="<?= date('d-m-Y') ?>">

        <!-- Active range label -->
        <span id="active-range-label" style="margin-left:auto;font-size:12px;color:var(--text-muted);background:#f1f5f9;padding:4px 12px;border-radius:20px;">📅 <?= date('1 M Y').' – '.date('d M Y') ?></span>
    </div>
</div>

<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px;">
    
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-main);">Expense Log</h3>
        <div style="display: flex; gap: 12px;">
            <a href="expenses_dashboard.php" class="btn-primary" style="background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
                <i class="ph-bold ph-chart-line-up"></i> Analytics Dashboard
            </a>
            <a href="expense_cat.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
                <i class="ph-bold ph-folder-open"></i> Manage Categories
            </a>
            <button class="btn-primary" style="width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;" onclick="loadModal('expenses_edit.php?payment_mode=cash');">
                <i class="ph-bold ph-plus"></i> Add Expense
            </button>
        </div>
    </div>

    <!-- Metrics Cards (dynamic — populated by fetchExpenseSummary) -->
    <div style="padding: 24px 24px 0 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;" id="expense_tiles">
        <div style="background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); padding: 20px; border-radius: 16px; border: 1px solid var(--primary-light);">
            <div style="color: var(--primary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Total Logs</div>
            <div style="font-size: 28px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 12px;">
                <span class="exp_number">0</span>
                <i class="ph-fill ph-receipt" style="color: var(--primary); opacity: 0.2;"></i>
            </div>
        </div>
        <div style="background: linear-gradient(135deg, #f8fafc 0%, #fee2e2 100%); padding: 20px; border-radius: 16px; border: 1px solid #fecaca;">
            <div style="color: var(--danger); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Total Spent (All)</div>
            <div style="font-size: 28px; font-weight: 700; color: var(--text-main);">
                ₹<span class="exp_total">0.00</span>
            </div>
        </div>
        <!-- Payment method breakdown tiles inserted here by JS -->
    </div>

    <div style="padding: 24px;">
        <input type="hidden" value="cash" id="expence_type" />
        <div class="table-responsive">
            <table id="get_expenses" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Vendor / Note</th>
                        <th style="white-space:nowrap;">Date</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<style>
/* Modern Table Reset */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern th { background: #f8fafc; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 1px solid var(--border-color); text-align: left; }
.table-modern td { padding: 16px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border-color); vertical-align: middle; }
.table-modern tbody tr:hover td { background: #f8fafc; }

.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: fadeUp 0.3s ease-out forwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.btn-edit { background: #e0e7ff; color: #4f46e5; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; margin-right: 4px; font-size: 13px; cursor: pointer; transition: 0.2s; }
.btn-edit:hover { background: #c7d2fe; }
.btn-delete { background: #fee2e2; color: #dc2626; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; margin-right: 4px; font-size: 13px; cursor: pointer; transition: 0.2s; }
.btn-delete:hover { background: #fecaca; }

/* Preset button hover */
.preset-btn:hover { border-color: var(--primary) !important; color: var(--primary) !important; background: var(--primary-light) !important; }
.preset-btn.active { border-color: var(--primary) !important; background: var(--primary-light) !important; color: var(--primary) !important; }
</style>

<!-- Custom V3 Modal Wrapper -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<script>
$(document).ready(function() {

    // ── Date preset logic ───────────────────────────────────────────────────
    var fmt = 'DD-MM-YYYY';
    var displayFmt = 'D MMM YYYY';

    function setDateRange(from, to) {
        $('#search_fromdate').val(from.format(fmt));
        $('#search_todate').val(to.format(fmt));
        $('#active-range-label').text('📅 ' + from.format(displayFmt) + ' – ' + to.format(displayFmt));
        redraw();
    }

    function applyPreset(preset) {
        var s, e;
        switch(preset) {
            case 'today':     s = e = moment(); break;
            case 'yesterday': s = e = moment().subtract(1,'days'); break;
            case 'last7':     s = moment().subtract(6,'days'); e = moment(); break;
            case 'last30':    s = moment().subtract(29,'days'); e = moment(); break;
            case 'thismonth': s = moment().startOf('month'); e = moment().endOf('month'); break;
            case 'lastmonth': s = moment().subtract(1,'month').startOf('month');
                              e = moment().subtract(1,'month').endOf('month'); break;
            case 'thisyear':  s = moment().startOf('year'); e = moment().endOf('year'); break;
            case 'custom': $('#custom-date-wrap').show(); return;
        }
        $('#custom-date-wrap').hide();
        setDateRange(s, e);
    }

    // Preset button clicks
    $(document).on('click', '.preset-btn', function() {
        $('.preset-btn').removeClass('active');
        $(this).addClass('active');
        applyPreset($(this).data('preset'));
    });

    // Apply button for custom range
    $('#btnFilter').click(function() { redraw(); fetchExpenseSummary(); });

    // Custom date range picker
    $('#dateRangePicker').daterangepicker({
        autoUpdateInput: false,
        startDate: moment().startOf('month'),
        endDate: moment(),
        locale: { format: fmt, cancelLabel: 'Clear' }
    }, function(start, end) {
        $('#dateRangePicker').val(start.format(fmt) + ' – ' + end.format(fmt));
        $('#search_fromdate').val(start.format(fmt));
        $('#search_todate').val(end.format(fmt));
        $('#active-range-label').text('📅 ' + start.format(displayFmt) + ' – ' + end.format(displayFmt));
    });
    $('#dateRangePicker').on('cancel.daterangepicker', function() {
        $(this).val('');
    });

    // ── DataTable ────────────────────────────────────────────────────────────
    var get_expenses = $('#get_expenses').DataTable({
        processing: true, serverSide: true, responsive: true,
        ajax: {
            url: 'ajax/salon_ajax.php', type: 'POST',
            data: function(d) {
                d.method     = 'get_expenses';
                d.fromdate   = $('#search_fromdate').val();
                d.todate     = $('#search_todate').val();
                d.expence_type = 'all'; // show all payment modes
            }
        },
        columns: [
            { data: 'exp_id', width: '60px' },
            { data: 'category_name', render: function(v) {
                return '<span style="color:var(--primary);font-weight:600;background:var(--primary-light);padding:3px 10px;border-radius:20px;font-size:12px;white-space:nowrap;">'+(v||'General')+'</span>';
            }},
            { data: 'exp_name', render: function(v) { return '<span style="font-weight:600;">'+v+'</span>'; }},
            { data: 'exp_total', render: function(v) {
                return '<span style="font-weight:700;color:var(--danger);white-space:nowrap;">-₹'+parseFloat(v).toFixed(2)+'</span>';
            }},
            { data: 'payment_mode', render: function(v) {
                if(!v) return '<span style="color:var(--text-muted);">—</span>';
                var icons={cash:'ph-money',card:'ph-credit-card',upi:'ph-device-mobile',wallet:'ph-wallet'};
                var icon=icons[(v||'').toLowerCase()]||'ph-currency-circle-dollar';
                return '<span style="display:inline-flex;align-items:center;gap:5px;font-size:13px;font-weight:600;white-space:nowrap;"><i class="ph '+icon+'" style="color:var(--primary);"></i>'+v.toUpperCase()+'</span>';
            }},
            { data: 'exp_note', render: function(v,t,row) {
                var note = v ? '<span style="color:var(--text-muted);font-size:13px;">'+v+'</span>' : '';
                var vendor = row.exp_vendor ? '<br><small style="color:var(--text-muted);"><i class="ph ph-storefront"></i> '+row.exp_vendor+'</small>' : '';
                return note+vendor || '<span style="color:var(--text-muted);">—</span>';
            }},
            { data: 'exp_date', render: function(v) {
                return '<span style="color:var(--text-muted);font-size:13px;white-space:nowrap;"><i class="ph ph-calendar-blank"></i> '+v+'</span>';
            }},
            { data: 'action', orderable: false }
        ]
    });

    function redraw() { get_expenses.draw(); fetchExpenseSummary(); }

    // ── Summary tiles ────────────────────────────────────────────────────────
    var tileColors = ['#e0e7ff:#4f46e5','#dcfce7:#16a34a','#fef9c3:#ca8a04','#fce7f3:#be185d','#f1f5f9:#64748b'];
    function fetchExpenseSummary() {
        $.post('ajax/salon_ajax.php', {
            method: 'expenses_summary',
            fromdate: $('#search_fromdate').val(),
            todate:   $('#search_todate').val()
        }, function(res) {
            try {
                var obj = JSON.parse(res);
                $('.exp_total').text(obj.exp_total || '0.00');
                $('.exp_number').text(obj.exp_number || '0');
                $('#expense_tiles .breakdown-tile').remove();
                if(obj.breakdown && obj.breakdown.length > 0) {
                    obj.breakdown.forEach(function(pm, i) {
                        var colors = (tileColors[i]||'#f1f5f9:#64748b').split(':');
                        var name = pm.payment_mode ? pm.payment_mode.charAt(0).toUpperCase()+pm.payment_mode.slice(1) : 'Other';
                        $('#expense_tiles').append(
                            '<div class="breakdown-tile" style="background:linear-gradient(135deg,#f8fafc,'+colors[0]+');padding:20px;border-radius:16px;border:1px solid '+colors[0]+';min-width:180px;">'+
                            '<div style="color:'+colors[1]+';font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">'+name+'</div>'+
                            '<div style="font-size:24px;font-weight:700;color:var(--text-main);">₹'+parseFloat(pm.total).toFixed(2)+'</div>'+
                            '<div style="font-size:12px;color:var(--text-muted);margin-top:4px;">'+pm.cnt+' transactions</div>'+
                            '</div>'
                        );
                    });
                }
            } catch(e) {}
        });
    }

    // ── Boot: apply "This Month" preset ─────────────────────────────────────
    applyPreset('thismonth');

    // Modal helpers
    $(document).on('click', '.close-modal', function(){ $('#commonModalOverlay').removeClass('active'); });
});

function loadModal(url) {
    $('#commonModalContent').html('<div style="padding:40px;text-align:center;"><i class="ph ph-spinner ph-spin" style="font-size:32px;color:var(--primary);"></i><p>Loading...</p></div>');
    $('#commonModalOverlay').addClass('active');
    $.ajax({url:url, success:function(data){ $('#commonModalContent').html(data); }});
}

$(document).on('click', '.modalButtonCommon', function(e){
    e.preventDefault();
    if($(this).attr('data-href')) loadModal($(this).attr('data-href'));
});

$(document).on('submit', 'form.ajax-form', function(e){
    e.preventDefault();
    var form=$(this), targetUrl=form.attr('action')||'ajax/salon_ajax.php';
    var submitBtn=form.find('button[type="submit"]'), originalText=submitBtn.html();
    submitBtn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled',true);
    $.ajax({
        type:'POST', url:targetUrl, data:form.serialize(),
        success:function(res){
            try {
                var obj=JSON.parse(res);
                if(obj.error==1){ alert('Error: '+obj.msg); submitBtn.html(originalText).prop('disabled',false); }
                else { alert('Success: '+obj.msg); $('#commonModalOverlay').removeClass('active'); if($.fn.DataTable.isDataTable('#get_expenses')) $('#get_expenses').DataTable().draw(false); }
            } catch(e){ alert('Server Error.'); submitBtn.html(originalText).prop('disabled',false); }
        }
    });
});
</script>

<?php include 'footer.php'; ?>

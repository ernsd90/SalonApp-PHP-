<?php
include "header.php";

if (!in_array(strtolower($user_role_name ?? ''), ['superadmin', 'admin'])) {
    echo "<div style='padding:40px;text-align:center;'>Access Denied.</div>";
    include "footer.php";
    exit;
}
?>

<div class="content-header" style="margin-bottom:24px;">
    <div>
        <h1 class="page-title" style="margin:0;font-size:24px;font-weight:800;display:flex;align-items:center;gap:10px;">
            <i class="ph-fill ph-whatsapp-logo" style="color:#25D366;"></i> WhatsApp Activity Logs
        </h1>
        <p style="margin:4px 0 0;color:var(--text-muted);font-size:14px;">Track exactly where and when WhatsApp messages were sent across the software.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:20px;margin-bottom:24px;">
    <div style="background:white;padding:20px;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:16px;">
        <div style="width:48px;height:48px;border-radius:12px;background:#e0f2fe;color:#0ea5e9;display:flex;align-items:center;justify-content:center;font-size:24px;"><i class="ph-fill ph-calendar"></i></div>
        <div><div style="font-size:12px;color:var(--text-muted);font-weight:700;text-transform:uppercase;">Period Total</div><div id="sum_period" style="font-size:24px;font-weight:800;color:var(--text-main);">0</div></div>
    </div>
    <div style="background:white;padding:20px;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:16px;">
        <div style="width:48px;height:48px;border-radius:12px;background:#dcfce7;color:#22c55e;display:flex;align-items:center;justify-content:center;font-size:24px;"><i class="ph-fill ph-sun"></i></div>
        <div><div style="font-size:12px;color:var(--text-muted);font-weight:700;text-transform:uppercase;">Sent Today</div><div id="sum_today" style="font-size:24px;font-weight:800;color:var(--text-main);">0</div></div>
    </div>
    <div style="background:white;padding:20px;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:16px;">
        <div style="width:48px;height:48px;border-radius:12px;background:#f3e8ff;color:#a855f7;display:flex;align-items:center;justify-content:center;font-size:24px;"><i class="ph-fill ph-whatsapp-logo"></i></div>
        <div><div style="font-size:12px;color:var(--text-muted);font-weight:700;text-transform:uppercase;">All Time</div><div id="sum_all" style="font-size:24px;font-weight:800;color:var(--text-main);">0</div></div>
    </div>
</div>

<div style="background:white;border-radius:20px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:24px;">
    <div style="margin-bottom:20px;display:flex;gap:16px;align-items:flex-end;">
        <div style="flex:1;max-width:250px;">
            <label style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;display:block;">Filter by Date</label>
            <select id="daterange" class="form-control">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="custom">Custom Date</option>
            </select>
        </div>
        <div id="custom_date_div" style="display:none;flex:1;max-width:300px;gap:10px;">
            <div style="flex:1;"><input type="date" id="start_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <div style="flex:1;"><input type="date" id="end_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
        </div>
        <div>
            <button class="btn-primary" onclick="tbl.draw()" style="padding:10px 20px;">Filter Logs</button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="wa_logs_table" class="table table-modern" style="width:100%;">
            <thead>
                <tr>
                    <th style="width:180px;">Date & Time</th>
                    <th>User</th>
                    <th>Module / Source</th>
                    <th>Message Details (URL)</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<style>
.table-modern th{background:#f8fafc;color:var(--text-muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:14px 16px;border-bottom:1px solid #e2e8f0;}
.table-modern td{padding:14px 16px;font-size:14px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
</style>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$('#daterange').change(function(){
    if($(this).val() === 'custom') $('#custom_date_div').css('display', 'flex');
    else $('#custom_date_div').hide();
    tbl.draw();
});

var tbl = $('#wa_logs_table').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    order: [[0, 'desc']],
    ajax: {
        url: 'ajax/whatsapp_reports_ajax.php',
        type: 'POST',
        data: function(d) {
            d.daterange = $('#daterange').val();
            d.start_date = $('#start_date').val();
            d.end_date = $('#end_date').val();
        }
    },
    drawCallback: function(settings) {
        var json = settings.json;
        if(json && json.summary) {
            $('#sum_period').text(json.summary.period);
            $('#sum_today').text(json.summary.today);
            $('#sum_all').text(json.summary.all);
        }
    },
    columns: [
        { data: 'created_at' },
        { data: 'user' },
        { data: 'module' },
        { data: 'message' }
    ]
});
</script>

<?php include "footer.php"; ?>

<?php
include 'header.php';
?>

<!-- Date Range Picker CSS/JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<style>
.report-card { background: white; border-radius: 16px; padding: 24px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 24px; }
.kpi-title { color: var(--text-muted); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
.kpi-value { font-size: 28px; font-weight: 700; color: var(--text-main); }
.table-reports { width: 100%; border-collapse: collapse; }
.table-reports th { background: #f8fafc; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid var(--border-color); text-align: left; }
.table-reports td { padding: 16px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border-color); }
.badge-success { background: #dcfce7; color: #16a34a; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.badge-danger { background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.badge-neutral { background: #f1f5f9; color: #64748b; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; }
</style>

<div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Advanced Reports</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Compare expenses across time periods, categories, and vendors.</p>
    </div>
    <a href="expenses_dashboard.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
        <i class="ph-bold ph-chart-pie-slice"></i> Dashboard
    </a>
</div>

<!-- Controls -->
<div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:0 4px 6px -1px rgba(0, 0, 0, 0.05);padding:18px 24px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="display:flex; flex-direction:column; gap:4px;">
            <label style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Timeframe</label>
            <input type="text" id="dateRangePicker" class="form-control" placeholder="Select date range" style="height:38px;width:230px;background:#f8fafc;font-size:13px;" />
            <input type="hidden" id="search_fromdate" value="<?= date('01-m-Y') ?>">
            <input type="hidden" id="search_todate"   value="<?= date('d-m-Y') ?>">
        </div>
        
        <div style="display:flex; flex-direction:column; gap:4px;">
            <label style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Compare Against</label>
            <select id="compare_type" class="form-control" style="height:38px;width:180px;background:#f8fafc;font-size:13px;">
                <option value="mom">Previous Month (MoM)</option>
                <option value="yoy">Previous Year (YoY)</option>
                <option value="wow">Previous Week (WoW)</option>
            </select>
        </div>
        
        <button id="btnGenerate" class="btn-primary" style="margin-top:18px;height:38px;padding:0 24px;box-shadow:none;width:auto;font-size:13px;">
            <i class="ph-bold ph-arrows-clockwise"></i> Generate Report
        </button>
    </div>
</div>

<!-- Summary -->
<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:24px; margin-bottom:24px;">
    <div class="report-card" style="margin-bottom:0;">
        <div class="kpi-title">Current Period Expense</div>
        <div class="kpi-value" id="val-curr">₹0.00</div>
    </div>
    <div class="report-card" style="margin-bottom:0;">
        <div class="kpi-title">Comparison Period Expense</div>
        <div class="kpi-value" id="val-prev" style="color:var(--text-muted);">₹0.00</div>
    </div>
    <div class="report-card" style="margin-bottom:0;">
        <div class="kpi-title">Overall Growth</div>
        <div class="kpi-value" id="val-growth">-</div>
    </div>
</div>

<!-- Data Tables -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
    <div class="report-card">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0;">Category Comparison</h3>
        <div style="overflow-x:auto;">
            <table class="table-reports" id="catTable">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Current</th>
                        <th>Previous</th>
                        <th>Growth</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="report-card">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0;">Vendor Comparison</h3>
        <div style="overflow-x:auto;">
            <table class="table-reports" id="vendorTable">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Current</th>
                        <th>Previous</th>
                        <th>Growth</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
function formatMoney(n) { return '₹' + Number(n).toFixed(2); }

function getBadge(grow) {
    if (grow > 0) return `<span class="badge-danger"><i class="ph-bold ph-arrow-up-right"></i> ${grow.toFixed(1)}%</span>`;
    if (grow < 0) return `<span class="badge-success"><i class="ph-bold ph-arrow-down-right"></i> ${Math.abs(grow).toFixed(1)}%</span>`;
    return `<span class="badge-neutral">0%</span>`;
}

function fetchReports() {
    $('#btnGenerate').html('<i class="ph ph-spinner ph-spin"></i> Loading...').prop('disabled', true);
    
    $.post('ajax/expenses_ajax.php', {
        method: 'get_reports_data',
        fromdate: $('#search_fromdate').val(),
        todate: $('#search_todate').val(),
        compare_type: $('#compare_type').val()
    }, function(res) {
        $('#btnGenerate').html('<i class="ph-bold ph-arrows-clockwise"></i> Generate Report').prop('disabled', false);
        try {
            const data = JSON.parse(res);
            
            $('#val-curr').text(formatMoney(data.current_total));
            $('#val-prev').text(formatMoney(data.prev_total));
            
            if (data.growth_percent > 0) {
                $('#val-growth').html(`<span style="color:#dc2626;">+${data.growth_percent.toFixed(1)}% <i class="ph-bold ph-arrow-up-right" style="font-size:18px;"></i></span>`);
            } else if (data.growth_percent < 0) {
                $('#val-growth').html(`<span style="color:#16a34a;">-${Math.abs(data.growth_percent).toFixed(1)}% <i class="ph-bold ph-arrow-down-right" style="font-size:18px;"></i></span>`);
            } else {
                $('#val-growth').html(`<span style="color:var(--text-muted);">0%</span>`);
            }

            // Cat Table
            let catHtml = '';
            data.category_comparison.sort((a,b) => b.current - a.current).forEach(c => {
                catHtml += `<tr>
                    <td style="font-weight:500;">${c.category}</td>
                    <td>${formatMoney(c.current)}</td>
                    <td style="color:var(--text-muted);">${formatMoney(c.previous)}</td>
                    <td>${getBadge(c.growth)}</td>
                </tr>`;
            });
            $('#catTable tbody').html(catHtml || '<tr><td colspan="4" style="text-align:center;">No data available</td></tr>');

            // Vendor Table
            let vendorHtml = '';
            data.vendor_comparison.sort((a,b) => b.current - a.current).slice(0, 15).forEach(v => {
                vendorHtml += `<tr>
                    <td style="font-weight:500;">${v.vendor}</td>
                    <td>${formatMoney(v.current)}</td>
                    <td style="color:var(--text-muted);">${formatMoney(v.previous)}</td>
                    <td>${getBadge(v.growth)}</td>
                </tr>`;
            });
            $('#vendorTable tbody').html(vendorHtml || '<tr><td colspan="4" style="text-align:center;">No data available</td></tr>');

        } catch(e) {
            console.error("Error parsing report data", e);
        }
    });
}

$(document).ready(function() {
    var fmt = 'DD-MM-YYYY';
    $('#dateRangePicker').daterangepicker({
        locale: { format: fmt },
        startDate: moment().startOf('month'),
        endDate: moment()
    }, function(start, end) {
        $('#search_fromdate').val(start.format(fmt));
        $('#search_todate').val(end.format(fmt));
    });

    $('#btnGenerate').click(fetchReports);

    // Initial load
    fetchReports();
});
</script>

<?php include 'footer.php'; ?>

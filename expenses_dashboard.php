<?php
include 'header.php';
?>

<!-- Date Range Picker CSS/JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* Modern Dashboard Styles */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}
.kpi-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.kpi-title {
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-main);
}
.kpi-icon {
    position: absolute;
    right: -10px;
    bottom: -10px;
    font-size: 80px;
    opacity: 0.04;
    color: var(--primary);
}
.chart-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.insight-alert {
    padding: 16px 20px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 14px;
    font-weight: 500;
}
.insight-warning { background: #fffbeb; border: 1px solid #fef3c7; color: #b45309; }
.insight-success { background: #f0fdf4; border: 1px solid #dcfce7; color: #15803d; }
.insight-danger { background: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; }
.insight-info { background: #eff6ff; border: 1px solid #dbeafe; color: #1d4ed8; }
</style>

<div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Expense Analytics</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Enterprise Business Intelligence Dashboard</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="expenses_approvals.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
            <i class="ph-bold ph-check-square"></i> Approvals
        </a>
        <a href="expenses_budgets.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
            <i class="ph-bold ph-wallet"></i> Budgets
        </a>
        <a href="expenses_reports.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
            <i class="ph-bold ph-chart-bar"></i> Advanced Reports
        </a>
        <a href="expenses.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
            <i class="ph-bold ph-arrow-left"></i> Back to Logs
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:0 4px 6px -1px rgba(0, 0, 0, 0.05);padding:18px 24px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <div style="display:flex;gap:6px;flex-wrap:wrap;" id="date-presets">
            <?php
            $presets = [
                ['thismonth',  'This Month'],
                ['lastmonth',  'Last Month'],
                ['last30',     'Last 30 Days'],
                ['thisyear',   'This Year'],
                ['custom',     'Custom Range'],
            ];
            foreach($presets as [$key,$label]):
            ?>
            <button class="preset-btn<?= $key==='thismonth'?' active':'' ?>" data-preset="<?= $key ?>" style="padding:7px 14px;border-radius:8px;border:1.5px solid <?= $key==='thismonth'?'var(--primary)':'var(--border-color)' ?>;background:<?= $key==='thismonth'?'var(--primary-light)':'#f8fafc' ?>;color:<?= $key==='thismonth'?'var(--primary)':'var(--text-muted)' ?>;font-size:13px;font-weight:600;cursor:pointer;transition:.15s;white-space:nowrap;"><?= $label ?></button>
            <?php endforeach; ?>
        </div>
        <div id="custom-date-wrap" style="display:none;align-items:center;gap:8px;">
            <input type="text" id="dateRangePicker" class="form-control" placeholder="Select date range" style="height:38px;width:230px;background:#f8fafc;font-size:13px;" />
        </div>
        <input type="hidden" id="search_fromdate" value="<?= date('01-m-Y') ?>">
        <input type="hidden" id="search_todate"   value="<?= date('d-m-Y') ?>">
        
        <div style="margin-left: auto; display: flex; gap: 8px;">
            <button onclick="exportReport('csv')" class="btn-primary" style="background:#f1f5f9; color:var(--text-main); border:1px solid var(--border-color); height:38px; padding:0 16px; font-size:13px; box-shadow:none;"><i class="ph-bold ph-download-simple"></i> CSV</button>
            <button onclick="exportReport('print')" class="btn-primary" style="background:#f1f5f9; color:var(--text-main); border:1px solid var(--border-color); height:38px; padding:0 16px; font-size:13px; box-shadow:none;"><i class="ph-bold ph-printer"></i> Print</button>
        </div>
    </div>
</div>

<!-- Smart Insights -->
<div id="insights-container" style="margin-bottom: 24px;"></div>

<!-- KPI Grid -->
<div class="dashboard-grid">
    <div class="kpi-card">
        <div class="kpi-title">Total Expense</div>
        <div class="kpi-value" id="kpi-total">₹0.00</div>
        <div style="font-size: 12px; margin-top: 8px;" id="kpi-compare">...</div>
        <i class="ph-fill ph-wallet kpi-icon"></i>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Daily Average</div>
        <div class="kpi-value" id="kpi-daily">₹0.00</div>
        <i class="ph-fill ph-calendar kpi-icon"></i>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Highest Category</div>
        <div class="kpi-value" id="kpi-cat" style="font-size: 22px;">-</div>
        <i class="ph-fill ph-chart-pie-slice kpi-icon"></i>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Top Vendor</div>
        <div class="kpi-value" id="kpi-vendor" style="font-size: 22px;">-</div>
        <i class="ph-fill ph-storefront kpi-icon"></i>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Pending Approvals</div>
        <div class="kpi-value" id="kpi-pending" style="color: #ea580c;">0</div>
        <i class="ph-fill ph-clock-countdown kpi-icon"></i>
    </div>
</div>

<!-- Charts Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <div class="chart-card">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0;">Expense Trend</h3>
        <canvas id="trendChart" height="100"></canvas>
    </div>
    <div class="chart-card">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0;">Category Distribution</h3>
        <canvas id="categoryChart" height="200"></canvas>
    </div>
</div>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 40px;">
    <div class="chart-card">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0;">Payment Modes</h3>
        <canvas id="paymentChart" height="150"></canvas>
    </div>
    <div class="chart-card">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0;">Cash vs Online</h3>
        <canvas id="cashOnlineChart" height="150"></canvas>
    </div>
</div>

<script>
let trendChart, categoryChart, paymentChart, cashOnlineChart;

// Setup Chart.js defaults
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#64748b';
Chart.defaults.plugins.tooltip.backgroundColor = '#1e293b';
Chart.defaults.plugins.tooltip.padding = 12;

function initCharts() {
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(ctxTrend, {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Expense', data: [], borderColor: '#4f46e5', backgroundColor: 'rgba(79, 70, 229, 0.1)', fill: true, tension: 0.4 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(ctxCat, {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [], backgroundColor: ['#4f46e5', '#ec4899', '#f59e0b', '#10b981', '#6366f1', '#8b5cf6'] }] },
        options: { responsive: true, cutout: '70%', plugins: { legend: { position: 'right' } } }
    });

    const ctxPm = document.getElementById('paymentChart').getContext('2d');
    paymentChart = new Chart(ctxPm, {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Amount', data: [], backgroundColor: '#6366f1', borderRadius: 6 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    const ctxCash = document.getElementById('cashOnlineChart').getContext('2d');
    cashOnlineChart = new Chart(ctxCash, {
        type: 'pie',
        data: { labels: ['Online/Card', 'Cash'], datasets: [{ data: [0, 0], backgroundColor: ['#10b981', '#f59e0b'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}

function fetchDashboardData() {
    $.post('ajax/expenses_ajax.php', {
        method: 'get_dashboard_data',
        fromdate: $('#search_fromdate').val(),
        todate: $('#search_todate').val()
    }, function(res) {
        try {
            const data = JSON.parse(res);
            
            // Update KPIs
            $('#kpi-total').text('₹' + Number(data.total_expense).toFixed(2));
            $('#kpi-daily').text('₹' + Number(data.daily_average).toFixed(2));
            $('#kpi-cat').text(data.highest_category);
            $('#kpi-vendor').text(data.top_vendor);
            $('#kpi-pending').text(data.pending_approvals);
            
            if (data.last_month_compare > 0) {
                $('#kpi-compare').html(`<span style="color:#b91c1c;"><i class="ph-bold ph-arrow-up-right"></i> ${data.last_month_compare.toFixed(1)}%</span> vs last month`);
            } else if (data.last_month_compare < 0) {
                $('#kpi-compare').html(`<span style="color:#15803d;"><i class="ph-bold ph-arrow-down-right"></i> ${Math.abs(data.last_month_compare).toFixed(1)}%</span> vs last month`);
            } else {
                $('#kpi-compare').html(`<span style="color:var(--text-muted);">- 0% vs last month</span>`);
            }

            // Update Insights
            let insightsHtml = '';
            data.insights.forEach(ins => {
                insightsHtml += `<div class="insight-alert insight-${ins.type}"><i class="ph-bold ${ins.icon}" style="font-size:20px;"></i> ${ins.text}</div>`;
            });
            $('#insights-container').html(insightsHtml);

            // Update Trend Chart
            trendChart.data.labels = data.expense_trend.map(t => t.date);
            trendChart.data.datasets[0].data = data.expense_trend.map(t => t.total);
            trendChart.update();

            // Update Category Chart
            categoryChart.data.labels = data.category_distribution.map(c => c.name);
            categoryChart.data.datasets[0].data = data.category_distribution.map(c => c.total);
            categoryChart.update();

            // Update Payment Chart
            paymentChart.data.labels = data.payment_modes.map(p => p.name);
            paymentChart.data.datasets[0].data = data.payment_modes.map(p => p.total);
            paymentChart.update();

            // Update Cash vs Online Chart
            cashOnlineChart.data.datasets[0].data = [data.cash_vs_online.online, data.cash_vs_online.cash];
            cashOnlineChart.update();

        } catch(e) {
            console.error("Error parsing dashboard data:", e);
        }
    });
}

$(document).ready(function() {
    initCharts();

    // Date Presets Logic
    var fmt = 'DD-MM-YYYY';
    function setDateRange(from, to) {
        $('#search_fromdate').val(from.format(fmt));
        $('#search_todate').val(to.format(fmt));
        fetchDashboardData();
    }

    function applyPreset(preset) {
        var s, e;
        switch(preset) {
            case 'last30':    s = moment().subtract(29,'days'); e = moment(); break;
            case 'thismonth': s = moment().startOf('month'); e = moment().endOf('month'); break;
            case 'lastmonth': s = moment().subtract(1,'month').startOf('month'); e = moment().subtract(1,'month').endOf('month'); break;
            case 'thisyear':  s = moment().startOf('year'); e = moment().endOf('year'); break;
            case 'custom': $('#custom-date-wrap').show(); return;
        }
        $('#custom-date-wrap').hide();
        setDateRange(s, e);
    }

    $('.preset-btn').click(function() {
        $('.preset-btn').removeClass('active').css({'border-color':'var(--border-color)', 'background':'#f8fafc', 'color':'var(--text-muted)'});
        $(this).addClass('active').css({'border-color':'var(--primary)', 'background':'var(--primary-light)', 'color':'var(--primary)'});
        applyPreset($(this).data('preset'));
    });

    $('#dateRangePicker').daterangepicker({
        locale: { format: fmt },
        startDate: moment().startOf('month'),
        endDate: moment()
    }, function(start, end) {
        setDateRange(start, end);
    });

    // Load initial data
    applyPreset('thismonth');
});

function exportReport(type) {
    let fdate = $('#search_fromdate').val();
    let tdate = $('#search_todate').val();
    if (type === 'csv') {
        window.location.href = `export_expenses.php?type=csv&fromdate=${fdate}&todate=${tdate}`;
    } else {
        window.open(`export_expenses.php?type=print&fromdate=${fdate}&todate=${tdate}`, '_blank');
    }
}
</script>

<style>
.preset-btn:hover { border-color: var(--primary) !important; color: var(--primary) !important; background: var(--primary-light) !important; }
.preset-btn.active { border-color: var(--primary) !important; background: var(--primary-light) !important; color: var(--primary) !important; }
</style>

<?php include 'footer.php'; ?>

<?php include 'header.php'; ?>

<!-- Google Charts for Dashboard -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 26px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Welcome Back, <?= htmlspecialchars(get_session_data('user_name')) ?>! 👋</h1>
    <p style="color: var(--text-muted); font-size: 15px;">Here's your comprehensive business overview at <strong><?= htmlspecialchars($salon_name) ?></strong> for today.</p>
</div>

<!-- TOP METRICS GRID -->
<div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <!-- Revenue Card -->
    <div class="metric-card" style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%); color: white; border-radius: 20px; padding: 24px; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.25); position: relative; overflow: hidden; transition: transform 0.3s ease;">
        <div style="position: absolute; right: -15%; top: -15%; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 50%; blur: 20px;"></div>
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; position: relative;">
            <div>
                <p style="margin: 0; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; opacity: 0.8;">Net Revenue</p>
                <h2 id="metric_revenue" style="margin: 4px 0 0 0; font-size: 36px; font-weight: 800; letter-spacing: -1px;">₹0</h2>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-trend-up" style="font-size: 24px;"></i>
            </div>
        </div>
        <div style="font-size: 13px; opacity: 0.9;">
            Today's total collections
        </div>
    </div>

    <!-- Expenses Card -->
    <div class="metric-card" style="background: white; border-radius: 20px; padding: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: transform 0.3s ease;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
                <p style="margin: 0; font-size: 13px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Money Outflow</p>
                <h2 id="metric_expenses" style="margin: 4px 0 0 0; font-size: 32px; font-weight: 800; color: var(--danger); letter-spacing: -1px;">₹0</h2>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-wallet" style="font-size: 24px;"></i>
            </div>
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Today's total expenses logged 
        </div>
    </div>

    <!-- Customers Today -->
    <div class="metric-card" style="background: white; border-radius: 20px; padding: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: transform 0.3s ease;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
                <p style="margin: 0; font-size: 13px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Total Customers</p>
                <h2 id="metric_active_jobs" style="margin: 4px 0 0 0; font-size: 32px; font-weight: 800; color: var(--text-main); letter-spacing: -1px;">0</h2>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-users" style="font-size: 24px;"></i>
            </div>
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Unique customers served today
        </div>
    </div>
    
    <!-- Packages/Memberships Revenue -->
    <div class="metric-card" style="background: white; border-radius: 20px; padding: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: transform 0.3s ease;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
                <p style="margin: 0; font-size: 13px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Subscriptions</p>
                <h2 id="metric_pkg_revenue" style="margin: 4px 0 0 0; font-size: 32px; font-weight: 800; color: var(--text-main); letter-spacing: -1px;">₹0</h2>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-crown" style="font-size: 24px;"></i>
            </div>
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Sub/Packages revenue today
        </div>
    </div>
</div>

<!-- MIDDLE CHARTS GRID -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 30px;">
    
    <!-- Monthly Trend Chart -->
    <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 24px; min-height: 400px; grid-column: span 2;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-main);">Daily Revenue & Expenses</h3>
            <select id="monthSelect_recap" class="form-control" style="width: 150px; padding: 6px 10px; font-size: 13px; background: #f8fafc; border-radius: 8px;">
                <?php
                for ($i = 0; $i <= 11; $i++) {
                    $month = date('M Y', strtotime("-$i months"));
                    $months = date('Y-m', strtotime("-$i months"));
                    echo "<option value='$months'>$month</option>";
                }
                ?>
            </select>
        </div>
        <div id="monthly_recap" style="width: 100%; height: 350px;"></div>
    </div>

    <!-- Staff Sales -->
    <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); min-height: 350px; overflow: hidden; display: flex; flex-direction: column;">
        <div style="padding: 24px 24px 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Staff Sales</h3>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button id="btn_wa_staff" style="padding: 6px 10px; border: none; border-radius: 8px; background: #25D366; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" title="Share on WhatsApp">
                    <i class="ph-bold ph-whatsapp-logo" style="font-size: 16px;"></i>
                </button>
                <select id="monthSelect_staff" class="form-control" style="width: 130px; padding: 6px 10px; font-size: 13px; background: #f8fafc; border-radius: 8px;">
                    <?php
                    for ($i = 0; $i <= 5; $i++) {
                        $month = date('M Y', strtotime("-$i months"));
                        $months = date('Y-m', strtotime("-$i months"));
                        echo "<option value='$months'>$month</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="table-responsive" style="flex-grow: 1; overflow-y: auto; max-height: 300px;">
            <table class="table-modern" style="width: 100%; margin: 0;">
                <thead style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th>Staff Name</th>
                        <th style="text-align:right;">Services</th>
                        <th style="text-align:right;">Products</th>
                        <th style="text-align:right; color:var(--primary);">Grand Total</th>
                    </tr>
                </thead>
                <tbody id="tbl_staff_sales">
                    <tr><td colspan="4" style="text-align:center; padding: 30px; color:var(--text-muted);"><i class="ph ph-spinner ph-spin"></i> Loading data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Services -->
    <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 24px; min-height: 350px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Top Selling Services</h3>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button id="btn_wa_service" style="padding: 6px 10px; border: none; border-radius: 8px; background: #25D366; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" title="Share on WhatsApp">
                    <i class="ph-bold ph-whatsapp-logo" style="font-size: 16px;"></i>
                </button>
                <select id="monthSelect_service" class="form-control" style="width: 130px; padding: 6px 10px; font-size: 13px; background: #f8fafc; border-radius: 8px;">
                    <?php
                    for ($i = 0; $i <= 5; $i++) {
                        $month = date('M Y', strtotime("-$i months"));
                        $months = date('Y-m', strtotime("-$i months"));
                        echo "<option value='$months'>$month</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div id="top_services" style="width: 100%; height: 280px;">
            <div style="display:flex; justify-content:center; align-items:center; height:100%; color:var(--text-muted);">Loading...</div>
        </div>
    </div>
</div>

<!-- BOTTOM DATA TABLES -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 40px;" class="bottom-grid">

    <div style="display: grid; gap: 24px;">
        <!-- Recent Invoices Table -->
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Recent Checkouts Today</h3>
                <a href="invoices.php" style="font-size: 13px; color: var(--primary); font-weight: 600; text-decoration: none;">View All &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table-modern" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Inv #</th>
                            <th>Customer Name</th>
                            <th>Payment</th>
                            <th>Time</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="tbl_recent_invoices">
                        <tr><td colspan="5" style="text-align:center; padding: 30px; color:var(--text-muted);"><i class="ph ph-spinner ph-spin"></i> Loading data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Expenses Table -->
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Recent Expenses Logged</h3>
                <a href="expenses.php" style="font-size: 13px; color: var(--primary); font-weight: 600; text-decoration: none;">View All &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table-modern" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Payment</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="tbl_recent_expenses">
                        <tr><td colspan="3" style="text-align:center; padding: 30px; color:var(--text-muted);"><i class="ph ph-spinner ph-spin"></i> Loading data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Vendor Payments -->
    <div style="display: grid; gap: 24px;">
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; height: fit-content;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="ph-fill ph-warning-circle" style="color: var(--warning); font-size: 20px;"></i>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Pending Payments</h3>
                </div>
            </div>
            <div id="pending_vendors_list" style="padding: 12px 24px;">
                <div style="text-align:center; padding: 30px; color:var(--text-muted);"><i class="ph ph-spinner ph-spin"></i> Loading...</div>
            </div>
            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); text-align: center; background:#f8fafc;">
                <a href="vendor_ledger.php" style="font-size: 13px; color: var(--primary); font-weight: 600; text-decoration: none;">Settle Accounts in Vendor Ledger</a>
            </div>
        </div>

        <!-- WhatsApp Daily Report -->
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px;">
                <i class="ph-fill ph-whatsapp-logo" style="color: #25D366; font-size: 20px;"></i>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">WhatsApp Reports</h3>
            </div>
            <div style="padding: 24px;">
                <div style="margin-bottom: 24px; border-bottom: 1px dashed var(--border-color); padding-bottom: 20px;">
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Daily Report Date</label>
                        <input type="date" id="whatsapp_report_date" class="form-control" value="<?= date('Y-m-d') ?>" style="padding: 10px; border-radius: 10px; border: 1px solid var(--border-color); width: 100%;">
                    </div>
                    <button id="btn_generate_whatsapp" class="btn-primary-modern" style="width: 100%; padding: 12px; border: none; border-radius: 12px; background: #25D366; color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s;">
                        <i class="ph-bold ph-paper-plane-tilt"></i> Send Daily Report
                    </button>
                    <div id="owner_numbers_list" style="margin-top: 8px; font-size: 11px; color: var(--text-muted); text-align: center;"></div>
                </div>
                
                <div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Monthly Report Month</label>
                        <input type="month" id="whatsapp_report_month" class="form-control" value="<?= date('Y-m') ?>" style="padding: 10px; border-radius: 10px; border: 1px solid var(--border-color); width: 100%;">
                    </div>
                    <button id="btn_generate_whatsapp_monthly" class="btn-primary-modern" style="width: 100%; padding: 12px; border: none; border-radius: 12px; background: #25D366; color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s;">
                        <i class="ph-bold ph-paper-plane-tilt"></i> Send Monthly Report
                    </button>
                    <div id="owner_numbers_list_monthly" style="margin-top: 8px; font-size: 11px; color: var(--text-muted); text-align: center;"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
/* Modern Data Table Scoped Core Styles */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern th { background: #f8fafc; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 24px; border-bottom: 1px solid var(--border-color); text-align: left; }
.table-modern td { padding: 16px 24px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border-color); vertical-align: middle; }
.table-modern tbody tr:last-child td { border-bottom: none; }
.table-modern tbody tr:hover td { background: #f8fafc; }
.metric-card:hover { transform: translateY(-4px); }

.pending-vendor-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px dashed var(--border-color);
}
.pending-vendor-item:last-child {
    border-bottom: none;
}

@media (max-width: 1000px) {
    .bottom-grid { grid-template-columns: 1fr; }
    .card-modern { grid-column: span 1 !important; }
}
</style>

<script>
$(document).ready(function() {
    
    // 1. Fetch KPI Dashboard Metrics
    function fetchDashboardMetrics() {
        $.ajax({
            url: "ajax/dashboard_ajax.php",
            type: "POST",
            dataType: "json",
            data: { method: "dashboard_metrics" },
            success: function(res) {
                if(res.error === 0) {
                    $('#metric_revenue').text('₹' + parseFloat(res.revenue).toFixed(2));
                    $('#metric_expenses').text('₹' + parseFloat(res.expenses).toFixed(2));
                    $('#metric_active_jobs').text(res.active_jobs);
                    $('#metric_pkg_revenue').text('₹' + parseFloat(res.mem_pkg_revenue).toFixed(2));
                }
            }
        });
    }

    // 2. Fetch Recent Invoices
    function fetchRecentInvoices() {
        $.ajax({
            url: "ajax/dashboard_ajax.php",
            type: "POST",
            dataType: "json",
            data: { method: "dashboard_recent_invoices" },
            success: function(res) {
                if(res.error === 0) {
                    var html = '';
                    if(res.data.length > 0) {
                        res.data.forEach(function(i) {
                            var modeBadge = i.payment_mode === 'cash' ? 'background:#dcfce7;color:#16a34a;' : 'background:#e0e7ff;color:#4f46e5;';
                            var modeName = i.payment_mode ? i.payment_mode.toUpperCase() : 'UNKNOWN';
                            html += '<tr>' +
                                '<td><span style="font-weight:600; color:var(--text-muted);">#'+i.invoice_id+'</span></td>' +
                                '<td style="font-weight:600;">'+i.cust_name+'</td>' +
                                '<td><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:20px; '+modeBadge+'">'+modeName+'</span></td>' +
                                '<td><span style="color:var(--text-muted); font-size:13px;">'+i.time+'</span></td>' +
                                '<td style="text-align:right; font-weight:700; color:var(--text-main);">₹'+parseFloat(i.grand_total).toFixed(2)+'</td>' +
                            '</tr>';
                        });
                    } else {
                        html = '<tr><td colspan="5" style="text-align:center; padding: 30px; color:var(--text-muted);">No invoices checked out today yet.</td></tr>';
                    }
                    $('#tbl_recent_invoices').html(html);
                }
            }
        });
    }

    // 3. Fetch Recent Expenses
    function fetchRecentExpenses() {
        $.ajax({
            url: "ajax/dashboard_ajax.php",
            type: "POST",
            dataType: "json",
            data: { method: "dashboard_recent_expenses" },
            success: function(res) {
                if(res.error === 0) {
                    var html = '';
                    if(res.data.length > 0) {
                        res.data.forEach(function(i) {
                            var modeBadge = 'background:#f1f5f9;color:#64748b;';
                            var modeName = i.payment_mode ? i.payment_mode.toUpperCase() : 'UNKNOWN';
                            html += '<tr>' +
                                '<td style="font-weight:600;">'+i.exp_name+'</td>' +
                                '<td><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:20px; '+modeBadge+'">'+modeName+'</span></td>' +
                                '<td style="text-align:right; font-weight:700; color:var(--danger);">-₹'+parseFloat(i.exp_total).toFixed(2)+'</td>' +
                            '</tr>';
                        });
                    } else {
                        html = '<tr><td colspan="3" style="text-align:center; padding: 30px; color:var(--text-muted);">No expenses logged today.</td></tr>';
                    }
                    $('#tbl_recent_expenses').html(html);
                }
            }
        });
    }

    // 4. Fetch Pending Vendors
    function fetchPendingVendors() {
        $.ajax({
            url: "ajax/dashboard_ajax.php",
            type: "POST",
            dataType: "json",
            data: { method: "dashboard_pending_vendors" },
            success: function(res) {
                if(res.error === 0) {
                    var html = '';
                    if(res.data.length > 0) {
                        res.data.forEach(function(v) {
                            html += '<div class="pending-vendor-item">' +
                                '<div style="display:flex; align-items:center; gap:12px;">' +
                                    '<div style="width:36px; height:36px; border-radius:8px; background:#fff7ed; color:#ea580c; display:flex; align-items:center; justify-content:center;"><i class="ph-fill ph-buildings"></i></div>' +
                                    '<div><div style="font-weight:600; color:var(--text-main); font-size:14px;">'+v.vendor_name+'</div><div style="font-size:12px; color:var(--text-muted);">Unsettled Balance</div></div>' +
                                '</div>' +
                                '<div style="font-weight:700; color:var(--warning); font-size:15px;">₹'+parseFloat(v.pending_amount).toFixed(2)+'</div>' +
                            '</div>';
                        });
                    } else {
                        html = '<div style="text-align:center; padding: 30px; color:var(--text-muted);"><i class="ph-fill ph-check-circle" style="font-size:32px; color:var(--success); margin-bottom:8px;"></i><br>All vendor accounts are settled!</div>';
                    }
                    $('#pending_vendors_list').html(html);
                }
            }
        });
    }

    // Call all initializers
    fetchDashboardMetrics();
    fetchRecentInvoices();
    fetchRecentExpenses();
    fetchPendingVendors();

    // Init Google Charts
    google.charts.load('current', {packages: ['corechart', 'bar']});
    
    // 5. Fetch Staff Sales Table
    var selectedStaffMonth = $('#monthSelect_staff').val();
    
    function fetchStaffSales() {
        $('#tbl_staff_sales').html('<tr><td colspan="4" style="text-align:center; padding: 30px; color:var(--text-muted);"><i class="ph ph-spinner ph-spin"></i> Loading data...</td></tr>');
        $.ajax({
            url: "ajax/dashboard_ajax.php",
            type: "POST",
            dataType: "json",
            data: { method: "dashboard_staff_sales", selectedMonth: selectedStaffMonth },
            success: function(res) {
                if(res.error === 0) {
                    window.currentStaffSalesData = res.data;
                    var html = '';
                    if(res.data.length > 0) {
                        res.data.forEach(function(s) {
                            html += '<tr>' +
                                '<td style="font-weight:600; font-size:13px;">'+s.staff_name+'</td>' +
                                '<td style="text-align:right; font-size:13px;">₹'+parseFloat(s.service).toFixed(2)+'</td>' +
                                '<td style="text-align:right; font-size:13px;">₹'+parseFloat(s.product).toFixed(2)+'</td>' +
                                '<td style="text-align:right; font-weight:700; color:var(--primary); font-size:14px;">₹'+parseFloat(s.grand).toFixed(2)+'</td>' +
                            '</tr>';
                        });
                    } else {
                        html = '<tr><td colspan="4" style="text-align:center; padding: 20px; color:var(--text-muted);">No staff sales recorded for ' + selectedStaffMonth + '.</td></tr>';
                    }
                    $('#tbl_staff_sales').html(html);
                }
            }
        });
    }

    fetchStaffSales();

    $('#monthSelect_staff').change(function() {
        selectedStaffMonth = $(this).val();
        fetchStaffSales();
    });

    $('#btn_wa_staff').click(function() {
        if (!window.currentStaffSalesData || window.currentStaffSalesData.length === 0) {
            alert('No staff sales data available to send.');
            return;
        }
        var msg = "*Staff Sales Report (" + $('#monthSelect_staff option:selected').text() + ")*\n\n";
        window.currentStaffSalesData.forEach(function(s) {
            msg += "*" + s.staff_name + "*\n";
            msg += "Service: ₹" + parseFloat(s.service).toFixed(2) + "\n";
            msg += "Product: ₹" + parseFloat(s.product).toFixed(2) + "\n";
            msg += "Total: *₹" + parseFloat(s.grand).toFixed(2) + "*\n\n";
        });
        
        var encodedMsg = encodeURIComponent(msg);
        var waUrl = "https://api.whatsapp.com/send?text=" + encodedMsg;
        $.post('ajax/whatsapp_log_ajax.php', { module: 'Dashboard Staff Report', target_url: waUrl });
        window.open(waUrl, '_blank');
    });

    var selectedSrvMonth = $('#monthSelect_service').val();

    $('#monthSelect_service').change(function() {
        selectedSrvMonth = $(this).val();
        drawSrvChart();
    });

    var selectedRecapMonth = $('#monthSelect_recap').val();
    $('#monthSelect_recap').change(function() {
        selectedRecapMonth = $(this).val();
        drawMonthlyRecap();
    });

    google.charts.setOnLoadCallback(function() {
        drawSrvChart();
        drawMonthlyRecap();
    });

    function drawSrvChart() {
        $.ajax({
            url: "ajax/chart_data.php",
            type: "POST",
            dataType: "json",
            data: { method: "top_services", selectedMonth: selectedSrvMonth },
            success: function(array_data) {
                window.currentTopServicesData = array_data;
                if(!array_data || array_data.length <= 1) {
                    $('#top_services').html('<div style="display:flex; justify-content:center; align-items:center; height:100%; color:var(--text-muted);">No data available for ' + selectedSrvMonth + '</div>');
                    return;
                }
                var data = google.visualization.arrayToDataTable(array_data);
                var options = {
                    pieHole: 0.45,
                    colors: ['#0ea5e9', '#ec4899', '#f97316', '#84cc16', '#6366f1', '#14b8a6'],
                    chartArea: { left: "5%", top: "10%", width: "90%", height: "80%" },
                    legend: { position: 'right', textStyle: { color: '#475569', fontSize: 13, fontName: 'Inter' } }
                };
                var chart = new google.visualization.PieChart(document.getElementById('top_services'));
                chart.draw(data, options);
            }
        });
    }

    $('#btn_wa_service').click(function() {
        if (!window.currentTopServicesData || window.currentTopServicesData.length <= 1) {
            alert('No services data available to send.');
            return;
        }
        var msg = "*Top Selling Services (" + $('#monthSelect_service option:selected').text() + ")*\n\n";
        for(var i = 1; i < window.currentTopServicesData.length; i++) {
            var item = window.currentTopServicesData[i];
            msg += item[0] + ": *₹" + parseFloat(item[1]).toFixed(2) + "*\n";
        }
        
        var encodedMsg = encodeURIComponent(msg);
        var waUrl = "https://api.whatsapp.com/send?text=" + encodedMsg;
        $.post('ajax/whatsapp_log_ajax.php', { module: 'Dashboard Service Report', target_url: waUrl });
        window.open(waUrl, '_blank');
    });

    function drawMonthlyRecap() {
        $.ajax({
            url: "ajax/chart_data.php",
            type: "POST",
            dataType: "json",
            data: { method: "monthly_recap", selectedMonth: selectedRecapMonth },
            success: function(array_data) {
                if(!array_data || array_data.length <= 1) {
                    $('#monthly_recap').html('<div style="display:flex; justify-content:center; align-items:center; height:100%; color:var(--text-muted);">No historical analytical data found for ' + selectedRecapMonth + '.</div>');
                    return;
                }
                var data = google.visualization.arrayToDataTable(array_data);
                var options = {
                    colors: ['#4f46e5', '#10b981', '#f59e0b'],
                    vAxis: { format: 'currency' },
                    legend: { position: 'top' },
                    chartArea: { width: '85%', height: '75%' }
                };
                var chart = new google.charts.Bar(document.getElementById('monthly_recap'));
                chart.draw(data, google.charts.Bar.convertOptions(options));
            }
        });
    }

    // 6. WhatsApp Report Generation
    $('#btn_generate_whatsapp').click(function() {
        var date = $('#whatsapp_report_date').val();
        var btn = $(this);
        var originalHtml = btn.html();
        
        btn.prop('disabled', true).html('<i class="ph ph-spinner ph-spin"></i> Generating...');
        
        $.ajax({
            url: "ajax/dashboard_ajax.php",
            type: "POST",
            dataType: "json",
            data: { method: "dashboard_whatsapp_report", date: date },
            success: function(res) {
                btn.prop('disabled', false).html(originalHtml);
                
                if(res.error === 0) {
                    var msg = "*"+res.date+"*\n\n";
                    msg += "Total Client: " + res.total_client + "\n";
                    msg += "Total Exp: " + Math.round(res.total_exp).toLocaleString('en-IN') + "\n\n";
                    
                    msg += "Cash Sale: " + Math.round(res.cash_sale).toLocaleString('en-IN') + "\n";
                    msg += "Bank Sale: " + Math.round(res.bank_sale).toLocaleString('en-IN') + "\n";
                    msg += "---------------------------------\n";
                    msg += "Total sale: " + Math.round(res.total_sale).toLocaleString('en-IN') + "\n\n";
                    
                    var extra = false;
                    var extraMsg = "";
                    if(res.service_sale > 0) { extraMsg += "Service Sale: " + Math.round(res.service_sale).toLocaleString('en-IN') + "\n"; extra = true; }
                    if(res.product_sale > 0) { extraMsg += "Product Sale: " + Math.round(res.product_sale).toLocaleString('en-IN') + "\n"; extra = true; }
                    if(res.membership_sale > 0) { extraMsg += "Membership Sale: " + Math.round(res.membership_sale).toLocaleString('en-IN') + "\n"; extra = true; }
                    if(res.package_sale > 0) { extraMsg += "Package Sale: " + Math.round(res.package_sale).toLocaleString('en-IN') + "\n"; extra = true; }
                    if(res.redemption > 0) { extraMsg += "Redumption: " + Math.round(res.redemption).toLocaleString('en-IN') + "\n"; extra = true; }
                    
                    if(extra) {
                        msg += extraMsg;
                        msg += "---------------------------------\n";
                        var grandTotalVal = parseFloat(res.total_sale) + parseFloat(res.redemption);
                        msg += "Grand Total: " + Math.round(grandTotalVal).toLocaleString('en-IN') + "\n";
                        msg += "----------------------------------\n\n";
                    }
                    
                    msg += "Total expenses MTD: " + Math.round(res.exp_mtd).toLocaleString('en-IN') + "\n";
                    msg += "Total sale MTD: *" + Math.round(res.sale_mtd).toLocaleString('en-IN') + "*\n";
                    
                    var encodedMsg = encodeURIComponent(msg);
                    var waUrl = "https://api.whatsapp.com/send?text=" + encodedMsg;
                    
                    if(res.owners && res.owners.length > 0) {
                        waUrl = "https://api.whatsapp.com/send?phone=91" + res.owners[0] + "&text=" + encodedMsg;
                        if(res.owners.length > 1) {
                            $('#owner_numbers_list').html('Defaulting to ' + res.owners[0] + '. Others: ' + res.owners.slice(1).join(', '));
                        } else {
                            $('#owner_numbers_list').html('Sending to owner: ' + res.owners[0]);
                        }
                    } else {
                        $('#owner_numbers_list').html('No owner numbers found. Please choose manually.');
                    }
                    
                    $.post('ajax/whatsapp_log_ajax.php', { module: 'Dashboard Daily WhatsApp Report', target_url: waUrl });
                    window.open(waUrl, '_blank');
                } else {
                    alert('Error: ' + res.msg);
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalHtml);
                alert('Connection error. Please try again.');
            }
        });
    });

    $('#btn_generate_whatsapp_monthly').click(function() {
        var month = $('#whatsapp_report_month').val();
        var btn = $(this);
        var originalHtml = btn.html();
        
        btn.prop('disabled', true).html('<i class="ph ph-spinner ph-spin"></i> Generating...');
        
        $.ajax({
            url: "ajax/dashboard_ajax.php",
            type: "POST",
            dataType: "json",
            data: { method: "dashboard_whatsapp_monthly_report", month: month },
            success: function(res) {
                btn.prop('disabled', false).html(originalHtml);
                
                if(res.error === 0) {
                    var msg = "*Monthly Report ("+res.date+")*\n\n";
                    msg += "Total Client: " + res.total_client + "\n";
                    msg += "Total Exp: " + Math.round(res.total_exp).toLocaleString('en-IN') + "\n\n";
                    
                    msg += "Cash Sale: " + Math.round(res.cash_sale).toLocaleString('en-IN') + "\n";
                    msg += "Bank Sale: " + Math.round(res.bank_sale).toLocaleString('en-IN') + "\n";
                    msg += "---------------------------------\n";
                    msg += "Total sale: " + Math.round(res.total_sale).toLocaleString('en-IN') + "\n\n";
                    
                    var extra = false;
                    var extraMsg = "";
                    if(res.service_sale > 0) { extraMsg += "Service Sale: " + Math.round(res.service_sale).toLocaleString('en-IN') + "\n"; extra = true; }
                    if(res.product_sale > 0) { extraMsg += "Product Sale: " + Math.round(res.product_sale).toLocaleString('en-IN') + "\n"; extra = true; }
                    if(res.membership_sale > 0) { extraMsg += "Membership Sale: " + Math.round(res.membership_sale).toLocaleString('en-IN') + "\n"; extra = true; }
                    if(res.package_sale > 0) { extraMsg += "Package Sale: " + Math.round(res.package_sale).toLocaleString('en-IN') + "\n"; extra = true; }
                    if(res.redemption > 0) { extraMsg += "Redumption: " + Math.round(res.redemption).toLocaleString('en-IN') + "\n"; extra = true; }
                    
                    if(extra) {
                        msg += extraMsg;
                        msg += "---------------------------------\n";
                        var grandTotalVal = parseFloat(res.total_sale) + parseFloat(res.redemption);
                        msg += "Grand Total: " + Math.round(grandTotalVal).toLocaleString('en-IN') + "\n";
                        msg += "----------------------------------\n\n";
                    }
                    
                    var encodedMsg = encodeURIComponent(msg);
                    var waUrl = "https://api.whatsapp.com/send?text=" + encodedMsg;
                    
                    if(res.owners && res.owners.length > 0) {
                        waUrl = "https://api.whatsapp.com/send?phone=91" + res.owners[0] + "&text=" + encodedMsg;
                        if(res.owners.length > 1) {
                            $('#owner_numbers_list_monthly').html('Defaulting to ' + res.owners[0] + '. Others: ' + res.owners.slice(1).join(', '));
                        } else {
                            $('#owner_numbers_list_monthly').html('Sending to owner: ' + res.owners[0]);
                        }
                    } else {
                        $('#owner_numbers_list_monthly').html('No owner numbers found. Please choose manually.');
                    }
                    
                    $.post('ajax/whatsapp_log_ajax.php', { module: 'Dashboard Monthly WhatsApp Report', target_url: waUrl });
                    window.open(waUrl, '_blank');
                } else {
                    alert('Error: ' + res.msg);
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalHtml);
                alert('Connection error. Please try again.');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>

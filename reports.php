<?php
include "header.php";
?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"/>

<!-- Page Title -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Reports & Analytics</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Comprehensive insights into salon performance</p>
</div>

<!-- Filter Section -->
<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; justify-content: space-between;">
        
        <!-- Tabs -->
        <ul class="nav nav-pills custom-tabs m-0" id="report-tabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-sales" type="button" role="tab">Sales</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pnl" type="button" role="tab" style="color:#0284c7; font-weight:700;"><i class="ph ph-chart-line-up" style="margin-right:4px;"></i>P&L</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ledger" type="button" role="tab">Ledger</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-services" type="button" role="tab">Services</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-staff" type="button" role="tab">Staff</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-expenses" type="button" role="tab">Expenses</button></li>
        </ul>

        <!-- Date Filter -->
        <div style="min-width: 250px;">
            <div style="position: relative; cursor:pointer;" id="reportdaterange">
                <i class="ph ph-calendar-blank" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <div class="form-control" style="padding-left: 44px; background: #f8fafc; height: 44px; display: flex; align-items: center justify-content: space-between;">
                    <span id="datelabel" style="font-size:14px; font-weight:600; color:var(--text-main);">Today</span>
                    <i class="ph ph-caret-down text-muted" style="margin-left:auto;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Content Wrapper -->
<div class="tab-content" id="report-tabContent" style="margin-bottom: 50px;">
    
    <!-- Sales Summary Tab -->
    <div class="tab-pane fade show active" id="tab-sales" role="tabpanel">
        <div id="sales-metrics" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:24px; margin-bottom:24px;">
            <!-- Tiles populated by JS -->
            <div style="background:linear-gradient(135deg,#f8fafc,#dcfce7);padding:24px;border-radius:20px;border:1px solid #bbf7d0;">
                <div style="color:#16a34a;font-size:13px;font-weight:600;text-transform:uppercase;margin-bottom:8px;">Total Revenue</div>
                <div style="font-size:32px;font-weight:700;">₹<span id="total_revenue" class="placeholder-glow"><span class="placeholder col-6"></span></span></div>
            </div>
            <div style="background:linear-gradient(135deg,#f8fafc,#e0e7ff);padding:24px;border-radius:20px;border:1px solid #c7d2fe;">
                <div style="color:#4f46e5;font-size:13px;font-weight:600;text-transform:uppercase;margin-bottom:8px;">Total Customers</div>
                <div style="font-size:32px;font-weight:700;"><span id="total_customers" class="placeholder-glow"><span class="placeholder col-4"></span></span></div>
            </div>
            <div style="background:linear-gradient(135deg,#f8fafc,#fef9c3);padding:24px;border-radius:20px;border:1px solid #fde68a;">
                <div style="color:#ca8a04;font-size:13px;font-weight:600;text-transform:uppercase;margin-bottom:8px;">Cash Collections</div>
                <div style="font-size:32px;font-weight:700;">₹<span id="cash_collections" class="placeholder-glow"><span class="placeholder col-6"></span></span></div>
            </div>
            <div style="background:linear-gradient(135deg,#f8fafc,#fce7f3);padding:24px;border-radius:20px;border:1px solid #fbcfe8;">
                <div style="color:#be185d;font-size:13px;font-weight:600;text-transform:uppercase;margin-bottom:8px;">Digital / Cards</div>
                <div style="font-size:32px;font-weight:700;">₹<span id="digital_cards" class="placeholder-glow"><span class="placeholder col-6"></span></span></div>
            </div>
        </div>
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text-main);">Daily Sales Breakdown</h3>
            </div>
            <div style="padding: 24px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table" id="salesTable" width="100%">
                        <thead>
                            <tr>
                                <th>Inv #</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Amount</th>
                                <th>Mode</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- P&L Summary Tab -->
    <div class="tab-pane fade" id="tab-pnl" role="tabpanel">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px; margin-bottom:24px;">
            <!-- Income Panel -->
            <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid #bbf7d0; box-shadow: var(--shadow-sm); overflow: hidden;">
                <div style="padding: 20px; border-bottom: 1px solid #bbf7d0; background: #f0fdf4;">
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #16a34a;"><i class="ph ph-trend-up" style="margin-right:6px;"></i>Income Breakdown</h3>
                </div>
                <div style="padding: 24px;">
                    <div id="pnl_income_body" style="min-height:150px;">
                        <div class="text-center text-muted" style="padding:20px;">Loading income...</div>
                    </div>
                    <div style="border-top:2px dashed #e2e8f0; margin-top:16px; padding-top:16px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-weight:700; color:var(--text-main);">Total Income</span>
                        <span style="font-weight:800; font-size:20px; color:#16a34a;" id="pnl_total_income">₹0.00</span>
                    </div>
                </div>
            </div>

            <!-- Expense Panel -->
            <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid #fecaca; box-shadow: var(--shadow-sm); overflow: hidden;">
                <div style="padding: 20px; border-bottom: 1px solid #fecaca; background: #fef2f2;">
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #dc2626;"><i class="ph ph-trend-down" style="margin-right:6px;"></i>Expense Breakdown</h3>
                </div>
                <div style="padding: 24px;">
                    <div id="pnl_expense_body" style="min-height:150px;">
                        <div class="text-center text-muted" style="padding:20px;">Loading expenses...</div>
                    </div>
                    <div style="border-top:2px dashed #e2e8f0; margin-top:16px; padding-top:16px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-weight:700; color:var(--text-main);">Total Expenses</span>
                        <span style="font-weight:800; font-size:20px; color:#dc2626;" id="pnl_total_expense">₹0.00</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Net Profit Wide -->
        <div class="card-modern" id="pnl_net_profit_card" style="background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; padding: 32px; text-align: center; color: white;">
            <div style="color: #94a3b8; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Net Profit / Loss</div>
            <div style="font-size: 48px; font-weight: 800;" id="pnl_net_profit"><i class="ph ph-spinner ph-spin"></i></div>
            <div id="pnl_profit_badge" style="display:inline-block; margin-top: 12px; padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; background: rgba(255,255,255,0.1);"></div>
        </div>
    </div>

    <!-- Detailed Ledger Tab -->
    <div class="tab-pane fade" id="tab-ledger" role="tabpanel">
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text-main);">Invoice Ledger</h3>
            </div>
            <div style="padding: 24px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table" id="ledgerTable" width="100%">
                        <thead>
                            <tr>
                                <th>Inv #</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Staff</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Tab -->
    <div class="tab-pane fade" id="tab-services" role="tabpanel">
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text-main);">Service Performance</h3>
            </div>
            <div style="padding: 24px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table" id="servicesTable" width="100%">
                        <thead>
                            <tr>
                                <th>Service Name</th>
                                <th>Category</th>
                                <th>Times Sold</th>
                                <th>Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Performance Tab -->
    <div class="tab-pane fade" id="tab-staff" role="tabpanel">
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text-main);">Staff Net Revenue</h3>
            </div>
            <div style="padding: 24px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table" id="staffTable" width="100%">
                        <thead>
                            <tr>
                                <th>Stylist</th>
                                <th>Clients</th>
                                <th>Services</th>
                                <th>Service (Redemption)</th>
                                <th>Package Sold</th>
                                <th>Membership Sold</th>
                                <th>Products Sold</th>
                                <th>Total Generated</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses Tab -->
    <div class="tab-pane fade" id="tab-expenses" role="tabpanel">
        <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text-main);">Expense Breakdown</h3>
            </div>
            <div style="padding: 24px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table" id="expensesTable" width="100%">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Method</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
/* Scoped tab styles to look beautiful */
.custom-tabs {
    border-bottom: 2px solid #e2e8f0;
    gap: 16px;
    padding-bottom: 8px;
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
}
.custom-tabs .nav-link {
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.2s;
    cursor: pointer;
}
.custom-tabs .nav-link:hover {
    color: var(--text-main);
    background: #f1f5f9;
}
.custom-tabs .nav-link.active {
    color: var(--primary);
    background: var(--primary-light);
}

/* Fallback Tab CSS since Bootstrap CSS is missing */
.tab-content > .tab-pane {
    display: none;
}
.tab-content > .active {
    display: block;
}
.fade {
    transition: opacity .15s linear;
}
@media (prefers-reduced-motion: reduce) {
    .fade { transition: none; }
}
.fade:not(.show) {
    opacity: 0;
}

.custom-table th {
    text-transform: uppercase;
    font-size: 11px;
    color: var(--text-muted);
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 12px;
}
.custom-table td {
    padding: 16px 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
</style>

<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script src="assets/js/reports.js?v=<?= time() ?>"></script>

<?php include "footer.php"; ?>

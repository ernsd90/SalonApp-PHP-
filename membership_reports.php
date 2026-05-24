<?php include 'header.php'; ?>

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;margin-bottom:4px;">Membership & Package Reports</h1>
    <p style="color:var(--text-muted);font-size:14px;">Financial analytics, liability tracking, and expiry alerts for memberships and packages.</p>
</div>

<!-- Date Filter -->
<div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:20px 24px;margin-bottom:24px;display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;">
    <div class="form-group" style="margin:0;flex:1;min-width:160px;">
        <label>From Date</label>
        <input type="date" id="rpt_from" class="form-control" value="<?= date('Y-m-01') ?>">
    </div>
    <div class="form-group" style="margin:0;flex:1;min-width:160px;">
        <label>To Date</label>
        <input type="date" id="rpt_to" class="form-control" value="<?= date('Y-m-d') ?>">
    </div>
    <button class="btn-primary" id="btn_load_reports" style="width:auto;padding:12px 24px;margin:0;display:flex;align-items:center;gap:8px;">
        <i class="ph ph-funnel"></i> Load Reports
    </button>
    <button class="btn-secondary" id="btn_export_csv" style="width:auto;padding:12px 20px;margin:0;display:flex;align-items:center;gap:8px;">
        <i class="ph ph-file-csv"></i> Export CSV
    </button>
</div>

<!-- KPI Widgets -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;margin-bottom:28px;" id="kpi_grid">
    <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border-radius:16px;padding:20px 22px;">
        <div style="font-size:11px;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Total Sold</div>
        <div id="k_total_sold" style="font-size:26px;font-weight:800;margin-top:6px;">—</div>
        <div style="font-size:11px;opacity:.7;margin-top:4px;">Memberships in period</div>
    </div>
    <div style="background:linear-gradient(135deg,#059669,#10b981);color:white;border-radius:16px;padding:20px 22px;">
        <div style="font-size:11px;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Active</div>
        <div id="k_active" style="font-size:26px;font-weight:800;margin-top:6px;">—</div>
        <div style="font-size:11px;opacity:.7;margin-top:4px;">Active memberships</div>
    </div>
    <div style="background:linear-gradient(135deg,#d97706,#f59e0b);color:white;border-radius:16px;padding:20px 22px;">
        <div style="font-size:11px;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Revenue</div>
        <div id="k_revenue" style="font-size:26px;font-weight:800;margin-top:6px;">—</div>
        <div style="font-size:11px;opacity:.7;margin-top:4px;">Membership payments</div>
    </div>
    <div style="background:linear-gradient(135deg,#dc2626,#ef4444);color:white;border-radius:16px;padding:20px 22px;">
        <div style="font-size:11px;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Wallet Liability</div>
        <div id="k_liability" style="font-size:26px;font-weight:800;margin-top:6px;">—</div>
        <div style="font-size:11px;opacity:.7;margin-top:4px;">Unredeemed wallet balance</div>
    </div>
    <div style="background:linear-gradient(135deg,#7c3aed,#a78bfa);color:white;border-radius:16px;padding:20px 22px;">
        <div style="font-size:11px;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Packages Active</div>
        <div id="k_pkg_active" style="font-size:26px;font-weight:800;margin-top:6px;">—</div>
        <div style="font-size:11px;opacity:.7;margin-top:4px;">Active service bundles</div>
    </div>
    <div style="background:linear-gradient(135deg,#0e7490,#06b6d4);color:white;border-radius:16px;padding:20px 22px;">
        <div style="font-size:11px;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Expiring (30d)</div>
        <div id="k_expiring" style="font-size:26px;font-weight:800;margin-top:6px;">—</div>
        <div style="font-size:11px;opacity:.7;margin-top:4px;">Packages expiring soon</div>
    </div>
</div>

<!-- Tabs -->
<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:20px;" id="report_tabs">
    <button class="rpt-tab active" data-tab="memberships" style="padding:10px 20px;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;background:var(--primary);color:white;">
        <i class="ph ph-identification-badge"></i> Memberships
    </button>
    <button class="rpt-tab" data-tab="packages" style="padding:10px 20px;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;background:#f1f5f9;color:var(--text-main);">
        <i class="ph ph-package"></i> Packages
    </button>
    <button class="rpt-tab" data-tab="liability" style="padding:10px 20px;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;background:#f1f5f9;color:var(--text-main);">
        <i class="ph ph-scales"></i> Liability
    </button>
    <button class="rpt-tab" data-tab="expiring" style="padding:10px 20px;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;background:#f1f5f9;color:var(--text-main);">
        <i class="ph ph-warning" style="color:orange;"></i> Expiring Packages
    </button>
</div>

<!-- Tab Content: Memberships -->
<div id="tab_memberships" class="rpt-tab-content">
    <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:900px;" id="mem_report_table">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);text-align:left;">Customer</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Mobile</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Plan</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Paid</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Outstanding</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Wallet</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Mode</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Status</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Expiry</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Actions</th>
                </tr>
            </thead>
            <tbody id="mem_report_body">
                <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted);">Click "Load Reports" to view data.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Tab Content: Packages -->
<div id="tab_packages" class="rpt-tab-content" style="display:none;">
    <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:900px;" id="pkg_report_table">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);text-align:left;">Customer</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Package</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Price</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Paid</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Outstanding</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Mode</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Purchased</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Expiry</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Status</th>
                    <th style="padding:12px 16px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">Actions</th>
                </tr>
            </thead>
            <tbody id="pkg_report_body">
                <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted);">Click "Load Reports" to view data.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Tab Content: Liability -->
<div id="tab_liability" class="rpt-tab-content" style="display:none;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border-color);background:#f8fafc;font-weight:600;">Wallet Liability (Accounting View)</div>
            <div style="padding:20px;">
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:14px;"><span>Advance Received (total wallet credits)</span><strong id="adv_received">—</strong></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:14px;"><span>Revenue Recognized (redeemed)</span><strong id="adv_redeemed" style="color:#059669;">—</strong></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:14px;font-weight:700;"><span>Outstanding Liability</span><strong id="adv_outstanding" style="color:#dc2626;">—</strong></div>
            </div>
        </div>
        <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:auto;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border-color);background:#f8fafc;font-weight:600;">Package Service Liability</div>
            <div style="padding:16px 20px;" id="pkg_liability_body">Loading...</div>
        </div>
    </div>
</div>

<!-- Tab Content: Expiring -->
<div id="tab_expiring" class="rpt-tab-content" style="display:none;">
    <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:auto;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border-color);background:#fff7ed;color:#92400e;font-weight:600;display:flex;align-items:center;gap:8px;">
            <i class="ph ph-warning" style="font-size:18px;"></i> Packages Expiring in the Next 30 Days
        </div>
        <div style="padding:20px;" id="expiring_body">Click "Load Reports" to see expiring packages.</div>
    </div>
</div>

<style>
.rpt-tab { transition:.2s; }
.rpt-tab.active { background:var(--primary) !important; color:white !important; }
.rpt-tab:not(.active):hover { background:#e2e8f0 !important; }
.modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px; }
.modal-overlay.active { display: flex; }
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: fadeUp 0.3s ease-out forwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<script>
$('.rpt-tab').click(function(){
    $('.rpt-tab').removeClass('active').css({background:'#f1f5f9',color:'var(--text-main)'});
    $(this).addClass('active').css({background:'var(--primary)',color:'white'});
    $('.rpt-tab-content').hide();
    $('#tab_' + $(this).data('tab')).show();
});

var allMembersData = [];
var allPkgData = [];

function loadReports() {
    var from = $('#rpt_from').val();
    var to   = $('#rpt_to').val();

    // Load membership report
    $.post('ajax/membership_ajax.php', {method:'membership_report_data',from_date:from,to_date:to}, function(res){
        var r = JSON.parse(res);
        if(r.error==0){
            $('#k_total_sold').text(r.total_sold);
            $('#k_active').text(r.active_count);
            $('#k_revenue').text('₹' + parseFloat(r.total_revenue).toLocaleString('en-IN',{maximumFractionDigits:0}));
            $('#k_liability').text('₹' + parseFloat(r.wallet_liability).toLocaleString('en-IN',{maximumFractionDigits:0}));

            // Liability tab
            var totalCredits = parseFloat(r.wallet_liability) + parseFloat(r.wallet_redeemed);
            $('#adv_received').text('₹' + totalCredits.toLocaleString('en-IN',{maximumFractionDigits:2}));
            $('#adv_redeemed').text('₹' + parseFloat(r.wallet_redeemed).toLocaleString('en-IN',{maximumFractionDigits:2}));
            $('#adv_outstanding').text('₹' + parseFloat(r.wallet_liability).toLocaleString('en-IN',{maximumFractionDigits:2}));

                allMembersData = r.members_list || [];
            var rows = '';
            if(allMembersData.length > 0) {
                allMembersData.forEach(function(m){
                    var statusColors = {active:'#059669',pending:'#d97706',expired:'#dc2626',refunded:'#6b7280',paused:'#7c3aed'};
                    var sc = statusColors[m.status] || '#6b7280';
                    var outstanding = parseFloat(m.remaining_amount||0);
                    var mode = (m.payment_mode||'—').toUpperCase();

                    // Action buttons
                    var clearBtn = outstanding > 0
                        ? '<button class="btn-clear-outstanding" data-type="mem" data-id="'+m.cm_id+'" data-amount="'+outstanding.toFixed(2)+'" data-name="'+$('<div>').text(m.cust_name).html()+'" style="background:#fff7ed;color:#d97706;border:1px solid #fed7aa;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;">&#128176; Clear Due</button>'
                        : '';
                    var printBtn = m.invoice_id
                        ? '<a href="print_invoice.php?invoice_id='+m.invoice_id+'" target="_blank" style="background:#e0e7ff;color:#4f46e5;border:none;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;">🖨 Print</a>'
                        : '';
                    var waMsg = 'Hi '+m.cust_name+', your '+m.plan_name+' membership is confirmed. Paid: ₹'+parseFloat(m.paid_amount).toFixed(0)+(outstanding>0?' | Due: ₹'+outstanding.toFixed(0):'')+'. Thank you!';
                    var waPhone = (m.cust_mobile||'').replace(/[^0-9]/g,'');
                    var waBtn = waPhone ? '<a href="https://wa.me/91'+waPhone+'?text='+encodeURIComponent(waMsg)+'" target="_blank" style="background:#dcfce7;color:#15803d;border:none;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;">&#128172; WA</a>' : '';
                    var walletBtn = '<button class="modalButtonCommon" data-href="customer_membership_view.php?cust_id='+m.cust_id+'" title="Wallet Ledger" style="background:#f3e8ff;color:#9333ea;border:none;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;">&#128179; Ledger</button>';

                    rows += '<tr>' +
                        '<td style="padding:11px 16px;font-weight:600;border-bottom:1px solid #f1f5f9;">'+m.cust_name+'</td>' +
                        '<td style="padding:11px 16px;color:var(--text-muted);font-size:13px;border-bottom:1px solid #f1f5f9;">'+m.cust_mobile+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;">'+m.plan_name+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;color:#059669;font-weight:600;">₹'+parseFloat(m.paid_amount).toFixed(2)+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;font-weight:700;color:'+(outstanding>0?'#dc2626':'#059669')+';">₹'+outstanding.toFixed(2)+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;color:var(--primary);font-weight:600;">₹'+parseFloat(m.wallet_credit||0).toFixed(2)+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;font-size:12px;">'+mode+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;"><span style="background:'+sc+'20;color:'+sc+';padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">'+m.status+'</span></td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;color:var(--text-muted);font-size:13px;">'+(m.expiry_date||'—')+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;"><div style="display:flex;gap:4px;flex-wrap:nowrap;">'+clearBtn+printBtn+walletBtn+waBtn+'</div></td>' +
                    '</tr>';
                });
            } else {
                rows = '<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted);">No data for selected period.</td></tr>';
            }
            $('#mem_report_body').html(rows);
        }
    });

    // Load package report
    $.post('ajax/membership_ajax.php', {method:'package_report_data',from_date:from,to_date:to}, function(res){
        var r = JSON.parse(res);
        if(r.error==0){
            $('#k_pkg_active').text(r.active_count);
            $('#k_expiring').text(r.expiring_soon);

            // Package table
            allPkgData = r.pkg_list || [];
            var rows = '';
            if(allPkgData.length > 0) {
                allPkgData.forEach(function(p){
                    var sc = p.status=='active' ? '#059669' : (p.status=='expired'?'#dc2626':'#6b7280');
                    var outstanding = parseFloat(p.remaining_amount||0);
                    var mode = (p.payment_mode||'—').toUpperCase();

                    var clearBtn = outstanding > 0
                        ? '<button class="btn-clear-outstanding" data-type="pkg" data-id="'+p.cp_id+'" data-amount="'+outstanding.toFixed(2)+'" data-name="'+$('<div>').text(p.cust_name).html()+'" style="background:#fff7ed;color:#d97706;border:1px solid #fed7aa;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;">&#128176; Clear Due</button>'
                        : '';
                    var printBtn = p.invoice_id
                        ? '<a href="print_invoice.php?invoice_id='+p.invoice_id+'" target="_blank" style="background:#e0e7ff;color:#4f46e5;border:none;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;">🖨 Print</a>'
                        : '';
                    var waMsg2 = 'Hi '+p.cust_name+', your '+p.package_name+' package is confirmed. Paid: ₹'+parseFloat(p.paid_amount||0).toFixed(0)+(outstanding>0?' | Due: ₹'+outstanding.toFixed(0):'')+'. Thank you!';
                    var waPhone2 = (p.cust_mobile||'').replace(/[^0-9]/g,'');
                    var waBtn = waPhone2 ? '<a href="https://wa.me/91'+waPhone2+'?text='+encodeURIComponent(waMsg2)+'" target="_blank" style="background:#dcfce7;color:#15803d;border:none;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;">&#128172; WA</a>' : '';
                    var walletBtn2 = '<button class="modalButtonCommon" data-href="customer_membership_view.php?cust_id='+p.cust_id+'" title="Wallet Ledger" style="background:#f3e8ff;color:#9333ea;border:none;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;">&#128179; Ledger</button>';

                    rows += '<tr>' +
                        '<td style="padding:11px 16px;font-weight:600;border-bottom:1px solid #f1f5f9;">'+p.cust_name+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;">'+p.package_name+'</td>' +
                        '<td style="padding:11px 16px;color:var(--primary);font-weight:600;border-bottom:1px solid #f1f5f9;">₹'+parseFloat(p.purchase_price).toFixed(2)+'</td>' +
                        '<td style="padding:11px 16px;color:#059669;font-weight:600;border-bottom:1px solid #f1f5f9;">₹'+parseFloat(p.paid_amount||0).toFixed(2)+'</td>' +
                        '<td style="padding:11px 16px;font-weight:700;color:'+(outstanding>0?'#dc2626':'#059669')+';border-bottom:1px solid #f1f5f9;">₹'+outstanding.toFixed(2)+'</td>' +
                        '<td style="padding:11px 16px;font-size:12px;border-bottom:1px solid #f1f5f9;">'+mode+'</td>' +
                        '<td style="padding:11px 16px;color:var(--text-muted);font-size:13px;border-bottom:1px solid #f1f5f9;">'+p.purchase_date+'</td>' +
                        '<td style="padding:11px 16px;color:var(--text-muted);font-size:13px;border-bottom:1px solid #f1f5f9;">'+(p.expiry_date||'—')+'</td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;"><span style="background:'+sc+'20;color:'+sc+';padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">'+p.status+'</span></td>' +
                        '<td style="padding:11px 16px;border-bottom:1px solid #f1f5f9;"><div style="display:flex;gap:4px;flex-wrap:nowrap;">'+clearBtn+printBtn+walletBtn2+waBtn+'</div></td>' +
                    '</tr>';
                });
            } else {
                rows = '<tr><td colspan="10" style="text-align:center;padding:30px;color:var(--text-muted);">No data for selected period.</td></tr>';
            }
            $('#pkg_report_body').html(rows);

            // Liability tab - service liability
            if(r.liability_rows && r.liability_rows.length > 0) {
                var liab = '<table style="width:100%;border-collapse:collapse;">';
                liab += '<tr style="background:#f8fafc;"><th style="padding:10px 12px;text-align:left;font-size:12px;color:var(--text-muted);">Service</th><th style="padding:10px 12px;text-align:right;font-size:12px;color:var(--text-muted);">Remaining Sessions</th><th style="padding:10px 12px;text-align:right;font-size:12px;color:var(--text-muted);">Est. Liability</th></tr>';
                r.liability_rows.forEach(function(l){
                    var remaining = (parseInt(l.quantity) - parseInt(l.total_used)) * parseInt(l.pkg_count);
                    var liab_val = remaining * parseFloat(l.service_price);
                    liab += '<tr><td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;">'+l.service_name+'</td>' +
                        '<td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:600;">'+remaining+'</td>' +
                        '<td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;text-align:right;color:#dc2626;font-weight:600;">₹'+liab_val.toFixed(2)+'</td></tr>';
                });
                liab += '</table>';
                $('#pkg_liability_body').html(liab);
            } else {
                $('#pkg_liability_body').html('<p style="color:var(--text-muted);text-align:center;padding:20px;">No active packages.</p>');
            }

            // Expiring tab
            // Load expiring packages directly
            $.post('ajax/membership_ajax.php', {method:'package_report_data',from_date:'2000-01-01',to_date:from}, function(){});
            var expiring_pkgs = allPkgData.filter(function(p){
                if(p.status != 'active') return false;
                var exp = new Date(p.expiry_date);
                var now = new Date();
                var diff = (exp - now) / 86400000;
                return diff >= 0 && diff <= 30;
            });
            if(expiring_pkgs.length > 0) {
                var ehtml = '';
                expiring_pkgs.forEach(function(p){
                    var diff = Math.ceil((new Date(p.expiry_date) - new Date()) / 86400000);
                    ehtml += '<div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f1f5f9;">' +
                        '<div><strong>'+p.cust_name+'</strong><br><span style="color:var(--text-muted);font-size:13px;">'+p.package_name+' | '+p.cust_mobile+'</span></div>' +
                        '<div style="text-align:right;"><span style="background:#fff7ed;color:#92400e;padding:4px 12px;border-radius:8px;font-size:13px;font-weight:700;">'+diff+' days left</span><br><span style="font-size:12px;color:var(--text-muted);">Expires: '+p.expiry_date+'</span></div></div>';
                });
                $('#expiring_body').html(ehtml);
            } else {
                $('#expiring_body').html('<p style="text-align:center;color:var(--text-muted);padding:30px;">No packages expiring in the next 30 days.</p>');
            }
        }
    });
}

$('#btn_load_reports').click(loadReports);
loadReports(); // auto load on page open

// CSV Export
$('#btn_export_csv').click(function(){
    if(!allMembersData.length && !allPkgData.length) { alert('Load reports first.'); return; }
    var csv = 'Customer,Mobile,Plan,Paid,Remaining,Wallet Credit,Status,Expiry\n';
    allMembersData.forEach(function(m){
        csv += [m.cust_name,m.cust_mobile,m.plan_name,m.paid_amount,m.remaining_amount,m.wallet_credit,m.status,m.expiry_date||''].join(',') + '\n';
    });
    csv += '\n\nCustomer,Mobile,Package,Price,Purchase Date,Expiry,Status\n';
    allPkgData.forEach(function(p){
        csv += [p.cust_name,p.cust_mobile,p.package_name,p.purchase_price,p.purchase_date,p.expiry_date||'',p.status].join(',') + '\n';
    });
    var a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'membership_package_report_<?= date('Y-m-d') ?>.csv';
    a.click();
});
</script>

<!-- Clear Outstanding Modal -->
<div id="clearOutstandingModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:20px;width:100%;max-width:420px;margin:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.4);padding:28px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
            <div style="width:38px;height:38px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">💰</div>
            <div>
                <div style="font-weight:800;font-size:16px;">Clear Outstanding</div>
                <div id="clear_modal_name" style="font-size:13px;color:var(--text-muted);"></div>
            </div>
            <button onclick="$('#clearOutstandingModal').hide();" style="margin-left:auto;background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">×</button>
        </div>
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px;margin-bottom:18px;">
            <div style="font-size:12px;color:#dc2626;font-weight:700;margin-bottom:4px;">OUTSTANDING AMOUNT</div>
            <div id="clear_modal_amount" style="font-size:26px;font-weight:800;color:#dc2626;"></div>
        </div>
        <div class="form-group">
            <label style="font-weight:700;">Payment Amount</label>
            <input type="number" id="clear_pay_amount" class="form-control" min="1" step="0.01">
        </div>
        <div class="form-group">
            <label style="font-weight:700;">Payment Method</label>
            <select id="clear_pay_mode" class="form-control">
                <?php 
                $pay_methods = select_array("SELECT * FROM hr_payment_methods WHERE (salon_id='$salon_id' OR is_global=1) AND status=1 ORDER BY sort_order ASC");
                if(!empty($pay_methods)) {
                    foreach($pay_methods as $pm) {
                        echo '<option value="'.htmlspecialchars($pm['method_key']).'">'.htmlspecialchars($pm['method_name']).'</option>';
                    }
                } else {
                    echo '<option value="cash">Cash</option>';
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label style="font-weight:700;">Payment Date</label>
            <input type="date" id="clear_pay_date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
            <label style="font-weight:700;">Notes (optional)</label>
            <input type="text" id="clear_pay_notes" class="form-control" placeholder="e.g. Cash received at counter">
        </div>
        <input type="hidden" id="clear_type">
        <input type="hidden" id="clear_id">
        <div style="display:flex;gap:10px;margin-top:8px;">
            <button onclick="submitClearOutstanding()" class="btn-primary" style="flex:1;margin:0;padding:12px;">
                <i class="ph ph-check-circle"></i> Confirm Payment
            </button>
            <button onclick="$('#clearOutstandingModal').hide();" class="btn-secondary" style="width:auto;margin:0;padding:12px 16px;">Cancel</button>
        </div>
    </div>
</div>

<script>
// Modal loader functions
$(document).on('click', '.modalButtonCommon', function(e) {
    e.preventDefault();
    loadModal($(this).data('href'));
});

$(document).on('click', '.close-modal', function(){
    $('#commonModalOverlay').removeClass('active');
});

function loadModal(url) {
    $('#commonModalContent').html('<div style="padding: 40px; text-align: center;"><i class="ph ph-spinner ph-spin" style="font-size: 32px; color: var(--primary);"></i><p>Loading...</p></div>');
    $('#commonModalOverlay').addClass('active');
    $.ajax({url: url, success: function(data) { $('#commonModalContent').html(data); }});
}

// Delegated click for dynamically-added Clear Due buttons
$(document).on('click', '.btn-clear-outstanding', function() {
    var type        = $(this).data('type');
    var id          = $(this).data('id');
    var outstanding = parseFloat($(this).data('amount'));
    var name        = $(this).data('name');
    $('#clear_type').val(type);
    $('#clear_id').val(id);
    $('#clear_pay_amount').val(outstanding.toFixed(2));
    $('#clear_modal_name').text(name);
    $('#clear_modal_amount').text('₹' + outstanding.toFixed(2));
    $('#clearOutstandingModal').css('display','flex');
});

function submitClearOutstanding() {
    var type   = $('#clear_type').val();
    var id     = $('#clear_id').val();
    var amount = parseFloat($('#clear_pay_amount').val());
    var mode   = $('#clear_pay_mode').val();
    var notes  = $('#clear_pay_notes').val();
    var date   = $('#clear_pay_date').val();

    if(!amount || amount <= 0) { alert('Please enter a valid amount.'); return; }

    var method = (type === 'mem') ? 'record_membership_payment' : 'record_package_payment';
    var idKey  = (type === 'mem') ? 'cm_id' : 'cp_id';
    var payload = { method: method, payment_mode: mode, amount: amount, notes: notes, payment_date: date };
    payload[idKey] = id;

    $.post('ajax/membership_ajax.php', payload, function(res){
        try {
            var r = JSON.parse(res);
            if(r.error) { alert('Error: ' + r.msg); return; }
            alert('✅ ' + r.msg);
            $('#clearOutstandingModal').hide();
            loadReports();
        } catch(e) { alert('Server error. Please try again.'); }
    });
}
</script>

<?php include 'footer.php'; ?>

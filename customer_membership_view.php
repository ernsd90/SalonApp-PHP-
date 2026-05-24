<?php
// Customer Membership Profile View - loaded via AJAX or modal
include_once 'config.php';
include_once 'function.php';

$cust_id = intval($_GET['cust_id'] ?? 0);
if (!$cust_id) { echo '<p style="padding:20px;color:red;">No customer ID provided.</p>'; exit; }

$customer = select_row("SELECT * FROM hr_customer WHERE cust_id='$cust_id'");
if (!$customer) { echo '<p style="padding:20px;color:red;">Customer not found.</p>'; exit; }

$memberships = select_array("SELECT cm.*, p.wallet_credit as plan_wallet_credit FROM hr_customer_membership cm
    LEFT JOIN hr_membership_plans p ON p.plan_id = cm.plan_id
    WHERE cm.cust_id='$cust_id' ORDER BY cm.cm_id DESC");

$packages = select_array("SELECT cp.* FROM hr_customer_packages cp WHERE cp.cust_id='$cust_id' ORDER BY cp.cp_id DESC");

$wallet_ledger = select_array("SELECT * FROM hr_customer_wallet WHERE cust_id='$cust_id' ORDER BY wallet_id DESC LIMIT 20");

// Expire check
foreach ($memberships as &$m) {
    if ($m['status']=='active' && $m['expiry_date'] && $m['expiry_date'] < date('Y-m-d')) {
        update_query("UPDATE hr_customer_membership SET status='expired' WHERE cm_id='{$m['cm_id']}'");
        $m['status'] = 'expired';
    }
}
foreach ($packages as &$p) {
    if ($p['status']=='active' && $p['expiry_date'] && $p['expiry_date'] < date('Y-m-d')) {
        update_query("UPDATE hr_customer_packages SET status='expired' WHERE cp_id='{$p['cp_id']}'");
        $p['status'] = 'expired';
    }
}
?>
<style>
.mp-tab-btn { padding:10px 18px;border:none;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer;background:#f1f5f9;color:var(--text-main);transition:.2s;margin-right:6px;margin-bottom:6px; }
.mp-tab-btn.active { background:var(--primary);color:white; }
.mp-tab { display:none; }
.mp-tab.active { display:block; }
.status-pill { display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase; }
</style>

<div style="padding:24px;">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
        <div>
            <h3 style="font-size:18px;font-weight:700;margin:0;"><?= htmlspecialchars($customer['cust_name']) ?></h3>
            <div style="color:var(--text-muted);font-size:14px;"><?= htmlspecialchars($customer['cust_mobile']) ?></div>
        </div>
        <div style="display:flex;align-items:flex-start;gap:24px;text-align:right;">
            <div>
                <div style="font-size:13px;color:var(--text-muted);">Wallet Balance</div>
                <div style="font-size:22px;font-weight:800;color:<?= $customer['cust_wallet'] > 0 ? 'var(--primary)' : 'var(--text-muted)' ?>;">₹<?= number_format($customer['cust_wallet'],2) ?></div>
            </div>
            <button type="button" class="close-modal" style="background:none;border:none;font-size:24px;color:var(--text-muted);cursor:pointer;margin-top:-4px;"><i class="ph ph-x"></i></button>
        </div>
    </div>

    <!-- Tabs -->
    <div style="margin-bottom:20px;">
        <button class="mp-tab-btn active" onclick="mpTab(this,'mp_memberships')"><i class="ph ph-identification-badge"></i> Memberships</button>
        <button class="mp-tab-btn" onclick="mpTab(this,'mp_packages')"><i class="ph ph-package"></i> Packages</button>
        <button class="mp-tab-btn" onclick="mpTab(this,'mp_wallet')"><i class="ph ph-wallet"></i> Wallet Ledger</button>
        <button class="mp-tab-btn" onclick="mpTab(this,'mp_outstanding')"><i class="ph ph-clock"></i> Outstanding</button>
    </div>

    <!-- Tab: Memberships -->
    <div id="mp_memberships" class="mp-tab active">
        <?php if(!$memberships): ?>
            <div style="text-align:center;padding:30px;color:var(--text-muted);">No memberships found. <a href="sell_membership.php?cust_id=<?= $cust_id ?>" style="color:var(--primary);">Sell one now</a></div>
        <?php else: ?>
            <?php foreach($memberships as $m):
                $sc = ['active'=>'#059669','pending'=>'#d97706','expired'=>'#dc2626','refunded'=>'#6b7280','paused'=>'#7c3aed'][$m['status']] ?? '#6b7280';
                $validity_months = $m['expiry_date'] && $m['start_date'] ? 'Expires ' . date('d M Y', strtotime($m['expiry_date'])) : 'Not activated';
                $payments = select_array("SELECT * FROM hr_membership_payments WHERE cm_id='{$m['cm_id']}' ORDER BY mp_id ASC");
            ?>
            <div style="border:1px solid var(--border-color);border-radius:14px;padding:18px 20px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                    <div>
                        <div style="font-weight:700;font-size:16px;"><?= htmlspecialchars($m['plan_name']) ?></div>
                        <div style="color:var(--text-muted);font-size:13px;margin-top:3px;"><?= $validity_months ?></div>
                    </div>
                    <span class="status-pill" style="background:<?= $sc ?>20;color:<?= $sc ?>;"><?= $m['status'] ?></span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-top:14px;padding-top:14px;border-top:1px solid var(--border-color);">
                    <div><div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Total Price</div><div style="font-size:15px;font-weight:700;">₹<?= number_format($m['total_price'],2) ?></div></div>
                    <div><div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Paid</div><div style="font-size:15px;font-weight:700;color:#059669;">₹<?= number_format($m['paid_amount'],2) ?></div></div>
                    <div><div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Remaining</div><div style="font-size:15px;font-weight:700;color:<?= $m['remaining_amount']>0?'#dc2626':'#059669' ?>;">₹<?= number_format($m['remaining_amount'],2) ?></div></div>
                    <div><div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Wallet Credit</div><div style="font-size:15px;font-weight:700;color:var(--primary);">₹<?= number_format($m['wallet_credit'],2) ?></div></div>
                </div>
                <?php if($m['remaining_amount'] > 0 && $m['status'] != 'refunded'): ?>
                <div style="margin-top:12px;padding:12px 14px;background:#fff7ed;border-radius:10px;border:1px solid #fed7aa;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div style="font-size:13px;font-weight:600;color:#92400e;">⚠ Outstanding: ₹<?= number_format($m['remaining_amount'],2) ?></div>
                        <button onclick="recordMembershipPayment(<?= $m['cm_id'] ?>, <?= $m['remaining_amount'] ?>)"
                            style="background:#d97706;color:white;border:none;padding:6px 14px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;">
                            Record Payment
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <?php if($m['status'] == 'active'): ?>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
                    <button onclick="pauseMembership(<?= $m['cm_id'] ?>)" style="background:#e0e7ff;color:#4f46e5;border:none;padding:6px 14px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;"><i class="ph ph-pause"></i> Pause</button>
                    <button onclick="refundMembership(<?= $m['cm_id'] ?>)" style="background:#fee2e2;color:#dc2626;border:none;padding:6px 14px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;"><i class="ph ph-arrow-counter-clockwise"></i> Refund</button>
                </div>
                <?php elseif($m['status'] == 'paused'): ?>
                <div style="margin-top:12px;">
                    <button onclick="resumeMembership(<?= $m['cm_id'] ?>)" style="background:#dcfce7;color:#059669;border:none;padding:6px 14px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;"><i class="ph ph-play"></i> Resume Membership</button>
                </div>
                <?php endif; ?>
                <?php if($payments): ?>
                <details style="margin-top:12px;"><summary style="font-size:13px;color:var(--text-muted);cursor:pointer;">Payment History (<?= count($payments) ?> entries)</summary>
                <div style="margin-top:8px;">
                <?php foreach($payments as $pay): ?>
                    <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                        <span style="color:var(--text-muted);"><?= date('d M Y', strtotime($pay['created_at'])) ?> · <?= ucfirst($pay['payment_mode']) ?></span>
                        <strong style="color:#059669;">₹<?= number_format($pay['amount'],2) ?></strong>
                    </div>
                <?php endforeach; ?>
                </div></details>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tab: Packages -->
    <div id="mp_packages" class="mp-tab">
        <?php if(!$packages): ?>
            <div style="text-align:center;padding:30px;color:var(--text-muted);">No packages found. <a href="sell_package.php?cust_id=<?= $cust_id ?>" style="color:var(--primary);">Sell one now</a></div>
        <?php else: ?>
            <?php foreach($packages as $cp):
                $sc = ['active'=>'#059669','expired'=>'#dc2626','refunded'=>'#6b7280','fully_used'=>'#7c3aed'][$cp['status']] ?? '#6b7280';
                $items = select_array("SELECT pi.service_id, pi.service_name, pi.quantity,
                    COALESCE(SUM(u.qty_used),0) AS used
                    FROM hr_package_items pi
                    LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id='{$cp['cp_id']}'
                    WHERE pi.pkg_id='{$cp['pkg_id']}' GROUP BY pi.item_id");
            ?>
            <div style="border:1px solid var(--border-color);border-radius:14px;padding:18px 20px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <div style="font-weight:700;font-size:16px;"><?= htmlspecialchars($cp['package_name']) ?></div>
                        <div style="color:var(--text-muted);font-size:13px;">Purchased: <?= $cp['purchase_date'] ?> · Expires: <?= $cp['expiry_date'] ?: '—' ?></div>
                    </div>
                    <span class="status-pill" style="background:<?= $sc ?>20;color:<?= $sc ?>;"><?= $cp['status'] ?></span>
                </div>
                <?php if($items): ?>
                <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border-color);">
                    <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Service Counts</div>
                    <?php foreach($items as $item):
                        $remaining = (int)$item['quantity'] - (int)$item['used'];
                        $pct = $item['quantity'] > 0 ? ($item['used'] / $item['quantity']) * 100 : 0;
                    ?>
                    <div style="margin-bottom:10px;">
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                            <span style="font-weight:500;"><?= htmlspecialchars($item['service_name']) ?></span>
                            <span style="color:<?= $remaining>0?'#059669':'#dc2626' ?>;font-weight:700;"><?= $remaining ?> / <?= $item['quantity'] ?> remaining</span>
                        </div>
                        <div style="background:#f1f5f9;border-radius:6px;height:6px;overflow:hidden;">
                            <div style="background:<?= $remaining>0?'#059669':'#dc2626' ?>;height:100%;width:<?= min(100,$pct) ?>%;transition:.3s;border-radius:6px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if($cp['status'] == 'active'): ?>
                <div style="margin-top:12px;">
                    <button onclick="refundPackage(<?= $cp['cp_id'] ?>)" style="background:#fee2e2;color:#dc2626;border:none;padding:6px 14px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;"><i class="ph ph-arrow-counter-clockwise"></i> Refund Package</button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tab: Wallet Ledger -->
    <div id="mp_wallet" class="mp-tab">
        <div style="border:1px solid var(--border-color);border-radius:14px;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="padding:10px 14px;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;text-align:left;border-bottom:1px solid var(--border-color);">Date</th>
                        <th style="padding:10px 14px;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;text-align:right;border-bottom:1px solid var(--border-color);">Credit</th>
                        <th style="padding:10px 14px;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;text-align:right;border-bottom:1px solid var(--border-color);">Debit</th>
                        <th style="padding:10px 14px;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;text-align:right;border-bottom:1px solid var(--border-color);">Balance</th>
                        <th style="padding:10px 14px;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;text-align:left;border-bottom:1px solid var(--border-color);">Remark</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($wallet_ledger): foreach($wallet_ledger as $w): ?>
                    <tr>
                        <td style="padding:10px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;color:var(--text-muted);"><?= date('d M Y H:i', strtotime($w['created_date'])) ?></td>
                        <td style="padding:10px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:600;color:#059669;"><?= $w['credit'] > 0 ? '₹'.number_format($w['credit'],2) : '—' ?></td>
                        <td style="padding:10px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:600;color:#dc2626;"><?= $w['debit']  > 0 ? '₹'.number_format($w['debit'], 2) : '—' ?></td>
                        <td style="padding:10px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;">₹<?= number_format($w['balance'],2) ?></td>
                        <td style="padding:10px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;color:var(--text-muted);"><?= htmlspecialchars($w['remark'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted);">No wallet transactions.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Outstanding -->
    <div id="mp_outstanding" class="mp-tab">
        <?php
        $outstanding_mems = array_filter($memberships, fn($m) => $m['remaining_amount'] > 0 && $m['status'] != 'refunded');
        $outstanding_pkgs = array_filter($packages, fn($p) => $p['remaining_amount'] > 0 && $p['status'] != 'refunded');
        
        if(!$outstanding_mems && !$outstanding_pkgs): ?>
            <div style="text-align:center;padding:30px;color:#059669;font-weight:600;"><i class="ph ph-check-circle" style="font-size:32px;display:block;margin-bottom:8px;"></i> No outstanding payments!</div>
        <?php else: 
            foreach($outstanding_mems as $m): ?>
            <div style="border:2px solid #fed7aa;border-radius:14px;padding:16px 20px;margin-bottom:14px;background:#fff7ed;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <div style="font-weight:700;"><span style="color:#d97706;">[Membership]</span> <?= htmlspecialchars($m['plan_name']) ?></div>
                        <div style="color:var(--text-muted);font-size:13px;">Paid: ₹<?= number_format($m['paid_amount'],2) ?> of ₹<?= number_format($m['total_price'],2) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:18px;font-weight:800;color:#dc2626;">₹<?= number_format($m['remaining_amount'],2) ?></div>
                        <div style="font-size:12px;color:var(--text-muted);">outstanding</div>
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:8px;">
                    <button onclick="recordMembershipPayment(<?= $m['cm_id'] ?>, <?= $m['remaining_amount'] ?>)"
                        style="background:#d97706;color:white;border:none;padding:8px 16px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;">
                        <i class="ph ph-currency-inr"></i> Pay Remaining ₹<?= number_format($m['remaining_amount'],2) ?>
                    </button>
                </div>
            </div>
        <?php endforeach; 
            foreach($outstanding_pkgs as $p): ?>
            <div style="border:2px solid #fed7aa;border-radius:14px;padding:16px 20px;margin-bottom:14px;background:#fff7ed;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <div style="font-weight:700;"><span style="color:#d97706;">[Package]</span> <?= htmlspecialchars($p['package_name']) ?></div>
                        <div style="color:var(--text-muted);font-size:13px;">Paid: ₹<?= number_format($p['paid_amount'],2) ?> of ₹<?= number_format($p['purchase_price'] + $p['gst_amount'],2) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:18px;font-weight:800;color:#dc2626;">₹<?= number_format($p['remaining_amount'],2) ?></div>
                        <div style="font-size:12px;color:var(--text-muted);">outstanding</div>
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:8px;">
                    <button onclick="recordPackagePayment(<?= $p['cp_id'] ?>, <?= $p['remaining_amount'] ?>)"
                        style="background:#d97706;color:white;border:none;padding:8px 16px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;">
                        <i class="ph ph-currency-inr"></i> Pay Remaining ₹<?= number_format($p['remaining_amount'],2) ?>
                    </button>
                </div>
            </div>
        <?php endforeach; 
        endif; ?>
    </div>
</div>

<script>
function mpTab(btn, id) {
    document.querySelectorAll('.mp-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.mp-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(id).classList.add('active');
}

function recordMembershipPayment(cm_id, remaining) {
    var amount = prompt('Enter payment amount (max ₹' + remaining + '):', remaining);
    if(!amount) return;
    var payMode = prompt('Payment mode (cash/card/upi):', 'cash') || 'cash';
    $.post('ajax/membership_ajax.php', {method:'record_membership_payment', cm_id:cm_id, amount:amount, payment_mode:payMode}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        if(r.error==0) location.reload();
    });
}

function recordPackagePayment(cp_id, remaining) {
    var amount = prompt('Enter payment amount (max ₹' + remaining + '):', remaining);
    if(!amount) return;
    var payMode = prompt('Payment mode (cash/card/upi):', 'cash') || 'cash';
    $.post('ajax/membership_ajax.php', {method:'record_package_payment', cp_id:cp_id, amount:amount, payment_mode:payMode}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        if(r.error==0) location.reload();
    });
}

function pauseMembership(cm_id) {
    if(!confirm('Pause this membership? Validity will be frozen until you resume.')) return;
    $.post('ajax/membership_ajax.php', {method:'pause_membership', cm_id:cm_id}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        if(r.error==0) location.reload();
    });
}

function resumeMembership(cm_id) {
    $.post('ajax/membership_ajax.php', {method:'resume_membership', cm_id:cm_id}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        if(r.error==0) location.reload();
    });
}

function refundMembership(cm_id) {
    if(!confirm('Refund this membership? This will deduct the wallet credit if already applied.')) return;
    $.post('ajax/membership_ajax.php', {method:'refund_membership', cm_id:cm_id}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        if(r.error==0) location.reload();
    });
}

function refundPackage(cp_id) {
    if(!confirm('Refund this package? Sessions will no longer be redeemable.')) return;
    $.post('ajax/membership_ajax.php', {method:'refund_package', cp_id:cp_id}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        if(r.error==0) location.reload();
    });
}
</script>

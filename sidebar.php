<?php
$cur = basename($_SERVER['PHP_SELF']);
function sb_active(array $pages): string {
    global $cur;
    return in_array($cur, $pages) ? 'active' : '';
}
?>

<aside class="sidebar" id="appSidebar">

    <!-- Logo -->
    <div class="sb-logo">
        <div class="sb-logo-icon"><i class="ph-fill ph-scissors"></i></div>
        <span class="sb-logo-text">Salon OS</span>
        <button id="close-sidebar-btn" class="sb-close-btn"><i class="ph ph-x"></i></button>
    </div>

    <nav class="sb-nav">

        <!-- ── POS & Sales ───────────────────────────── -->
        <div class="sb-section">
            <div class="sb-section-label">POS &amp; Sales</div>

            <?php if(check_user_permission('dashboard','view',$user_id)): ?>
            <a href="index.php" class="sb-link <?= sb_active(['index.php']) ?>">
                <i class="ph ph-squares-four"></i><span>Dashboard</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('billing','view',$user_id)): ?>
            <a href="billing.php" class="sb-link <?= sb_active(['billing.php']) ?>">
                <i class="ph ph-receipt"></i><span>POS Terminal</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('job_cards','view',$user_id)): ?>
            <a href="job_card_list.php" class="sb-link <?= sb_active(['job_card_list.php','job_card.php']) ?>">
                <i class="ph ph-clipboard-text"></i><span>Job Cards</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('invoices','view',$user_id)): ?>
            <a href="invoices.php" class="sb-link <?= sb_active(['invoices.php']) ?>">
                <i class="ph ph-file-text"></i><span>Invoice History</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('expenses','view',$user_id)): ?>
            <a href="expenses.php" class="sb-link <?= sb_active(['expenses.php','expense_cat.php']) ?>">
                <i class="ph ph-money-wavy"></i><span>Expenses</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- ── Memberships ──────────────────────────── -->
        <div class="sb-section">
            <div class="sb-section-label">Memberships &amp; Packages</div>

            <?php if(check_user_permission('membership_plans','view',$user_id)): ?>
            <a href="membership_plans.php" class="sb-link <?= sb_active(['membership_plans.php','membership_plan_edit.php']) ?>">
                <i class="ph ph-identification-badge"></i><span>Membership Plans</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('packages','view',$user_id)): ?>
            <a href="packages_new.php" class="sb-link <?= sb_active(['packages_new.php','package_new_edit.php']) ?>">
                <i class="ph ph-package"></i><span>Service Packages</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('sell_membership','view',$user_id)): ?>
            <a href="sell_membership.php" class="sb-link <?= sb_active(['sell_membership.php']) ?>">
                <i class="ph ph-currency-inr"></i><span>Sell Membership</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('sell_package','view',$user_id)): ?>
            <a href="sell_package.php" class="sb-link <?= sb_active(['sell_package.php']) ?>">
                <i class="ph ph-shopping-bag"></i><span>Sell Package</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('membership_reports','view',$user_id)): ?>
            <a href="membership_reports.php" class="sb-link <?= sb_active(['membership_reports.php']) ?>">
                <i class="ph ph-chart-pie-slice"></i><span>Membership Reports</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- ── CRM & Loyalty ────────────────────────── -->
        <div class="sb-section">
            <div class="sb-section-label">CRM &amp; Loyalty</div>

            <?php if(check_user_permission('customer','view',$user_id)): ?>
            <a href="customers.php" class="sb-link <?= sb_active(['customers.php']) ?>">
                <i class="ph ph-users"></i><span>Customers</span>
            </a>
            <?php endif; ?>

            <a href="loyalty.php" class="sb-link <?= sb_active(['loyalty.php']) ?>">
                <i class="ph ph-trophy"></i><span>Loyalty &amp; Rewards</span>
            </a>

            <a href="campaigns.php" class="sb-link <?= sb_active(['campaigns.php']) ?>">
                <i class="ph ph-megaphone"></i><span>Campaigns</span>
            </a>
        </div>

        <!-- ── Analytics ────────────────────────────── -->
        <?php if(check_user_permission('report','view',$user_id)): ?>
        <div class="sb-section">
            <div class="sb-section-label">Reports &amp; Analytics</div>

            <a href="reports.php" class="sb-link <?= sb_active(['reports.php']) ?>">
                <i class="ph ph-chart-bar"></i><span>Sales Reports</span>
            </a>

            <a href="crm_reports.php" class="sb-link <?= sb_active(['crm_reports.php']) ?>">
                <i class="ph ph-users-three"></i><span>CRM Analytics</span>
            </a>

            <a href="analytics.php" class="sb-link <?= sb_active(['analytics.php']) ?>">
                <i class="ph ph-chart-line-up"></i><span>Advanced Analytics</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Catalogue ─────────────────────────────── -->
        <div class="sb-section">
            <div class="sb-section-label">Catalogue</div>

            <?php if(check_user_permission('cataloge','view',$user_id)): ?>
            <a href="services.php" class="sb-link <?= sb_active(['services.php','service_cat.php']) ?>">
                <i class="ph ph-sparkle"></i><span>Services</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('product','view',$user_id)): ?>
            <a href="product.php" class="sb-link <?= sb_active(['product.php','product_brand.php']) ?>">
                <i class="ph ph-bag"></i><span>Products</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- ── Inventory ─────────────────────────────── -->
        <?php
        $show_inv = check_user_permission('vendors','view',$user_id)
                 || check_user_permission('purchase_bills','view',$user_id)
                 || check_user_permission('vendor_ledger','view',$user_id);
        if($show_inv):
        ?>
        <div class="sb-section">
            <div class="sb-section-label">Inventory</div>

            <?php if(check_user_permission('vendors','view',$user_id)): ?>
            <a href="vendors.php" class="sb-link <?= sb_active(['vendors.php','vendor_edit.php','vendor_ledger.php']) ?>">
                <i class="ph ph-buildings"></i><span>Vendors</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('purchase_bills','view',$user_id)): ?>
            <a href="purchase_bills.php" class="sb-link <?= sb_active(['purchase_bills.php','purchase_bill_create.php','purchase_bill_view.php','purchase_bill_pay.php']) ?>">
                <i class="ph ph-note-pencil"></i><span>Purchase Bills</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('vendor_ledger','view',$user_id)): ?>
            <a href="vendor_ledger.php" class="sb-link <?= sb_active(['vendor_ledger.php']) ?>">
                <i class="ph ph-book-open"></i><span>Vendor Ledger</span>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ── System ────────────────────────────────── -->
        <div class="sb-section">
            <div class="sb-section-label">System</div>

            <?php if(check_user_permission('staff','view',$user_id)): ?>
            <a href="staff.php" class="sb-link <?= sb_active(['staff.php']) ?>">
                <i class="ph ph-identification-card"></i><span>Staff Directory</span>
            </a>
            <a href="staff_analytics.php" class="sb-link <?= sb_active(['staff_analytics.php']) ?>">
                <i class="ph ph-chart-polar"></i><span>Staff Analytics</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('users','view',$user_id)): ?>
            <a href="users.php" class="sb-link <?= sb_active(['users.php']) ?>">
                <i class="ph ph-user-gear"></i><span>Users</span>
            </a>
            <?php endif; ?>

            <?php if(check_user_permission('payment_methods','view',$user_id)): ?>
            <a href="payment_methods.php" class="sb-link <?= sb_active(['payment_methods.php']) ?>">
                <i class="ph ph-credit-card"></i><span>Payment Methods</span>
            </a>
            <?php endif; ?>

            <?php if(is_superadmin()): ?>
            <a href="salons.php" class="sb-link sb-link--accent">
                <i class="ph-fill ph-storefront"></i><span>Manage Outlets</span>
            </a>
            <?php endif; ?>
        </div>

    </nav>

    <!-- Logout at bottom -->
    <div class="sb-footer">
        <a href="logout.php" class="sb-link sb-link--danger">
            <i class="ph ph-sign-out"></i><span>Logout</span>
        </a>
    </div>

</aside>

<style>
/* ═══════════════════════════════════════════════════
   SIDEBAR – Clean & Minimal
   (position/width/flex handled by style.css .sidebar)
═══════════════════════════════════════════════════ */
.sidebar {
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    width: 240px;
    flex-shrink: 0;
    background: #fff;
    border-right: 1px solid #e8ecf0;
}

/* Logo strip */
.sb-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 18px;
    height: 62px;
    border-bottom: 1px solid #f0f2f5;
    flex-shrink: 0;
}
.sb-logo-icon {
    width: 32px; height: 32px;
    background: var(--primary);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 17px; flex-shrink: 0;
}
.sb-logo-text {
    font-size: 17px; font-weight: 800;
    color: #0f172a; letter-spacing: -0.4px;
}
.sb-close-btn {
    display: none; margin-left: auto;
    background: none; border: none;
    font-size: 18px; color: #94a3b8; cursor: pointer;
}

/* Scrollable nav */
.sb-nav {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 10px 10px 0;
    scrollbar-width: thin;
    scrollbar-color: #e2e8f0 transparent;
}
.sb-nav::-webkit-scrollbar { width: 4px; }
.sb-nav::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

/* Section group */
.sb-section { margin-bottom: 4px; }
.sb-section-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #b0bac6;
    padding: 14px 8px 5px;
}

/* Nav link */
.sb-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    text-decoration: none;
    color: #4a5568;
    font-size: 13.5px;
    font-weight: 500;
    transition: background 0.15s, color 0.15s;
    margin-bottom: 1px;
}
.sb-link i {
    font-size: 17px;
    flex-shrink: 0;
    color: #94a3b8;
    transition: color 0.15s;
}
.sb-link:hover { background: #f5f7fa; color: #1e293b; }
.sb-link:hover i { color: var(--primary); }
.sb-link.active { background: var(--primary-light, #ede9fe); color: var(--primary); font-weight: 600; }
.sb-link.active i { color: var(--primary); }

.sb-link--accent { color: var(--primary) !important; }
.sb-link--accent i { color: var(--primary) !important; }
.sb-link--danger { color: #ef4444 !important; }
.sb-link--danger i { color: #ef4444 !important; }
.sb-link--danger:hover { background: #fff1f2; }

/* Footer / Logout */
.sb-footer {
    padding: 8px 10px 14px;
    border-top: 1px solid #f0f2f5;
    flex-shrink: 0;
}

/* Mobile */
@media (max-width: 768px) {
    .sidebar { position: fixed; top: 0; left: 0; z-index: 200; transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); box-shadow: 6px 0 28px rgba(0,0,0,.12); }
    .sb-close-btn { display: block !important; }
    #mobile-menu-btn { display: block !important; }
}
</style>

<script>
$(document).ready(function(){
    $('#mobile-menu-btn').click(function(){ $('#appSidebar').addClass('open'); });
    $('#close-sidebar-btn').click(function(){ $('#appSidebar').removeClass('open'); });

    $('#globalSalonSelect').change(function(){
        var s = $(this).val();
        $.post('ajax/auth_ajax.php', { method:'switch_salon', new_salon_id:s }, function(res){
            var r = JSON.parse(res);
            if(r.error==0) location.reload(); else alert(r.msg);
        });
    });
});
</script>

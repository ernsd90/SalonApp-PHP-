<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<?php
// Auto-create the hr_payment_methods table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `hr_payment_methods` (
    `method_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `salon_id` bigint(20) NOT NULL DEFAULT 0,
    `method_name` varchar(100) NOT NULL,
    `method_key` varchar(50) NOT NULL,
    `is_global` tinyint(1) NOT NULL DEFAULT 0,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `status` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed default methods if table is empty
$count = select_row("SELECT COUNT(*) as cnt FROM hr_payment_methods WHERE is_global=1");
if(intval($count['cnt']) == 0) {
    $seeds = [
        ['Cash', 'cash', 0], 
        ['Card / POS', 'card', 10], 
        ['UPI / Online Transfer', 'upi', 20], 
        ['Wallet Balance', 'wallet', 30]
    ];
    foreach($seeds as $s) {
        $conn->query("INSERT INTO `hr_payment_methods` (`method_name`,`method_key`,`is_global`,`sort_order`,`status`) VALUES ('$s[0]','$s[1]',1,$s[2],1)");
    }
}
?>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Payment Methods</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Manage payment modes used across Invoicing, POS, and Expense tracking.</p>
</div>

<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
    
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Configured Methods</h3>
        <button class="btn-primary" style="width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;" onclick="loadModal('payment_method_edit.php');">
            <i class="ph-bold ph-plus"></i> Add Method
        </button>
    </div>

    <div style="padding: 24px;">
        <table id="get_payment_methods" class="table-modern" style="width: 100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Method Name</th>
                    <th>Key / Code</th>
                    <th>Scope</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th style="width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $salon_id = get_session_data('salon_id');
                $methods = select_array("SELECT * FROM `hr_payment_methods` WHERE (`salon_id`='$salon_id' OR `is_global`=1) ORDER BY `sort_order` ASC");
                if($methods): foreach($methods as $m): ?>
                <tr>
                    <td><?= $m['method_id'] ?></td>
                    <td><strong><?= htmlspecialchars($m['method_name']) ?></strong></td>
                    <td><code style="background:#f1f5f9; padding: 2px 8px; border-radius: 6px; font-size:13px;"><?= htmlspecialchars($m['method_key']) ?></code></td>
                    <td><?= $m['is_global'] ? '<span style="color:var(--primary); font-weight:600;">Global</span>' : '<span style="color:var(--text-muted);">Outlet Only</span>' ?></td>
                    <td><?= $m['sort_order'] ?></td>
                    <td>
                        <?php if($m['status']): ?>
                            <span style="padding: 4px 10px; background: #dcfce7; color: #16a34a; border-radius: 20px; font-size: 12px; font-weight: 600;">Active</span>
                        <?php else: ?>
                            <span style="padding: 4px 10px; background: #fee2e2; color: #dc2626; border-radius: 20px; font-size: 12px; font-weight: 600;">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($m['is_global'] == 1 && !is_superadmin()): ?>
                            <span style="color:var(--text-muted); font-size:12px; background:#f1f5f9; padding:4px 8px; border-radius:6px; display:inline-block;"><i class="ph ph-lock"></i> Locked</span>
                        <?php else: ?>
                            <button type="button" class="modalButtonCommon" data-href="payment_method_edit.php?method_id=<?= $m['method_id'] ?>" style="background:#f1f5f9; border:none; width:32px; height:32px; border-radius:8px; cursor:pointer; color:var(--primary);">
                                <i class="ph ph-pencil"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center; padding: 30px; color:var(--text-muted);">No payment methods configured yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern th { background: #f8fafc; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 1px solid var(--border-color); text-align: left; }
.table-modern td { padding: 14px 16px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border-color); vertical-align: middle; }
.table-modern tbody tr:hover td { background: #f8fafc; }
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); overflow: hidden; }
</style>

<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<script>
function loadModal(url) {
    $('#commonModalContent').html('<div style="padding: 40px; text-align: center;"><i class="ph ph-spinner ph-spin" style="font-size: 32px; color: var(--primary);"></i><p>Loading...</p></div>');
    $('#commonModalOverlay').addClass('active');
    $.ajax({ url: url, success: function(data) { $('#commonModalContent').html(data); } });
}

$(document).on('click', '.modalButtonCommon', function(e) {
    e.preventDefault();
    loadModal($(this).attr('data-href'));
});
$(document).on('click', '.close-modal', function() { $('#commonModalOverlay').removeClass('active'); });

$(document).on('submit', 'form.ajax-form', function(e) {
    e.preventDefault();
    var form = $(this);
    var btn = form.find('button[type="submit"]');
    var orig = btn.html();
    btn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);
    $.ajax({
        type: "POST", url: form.attr('data-action-url'), data: form.serialize(),
        success: function(res) {
            try {
                var obj = JSON.parse(res);
                if (obj.error == 1) { alert("Error: " + obj.msg); btn.html(orig).prop('disabled', false); }
                else { alert(obj.msg); $('#commonModalOverlay').removeClass('active'); location.reload(); }
            } catch(e) { alert("Unexpected response"); btn.html(orig).prop('disabled', false); }
        }
    });
});
</script>

<?php include 'footer.php'; ?>

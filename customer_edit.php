<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config.php";
include "function.php";

$cust_action = "create";
$cust_name = $cust_mobile = $cust_password = $cust_wallet = $cust_outstanding = $cust_id = "";
$cust_gender = $cust_dob = $cust_anniversary = "";
$segment = '';

if(isset($_REQUEST['cust_id']) && is_numeric($_REQUEST['cust_id'])){
    $cust_action = "edit";
    $sql = "SELECT * FROM `hr_customer` WHERE `cust_id`='".mysqli_real_escape_string($conn, $_REQUEST['cust_id'])."'";
    $cust = select_row($sql);
    if($cust) {
        $cust_id = $cust['cust_id'];
        $cust_name = $cust['cust_name'];
        $cust_mobile = $cust['cust_mobile'];
        $cust_wallet = $cust['cust_wallet'];
        $cust_outstanding = $cust['cust_outstanding'];
        $cust_gender = $cust['cust_gender'] ?? '';
        $cust_dob = $cust['cust_dob'] ?? '';
        $cust_anniversary = $cust['cust_anniversary'] ?? '';

        // Calculate segment
        $spend_row = select_row("SELECT COALESCE(SUM(grand_total),0) as total_spent FROM hr_invoice WHERE cust_id='$cust_id' AND delete_bill=0");
        $total_spent = (float)$spend_row['total_spent'];
        $visit_count = (int)select_row("SELECT COUNT(invoice_id) as cnt FROM hr_invoice WHERE cust_id='$cust_id' AND delete_bill=0")['cnt'];
        $last_visit_row = select_row("SELECT MAX(invoice_date) as lv FROM hr_invoice WHERE cust_id='$cust_id' AND delete_bill=0");
        $days_since = $last_visit_row['lv'] ? floor((time() - strtotime($last_visit_row['lv'])) / 86400) : 9999;
        if($total_spent >= 20000 || $visit_count >= 20) $segment = 'VIP';
        elseif($days_since > 90) $segment = 'Lapsed';
        elseif($visit_count >= 3) $segment = 'Regular';
        else $segment = 'New';
    }
}
?>

<?php
$seg_colors = ['VIP'=>['bg'=>'#fef3c7','color'=>'#92400e','icon'=>'ph-crown-simple'],'Regular'=>['bg'=>'#dcfce7','color'=>'#14532d','icon'=>'ph-check-circle'],'Lapsed'=>['bg'=>'#fee2e2','color'=>'#7f1d1d','icon'=>'ph-clock-counter-clockwise'],'New'=>['bg'=>'#e0e7ff','color'=>'#3730a3','icon'=>'ph-star']];
$seg = $seg_colors[$segment] ?? ['bg'=>'#f1f5f9','color'=>'#475569','icon'=>'ph-user'];
?>
<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <div>
        <h3 style="font-size: 18px; font-weight: 600; margin: 0;"><?= $cust_action == 'create' ? 'Add New Customer' : htmlspecialchars($cust_name) ?></h3>
        <?php if($segment): ?>
        <span style="display:inline-flex;align-items:center;gap:4px;margin-top:6px;background:<?=$seg['bg']?>;color:<?=$seg['color']?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
            <i class="ph-fill <?=$seg['icon']?>"></i> <?=$segment?>
        </span>
        <?php endif; ?>
    </div>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/customer_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $cust_action == 'create' ? 'customer_create' : 'customer_update' ?>">
    <?php if($cust_action == 'edit') echo '<input name="cust_id" type="hidden" value="'.$cust_id.'">'; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div class="form-group">
            <label>Customer Name</label>
            <input required name="cust_name" type="text" class="form-control" value="<?= htmlspecialchars($cust_name) ?>">
        </div>
        
        <div class="form-group">
            <label>Contact Number</label>
            <input required name="cust_mobile" type="text" class="form-control" value="<?= htmlspecialchars($cust_mobile) ?>">
        </div>

        <div class="form-group">
            <label>Gender</label>
            <select name="cust_gender" class="form-control">
                <option value="">-- Select --</option>
                <option value="Male" <?= $cust_gender=='Male'?'selected':'' ?>>Male</option>
                <option value="Female" <?= $cust_gender=='Female'?'selected':'' ?>>Female</option>
                <option value="Other" <?= $cust_gender=='Other'?'selected':'' ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="ph ph-cake" style="color:var(--primary);"></i> Date of Birth</label>
            <input name="cust_dob" type="date" class="form-control" value="<?= htmlspecialchars($cust_dob) ?>">
        </div>

        <div class="form-group">
            <label><i class="ph ph-heart" style="color:#e11d48;"></i> Anniversary Date</label>
            <input name="cust_anniversary" type="date" class="form-control" value="<?= htmlspecialchars($cust_anniversary) ?>">
        </div>
        
        <?php if($cust_action == 'edit'): ?>
        <div class="form-group" style="grid-column: span 2; padding: 16px; background: #e0e7ff; border-radius: 8px; border: 1px dashed #6366f1;">
            <p style="margin:0 0 12px 0; font-size: 13px; color: #4f46e5; font-weight: 600;"><i class="ph ph-shield-check"></i> Security Override</p>
            <label style="color: #4f46e5;">Provide Active Salon ID to Authorize Edit</label>
            <input required name="cust_password" type="password" class="form-control" placeholder="Enter System/Salon ID to verify" style="background: white;">
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label>Wallet Balance (₹)</label>
            <input name="cust_wallet" type="number" class="form-control" value="<?= htmlspecialchars($cust_wallet) ?>">
        </div>

        <div class="form-group">
            <label>Outstanding Debt (₹)</label>
            <input name="cust_outstanding" type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($cust_outstanding) ?>">
        </div>

    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel Configuration</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Confirm Changes</button>
    </div>
</form>

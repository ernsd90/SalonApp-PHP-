<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include "function.php";

$user_action = "create";
$exp_id = $exp_name = $exp_total = $exp_date = $exp_catId = $exp_vendor = $exp_note = "";
$payment_mode = "cash"; // default

if(isset($_REQUEST['exp_id']) && is_numeric($_REQUEST['exp_id'])){
    $user_action = "edit";
    $sql = "SELECT * FROM `hr_expenses` WHERE `exp_id`='".mysqli_real_escape_string($conn, $_REQUEST['exp_id'])."'";
    $expense = select_row($sql);
    if($expense) {
        extract($expense); 
    }
}
$salon_id = get_session_data('salon_id');
$sql2 = "SELECT * FROM `hr_expenses_category` where salon_id='".$salon_id."' ORDER BY category_name ASC";
$expenses_catNames = select_array($sql2);
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0; color: var(--text-main);"><?= $user_action == 'create' ? 'Log New Expense' : 'Edit Expense Log' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/salon_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $user_action == 'create' ? 'create_expenses' : 'update_expenses' ?>">
    <?php if($user_action == 'edit'): ?>
        <input name="exp_id" type="hidden" value="<?= $exp_id ?>">
    <?php endif; ?>
    <input name="salon_id" type="hidden" value="<?= $salon_id ?>">>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div class="form-group" style="grid-column: span 2;">
            <label>Expense Name/Title</label>
            <input required name="exp_name" type="text" class="form-control" placeholder="e.g. Utility Bill, Restock" value="<?= htmlspecialchars($exp_name) ?>">
        </div>
        
        <div class="form-group">
            <label>Amount (₹)</label>
            <input required name="exp_total" type="number" step="0.01" min="0" class="form-control" placeholder="0.00" value="<?= htmlspecialchars($exp_total) ?>">
        </div>

        <div class="form-group">
            <label>Category</label>
            <select name="exp_catId" class="form-control" style="background: white;" required>
                <option value="">Select Category</option>
                <?php foreach($expenses_catNames as $name): ?>
                    <option <?= ($name['exp_catId'] == $exp_catId) ? 'selected' : '' ?> value="<?= $name['exp_catId'] ?>"><?= htmlspecialchars($name['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Expense Date</label>
            <input required name="exp_date" type="date" class="form-control" value="<?= $user_action == 'create' ? date('Y-m-d') : date('Y-m-d', strtotime($exp_date)) ?>" style="background: white;">
        </div>

        <div class="form-group">
            <label>Payment Mode</label>
            <select name="payment_mode" class="form-control" style="background: white;" required>
                <?php
                $salon_id_for_pm = get_session_data('salon_id');
                $payment_methods = select_array("SELECT * FROM `hr_payment_methods` WHERE (`salon_id`='".$salon_id_for_pm."' OR `is_global`=1) AND `status`=1 ORDER BY `sort_order` ASC");
                if(!$payment_methods) {
                    // Fallback to defaults if custom table doesn't exist or is empty
                    $payment_methods = [
                        ['method_key'=>'cash','method_name'=>'Cash'],
                        ['method_key'=>'card','method_name'=>'Card / POS'],
                        ['method_key'=>'upi','method_name'=>'UPI / Online Transfer'],
                        ['method_key'=>'wallet','method_name'=>'Wallet Balance'],
                    ];
                    foreach($payment_methods as $m): ?>
                        <option value="<?= $m['method_key'] ?>" <?= ($payment_mode == $m['method_key']) ? 'selected' : '' ?>><?= $m['method_name'] ?></option>
                    <?php endforeach;
                } else {
                    foreach($payment_methods as $m): ?>
                        <option value="<?= htmlspecialchars($m['method_key']) ?>" <?= ($payment_mode == $m['method_key']) ? 'selected' : '' ?>><?= htmlspecialchars($m['method_name']) ?></option>
                    <?php endforeach;
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Associated Vendor</label>
            <input name="exp_vendor" type="text" class="form-control" placeholder="Optional" value="<?= htmlspecialchars($exp_vendor) ?>">
        </div>

        <div class="form-group" style="grid-column: span 2;">
            <label>Notes & Description</label>
            <textarea id="exp_note" name="exp_note" class="form-control" rows="3" placeholder="Add any details..."><?= htmlspecialchars($exp_note) ?></textarea>
        </div>

    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Save Expense</button>
    </div>
</form>

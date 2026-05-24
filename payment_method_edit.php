<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include "function.php";

$salon_id = get_session_data('salon_id');

$action = "create";
$method_name = $method_key = '';
$sort_order = 0;
$status = 1;
$is_global = 0;

if(isset($_REQUEST['method_id']) && is_numeric($_REQUEST['method_id'])){
    $action = "edit";
    $pm = select_row("SELECT * FROM `hr_payment_methods` WHERE `method_id`='".intval($_REQUEST['method_id'])."'");
    if($pm) extract($pm);
}
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0;"><?= $action == 'create' ? 'Add Payment Method' : 'Edit Payment Method' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/payment_method_ajax.php" method="post" style="padding: 24px;">
    <input name="action" type="hidden" value="<?= $action ?>">
    <?php if($action == 'edit'): ?>
    <input name="method_id" type="hidden" value="<?= $method_id ?>">
    <?php endif; ?>
    <input name="salon_id" type="hidden" value="<?= $salon_id ?>">

    <div style="display: flex; flex-direction: column; gap: 20px;">

        <div class="form-group">
            <label>Method Name <small style="color:var(--text-muted);">(Displayed to users)</small></label>
            <input required name="method_name" type="text" class="form-control" placeholder="e.g. UPI / Online Transfer" value="<?= htmlspecialchars($method_name) ?>">
        </div>

        <div class="form-group">
            <label>Method Key <small style="color:var(--text-muted);">(Short code, no spaces — used internally)</small></label>
            <input required name="method_key" type="text" class="form-control" placeholder="e.g. upi, card, paytm" pattern="[a-z0-9_]+" title="Lowercase letters, numbers, underscores only" value="<?= htmlspecialchars($method_key) ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Sort Order</label>
                <input name="sort_order" type="number" class="form-control" min="0" value="<?= intval($sort_order) ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $status == 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <?php if(is_superadmin()): ?>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_global" value="1" <?= $is_global ? 'checked' : '' ?> style="margin-right: 8px;">
                Make Global (available to all outlets)
            </label>
        </div>
        <?php endif; ?>

    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;"><?= $action == 'create' ? 'Add Method' : 'Save Changes' ?></button>
    </div>
</form>

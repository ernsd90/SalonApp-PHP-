<?php
if(session_status()===PHP_SESSION_NONE) session_start();
include "config.php"; include "function.php";
$salon_id = get_session_data('salon_id');

$action = 'create';
$vendor_id = $vendor_name = $vendor_phone = $vendor_email = $vendor_address = $vendor_gst = '';
$status = 1;

if(isset($_REQUEST['vendor_id']) && is_numeric($_REQUEST['vendor_id'])){
    $action = 'edit';
    $v = select_row("SELECT * FROM hr_vendor WHERE id='".intval($_REQUEST['vendor_id'])."'");
    if($v) extract($v, EXTR_PREFIX_ALL, 'v');
    $vendor_id      = $v['id'] ?? '';
    $vendor_name    = $v['vendor_name'] ?? '';
    $vendor_phone   = $v['vendor_phone'] ?? '';
    $vendor_email   = $v['vendor_email'] ?? '';
    $vendor_address = $v['vendor_address'] ?? '';
    $vendor_gst     = $v['vendor_gst'] ?? '';
    $status         = $v['status'] ?? 1;
}
?>
<div class="modal-header" style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid var(--border-color);">
    <h3 style="font-size:18px;font-weight:600;margin:0;"><?= $action=='create' ? 'Add Vendor' : 'Edit Vendor' ?></h3>
    <button type="button" class="close-modal" style="background:none;border:none;font-size:20px;color:var(--text-muted);cursor:pointer;"><i class="ph ph-x"></i></button>
</div>
<form class="ajax-form" data-action-url="ajax/inventory_ajax.php" method="post" style="padding:24px;">
    <input name="method" type="hidden" value="<?= $action=='create' ? 'create_vendor' : 'update_vendor' ?>">
    <?php if($action=='edit'): ?><input name="vendor_id" type="hidden" value="<?= $vendor_id ?>"><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
        <div class="form-group" style="grid-column:span 2;">
            <label>Vendor / Company Name <span style="color:var(--danger);">*</span></label>
            <input required name="vendor_name" type="text" class="form-control" placeholder="e.g. Neeraj Distributors" value="<?= htmlspecialchars($vendor_name) ?>">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input name="vendor_phone" type="text" class="form-control" placeholder="Mobile / Landline" value="<?= htmlspecialchars($vendor_phone) ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input name="vendor_email" type="email" class="form-control" placeholder="vendor@email.com" value="<?= htmlspecialchars($vendor_email) ?>">
        </div>
        <div class="form-group">
            <label>GST Number</label>
            <input name="vendor_gst" type="text" class="form-control" placeholder="15-digit GSTIN" value="<?= htmlspecialchars($vendor_gst) ?>">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1" <?= $status==1?'selected':'' ?>>Active</option>
                <option value="0" <?= $status==0?'selected':'' ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group" style="grid-column:span 2;">
            <label>Address</label>
            <textarea name="vendor_address" class="form-control" rows="2" placeholder="Full vendor address"><?= htmlspecialchars($vendor_address) ?></textarea>
        </div>
    </div>

    <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:12px;border-top:1px solid var(--border-color);padding-top:20px;">
        <button type="button" class="close-modal form-control" style="width:auto;background:white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width:auto;margin-top:0;padding:10px 24px;"><?= $action=='create'?'Save Vendor':'Update Vendor' ?></button>
    </div>
</form>

<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include "function.php";

$user_action = "create";
$service_id = $service_name = $service_price = $service_catid = $service_reminder = "";
$service_status = 1;

if(isset($_REQUEST['service_id']) && is_numeric($_REQUEST['service_id'])){
    $user_action = "edit";
    $sql = "SELECT * FROM `hr_services` WHERE `service_id`='".mysqli_real_escape_string($conn, $_REQUEST['service_id'])."'";
    $user = select_row($sql);
    if($user) {
        extract($user); 
    }
}

$salon_id = get_session_data('salon_id');
$sql2 = "SELECT * FROM `hr_servicesCategory` where salon_id='".$salon_id."' ORDER BY service_catName ASC";
$service_catNames = select_array($sql2);
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0; color: var(--text-main);"><?= $user_action == 'create' ? 'Add New Service' : 'Edit Service Configuration' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/salon_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $user_action == 'create' ? 'create_services' : 'update_services' ?>">
    <?php if($user_action == 'edit') echo '<input name="service_id" type="hidden" value="'.$service_id.'">'; ?>
    <input name="salon_id" type="hidden" value="<?= $salon_id ?>">
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div class="form-group" style="grid-column: span 2;">
            <label>Service Name</label>
            <input required name="service_name" type="text" class="form-control" placeholder="e.g. Premium Haircut" value="<?= htmlspecialchars($service_name) ?>">
        </div>
        
        <div class="form-group">
            <label>Price (₹)</label>
            <input required name="service_price" type="number" step="0.01" class="form-control" placeholder="0.00" value="<?= htmlspecialchars($service_price) ?>">
        </div>
        
        <div class="form-group">
            <label>Service Category</label>
            <select name="service_catid" class="form-control" style="background: white;">
                <option value="">Select Category</option>
                <?php foreach($service_catNames as $name): ?>
                    <option <?= ($name['service_catid'] == $service_catid) ? 'selected' : '' ?> value="<?= $name['service_catid'] ?>"><?= htmlspecialchars($name['service_catName']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Service Reminder (Days)</label>
            <input required name="service_reminder" type="number" class="form-control" placeholder="e.g. 30" value="<?= htmlspecialchars($service_reminder) ?>">
            <small style="color: var(--text-muted); font-size: 11px;">Days after which client is reminded to return.</small>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; justify-content: center;">
            <label>Status</label>
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                <input name="service_status" id="service_status" value="1" type="checkbox" style="width: 18px; height: 18px; accent-color: var(--primary);" <?= $service_status == 1 ? 'checked' : '' ?>>
                <span style="font-size: 14px; font-weight: 500; color: var(--text-main);">Active (Bookable)</span>
            </label>
        </div>

    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Save Service</button>
    </div>
</form>

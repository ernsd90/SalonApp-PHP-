<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include "function.php";

$user_action = "create";
$brand_id = $brand_name = "";
$status = 1;

if(isset($_REQUEST['brand_id']) && is_numeric($_REQUEST['brand_id'])){
    $user_action = "edit";
    $sql = "SELECT * FROM `hr_product_brand` WHERE `brand_id`='".mysqli_real_escape_string($conn, $_REQUEST['brand_id'])."'";
    $brand = select_row($sql);
    if($brand) {
        extract($brand); 
    }
}
$salon_id = get_session_data('salon_id');
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0; color: var(--text-main);"><?= $user_action == 'create' ? 'Add New Brand' : 'Edit Brand' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/salon_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $user_action == 'create' ? 'create_product_brand' : 'update_product_brand' ?>">
    <?php if($user_action == 'edit') echo '<input name="brand_id" type="hidden" value="'.$brand_id.'">'; ?>
    <input name="salon_id" type="hidden" value="<?= $salon_id ?>">
    
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <div class="form-group">
            <label>Brand Name</label>
            <input required name="brand_name" type="text" class="form-control" placeholder="e.g. L'Oreal, Olaplex" value="<?= htmlspecialchars($brand_name) ?>">
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; justify-content: center;">
            <label>Brand Status</label>
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                <input name="status" id="status" value="1" type="checkbox" style="width: 18px; height: 18px; accent-color: var(--primary);" <?= $status == 1 ? 'checked' : '' ?>>
                <span style="font-size: 14px; font-weight: 500; color: var(--text-main);">Active</span>
            </label>
        </div>
        
    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Save Brand</button>
    </div>
</form>

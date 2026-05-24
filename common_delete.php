<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include "function.php";

$form_page = "salon_form";
$item_type = "Record";

if(isset($_REQUEST['serviceCat_id']) && is_numeric($_REQUEST['serviceCat_id'])){
    $id = $_REQUEST['serviceCat_id'];
    $method = "serviceCat_delete";
    $item_type = "Service Category";
}else if(isset($_REQUEST['pkg_id']) && is_numeric($_REQUEST['pkg_id'])){
    $id = $_REQUEST['pkg_id'];
    $method = "delete_package";
    $item_type = "Package";
}else if(isset($_REQUEST['inventorybill_id']) && is_numeric($_REQUEST['inventorybill_id'])){
    $id = $_REQUEST['inventorybill_id'];
    $method = "delete_inventorybill";
    $item_type = "Inventory Bill";
}else if(isset($_REQUEST['service_id']) && is_numeric($_REQUEST['service_id'])){
    $id = $_REQUEST['service_id'];
    $method = "delete_service";
    $item_type = "Service";
}else if(isset($_REQUEST['service_catid']) && is_numeric($_REQUEST['service_catid'])){
    $id = $_REQUEST['service_catid'];
    $method = "delete_services_cat";
    $item_type = "Service Category";
}else if(isset($_REQUEST['product_id']) && is_numeric($_REQUEST['product_id'])){
    $id = $_REQUEST['product_id'];
    $method = "delete_product";
    $item_type = "Product";
}else if(isset($_REQUEST['brand_id']) && is_numeric($_REQUEST['brand_id'])){
    $id = $_REQUEST['brand_id'];
    $method = "delete_product_brand";
    $item_type = "Product Brand";
}else if(isset($_REQUEST['exp_catId']) && is_numeric($_REQUEST['exp_catId'])){
    $id = $_REQUEST['exp_catId'];
    $method = "delete_expenses_cat";
    $item_type = "Expense Category";
}else if(isset($_REQUEST['exp_id']) && is_numeric($_REQUEST['exp_id'])){
    $id = $_REQUEST['exp_id'];
    $method = "delete_expenses";
    $item_type = "Expense Log";
}else if(isset($_REQUEST['staff_id']) && is_numeric($_REQUEST['staff_id'])){
    $id = $_REQUEST['staff_id'];
    $method = "delete_staff";
    $form_page = "user_form";
    $item_type = "Staff Member";
} else {
    echo "<div style='padding:24px; text-align:center; color:var(--danger); font-weight:600;'>Invalid Request or ID Missing.</div>";
    exit;
}
?>
<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid var(--border-color); background: #fef2f2;">
    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--danger);">Delete <?= htmlspecialchars($item_type) ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--danger); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" id="<?= htmlspecialchars($form_page) ?>" method="post" data-action-url="ajax/salon_ajax.php" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= htmlspecialchars($method) ?>">
    <input name="id" type="hidden" value="<?= htmlspecialchars($id) ?>">
    
    <div style="text-align: center; padding: 16px 0;">
        <i class="ph ph-warning-circle" style="font-size: 48px; color: var(--danger); margin-bottom: 16px;"></i>
        <h4 style="font-size: 16px; font-weight: 600; color: var(--text-main); margin-bottom: 8px;">Are you sure you want to delete this <?= htmlspecialchars($item_type) ?>?</h4>
        <p style="color: var(--text-muted); font-size: 14px;">This action cannot be undone and may affect associated records.</p>
    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px; background: var(--danger); color: white;">Confirm Deletion</button>
    </div>
</form>

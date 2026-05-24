<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include "function.php";

$user_action = "create";
$product_id = $product_name = $product_price = $brand_id = "";

if(isset($_REQUEST['product_id']) && is_numeric($_REQUEST['product_id'])){
    $user_action = "edit";
    $sql = "SELECT * FROM `hr_product` WHERE `product_id`='".mysqli_real_escape_string($conn, $_REQUEST['product_id'])."'";
    $product = select_row($sql);
    if($product) {
        extract($product);
    }
}

$salon_id = get_session_data('salon_id');
$sql = "SELECT * FROM `hr_product_brand` WHERE `salon_id`='".$salon_id."' ORDER BY brand_name ASC";
$allbrand = select_array($sql);
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0; color: var(--text-main);"><?= $user_action == 'create' ? 'Add New Product' : 'Edit Product Details' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/salon_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $user_action == 'create' ? 'create_product' : 'update_product' ?>">
    <?php if($user_action == 'edit') echo '<input name="product_id" type="hidden" value="'.$product_id.'">'; ?>
    <input name="salon_id" type="hidden" value="<?= $salon_id ?>">
    <input name="product_status" id="product_status" value="1" type="hidden">
    
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <div class="form-group">
            <label>Product Brand</label>
            <select name="brand_id" class="form-control" style="background: white;" required>
                <option value="">Select a Brand</option>
                <?php foreach($allbrand as $brand): ?>
                    <option <?= ($brand['brand_id'] == $brand_id) ? 'selected' : '' ?> value="<?= $brand['brand_id'] ?>"><?= htmlspecialchars($brand['brand_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Product Name</label>
            <input required name="product_name" type="text" class="form-control" placeholder="e.g. Argan Oil Hair Serum" value="<?= htmlspecialchars($product_name) ?>">
        </div>
        
        <div class="form-group">
            <label>Retail Price (MRP ₹)</label>
            <input required name="product_price" type="number" step="0.01" min="0" class="form-control" placeholder="0.00" value="<?= htmlspecialchars($product_price) ?>">
        </div>

    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Save Product</button>
    </div>
</form>

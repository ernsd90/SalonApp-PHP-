<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include "function.php";

$user_action = "create";
$exp_catId = $category_name = "";

if(isset($_REQUEST['exp_catId']) && is_numeric($_REQUEST['exp_catId'])){
    $user_action = "edit";
    $sql = "SELECT * FROM `hr_expenses_category` WHERE `exp_catId`='".mysqli_real_escape_string($conn, $_REQUEST['exp_catId'])."'";
    $category = select_row($sql);
    if($category) {
        extract($category); 
    }
}
$salon_id = get_session_data('salon_id');
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0; color: var(--text-main);"><?= $user_action == 'create' ? 'Create New Category' : 'Edit Category' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/salon_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $user_action == 'create' ? 'create_expenses_cat' : 'update_expenses_cat' ?>">
    <?php if($user_action == 'edit') echo '<input name="exp_catId" type="hidden" value="'.$exp_catId.'">'; ?>
    <input name="salon_id" type="hidden" value="<?= $salon_id ?>">
    
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <div class="form-group">
            <label>Category Label</label>
            <input required name="category_name" type="text" class="form-control" placeholder="e.g. Toiletries, Maintenance" value="<?= htmlspecialchars($category_name) ?>">
        </div>
        
    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Save Category</button>
    </div>
</form>

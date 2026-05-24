<?php
include "config.php";

$role_action = "create";
$role_name = "";

if(isset($_REQUEST['role_id']) && is_numeric($_REQUEST['role_id'])){
    $role_action = "edit";
    $sql = "SELECT * FROM `hr_user_role` WHERE `role_id`='".mysqli_real_escape_string($conn, $_REQUEST['role_id'])."'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $role_name = $row['role_name'];
        $role_id = $row['role_id'];
    }
}
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0;"><?= $role_action == 'create' ? 'Create New Role' : 'Edit Role Name' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/user_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $role_action == 'create' ? 'create_role' : 'update_role' ?>">
    <?php if($role_action == 'edit') echo '<input name="role_id" type="hidden" value="'.$role_id.'">'; ?>
    
    <div class="form-group">
        <label>System Role Name</label>
        <input required name="role_name" type="text" class="form-control" placeholder="e.g. Senior Stylist" value="<?= htmlspecialchars($role_name) ?>">
    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Save Role</button>
    </div>
</form>

<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config.php";
include "function.php";

$session_user_type = get_session_data('user_type');
$session_salon_id = get_session_data('salon_id');

$user_action = "create";
$full_name = $username = $password = $user_mobile = "";

if(isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])){
    $user_action = "edit";
    $sql = "SELECT * FROM `hr_user` WHERE `user_id`='".mysqli_real_escape_string($conn, $_REQUEST['user_id'])."'";
    $user = select_row($sql);
    if($user) {
        $full_name = $user['full_name'];
        $username = $user['username'];
        $password = $user['password'];
        $user_mobile = $user['user_mobile'];
        $user_role_id = $user['role_id'];
        $edit_user_type = $user['user_type'];
        $salon_id = $user['salon_id'];
        $user_id = $user['user_id'];
    }
}

$roles = select_array("SELECT * FROM `hr_user_role`");
$salon_sql = "SELECT salon_id as salon_ids, salon_name, salon_address FROM `hr_salon` " . ($session_user_type == 1 ? "" : "WHERE salon_id='".$session_salon_id."'");
$all_salon = select_array($salon_sql);
?>
<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0;"><?= $user_action == 'create' ? 'Add New User' : 'Edit User' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<!-- Notice the ajax-form class, which binds to our JS handler in users.php -->
<form class="ajax-form" data-action-url="ajax/user_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $user_action == 'create' ? 'create_user' : 'update_user' ?>">
    <?php if($user_action == 'edit') echo '<input name="user_id" type="hidden" value="'.$user_id.'">'; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div class="form-group">
            <label>Full Name</label>
            <input required name="full_name" type="text" class="form-control" placeholder="John Doe" value="<?= htmlspecialchars($full_name) ?>">
        </div>
        
        <div class="form-group">
            <label>Username</label>
            <input required type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" placeholder="johndoe">
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input <?= $user_action == 'create' ? 'required' : '' ?> name="user_password" type="text" class="form-control" value="<?= htmlspecialchars($password) ?>" placeholder="Leave blank to keep current">
        </div>
        
        <div class="form-group">
            <label>Mobile Number</label>
            <input value="<?= htmlspecialchars($user_mobile) ?>" name="user_mobile" type="text" class="form-control" placeholder="+91 999 999 9999">
        </div>
        
        <div class="form-group">
            <label>Outlet Assignment</label>
            <select required name="salon_id" class="form-control" <?= ($session_user_type != 1) ? 'disabled' : '' ?>>
                <?php foreach($all_salon as $salon): ?>
                    <option value="<?= $salon['salon_ids'] ?>" <?= (isset($salon_id) && $salon_id == $salon['salon_ids']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($salon['salon_name']) ?> (<?= htmlspecialchars($salon['salon_address']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if($session_user_type != 1 && isset($salon_id)): ?>
                <input type="hidden" name="salon_id" value="<?= $salon_id ?>">
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>System Role</label>
            <select required name="role_id" class="form-control">
                <?php foreach($roles as $role): ?>
                    <option value="<?= $role['role_id'] ?>" <?= (isset($user_role_id) && $user_role_id == $role['role_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role['role_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if($session_user_type == 1): ?>
        <div class="form-group" style="grid-column: span 2;">
            <label>Admin Privilege Level</label>
            <select required name="user_type" class="form-control">
                <option value="3" <?= (isset($edit_user_type) && $edit_user_type == 3) ? 'selected' : '' ?>>Staff User</option>
                <option value="2" <?= (isset($edit_user_type) && $edit_user_type == 2) ? 'selected' : '' ?>>Outlet Admin</option>
                <option value="1" <?= (isset($edit_user_type) && $edit_user_type == 1) ? 'selected' : '' ?>>Superadmin</option>
            </select>
        </div>
        <?php endif; ?>

    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Save User</button>
    </div>
</form>

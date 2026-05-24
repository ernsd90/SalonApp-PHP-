<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config.php";
include "function.php";

$full_name = "Unknown User";
$username = "";
if(isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])){
    $sql = "SELECT * FROM `hr_user` WHERE `user_id`='".mysqli_real_escape_string($conn, $_REQUEST['user_id'])."'";
    $user = select_row($sql);
    if($user) {
        $full_name = $user['full_name'];
        $username = $user['username'];
        $user_id = $user['user_id'];
    }
}
?>
<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0; color: var(--danger);">Delete System User</h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/user_ajax.php" method="post" style="padding: 24px;">
    <input name="method" type="hidden" value="delete_user">
    <input name="user_id" type="hidden" value="<?= isset($user_id) ? $user_id : '' ?>">
    
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #fef2f2; color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px;">
            <i class="ph ph-warning"></i>
        </div>
        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Are you absolutely sure?</h4>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.5; margin: 0;">
            This will permanently delete the user <strong><?= htmlspecialchars($full_name) ?> (@<?= htmlspecialchars($username) ?>)</strong> from the system. This action cannot be undone.
        </p>
    </div>

    <div style="display: flex; justify-content: center; gap: 12px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white; padding: 10px 24px;">Cancel Request</button>
        <button type="submit" style="width: auto; padding: 10px 24px; background: var(--danger); color: white; border: none; border-radius: var(--border-radius); font-weight: 600; cursor: pointer; transition: 0.2s ease;">Yes, Delete User</button>
    </div>
</form>

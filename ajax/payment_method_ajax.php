<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$salon_id = get_session_data('salon_id');
$action = isset($_POST['action']) ? $_POST['action'] : '';
$error = 0; $msg = '';

if($action == 'create') {
    $method_name  = mysqli_real_escape_string($conn, $_POST['method_name']);
    $method_key   = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['method_key']));
    $sort_order   = intval($_POST['sort_order']);
    $status       = intval($_POST['status']);
    $is_global    = isset($_POST['is_global']) ? 1 : 0;
    $pm_salon_id  = $is_global ? 0 : $salon_id;

    // Check duplicate key
    $exists = num_rows("SELECT method_id FROM hr_payment_methods WHERE method_key='$method_key'");
    if($exists > 0) {
        $error = 1; $msg = "A payment method with this key already exists.";
    } else {
        $sql = "INSERT INTO `hr_payment_methods` SET `salon_id`='$pm_salon_id', `method_name`='$method_name', `method_key`='$method_key', `sort_order`='$sort_order', `status`='$status', `is_global`='$is_global'";
        insert_query($sql);
        $msg = "Payment method added successfully.";
    }

} elseif($action == 'edit') {
    $method_id    = intval($_POST['method_id']);
    $method_name  = mysqli_real_escape_string($conn, $_POST['method_name']);
    $method_key   = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['method_key']));
    $sort_order   = intval($_POST['sort_order']);
    $status       = intval($_POST['status']);

    $pm = select_row("SELECT * FROM hr_payment_methods WHERE method_id='$method_id'");
    if($pm['is_global'] == 1 && !is_superadmin()) {
        echo json_encode(['error' => 1, 'msg' => 'You cannot edit a global payment method.']);
        exit;
    }

    $is_global_sql = "";
    if(is_superadmin()) {
        $is_global = isset($_POST['is_global']) ? 1 : 0;
        $pm_salon_id = $is_global ? 0 : $salon_id;
        $is_global_sql = ", `is_global`='$is_global', `salon_id`='$pm_salon_id'";
    }

    $sql = "UPDATE `hr_payment_methods` SET `method_name`='$method_name', `method_key`='$method_key', `sort_order`='$sort_order', `status`='$status' $is_global_sql WHERE `method_id`='$method_id'";
    update_query($sql);
    $msg = "Payment method updated successfully.";
} else {
    $error = 1; $msg = "Invalid action.";
}

echo json_encode(['error' => $error, 'msg' => $msg]);

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$user_id  = get_session_data('user_id') ?: 0;
$salon_id = get_session_data('salon_id') ?: 0;

$module = mysqli_real_escape_string($conn, $_POST['module'] ?? 'Unknown');
$target_url = mysqli_real_escape_string($conn, $_POST['target_url'] ?? '');

if ($salon_id > 0) {
    insert_query("INSERT INTO hr_whatsapp_logs SET 
        salon_id='$salon_id', 
        user_id='$user_id', 
        module='$module', 
        message='$target_url'");
}

echo json_encode(['error'=>false]);

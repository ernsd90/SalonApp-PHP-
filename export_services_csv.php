<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE); 
if (session_status() === PHP_SESSION_NONE) session_start();

include "config.php";
include "function.php";

$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

if(!$salon_id) {
    die("Session expired. Please login again.");
}

$filename = "services_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, ['Category', 'Service Name', 'Price', 'Reminder (Days)', 'Status', 'Variations (Name:Price|Name2:Price2)']);

// Fetch services joined with categories
$query = "
    SELECT 
        c.service_catName, 
        s.service_id,
        s.service_name, 
        s.service_price, 
        s.service_reminder, 
        s.service_status 
    FROM hr_services s
    LEFT JOIN hr_servicesCategory c ON s.service_catid = c.service_catid 
    WHERE s.salon_id = '".$salon_id."'
    ORDER BY c.service_catName ASC, s.service_name ASC
";

$result = mysqli_query($conn, $query);

if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $s_id = $row['service_id'];
        
        // Fetch variations
        $var_str = '';
        $var_q = mysqli_query($conn, "SELECT var_name, var_price FROM hr_service_variations WHERE service_id='$s_id' AND salon_id='$salon_id' ORDER BY sort_order ASC, var_id ASC");
        if($var_q && mysqli_num_rows($var_q) > 0) {
            $vars_arr = [];
            while($v = mysqli_fetch_assoc($var_q)) {
                $vars_arr[] = trim($v['var_name']) . ':' . floatval($v['var_price']);
            }
            $var_str = implode('|', $vars_arr);
        }

        fputcsv($output, [
            $row['service_catName'] ? $row['service_catName'] : 'Uncategorized',
            $row['service_name'],
            $row['service_price'],
            $row['service_reminder'] ? $row['service_reminder'] : '0',
            $row['service_status'] == 1 ? 'Active' : 'Disabled',
            $var_str
        ]);
    }
}

fclose($output);
exit();
?>

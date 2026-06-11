<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE); 
if (session_status() === PHP_SESSION_NONE) session_start();

include "config.php";
include "function.php";

$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

if(!$salon_id) {
    echo json_encode(['error' => 1, 'msg' => 'Session expired. Please login again.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    
    $file = $_FILES['csv_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 1, 'msg' => 'Error uploading file.']);
        exit();
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'csv') {
        echo json_encode(['error' => 1, 'msg' => 'Invalid file format. Please upload a CSV file.']);
        exit();
    }
    
    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
        // Read header
        $header = fgetcsv($handle, 1000, ",");
        
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Expected format: Category, Service Name, Price, Reminder (Days), Status
            if (count($data) < 3) {
                $skipped++;
                continue;
            }
            
            $cat_name = trim($data[0]);
            $svc_name = trim($data[1]);
            $price = floatval($data[2]);
            $reminder = isset($data[3]) ? intval($data[3]) : 0;
            
            $status_str = isset($data[4]) ? strtolower(trim($data[4])) : 'active';
            $status = ($status_str === 'disabled' || $status_str === '0') ? 0 : 1;
            
            $variations_str = isset($data[5]) ? trim($data[5]) : '';
            
            if(empty($svc_name)) {
                $skipped++;
                continue;
            }
            
            $cat_id = 0;
            if (!empty($cat_name) && strtolower($cat_name) !== 'uncategorized') {
                $cat_res = select_row("SELECT service_catid FROM hr_servicesCategory WHERE service_catName = '".mysqli_real_escape_string($conn, $cat_name)."' AND salon_id = '$salon_id'");
                if ($cat_res) {
                    $cat_id = $cat_res['service_catid'];
                } else {
                    mysqli_query($conn, "INSERT INTO hr_servicesCategory SET service_catName = '".mysqli_real_escape_string($conn, $cat_name)."', salon_id = '$salon_id', user_id = '$user_id'");
                    $cat_id = mysqli_insert_id($conn);
                }
            }
            
            // Check if service exists
            $svc_res = select_row("SELECT service_id FROM hr_services WHERE service_name = '".mysqli_real_escape_string($conn, $svc_name)."' AND salon_id = '$salon_id'");
            
            $s_id = 0;
            if ($svc_res) {
                // Update
                $s_id = $svc_res['service_id'];
                mysqli_query($conn, "UPDATE hr_services SET service_price='$price', service_reminder='$reminder', service_status='$status', service_catid='$cat_id' WHERE service_id='$s_id'");
                $updated++;
            } else {
                // Insert
                mysqli_query($conn, "INSERT INTO hr_services SET service_name='".mysqli_real_escape_string($conn, $svc_name)."', salon_id='$salon_id', service_price='$price', service_reminder='$reminder', service_status='$status', service_catid='$cat_id', user_id='$user_id'");
                $s_id = mysqli_insert_id($conn);
                $inserted++;
            }

            // Handle Variations
            if($s_id > 0) {
                // Delete existing variations to sync cleanly
                mysqli_query($conn, "DELETE FROM hr_service_variations WHERE service_id='$s_id' AND salon_id='$salon_id'");
                
                if(!empty($variations_str)) {
                    $vars_list = explode('|', $variations_str);
                    $sort_order = 0;
                    foreach($vars_list as $v_item) {
                        $v_item = trim($v_item);
                        if(empty($v_item)) continue;
                        $parts = explode(':', $v_item);
                        if(count($parts) >= 2) {
                            $v_name = trim($parts[0]);
                            $v_price = floatval($parts[1]);
                            mysqli_query($conn, "INSERT INTO hr_service_variations SET service_id='$s_id', salon_id='$salon_id', var_name='".mysqli_real_escape_string($conn, $v_name)."', var_price='$v_price', sort_order='$sort_order'");
                            $sort_order++;
                        }
                    }
                }
            }
        }
        fclose($handle);
        
        echo json_encode(['error' => 0, 'msg' => "Import successful. Inserted: $inserted, Updated: $updated, Skipped: $skipped."]);
        exit();
    } else {
        echo json_encode(['error' => 1, 'msg' => 'Failed to read file.']);
        exit();
    }
}

echo json_encode(['error' => 1, 'msg' => 'Invalid request.']);
?>

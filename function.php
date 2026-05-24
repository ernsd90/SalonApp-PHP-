<?php
// Core Database Wrapper & Utilities for V3

function select_row($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

function select_array($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    $data = [];
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

function insert_query($sql) {
    global $conn;
    $res = mysqli_query($conn, $sql);
    if($res) {
        return mysqli_insert_id($conn);
    }
    return false;
}

function update_query($sql) {
    global $conn;
    return mysqli_query($conn, $sql);
}

function num_rows($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if($result) {
        return mysqli_num_rows($result);
    }
    return 0;
}

// Session Management
function get_session_data($key) {
    if(isset($_SESSION['userdata'])) {
        $data = json_decode($_SESSION['userdata'], true);
        if(isset($data[$key])) return $data[$key];
    }
    if(isset($_COOKIE['userdata'])) {
        $data = json_decode($_COOKIE['userdata'], true);
        if(isset($data[$key])) return $data[$key];
    }
    return null;
}

// Global User Context Variables for AJAX files
$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');
$role_id = get_session_data('role_id');
$cash_discount = 0; // Legacy default
$payment_method = ['cash' => 'Cash', 'online' => 'Online', 'cc' => 'Credit Card', 'pkg' => 'Package'];

function check_login() {
    if(!get_session_data('user_id')) {
        header("Location: login.php");
        exit;
    }
}

function is_superadmin() {
    return get_session_data('user_type') == 1;
}

function get_active_salon_id() {
    return get_session_data('salon_id');
}

// Check User Permission Matrix
function check_user_permission($type, $permission, $user_id) {
    if($user_id == 1 || $user_id == 8) return true; // Superadmin overrides
    
    $user = select_row("SELECT role_id FROM `hr_user` WHERE `user_id`='".mysqli_real_escape_string($GLOBALS['conn'], $user_id)."'");
    if($user && isset($user['role_id'])) {
        $role = select_row("SELECT role_permission FROM `hr_user_role` WHERE role_id='".mysqli_real_escape_string($GLOBALS['conn'], $user['role_id'])."'");
        if($role && isset($role['role_permission'])) {
            $perms = json_decode($role['role_permission'], true);
            if(isset($perms[$type][$permission]) && $perms[$type][$permission] == 1) {
                return true;
            }
        }
    }
    return false;
}

// Analytics and Legacy Utility Functions
function getMonthsInRange($startDate, $endDate) {
    $months = array();
    while (strtotime($startDate) <= strtotime($endDate)) {
        $last_day = date('t', strtotime($startDate));
        if(date('m', strtotime($startDate)) == date('m', strtotime($endDate))){
            $last_day = date('d', strtotime($endDate));
        }

        $months[] = array(
            'fromdate' => date('Y', strtotime($startDate))."-".date('m', strtotime($startDate))."-".date('d', strtotime($startDate)),
            'todate' => date('Y', strtotime($startDate))."-".date('m', strtotime($startDate))."-".$last_day,
        );
        $startDate = date('01 M Y', strtotime($startDate . '+ 1 month'));
    }
    return $months;
}

function get_cash_discount($all_discount,$month) {
    $cash_discount = 0;
    if(!is_array($all_discount)) return $cash_discount;
    
    $curr_month = date('m-Y',strtotime($month));
    foreach($all_discount as $single_discount){
        $discount_month = date('m-Y',strtotime($single_discount['month_discount']));
        if($curr_month == $discount_month){
             $cash_discount = $single_discount['cash_discount'];
             break;
        }
    }
    return $cash_discount;
}

/**
 * Get (or generate) a short, secure share token for an invoice.
 * Auto-creates the share_token column if it doesn't exist yet.
 *
 * @param  int    $invoice_id
 * @return string 12-char hex token
 */
function getInvoiceShareToken($invoice_id) {
    global $conn;

    // One-time column + index migration.
    // Wrapped in try/catch because some servers throw mysqli_sql_exception
    // instead of returning false when the column/index already exists.
    try { mysqli_query($conn, "ALTER TABLE `hr_invoice` ADD COLUMN `share_token` VARCHAR(16) NULL DEFAULT NULL AFTER `invoice_id`"); } catch (Exception $e) { /* column already exists — ignore */ }
    try { mysqli_query($conn, "CREATE UNIQUE INDEX idx_share_token ON `hr_invoice` (`share_token`)"); } catch (Exception $e) { /* index already exists — ignore */ }

    $safe_id = intval($invoice_id);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT share_token FROM hr_invoice WHERE invoice_id='$safe_id'"));

    if (!empty($row['share_token'])) {
        return $row['share_token'];
    }

    // Generate unique token
    do {
        $token = bin2hex(random_bytes(6)); // 12-char lowercase hex, 2^48 possibilities
        $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT invoice_id FROM hr_invoice WHERE share_token='$token'"));
    } while ($check); // retry on (extremely unlikely) collision

    mysqli_query($conn, "UPDATE hr_invoice SET share_token='$token' WHERE invoice_id='$safe_id'");
    return $token;
}


/**
 * Send customer bill notification via Make.com webhook.
 *
 * @param string $cust_name      Customer full name
 * @param string $cust_phone     Mobile number with country code (e.g. 919876543210)
 * @param float  $total_amount   Grand total amount billed
 * @param string $payment_method Payment method used (e.g. Cash, Card/UPI, Package)
 * @param string $invoice_link   Full URL to the printable invoice (or invoice ID)
 * @param string $salon_name     Name of the salon
 * @param string $webhook_url    Make.com webhook URL (configured in Outlet Settings)
 *
 * @return array  ['success' => bool, 'response' => string|null, 'error' => string|null]
 */
function sendBillToMake($cust_name, $cust_phone, $total_amount, $payment_method, $invoice_link, $salon_name = '', $webhook_url = '') {

    if (empty(trim($webhook_url))) {
        return ['success' => false, 'response' => null, 'error' => 'No webhook URL configured for this outlet.'];
    }

    $payload = [
        "cust_name"      => (string) $cust_name,
        "cust_phone"     => (string) $cust_phone,
        "total_amount"   => (string) $total_amount,
        "payment_method" => (string) $payment_method,
        "invoice_link"   => (string) $invoice_link,
        "salon_name"     => (string) $salon_name,
    ];

    $json_payload = json_encode($payload);

    // Use cURL for better error handling and timeout control
    if (function_exists('curl_init')) {
        $ch = curl_init($webhook_url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json_payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_payload),
            ],
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'response' => null, 'error' => $error];
        }
        return ['success' => ($http_code >= 200 && $http_code < 300), 'response' => $response, 'error' => null];
    }

    // Fallback: file_get_contents with stream context
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($json_payload) . "\r\n",
            'method'  => 'POST',
            'content' => $json_payload,
            'timeout' => 10,
            'ignore_errors' => true,
        ],
    ];
    $context  = stream_context_create($options);
    $response = @file_get_contents($webhook_url, false, $context);

    if ($response === false) {
        return ['success' => false, 'response' => null, 'error' => 'file_get_contents failed'];
    }
    return ['success' => true, 'response' => $response, 'error' => null];
}
?>

<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE); 
if (session_status() === PHP_SESSION_NONE) session_start();

include "../config.php";
include "../function.php";

$user_id      = get_session_data('user_id');
$salon_id     = get_session_data('salon_id');

$method = $_REQUEST["method"] ?? '';

if ($method && function_exists($method))
    echo json_encode($method());
else
    echo json_encode(['error' => 1, 'msg' => 'Method Not Found: ' . $method]);

function dashboard_metrics() {
    global $salon_id;
    $date = date('Y-m-d');
    
    // Total Revenue (Cash + CC + UPI etc, excluding packages/memberships where payment is pkg)
    $inv_total = select_row("SELECT SUM(grand_total) as val FROM `hr_invoice` WHERE salon_id='$salon_id' AND delete_bill!=1 AND payment_mode!='pkg' AND DATE(invoice_date)='$date'");
    
    // Expenses
    $exp_total = select_row("SELECT SUM(exp_total) as val FROM `hr_expenses` WHERE salon_id='$salon_id' AND DATE(exp_date)='$date'");

    // Total Customers Today
    $cust_count = select_row("SELECT count(DISTINCT cust_mob) as val FROM `hr_invoice` WHERE salon_id='$salon_id' AND delete_bill!=1 AND DATE(invoice_date)='$date'");

    // Completed Invoices Counter
    $inv_count = select_row("SELECT count(*) as val FROM `hr_invoice` WHERE salon_id='$salon_id' AND delete_bill!=1 AND DATE(invoice_date)='$date'");

    // Memberships/Packages Sold today (Recording from Invoice Table)
    $mem_pkg_sales_inv = select_row("SELECT SUM(sv.service_total_wth_gst) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND (sv.service_cat LIKE 'Membership%' OR sv.service_cat LIKE 'Package%') AND DATE(i.invoice_date)='$date'");
    
    // ADDITION: Include dedicated Membership and Package sales tables (today)
    $ext_mem = select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND cm.status!='refunded' AND DATE(mp.created_at)='$date'")['total'] ?? 0;
    $ext_pkg = select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND status!='refunded' AND purchase_date='$date'")['total'] ?? 0;

    $revenue = ($inv_total['val'] ? floatval($inv_total['val']) : 0) + $ext_mem + $ext_pkg;
    $mem_pkg_revenue = ($mem_pkg_sales_inv['val'] ? floatval($mem_pkg_sales_inv['val']) : 0) + $ext_mem + $ext_pkg;

    return [
        'error' => 0,
        'revenue' => $revenue,
        'expenses' => $exp_total['val'] ? floatval($exp_total['val']) : 0,
        'active_jobs' => $cust_count['val'] ? intval($cust_count['val']) : 0,
        'invoice_count' => $inv_count['val'] ? intval($inv_count['val']) : 0,
        'mem_pkg_revenue' => $mem_pkg_revenue
    ];
}

function dashboard_recent_invoices() {
    global $salon_id;
    $date = date('Y-m-d');
    $sql = "SELECT invoice_id, cust_name, grand_total, payment_mode, invoice_date FROM `hr_invoice` WHERE salon_id='$salon_id' AND delete_bill!=1 AND DATE(invoice_date)='$date' ORDER BY invoice_id DESC LIMIT 5";
    $recent = select_array($sql);
    
    foreach($recent as &$r) {
        $r['time'] = date('h:i A', strtotime($r['invoice_date']));
    }
    
    return ['error' => 0, 'data' => $recent ? $recent : []];
}

function dashboard_recent_expenses() {
    global $salon_id;
    $date = date('Y-m-d');
    $sql = "SELECT exp_name, exp_total, payment_mode, exp_date FROM `hr_expenses` WHERE salon_id='$salon_id' AND DATE(exp_date)='$date' ORDER BY exp_id DESC LIMIT 5";
    $recent = select_array($sql);
    return ['error' => 0, 'data' => $recent ? $recent : []];
}

function dashboard_pending_vendors() {
    global $salon_id;
    
    // Group by vendor and get those where amt_in (billed) > amt_out (paid)
    // Formula: Pending = sum(amt_in) - sum(amt_out)
    $sql = "SELECT v.vendor_name, v.id as vendor_id, (SUM(p.amt_in) - SUM(p.amt_out)) as pending_amount 
            FROM `hr_vendor_payment` as p 
            JOIN hr_vendor as v ON v.id = p.vendor_id 
            WHERE p.salon_id='$salon_id' AND p.bill_deleted!=1 
            GROUP BY p.vendor_id
            HAVING pending_amount > 0
            ORDER BY pending_amount DESC LIMIT 5";
            
    $pending = select_array($sql);
    return ['error' => 0, 'data' => $pending ? $pending : []];
}

function dashboard_staff_sales() {
    global $salon_id;
    $selected_month = $_REQUEST['selectedMonth'] ?? date('Y-m'); // Expected format 'Y-m'
    
    // We get start and end dates of the selected month
    $start_date = $selected_month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $staff = select_array("SELECT staff_id, staff_name FROM hr_staff WHERE salon_id='$salon_id' AND staff_status=1");
    
    $data = [];
    if($staff) {
        foreach($staff as $st) {
            $sid = $st['staff_id'];
            $where = " AND s.staff_id='$sid' AND DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date'";
            
            $svc = select_row("SELECT sum(ss.service_total_wth_gst) as val FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id join hr_invoice_service as ss on ss.id=s.invoice_service where i.salon_id='$salon_id' and ss.service NOT LIKE 'Outstanding%' and ss.service_cat NOT LIKE 'Product%' and ss.service_cat NOT LIKE 'Membership' and ss.service_cat NOT LIKE 'Package' and i.delete_bill!='1' and i.payment_mode!='pkg' ".$where);
            
            $prd = select_row("SELECT sum(ss.service_total_wth_gst) as val FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id join hr_invoice_service as ss on ss.id=s.invoice_service where i.salon_id='$salon_id' and i.delete_bill!='1' and ss.service_cat LIKE 'Product%' and i.payment_mode!='pkg' ".$where);
            
            $svc_val = $svc['val'] ? floatval($svc['val']) : 0;
            $prd_val = $prd['val'] ? floatval($prd['val']) : 0;
            $grand = $svc_val + $prd_val;
            
            if ($grand > 0) {
                $data[] = [
                    'staff_name' => $st['staff_name'],
                    'service' => $svc_val,
                    'product' => $prd_val,
                    'grand' => $grand
                ];
            }
        }
    }
    
    // Sort by grand total DESC
    usort($data, function($a, $b) {
        return $b['grand'] <=> $a['grand'];
    });
    
    return ['error' => 0, 'data' => $data];
}

function dashboard_whatsapp_report() {
    global $salon_id;
    $date = isset($_REQUEST['date']) ? date('Y-m-d', strtotime($_REQUEST['date'])) : date('Y-m-d');
    $month_start = date('Y-m-01', strtotime($date));
    
    // 1. Basic Stats
    $cust_count = select_row("SELECT count(DISTINCT cust_mob) as val FROM `hr_invoice` WHERE salon_id='$salon_id' AND delete_bill!=1 AND DATE(invoice_date)='$date'");
    $exp_today = select_row("SELECT SUM(exp_total) as val FROM `hr_expenses` WHERE salon_id='$salon_id' AND DATE(exp_date)='$date'");
    
    // 2. Payment Breakdown (Using hr_invoice_payment for accuracy)
    $cash_sale = select_row("SELECT SUM(p.grand_total) as val FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND p.payment_mode='cash' AND DATE(i.invoice_date)='$date'");
    $bank_sale = select_row("SELECT SUM(p.grand_total) as val FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND p.payment_mode NOT IN ('cash', 'pkg', 'wallet', 'package') AND DATE(i.invoice_date)='$date'");
    
    // 3. Category Breakdown (Proportional Net Amount)
    // Formula: (Line Item Total / Invoice Total Items) * (Total Revenue-based Payments for that invoice)
    // This handles mixed bills, global discounts, and split payments (Card + Package) correctly.
    
    $revenue_payment_subquery = "(SELECT SUM(grand_total) FROM hr_invoice_payment WHERE invoice_id=i.invoice_id AND payment_mode NOT IN ('pkg', 'wallet', 'package'))";
    $common_subquery = " (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * $revenue_payment_subquery ";
    
    $service_sale = select_row("SELECT SUM($common_subquery) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND sv.service_cat NOT LIKE 'Product%' AND sv.service_cat NOT LIKE 'Membership%' AND sv.service_cat NOT LIKE 'Package%' AND DATE(i.invoice_date)='$date'");
    
    $product_sale = select_row("SELECT SUM($common_subquery) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND sv.service_cat LIKE 'Product%' AND DATE(i.invoice_date)='$date'");
    
    $mem_sale = select_row("SELECT SUM($common_subquery) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND sv.service_cat LIKE 'Membership%' AND DATE(i.invoice_date)='$date'");
    
    $pkg_sale = select_row("SELECT SUM($common_subquery) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND sv.service_cat LIKE 'Package%' AND DATE(i.invoice_date)='$date'");
    
    // Redemption (specifically paid via pkg/wallet)
    $redemption = select_row("SELECT SUM(p.grand_total) as val FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND (p.payment_mode='pkg' OR p.payment_mode='wallet' OR p.payment_mode='package') AND DATE(i.invoice_date)='$date'");

    // 4. MTD (Month-to-Date) - Using JOINs for accuracy
    $sale_mtd_res = select_row("SELECT SUM(p.grand_total) as val FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND p.payment_mode NOT IN ('pkg', 'wallet', 'package') AND (DATE(i.invoice_date) BETWEEN '$month_start' AND '$date')");
    $ext_mem_cash = select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND mp.payment_mode='cash' AND cm.status!='refunded' AND DATE(mp.created_at)='$date'")['total'] ?? 0;
    $ext_mem_cc = select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND mp.payment_mode!='cash' AND cm.status!='refunded' AND DATE(mp.created_at)='$date'")['total'] ?? 0;
    
    $ext_pkg_cash = select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND payment_mode='cash' AND status!='refunded' AND purchase_date='$date'")['total'] ?? 0;
    $ext_pkg_cc = select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND payment_mode!='cash' AND status!='refunded' AND purchase_date='$date'")['total'] ?? 0;

    $cash_sale_val = ($cash_sale['val'] ?: 0) + $ext_mem_cash + $ext_pkg_cash;
    $bank_sale_val = ($bank_sale['val'] ?: 0) + $ext_mem_cc + $ext_pkg_cc;
    $mem_sale_val = ($mem_sale['val'] ?: 0) + ($ext_mem_cash + $ext_mem_cc);
    $pkg_sale_val = ($pkg_sale['val'] ?: 0) + ($ext_pkg_cash + $ext_pkg_cc);

    $mtd_mem = select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND cm.status!='refunded' AND (DATE(mp.created_at) BETWEEN '$month_start' AND '$date')")['total'] ?? 0;
    $mtd_pkg = select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND status!='refunded' AND (purchase_date BETWEEN '$month_start' AND '$date')")['total'] ?? 0;

    $sale_mtd_total = ($sale_mtd_res['val'] ?: 0) + $mtd_mem + $mtd_pkg;
    $exp_mtd_val = select_row("SELECT SUM(exp_total) as val FROM `hr_expenses` WHERE salon_id='$salon_id' AND (DATE(exp_date) BETWEEN '$month_start' AND '$date')")['val'] ?: 0;

    // Fetch Owner numbers
    $owners = select_array("SELECT mobile_no FROM hr_user_owner WHERE salon_id='$salon_id' AND is_active=1");
    $owner_mobs = [];
    if($owners) {
        foreach($owners as $o) $owner_mobs[] = $o['mobile_no'];
    }

    return [
        'error' => 0,
        'date' => date('d-m-Y', strtotime($date)),
        'total_client' => $cust_count['val'] ?: 0,
        'total_exp' => $exp_today['val'] ?: 0,
        'cash_sale' => $cash_sale_val,
        'bank_sale' => $bank_sale_val,
        'total_sale' => $cash_sale_val + $bank_sale_val,
        'service_sale' => $service_sale['val'] ?: 0,
        'product_sale' => $product_sale['val'] ?: 0,
        'membership_sale' => $mem_sale_val,
        'package_sale' => $pkg_sale_val,
        'redemption' => $redemption['val'] ?: 0,
        'exp_mtd' => $exp_mtd_val,
        'sale_mtd' => $sale_mtd_total,
        'owners' => $owner_mobs
    ];
}

function dashboard_whatsapp_monthly_report() {
    global $salon_id;
    $month = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    
    // 1. Basic Stats
    $cust_count = select_row("SELECT count(DISTINCT cust_mob) as val FROM `hr_invoice` WHERE salon_id='$salon_id' AND delete_bill!=1 AND (DATE(invoice_date) BETWEEN '$start_date' AND '$end_date')");
    $exp_today = select_row("SELECT SUM(exp_total) as val FROM `hr_expenses` WHERE salon_id='$salon_id' AND (DATE(exp_date) BETWEEN '$start_date' AND '$end_date')");
    
    // 2. Payment Breakdown
    $cash_sale = select_row("SELECT SUM(p.grand_total) as val FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND p.payment_mode='cash' AND (DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date')");
    $bank_sale = select_row("SELECT SUM(p.grand_total) as val FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND p.payment_mode NOT IN ('cash', 'pkg', 'wallet', 'package') AND (DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date')");
    
    // 3. Category Breakdown
    $revenue_payment_subquery = "(SELECT SUM(grand_total) FROM hr_invoice_payment WHERE invoice_id=i.invoice_id AND payment_mode NOT IN ('pkg', 'wallet', 'package'))";
    $common_subquery = " (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * $revenue_payment_subquery ";
    
    $service_sale = select_row("SELECT SUM($common_subquery) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND sv.service_cat NOT LIKE 'Product%' AND sv.service_cat NOT LIKE 'Membership%' AND sv.service_cat NOT LIKE 'Package%' AND (DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date')");
    
    $product_sale = select_row("SELECT SUM($common_subquery) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND sv.service_cat LIKE 'Product%' AND (DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date')");
    
    $mem_sale = select_row("SELECT SUM($common_subquery) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND sv.service_cat LIKE 'Membership%' AND (DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date')");
    
    $pkg_sale = select_row("SELECT SUM($common_subquery) as val FROM hr_invoice i JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND sv.service_cat LIKE 'Package%' AND (DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date')");
    
    // Redemption
    $redemption = select_row("SELECT SUM(p.grand_total) as val FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND (p.payment_mode='pkg' OR p.payment_mode='wallet' OR p.payment_mode='package') AND (DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date')");

    // External Packages and Memberships
    $ext_mem_cash = select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND mp.payment_mode='cash' AND cm.status!='refunded' AND (DATE(mp.created_at) BETWEEN '$start_date' AND '$end_date')")['total'] ?? 0;
    $ext_mem_cc = select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND mp.payment_mode!='cash' AND cm.status!='refunded' AND (DATE(mp.created_at) BETWEEN '$start_date' AND '$end_date')")['total'] ?? 0;
    
    $ext_pkg_cash = select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND payment_mode='cash' AND status!='refunded' AND (purchase_date BETWEEN '$start_date' AND '$end_date')")['total'] ?? 0;
    $ext_pkg_cc = select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND payment_mode!='cash' AND status!='refunded' AND (purchase_date BETWEEN '$start_date' AND '$end_date')")['total'] ?? 0;

    $cash_sale_val = ($cash_sale['val'] ?: 0) + $ext_mem_cash + $ext_pkg_cash;
    $bank_sale_val = ($bank_sale['val'] ?: 0) + $ext_mem_cc + $ext_pkg_cc;
    $mem_sale_val = ($mem_sale['val'] ?: 0) + ($ext_mem_cash + $ext_mem_cc);
    $pkg_sale_val = ($pkg_sale['val'] ?: 0) + ($ext_pkg_cash + $ext_pkg_cc);

    $total_sale_total = $cash_sale_val + $bank_sale_val;

    // Fetch Owner numbers
    $owners = select_array("SELECT mobile_no FROM hr_user_owner WHERE salon_id='$salon_id' AND is_active=1");
    $owner_mobs = [];
    if($owners) {
        foreach($owners as $o) $owner_mobs[] = $o['mobile_no'];
    }

    return [
        'error' => 0,
        'date' => date('F Y', strtotime($start_date)),
        'total_client' => $cust_count['val'] ?: 0,
        'total_exp' => $exp_today['val'] ?: 0,
        'cash_sale' => $cash_sale_val,
        'bank_sale' => $bank_sale_val,
        'total_sale' => $total_sale_total,
        'service_sale' => $service_sale['val'] ?: 0,
        'product_sale' => $product_sale['val'] ?: 0,
        'membership_sale' => $mem_sale_val,
        'package_sale' => $pkg_sale_val,
        'redemption' => $redemption['val'] ?: 0,
        'owners' => $owner_mobs
    ];
}

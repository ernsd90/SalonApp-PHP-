<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

// Public access — validated by staff_id + mob instead of session
$staff_id = intval($_REQUEST['staff_id'] ?? 0);
$mob      = trim($_REQUEST['mob'] ?? '');

if ($staff_id < 1 || empty($mob)) {
    echo json_encode(['error' => 1, 'msg' => 'Invalid parameters']);
    exit;
}

// Validate staff exists with matching mobile — also fetch multiplier + components from salon
$staff = select_row("SELECT s.*, sl.salon_name, sl.logo, sl.staff_global_target,
    COALESCE(sl.staff_target_multiplier, 5) as staff_target_multiplier,
    COALESCE(sl.staff_target_components, 'services,redemptions,packages,memberships,products') as staff_target_components
    FROM hr_staff s 
    JOIN hr_salon sl ON sl.salon_id = s.salon_id 
    WHERE s.staff_id='$staff_id' AND s.staff_mob='".mysqli_real_escape_string($conn, $mob)."'");

if (!$staff) {
    echo json_encode(['error' => 1, 'msg' => 'Staff not found']);
    exit;
}

$salon_id = $staff['salon_id'];

$method = $_REQUEST['method'] ?? '';
if ($method && function_exists($method)) {
    echo json_encode($method());
} else {
    echo json_encode(['error' => 1, 'msg' => 'Method not found']);
}

// ─── Helper: get date range from filter ────────────────────────────────────
function get_date_range() {
    $filter = $_REQUEST['filter'] ?? 'this_month';
    $custom_month = $_REQUEST['custom_month'] ?? date('Y-m');

    switch ($filter) {
        case 'today':
            return [date('Y-m-d'), date('Y-m-d')];
        case 'yesterday':
            $y = date('Y-m-d', strtotime('-1 day'));
            return [$y, $y];
        case 'last_7':
            return [date('Y-m-d', strtotime('-6 days')), date('Y-m-d')];
        case 'this_month':
            return [date('Y-m-01'), date('Y-m-d')];
        case 'last_month':
            return [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))];
        case 'custom_month':
            $parts = explode('-', $custom_month);
            $y = intval($parts[0] ?? date('Y'));
            $m = intval($parts[1] ?? date('m'));
            $from = sprintf('%04d-%02d-01', $y, $m);
            $to   = date('Y-m-t', strtotime($from));
            return [$from, $to];
        default:
            return [date('Y-m-01'), date('Y-m-d')];
    }
}

// ─── get_staff_info ────────────────────────────────────────────────────────
function get_staff_info() {
    global $staff, $staff_id;
    $joining = '';
    if (!empty($staff['joining_date']) && $staff['joining_date'] != '0000-00-00') {
        $joining = date('d M Y', strtotime($staff['joining_date']));
    }
    return [
        'error'              => 0,
        'staff_name'         => $staff['staff_name'],
        'staff_role'         => $staff['staff_role'] ?? '',
        'department'         => $staff['department'] ?? '',
        'seniority'          => $staff['seniority'] ?? '',
        'joining_date'       => $joining,
        'staff_mob'          => $staff['staff_mob'],
        'salon_name'         => $staff['salon_name'],
        'notify_daily_sale'  => intval($staff['notify_daily_sale']),
        'staff_salary'       => floatval($staff['staff_salary']),
        'multiplier'         => floatval($staff['staff_target_multiplier']),
        'target'             => floatval($staff['staff_salary']) * floatval($staff['staff_target_multiplier']),
    ];
}

// ─── get_staff_kpis ────────────────────────────────────────────────────────
function get_staff_kpis() {
    global $staff_id, $salon_id, $staff;
    [$from, $to] = get_date_range();

    // Total revenue for staff in period
    $rev_row = select_row("SELECT SUM(s.staff_work_price) as rev, COUNT(DISTINCT i.invoice_id) as invoices
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id'
          AND i.delete_bill!=1
          AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");

    $total_rev  = floatval($rev_row['rev'] ?? 0);
    $invoices   = intval($rev_row['invoices'] ?? 0);

    // Unique clients
    $clients_row = select_row("SELECT COUNT(DISTINCT i.cust_id) as cnt
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id'
          AND i.delete_bill!=1
          AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $unique_clients = intval($clients_row['cnt'] ?? 0);

    // Repeat clients (same staff, same period, visited > 1 time)
    $repeat_row = select_array("SELECT i.cust_id, COUNT(DISTINCT i.invoice_id) as visits
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id'
          AND i.delete_bill!=1
          AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'
        GROUP BY i.cust_id
        HAVING visits > 1");
    $repeat_clients = count($repeat_row);
    $new_clients    = max(0, $unique_clients - $repeat_clients);

    // Target = multiplier × this staff's salary
    $multiplier    = floatval($staff['staff_target_multiplier']);
    $staff_salary  = floatval($staff['staff_salary']);
    $monthly_target = $staff_salary * $multiplier;

    // Compute target_revenue = only selected components
    $components = array_map('trim', explode(',', $staff['staff_target_components']));
    $target_revenue = 0;

    if (in_array('services', $components) || in_array('redemptions', $components)) {
        // Services = non-wallet/pkg direct; Redemptions = wallet/pkg
        $svc_rdm = select_row("SELECT
            COALESCE(SUM(IF(
                (p.pm IS NULL OR p.pm NOT IN ('wallet','pkg')) AND i.payment_mode NOT IN ('wallet','pkg') AND (isrv.pkg_id IS NULL OR isrv.pkg_id=0),
                s.total_amt, 0
            )),0) as svc_rev,
            COALESCE(SUM(IF(
                p.pm IN ('wallet','pkg') OR isrv.pkg_id>0 OR i.payment_mode IN ('wallet','pkg'),
                s.total_amt, 0
            )),0) as rdm_rev
            FROM hr_invoice_staff s
            JOIN hr_invoice i ON i.invoice_id=s.invoice_id
            JOIN hr_invoice_service isrv ON isrv.id=s.invoice_service
            LEFT JOIN (SELECT invoice_id, MAX(payment_mode) as pm FROM hr_invoice_payment WHERE payment_mode IN ('wallet','pkg') GROUP BY invoice_id) p ON p.invoice_id=i.invoice_id
            WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1
              AND LOWER(isrv.service_cat) NOT LIKE '%product%'
              AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
        if (in_array('services', $components))    $target_revenue += floatval($svc_rdm['svc_rev'] ?? 0);
        if (in_array('redemptions', $components)) $target_revenue += floatval($svc_rdm['rdm_rev'] ?? 0);
    }
    if (in_array('products', $components)) {
        $prd = select_row("SELECT COALESCE(SUM(s.total_amt),0) as rev FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id JOIN hr_invoice_service isrv ON isrv.id=s.invoice_service WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND LOWER(isrv.service_cat) LIKE '%product%' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
        $target_revenue += floatval($prd['rev'] ?? 0);
    }
    if (in_array('packages', $components)) {
        $pkg = select_row("SELECT COALESCE(SUM(purchase_price),0) as rev FROM hr_customer_packages WHERE salon_id='$salon_id' AND sold_by='$staff_id' AND status!='refunded' AND purchase_date BETWEEN '$from' AND '$to'");
        $target_revenue += floatval($pkg['rev'] ?? 0);
    }
    if (in_array('memberships', $components)) {
        $mem = select_row("SELECT COALESCE(SUM(mp.amount),0) as rev FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id=mp.cm_id WHERE mp.salon_id='$salon_id' AND cm.sold_by='$staff_id' AND cm.status!='refunded' AND DATE(mp.created_at) BETWEEN '$from' AND '$to'");
        $target_revenue += floatval($mem['rev'] ?? 0);
    }

    $target_pct = $monthly_target > 0 ? min(round(($target_revenue / $monthly_target) * 100, 1), 999) : 0;

    // Services count
    $svc_row = select_row("SELECT COUNT(s.id) as cnt FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $services_count = intval($svc_row['cnt'] ?? 0);

    return [
        'error'          => 0,
        'total_revenue'  => $total_rev,
        'target_revenue' => $target_revenue,
        'monthly_target' => $monthly_target,
        'multiplier'     => $multiplier,
        'staff_salary'   => $staff_salary,
        'components'     => $components,
        'target_pct'     => $target_pct,
        'unique_clients' => $unique_clients,
        'repeat_clients' => $repeat_clients,
        'new_clients'    => $new_clients,
        'total_invoices' => $invoices,
        'services_count' => $services_count,
        'from'           => $from,
        'to'             => $to,
    ];
}

// ─── get_staff_comparison ──────────────────────────────────────────────────
function get_staff_comparison() {
    global $staff_id, $salon_id;
    [$from, $to] = get_date_range();

    // Current period revenue
    $cur = select_row("SELECT COALESCE(SUM(s.staff_work_price),0) as rev FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $cur_rev = floatval($cur['rev']);

    // Previous same-length period
    $days = max(1, (strtotime($to) - strtotime($from)) / 86400 + 1);
    $prev_to   = date('Y-m-d', strtotime($from) - 86400);
    $prev_from = date('Y-m-d', strtotime($prev_to) - ($days - 1) * 86400);

    $prev = select_row("SELECT COALESCE(SUM(s.staff_work_price),0) as rev FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND DATE(i.invoice_date) BETWEEN '$prev_from' AND '$prev_to'");
    $prev_rev = floatval($prev['rev']);

    $change_pct = 0;
    $direction  = 'neutral';
    if ($prev_rev > 0) {
        $change_pct = round((($cur_rev - $prev_rev) / $prev_rev) * 100, 1);
        $direction  = $change_pct >= 0 ? 'up' : 'down';
    } elseif ($cur_rev > 0) {
        $change_pct = 100;
        $direction  = 'up';
    }

    // Last month full data
    $lm_from = date('Y-m-01', strtotime('first day of last month'));
    $lm_to   = date('Y-m-t',  strtotime('last day of last month'));
    $lm = select_row("SELECT COALESCE(SUM(s.staff_work_price),0) as rev FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND DATE(i.invoice_date) BETWEEN '$lm_from' AND '$lm_to'");
    $lm_rev = floatval($lm['rev']);

    return [
        'error'       => 0,
        'current'     => $cur_rev,
        'previous'    => $prev_rev,
        'prev_from'   => $prev_from,
        'prev_to'     => $prev_to,
        'change_pct'  => abs($change_pct),
        'direction'   => $direction,
        'last_month'  => $lm_rev,
        'lm_from'     => $lm_from,
        'lm_to'       => $lm_to,
    ];
}

// ─── get_staff_comprehensive ───────────────────────────────────────────────
function get_staff_comprehensive() {
    global $staff_id, $salon_id;
    [$from, $to] = get_date_range();

    // Unique clients
    $clients_row = select_row("SELECT COUNT(DISTINCT i.cust_id) as cnt FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $clients = intval($clients_row['cnt'] ?? 0);

    // Services Revenue (direct, not wallet/pkg)
    $svc = select_row("SELECT COALESCE(SUM(s.total_amt),0) as rev FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id JOIN hr_invoice_service isrv ON isrv.id=s.invoice_service LEFT JOIN (SELECT invoice_id, MAX(payment_mode) as pm FROM hr_invoice_payment WHERE payment_mode IN ('wallet','pkg') GROUP BY invoice_id) p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND (p.pm IS NULL OR p.pm NOT IN ('wallet','pkg')) AND i.payment_mode NOT IN ('wallet','pkg') AND LOWER(isrv.service_cat) NOT LIKE '%product%' AND LOWER(isrv.service_cat) NOT LIKE '%package%' AND LOWER(isrv.service_cat) NOT LIKE '%membership%' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $services_rev = floatval($svc['rev']);

    // Redemptions
    $rdm = select_row("SELECT COALESCE(SUM(s.total_amt),0) as rev FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id JOIN hr_invoice_service isrv ON isrv.id=s.invoice_service LEFT JOIN (SELECT invoice_id, MAX(payment_mode) as pm FROM hr_invoice_payment WHERE payment_mode IN ('wallet','pkg') GROUP BY invoice_id) p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND (p.pm IN ('wallet','pkg') OR isrv.pkg_id>0 OR i.payment_mode IN ('wallet','pkg')) AND LOWER(isrv.service_cat) NOT LIKE '%product%' AND LOWER(isrv.service_cat) NOT LIKE '%package%' AND LOWER(isrv.service_cat) NOT LIKE '%membership%' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $redemptions = floatval($rdm['rev']);

    // Products
    $prd = select_row("SELECT COALESCE(SUM(s.total_amt),0) as rev FROM hr_invoice_staff s JOIN hr_invoice i ON i.invoice_id=s.invoice_id JOIN hr_invoice_service isrv ON isrv.id=s.invoice_service WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1 AND LOWER(isrv.service_cat) LIKE '%product%' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $products_sold = floatval($prd['rev']);

    // Packages sold by this staff
    $pkg = select_row("SELECT COALESCE(SUM(purchase_price),0) as rev FROM hr_customer_packages WHERE salon_id='$salon_id' AND sold_by='$staff_id' AND status!='refunded' AND purchase_date BETWEEN '$from' AND '$to'");
    $packages_sold = floatval($pkg['rev']);

    // Memberships sold
    $mem = select_row("SELECT COALESCE(SUM(mp.amount),0) as rev FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id=mp.cm_id WHERE mp.salon_id='$salon_id' AND cm.sold_by='$staff_id' AND cm.status!='refunded' AND DATE(mp.created_at) BETWEEN '$from' AND '$to'");
    $memberships_sold = floatval($mem['rev']);

    $total = $services_rev + $redemptions + $products_sold + $packages_sold + $memberships_sold;

    return [
        'error'            => 0,
        'clients'          => $clients,
        'services_rev'     => $services_rev,
        'redemptions'      => $redemptions,
        'products_sold'    => $products_sold,
        'packages_sold'    => $packages_sold,
        'memberships_sold' => $memberships_sold,
        'total_generated'  => $total,
    ];
}

// ─── get_staff_service_breakdown ──────────────────────────────────────────
function get_staff_service_breakdown() {
    global $staff_id, $salon_id;
    [$from, $to] = get_date_range();

    $rows = select_array("SELECT isrv.service as service_name,
        COUNT(s.id) as total_count,
        COALESCE(SUM(s.total_amt),0) as total_revenue,
        SUM(IF(p.pm IN ('wallet','pkg') OR isrv.pkg_id>0 OR i.payment_mode IN ('wallet','pkg'),1,0)) as redemp_count,
        COALESCE(SUM(IF(p.pm IN ('wallet','pkg') OR isrv.pkg_id>0 OR i.payment_mode IN ('wallet','pkg'), s.total_amt, 0)),0) as redemp_revenue,
        SUM(IF((p.pm IS NULL OR p.pm NOT IN ('wallet','pkg')) AND (isrv.pkg_id IS NULL OR isrv.pkg_id=0) AND i.payment_mode NOT IN ('wallet','pkg'),1,0)) as other_count,
        COALESCE(SUM(IF((p.pm IS NULL OR p.pm NOT IN ('wallet','pkg')) AND (isrv.pkg_id IS NULL OR isrv.pkg_id=0) AND i.payment_mode NOT IN ('wallet','pkg'), s.total_amt, 0)),0) as other_revenue
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id=s.invoice_id
        JOIN hr_invoice_service isrv ON isrv.id=s.invoice_service
        LEFT JOIN (SELECT invoice_id, MAX(payment_mode) as pm FROM hr_invoice_payment WHERE payment_mode IN ('wallet','pkg') GROUP BY invoice_id) p ON p.invoice_id=i.invoice_id
        WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1
          AND isrv.service != '' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'
        GROUP BY isrv.service
        ORDER BY total_revenue DESC");

    return ['error' => 0, 'breakdown' => $rows ?: []];
}

// ─── get_staff_repeat_analysis ─────────────────────────────────────────────
function get_staff_repeat_analysis() {
    global $staff_id, $salon_id;
    [$from, $to] = get_date_range();

    $visits = select_array("SELECT i.cust_id, c.Cust_name as cust_name, COUNT(DISTINCT i.invoice_id) as visits
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id=s.invoice_id
        LEFT JOIN hr_customer c ON c.Cust_id=i.cust_id
        WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1
          AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'
        GROUP BY i.cust_id
        ORDER BY visits DESC");

    $total_unique = count($visits);
    $repeats = array_filter($visits, fn($v) => intval($v['visits']) > 1);
    $repeat_count = count($repeats);
    $new_count = $total_unique - $repeat_count;
    $repeat_rate = $total_unique > 0 ? round(($repeat_count / $total_unique) * 100, 1) : 0;

    return [
        'error'        => 0,
        'total_unique' => $total_unique,
        'repeat_count' => $repeat_count,
        'new_count'    => $new_count,
        'repeat_rate'  => $repeat_rate,
        'top_repeats'  => array_slice(array_values($repeats), 0, 10),
    ];
}

// ─── get_staff_daily_billing ──────────────────────────────────────────────
function get_staff_daily_billing() {
    global $staff_id, $salon_id, $conn;
    [$from, $to] = get_date_range();

    $rows = select_array("SELECT 
        DATE(i.invoice_date) as bill_date,
        i.invoice_id,
        COALESCE(c.Cust_name, i.cust_name) as cust_name,
        COALESCE(c.Cust_mobile, '') as cust_mobile,
        i.payment_mode,
        i.grand_total,
        GROUP_CONCAT(DISTINCT isrv.service ORDER BY isrv.id SEPARATOR ', ') as services
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id=s.invoice_id
        LEFT JOIN hr_customer c ON c.Cust_id=i.cust_id
        JOIN hr_invoice_service isrv ON isrv.invoice_id=i.invoice_id AND isrv.service != ''
        WHERE i.salon_id='$salon_id' AND s.staff_id='$staff_id' AND i.delete_bill!=1
          AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'
        GROUP BY i.invoice_id
        ORDER BY i.invoice_date DESC");

    $payment_labels = [
        'cash'   => 'Cash',
        'cc'     => 'Card/UPI',
        'pkg'    => 'Package',
        'wallet' => 'Wallet',
        'split'  => 'Part Payment',
    ];

    $result = [];
    foreach ($rows as $r) {
        $mob = $r['cust_mobile'];
        if (strlen($mob) >= 4) {
            $masked = substr($mob, 0, 2) . str_repeat('X', max(0, strlen($mob) - 4)) . substr($mob, -2);
        } else {
            $masked = str_repeat('X', strlen($mob));
        }

        $result[] = [
            'bill_date'    => date('d M Y', strtotime($r['bill_date'])),
            'invoice_id'   => $r['invoice_id'],
            'cust_name'    => $r['cust_name'] ?: 'Walk-in',
            'cust_mobile'  => $masked,
            'payment_mode' => $payment_labels[$r['payment_mode']] ?? ucfirst($r['payment_mode']),
            'grand_total'  => floatval($r['grand_total']),
            'services'     => $r['services'],
        ];
    }

    return ['error' => 0, 'bills' => $result];
}

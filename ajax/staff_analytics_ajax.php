<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

$method = $_REQUEST["method"] ?? '';

if ($method && function_exists($method)) {
    echo json_encode($method());
} else {
    echo json_encode(['error' => 1, 'msg' => 'Method not found']);
}

// Helper function to build staff filter SQL
function get_staff_filter_sql() {
    global $salon_id;
    $dept   = $_REQUEST['department'] ?? '';
    $role   = $_REQUEST['staff_role'] ?? '';
    $gender = $_REQUEST['gender'] ?? '';
    $senior = $_REQUEST['seniority'] ?? '';

    $sql = "SELECT staff_id, staff_name, staff_salary FROM hr_staff WHERE salon_id='$salon_id' AND staff_status=1";
    if (!empty($dept))   $sql .= " AND department='" . mysqli_real_escape_string($GLOBALS['conn'], $dept) . "'";
    if (!empty($role))   $sql .= " AND staff_role='" . mysqli_real_escape_string($GLOBALS['conn'], $role) . "'";
    if (!empty($gender)) $sql .= " AND gender='" . mysqli_real_escape_string($GLOBALS['conn'], $gender) . "'";
    if (!empty($senior)) $sql .= " AND seniority='" . mysqli_real_escape_string($GLOBALS['conn'], $senior) . "'";

    $staff = select_array($sql);
    $staff_ids = !empty($staff) ? array_column($staff, 'staff_id') : [0];
    
    return [
        'staff' => $staff,
        'sql'   => "AND s.staff_id IN (" . implode(',', $staff_ids) . ")"
    ];
}

function get_dashboard_kpis() {
    global $salon_id;
    $from = !empty($_REQUEST['from_date']) ? date('Y-m-d', strtotime($_REQUEST['from_date'])) : date('Y-m-01');
    $to   = !empty($_REQUEST['to_date'])   ? date('Y-m-d', strtotime($_REQUEST['to_date']))   : date('Y-m-d');
    
    $filter_data = get_staff_filter_sql();
    $staff_list = $filter_data['staff'];
    $staff_sql = $filter_data['sql'];

    $total_staff = count($staff_list);
    
    // Total Revenue by Staff
    $revenue_data = select_array("SELECT s.staff_id, st.staff_name, COUNT(s.invoice_id) as total_services, SUM(s.staff_work_price) as revenue 
        FROM hr_invoice_staff s 
        JOIN hr_staff st ON st.staff_id=s.staff_id
        WHERE s.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59' $staff_sql 
        GROUP BY s.staff_id 
        ORDER BY revenue DESC");

    $top_performer = !empty($revenue_data) ? $revenue_data[0]['staff_name'] : 'N/A';
    $highest_revenue = !empty($revenue_data) ? floatval($revenue_data[0]['revenue']) : 0;
    
    // Sort by bookings
    $booked_data = $revenue_data;
    usort($booked_data, function($a, $b) { return $b['total_services'] - $a['total_services']; });
    $most_booked = !empty($booked_data) ? $booked_data[0]['staff_name'] : 'N/A';

    // Target Calculation (configurable multiplier × salary, filtered by components)
    $target_row = select_row("SELECT COALESCE(staff_target_multiplier, 5) as m, COALESCE(staff_target_components, 'services,redemptions,packages,memberships,products') as c FROM hr_salon WHERE salon_id='$salon_id'");
    $target_multiplier  = $target_row ? floatval($target_row['m']) : 5;
    if ($target_multiplier <= 0) $target_multiplier = 5;
    $components = $target_row ? array_map('trim', explode(',', $target_row['c'])) : ['services','redemptions','packages','memberships','products'];

    $total_salary   = array_sum(array_column($staff_list, 'staff_salary'));
    $monthly_target = $total_salary * $target_multiplier;

    // Build target_generated from selected components only
    // Get date range for this call (use same dates as revenue_data was fetched with)
    $from = $_REQUEST['from'] ?? date('Y-m-01');
    $to   = $_REQUEST['to']   ?? date('Y-m-d');
    $target_generated = 0;

    if (in_array('services', $components) || in_array('redemptions', $components) || in_array('products', $components)) {
        $staff_ids_str = implode(',', array_map('intval', array_column($staff_list, 'staff_id')));
        if ($staff_ids_str) {
            $rows = select_row("SELECT
                COALESCE(SUM(IF((p.pm IS NULL OR p.pm NOT IN ('wallet','pkg')) AND i.payment_mode NOT IN ('wallet','pkg') AND (isrv.pkg_id IS NULL OR isrv.pkg_id=0) AND LOWER(isrv.service_cat) NOT LIKE '%product%', s.total_amt, 0)),0) as svc_rev,
                COALESCE(SUM(IF(p.pm IN ('wallet','pkg') OR isrv.pkg_id>0 OR i.payment_mode IN ('wallet','pkg'), s.total_amt, 0)),0) as rdm_rev,
                COALESCE(SUM(IF(LOWER(isrv.service_cat) LIKE '%product%', s.total_amt, 0)),0) as prd_rev
                FROM hr_invoice_staff s
                JOIN hr_invoice i ON i.invoice_id=s.invoice_id
                JOIN hr_invoice_service isrv ON isrv.id=s.invoice_service
                LEFT JOIN (SELECT invoice_id, MAX(payment_mode) as pm FROM hr_invoice_payment WHERE payment_mode IN ('wallet','pkg') GROUP BY invoice_id) p ON p.invoice_id=i.invoice_id
                WHERE i.salon_id='$salon_id' AND s.staff_id IN ($staff_ids_str) AND i.delete_bill!=1
                  AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
            if (in_array('services', $components))    $target_generated += floatval($rows['svc_rev'] ?? 0);
            if (in_array('redemptions', $components)) $target_generated += floatval($rows['rdm_rev'] ?? 0);
            if (in_array('products', $components))    $target_generated += floatval($rows['prd_rev'] ?? 0);
        }
    }
    if (in_array('packages', $components)) {
        $pkg = select_row("SELECT COALESCE(SUM(purchase_price),0) as rev FROM hr_customer_packages WHERE salon_id='$salon_id' AND status!='refunded' AND purchase_date BETWEEN '$from' AND '$to'");
        $target_generated += floatval($pkg['rev'] ?? 0);
    }
    if (in_array('memberships', $components)) {
        $mem = select_row("SELECT COALESCE(SUM(mp.amount),0) as rev FROM hr_membership_payments mp WHERE mp.salon_id='$salon_id' AND DATE(mp.created_at) BETWEEN '$from' AND '$to'");
        $target_generated += floatval($mem['rev'] ?? 0);
    }

    $total_generated = array_sum(array_column($revenue_data, 'revenue')); // all revenue for display
    $target_pct = $monthly_target > 0 ? round(($target_generated / $monthly_target) * 100, 1) : 0;

    return [
        'error'           => 0,
        'total_staff'     => $total_staff,
        'top_performer'   => $top_performer,
        'highest_revenue' => $highest_revenue,
        'most_booked'     => $most_booked,
        'target_pct'      => $target_pct,
        'total_generated' => $total_generated,
        'target_generated'=> $target_generated,
        'monthly_target'  => $monthly_target,
        'components'      => $components,
        'revenue_data'    => $revenue_data // For charts
    ];
}


function get_performance_metrics() {
    global $salon_id;
    $from = !empty($_REQUEST['from_date']) ? date('Y-m-d', strtotime($_REQUEST['from_date'])) : date('Y-m-01');
    $to   = !empty($_REQUEST['to_date'])   ? date('Y-m-d', strtotime($_REQUEST['to_date']))   : date('Y-m-d');
    
    $filter_data = get_staff_filter_sql();
    $staff_sql = $filter_data['sql'];

    $metrics = select_array("SELECT st.staff_name, 
        COUNT(s.id) as completed_services, 
        COALESCE(SUM(s.staff_work_price), 0) as total_revenue,
        COUNT(DISTINCT s.invoice_id) as total_invoices
        FROM hr_staff st
        LEFT JOIN hr_invoice_staff s ON s.staff_id=st.staff_id AND s.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
        WHERE st.salon_id='$salon_id' AND st.staff_status=1 
        GROUP BY st.staff_id 
        ORDER BY total_revenue DESC");

    $formatted = [];
    foreach($metrics as $m) {
        $avg_billing = $m['total_invoices'] > 0 ? round($m['total_revenue'] / $m['total_invoices'], 2) : 0;
        // Pseudo logic for upsell: revenue per service vs average
        $avg_per_service = $m['completed_services'] > 0 ? round($m['total_revenue'] / $m['completed_services'], 2) : 0;
        
        $formatted[] = [
            'staff_name' => $m['staff_name'],
            'completed_services' => $m['completed_services'],
            'total_revenue' => floatval($m['total_revenue']),
            'avg_billing' => $avg_billing,
            'avg_per_service' => $avg_per_service
        ];
    }

    return ['error' => 0, 'data' => $formatted];
}

function get_service_analytics() {
    global $salon_id;
    $from = !empty($_REQUEST['from_date']) ? date('Y-m-d', strtotime($_REQUEST['from_date'])) : date('Y-m-01');
    $to   = !empty($_REQUEST['to_date'])   ? date('Y-m-d', strtotime($_REQUEST['to_date']))   : date('Y-m-d');
    
    $filter_data = get_staff_filter_sql();
    $staff_list = $filter_data['staff'];
    $staff_sql = $filter_data['sql'];

    // Popular services
    $popular = select_array("SELECT s.service as service_name, COUNT(s.id) as count 
        FROM hr_invoice_service s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
        AND s.service != '' AND s.staff_id != 'Select Staff' AND s.staff_id != ''
        GROUP BY s.service 
        ORDER BY count DESC LIMIT 10");

    // Heatmap / Peak hours (Using invoice creation time as proxy)
    $peak_hours = select_array("SELECT HOUR(i.invoice_date) as hour, COUNT(*) as count 
        FROM hr_invoice i
        JOIN hr_invoice_staff s ON s.invoice_id=i.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59' $staff_sql
        GROUP BY hour ORDER BY hour ASC");

    // Staff Service Breakdown (split-aware using hr_invoice_staff)
    $breakdown_mapped = select_array("SELECT st.staff_name, isrv.service as service_name, 
           COUNT(s.id) as total_count,
           SUM(s.total_amt) as total_revenue,
           SUM(IF(p.payment_mode IN ('wallet', 'pkg') OR isrv.pkg_id > 0 OR i.payment_mode IN ('wallet', 'pkg'), 1, 0)) as redemp_count,
           SUM(IF(p.payment_mode IN ('wallet', 'pkg') OR isrv.pkg_id > 0 OR i.payment_mode IN ('wallet', 'pkg'), s.total_amt, 0)) as redemp_revenue,
           SUM(IF((p.payment_mode IS NULL OR p.payment_mode NOT IN ('wallet', 'pkg')) AND (isrv.pkg_id IS NULL OR isrv.pkg_id = 0) AND i.payment_mode NOT IN ('wallet', 'pkg'), 1, 0)) as other_count,
           SUM(IF((p.payment_mode IS NULL OR p.payment_mode NOT IN ('wallet', 'pkg')) AND (isrv.pkg_id IS NULL OR isrv.pkg_id = 0) AND i.payment_mode NOT IN ('wallet', 'pkg'), s.total_amt, 0)) as other_revenue
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        JOIN hr_staff st ON st.staff_id = s.staff_id
        JOIN hr_invoice_service isrv ON isrv.id = s.invoice_service
        LEFT JOIN (
            SELECT invoice_id, MAX(payment_mode) as payment_mode 
            FROM hr_invoice_payment 
            WHERE payment_mode IN ('wallet', 'pkg') 
            GROUP BY invoice_id
        ) p ON p.invoice_id = i.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
          AND isrv.service != '' AND i.delete_bill!=1 $staff_sql
        GROUP BY s.staff_id, isrv.service");

    $final_breakdown = [];
    $breakdown_map = [];

    if ($breakdown_mapped) {
        foreach($breakdown_mapped as $row) {
            $staff_name = $row['staff_name'];
            $service_name = $row['service_name'];
            $key = $staff_name . '|' . $service_name;
            
            $item = [
                'staff_name' => $staff_name,
                'service_name' => $service_name,
                'total_count' => intval($row['total_count']),
                'total_revenue' => floatval($row['total_revenue']),
                'redemp_count' => intval($row['redemp_count']),
                'redemp_revenue' => floatval($row['redemp_revenue']),
                'other_count' => intval($row['other_count']),
                'other_revenue' => floatval($row['other_revenue'])
            ];
            
            $breakdown_map[$key] = count($final_breakdown);
            $final_breakdown[] = $item;
        }
    }

    // Determine Counter Staff details
    $counter_staff = select_row("SELECT staff_id, staff_name FROM hr_staff WHERE salon_id='$salon_id' AND LOWER(staff_name) LIKE '%counter%' LIMIT 1");
    $counter_staff_id = $counter_staff ? intval($counter_staff['staff_id']) : 116;
    $counter_name = $counter_staff ? $counter_staff['staff_name'] : 'Tress Lounge Counter';

    // Verify if Counter is included in filter
    $is_counter_included = false;
    if (empty($staff_list)) {
        $is_counter_included = true;
    } else {
        foreach($staff_list as $st) {
            if (intval($st['staff_id']) === $counter_staff_id) {
                $is_counter_included = true;
                break;
            }
        }
    }

    // Fetch and append unmapped service sales to Counter Staff if included
    if ($is_counter_included) {
        $breakdown_unmapped = select_array("SELECT isrv.service as service_name,
               COUNT(isrv.id) as total_count,
               SUM((isrv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * i.grand_total) as total_revenue,
               SUM(IF(p.payment_mode IN ('wallet', 'pkg') OR isrv.pkg_id > 0 OR i.payment_mode IN ('wallet', 'pkg'), 1, 0)) as redemp_count,
               SUM(IF(p.payment_mode IN ('wallet', 'pkg') OR isrv.pkg_id > 0 OR i.payment_mode IN ('wallet', 'pkg'), (isrv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * i.grand_total, 0)) as redemp_revenue,
               SUM(IF((p.payment_mode IS NULL OR p.payment_mode NOT IN ('wallet', 'pkg')) AND (isrv.pkg_id IS NULL OR isrv.pkg_id = 0) AND i.payment_mode NOT IN ('wallet', 'pkg'), 1, 0)) as other_count,
               SUM(IF((p.payment_mode IS NULL OR p.payment_mode NOT IN ('wallet', 'pkg')) AND (isrv.pkg_id IS NULL OR isrv.pkg_id = 0) AND i.payment_mode NOT IN ('wallet', 'pkg'), (isrv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * i.grand_total, 0)) as other_revenue
            FROM hr_invoice_service isrv
            JOIN hr_invoice i ON i.invoice_id = isrv.invoice_id
            LEFT JOIN hr_invoice_staff s ON s.invoice_service = isrv.id
            LEFT JOIN (
                SELECT invoice_id, MAX(payment_mode) as payment_mode 
                FROM hr_invoice_payment 
                WHERE payment_mode IN ('wallet', 'pkg') 
                GROUP BY invoice_id
            ) p ON p.invoice_id = i.invoice_id
            WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
              AND isrv.service != '' AND i.delete_bill!=1
              AND s.staff_id IS NULL
            GROUP BY isrv.service");

        if ($breakdown_unmapped) {
            foreach($breakdown_unmapped as $row) {
                $service_name = $row['service_name'];
                $key = $counter_name . '|' . $service_name;
                
                if (isset($breakdown_map[$key])) {
                    $idx = $breakdown_map[$key];
                    $final_breakdown[$idx]['total_count'] += intval($row['total_count']);
                    $final_breakdown[$idx]['total_revenue'] += floatval($row['total_revenue']);
                    $final_breakdown[$idx]['redemp_count'] += intval($row['redemp_count']);
                    $final_breakdown[$idx]['redemp_revenue'] += floatval($row['redemp_revenue']);
                    $final_breakdown[$idx]['other_count'] += intval($row['other_count']);
                    $final_breakdown[$idx]['other_revenue'] += floatval($row['other_revenue']);
                } else {
                    $item = [
                        'staff_name' => $counter_name,
                        'service_name' => $service_name,
                        'total_count' => intval($row['total_count']),
                        'total_revenue' => floatval($row['total_revenue']),
                        'redemp_count' => intval($row['redemp_count']),
                        'redemp_revenue' => floatval($row['redemp_revenue']),
                        'other_count' => intval($row['other_count']),
                        'other_revenue' => floatval($row['other_revenue'])
                    ];
                    $breakdown_map[$key] = count($final_breakdown);
                    $final_breakdown[] = $item;
                }
            }
        }
    }

    // Sort by staff name alphabetically, and then by total_count descending
    usort($final_breakdown, function($a, $b) {
        if ($a['staff_name'] === $b['staff_name']) {
            return $b['total_count'] <=> $a['total_count'];
        }
        return strcmp($a['staff_name'], $b['staff_name']);
    });

    return ['error' => 0, 'popular_services' => $popular, 'peak_hours' => $peak_hours, 'breakdown' => $final_breakdown];
}

function get_comprehensive_report() {
    global $salon_id;
    $from = !empty($_REQUEST['from_date']) ? date('Y-m-d', strtotime($_REQUEST['from_date'])) : date('Y-m-01');
    $to   = !empty($_REQUEST['to_date'])   ? date('Y-m-d', strtotime($_REQUEST['to_date']))   : date('Y-m-d');
    
    $filter_data = get_staff_filter_sql();
    $staff_list = $filter_data['staff'];
    
    $report = [];
    foreach($staff_list as $st) {
        $report[$st['staff_id']] = [
            'staff_name' => $st['staff_name'],
            'clients' => 0,
            'services_rev' => 0,
            'redemptions' => 0,
            'packages_sold' => 0,
            'memberships_sold' => 0,
            'products_sold' => 0,
            'total_generated' => 0
        ];
    }
    
    // Ensure Counter Staff exists in the report array
    $counter_staff = select_row("SELECT staff_id, staff_name FROM hr_staff WHERE salon_id='$salon_id' AND LOWER(staff_name) LIKE '%counter%' LIMIT 1");
    $counter_staff_id = $counter_staff ? intval($counter_staff['staff_id']) : 116;
    $counter_staff_name = $counter_staff ? $counter_staff['staff_name'] : 'Tress Lounge Counter';
    if (!isset($report[$counter_staff_id])) {
        $report[$counter_staff_id] = [
            'staff_name' => $counter_staff_name,
            'clients' => 0,
            'services_rev' => 0,
            'redemptions' => 0,
            'packages_sold' => 0,
            'memberships_sold' => 0,
            'products_sold' => 0,
            'total_generated' => 0
        ];
    }

    // 1. Clients
    $q_clients = select_array("SELECT s.staff_id, COUNT(DISTINCT i.cust_id) as count 
        FROM hr_invoice_staff s 
        JOIN hr_invoice i ON i.invoice_id=s.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
        GROUP BY s.staff_id");
    if($q_clients) {
        foreach($q_clients as $row) { 
            if(isset($report[$row['staff_id']])) $report[$row['staff_id']]['clients'] = intval($row['count']); 
        }
    }

    // 2. Services Rev (excluding Package/Wallet, Products, and Packages/Memberships Sold)
    $q_services = select_array("SELECT s.staff_id, SUM(s.total_amt) as rev
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        JOIN hr_invoice_service isrv ON isrv.id = s.invoice_service
        LEFT JOIN (
            SELECT invoice_id, MAX(payment_mode) as payment_mode 
            FROM hr_invoice_payment 
            WHERE payment_mode IN ('wallet', 'pkg') 
            GROUP BY invoice_id
        ) p ON p.invoice_id = i.invoice_id
        WHERE i.salon_id = '$salon_id' 
          AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
          AND (p.payment_mode IS NULL OR p.payment_mode NOT IN ('wallet', 'pkg'))
          AND i.payment_mode NOT IN ('wallet', 'pkg')
          AND LOWER(isrv.service_cat) NOT LIKE '%product%'
          AND LOWER(isrv.service_cat) NOT LIKE '%package%'
          AND LOWER(isrv.service_cat) NOT LIKE '%membership%'
        GROUP BY s.staff_id");
    if($q_services) {
        foreach($q_services as $row) {
            $sid = $row['staff_id'];
            if(isset($report[$sid])) $report[$sid]['services_rev'] = floatval($row['rev']);
        }
    }

    // 2.5 Products Sold (from hr_invoice_service via hr_invoice_staff to be split-aware)
    $q_products = select_array("SELECT s.staff_id, SUM(s.total_amt) as rev
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        JOIN hr_invoice_service isrv ON isrv.id = s.invoice_service
        WHERE i.salon_id = '$salon_id' 
          AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
          AND LOWER(isrv.service_cat) LIKE '%product%'
        GROUP BY s.staff_id");
    if($q_products) {
        foreach($q_products as $row) {
            $sid = $row['staff_id'];
            if(isset($report[$sid])) $report[$sid]['products_sold'] = floatval($row['rev']);
        }
    }

    // 3. Package Sold (from hr_customer_packages - aligned with card query)
    $q_pkgs = select_array("SELECT COALESCE(sold_by, 0) as staff_id, SUM(purchase_price) as rev 
        FROM hr_customer_packages
        WHERE salon_id='$salon_id' AND purchase_date BETWEEN '$from' AND '$to' AND status != 'refunded'
        GROUP BY sold_by");
    if($q_pkgs) {
        foreach($q_pkgs as $row) {
            $sid = $row['staff_id'];
            if(isset($report[$sid])) $report[$sid]['packages_sold'] += floatval($row['rev']);
        }
    }

    // 4. Membership Sold (from hr_membership_payments & hr_customer_membership - aligned with card query)
    $q_mems = select_array("SELECT COALESCE(cm.sold_by, 0) as staff_id, SUM(mp.amount) as rev 
        FROM hr_membership_payments mp
        JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id
        WHERE mp.salon_id = '$salon_id' AND DATE(mp.created_at) BETWEEN '$from' AND '$to' AND cm.status != 'refunded'
        GROUP BY cm.sold_by");
    if($q_mems) {
        foreach($q_mems as $row) {
            $sid = $row['staff_id'];
            if(isset($report[$sid])) $report[$sid]['memberships_sold'] += floatval($row['rev']);
        }
    }

    // 5. Redemptions (calculated total amount for services paid via wallet or package)
    $q_redemps = select_array("SELECT s.staff_id, SUM(s.total_amt) as rev
        FROM hr_invoice_staff s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        JOIN hr_invoice_service isrv ON isrv.id = s.invoice_service
        LEFT JOIN (
            SELECT invoice_id, MAX(payment_mode) as payment_mode 
            FROM hr_invoice_payment 
            WHERE payment_mode IN ('wallet', 'pkg') 
            GROUP BY invoice_id
        ) p ON p.invoice_id = i.invoice_id
        WHERE i.salon_id = '$salon_id' 
          AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
          AND (p.payment_mode IN ('wallet', 'pkg') OR isrv.pkg_id > 0 OR i.payment_mode IN ('wallet', 'pkg'))
          AND LOWER(isrv.service_cat) NOT LIKE '%product%'
          AND LOWER(isrv.service_cat) NOT LIKE '%package%'
          AND LOWER(isrv.service_cat) NOT LIKE '%membership%'
        GROUP BY s.staff_id");
    if($q_redemps) {
        foreach($q_redemps as $row) {
            $sid = $row['staff_id'];
            if(isset($report[$sid])) $report[$sid]['redemptions'] = floatval($row['rev']);
        }
    }

    // --- CARD TOTALS & UNASSIGNED CATCH-ALL ALLOCATION ---
    // A. Card Services Total
    $service_res = select_row("
        SELECT SUM(
            (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * i.grand_total
        ) as st 
        FROM hr_invoice i 
        JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id 
        WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 
          AND sv.service_cat NOT LIKE 'Product%' 
          AND sv.service_cat NOT LIKE 'Membership%' 
          AND sv.service_cat NOT LIKE 'Package%' 
          AND i.payment_mode!='pkg' AND i.payment_mode!='wallet'
          AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $card_services = $service_res['st'] ? floatval($service_res['st']) : 0;

    $split_wallet_service_res = select_row("
        SELECT SUM(p.grand_total) as swt
        FROM hr_invoice i
        JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id
        WHERE i.salon_id='$salon_id' AND i.delete_bill!=1
          AND i.payment_mode='split' AND p.payment_mode='wallet'
          AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $split_wallet_in_service = $split_wallet_service_res['swt'] ? floatval($split_wallet_service_res['swt']) : 0;
    $card_services_net = max(0, $card_services - $split_wallet_in_service);

    // B. Card Products Total
    $product_res = select_row("
        SELECT SUM(
            (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * i.grand_total
        ) as pt 
        FROM hr_invoice i 
        JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id 
        WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 
          AND sv.service_cat LIKE 'Product%' 
          AND i.payment_mode!='pkg' AND i.payment_mode!='wallet'
          AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $card_products = $product_res['pt'] ? floatval($product_res['pt']) : 0;

    // C. Card Redemptions Total (Wallet & Package redemptions)
    $redemption_res = select_row("SELECT SUM(grand_total) as rt FROM hr_invoice i WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND i.payment_mode='pkg' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $redemption_total = $redemption_res['rt'] ? floatval($redemption_res['rt']) : 0;
    
    $wallet_res = select_row("SELECT SUM(grand_total) as wt FROM hr_invoice i WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND i.payment_mode='wallet' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $wallet_total = $wallet_res['wt'] ? floatval($wallet_res['wt']) : 0;
    
    $split_pkg_res = select_row("SELECT SUM(p.grand_total) as sqt FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND i.payment_mode='split' AND p.payment_mode='pkg' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $redemption_total += $split_pkg_res['sqt'] ? floatval($split_pkg_res['sqt']) : 0;
    
    $split_wallet_res = select_row("SELECT SUM(p.grand_total) as swt FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!=1 AND i.payment_mode='split' AND p.payment_mode='wallet' AND DATE(i.invoice_date) BETWEEN '$from' AND '$to'");
    $wallet_total += $split_wallet_res['swt'] ? floatval($split_wallet_res['swt']) : 0;
    
    $card_redemptions = $redemption_total + $wallet_total;

    // D. Card Memberships & Packages Total
    $card_memberships = floatval(select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND cm.status!='refunded' AND (DATE(mp.created_at) BETWEEN '$from' AND '$to')")['total'] ?? 0);
    $card_packages = floatval(select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND status!='refunded' AND (purchase_date BETWEEN '$from' AND '$to')")['total'] ?? 0);

    // E. Card Total Invoices (Clients/visits count alignment)
    $total_invoices_count = intval(select_row("SELECT COUNT(invoice_id) as count FROM hr_invoice WHERE salon_id='$salon_id' AND delete_bill!=1 AND DATE(invoice_date) BETWEEN '$from' AND '$to'")['count'] ?? 0);

    // Calculate sum of other staff for each category and allocate the remainder to Counter Staff
    $other_services_sum = 0;
    $other_products_sum = 0;
    $other_redemptions_sum = 0;
    $other_packages_sum = 0;
    $other_memberships_sum = 0;
    $other_clients_sum = 0;

    foreach($report as $sid => $data) {
        if ($sid != $counter_staff_id) {
            $other_services_sum += $data['services_rev'];
            $other_products_sum += $data['products_sold'];
            $other_redemptions_sum += $data['redemptions'];
            $other_packages_sum += $data['packages_sold'];
            $other_memberships_sum += $data['memberships_sold'];
            $other_clients_sum += $data['clients'];
        }
    }

    // Allocate difference to Counter Staff to guarantee mathematically perfect totals
    $report[$counter_staff_id]['services_rev'] = max(0, $card_services_net - $other_services_sum);
    $report[$counter_staff_id]['products_sold'] = max(0, $card_products - $other_products_sum);
    $report[$counter_staff_id]['redemptions'] = max(0, $card_redemptions - $other_redemptions_sum);
    $report[$counter_staff_id]['packages_sold'] = max(0, $card_packages - $other_packages_sum);
    $report[$counter_staff_id]['memberships_sold'] = max(0, $card_memberships - $other_memberships_sum);
    
    // For clients/visits, we make sure that the total matches the overall invoice count
    $report[$counter_staff_id]['clients'] = max($report[$counter_staff_id]['clients'], $total_invoices_count - $other_clients_sum);

    // Calculate Total Generated for each staff and build final report
    $final_report = [];
    foreach($report as $sid => $data) {
        $data['total_generated'] = $data['services_rev'] + $data['redemptions'] + $data['packages_sold'] + $data['memberships_sold'] + $data['products_sold'];
        if ($data['total_generated'] > 0 || $data['clients'] > 0 || $data['redemptions'] > 0) {
            $final_report[] = $data;
        }
    }

    // Sort by Total Generated descending
    usort($final_report, function($a, $b) { return $b['total_generated'] <=> $a['total_generated']; });
    $final_report = array_reverse($final_report);

    return ['error' => 0, 'data' => $final_report];
}

function get_ai_insights() {
    global $salon_id;
    // Simple smart heuristics
    $current_month_start = date('Y-m-01');
    $last_month_start = date('Y-m-d', strtotime('first day of last month'));
    $last_month_end = date('Y-m-d', strtotime('last day of last month'));

    $insights = [];

    // Predict low performing staff (Drop in revenue > 20%)
    $current_rev = select_array("SELECT s.staff_id, st.staff_name, SUM(s.staff_work_price) as rev FROM hr_invoice_staff s JOIN hr_staff st ON st.staff_id=s.staff_id WHERE s.invoice_date >= '$current_month_start 00:00:00' GROUP BY s.staff_id");
    $last_rev = select_array("SELECT s.staff_id, SUM(s.staff_work_price) as rev FROM hr_invoice_staff s WHERE s.invoice_date BETWEEN '$last_month_start 00:00:00' AND '$last_month_end 23:59:59' GROUP BY s.staff_id");
    
    $last_map = [];
    foreach($last_rev as $l) $last_map[$l['staff_id']] = floatval($l['rev']);

    foreach($current_rev as $c) {
        $id = $c['staff_id'];
        $curr = floatval($c['rev']);
        // Extrapolate current month
        $days_passed = date('j');
        $days_in_month = date('t');
        $projected = ($curr / $days_passed) * $days_in_month;
        
        if (isset($last_map[$id]) && $last_map[$id] > 0) {
            $drop = (($last_map[$id] - $projected) / $last_map[$id]) * 100;
            if ($drop > 20) {
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Performance Alert',
                    'message' => "{$c['staff_name']} is projected to drop ".round($drop)."% in revenue compared to last month."
                ];
            }
        }
    }

    // Recommend upselling (High services, low avg billing)
    $metrics = get_performance_metrics()['data'];
    if (!empty($metrics)) {
        $avg_all_billing = array_sum(array_column($metrics, 'avg_billing')) / count($metrics);
        foreach($metrics as $m) {
            if ($m['completed_services'] > 10 && $m['avg_billing'] < ($avg_all_billing * 0.7)) {
                $insights[] = [
                    'type' => 'info',
                    'title' => 'Upsell Opportunity',
                    'message' => "{$m['staff_name']} has high volume but their average ticket (₹{$m['avg_billing']}) is 30% below salon average. Recommend upsell training."
                ];
            }
        }
    }

    // Suggest Staff Scheduling
    $peak = select_array("SELECT HOUR(i.invoice_date) as hour, COUNT(*) as count FROM hr_invoice i WHERE i.salon_id='$salon_id' GROUP BY hour ORDER BY count DESC LIMIT 2");
    if(count($peak) >= 2) {
        $insights[] = [
            'type' => 'success',
            'title' => 'Smart Scheduling',
            'message' => "Peak salon hours are consistently around ".str_pad($peak[0]['hour'], 2, '0', STR_PAD_LEFT).":00 and ".str_pad($peak[1]['hour'], 2, '0', STR_PAD_LEFT).":00. Ensure overlapping shifts during these periods."
        ];
    }

    if (empty($insights)) {
         $insights[] = ['type' => 'success', 'title' => 'All Good', 'message' => 'Staff performance is stable across all metrics.'];
    }

    return ['error' => 0, 'data' => $insights];
}

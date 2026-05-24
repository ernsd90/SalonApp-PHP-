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

    // Target Calculation (5x Salary)
    $total_salary = array_sum(array_column($staff_list, 'staff_salary'));
    $monthly_target = $total_salary * 5;
    $total_generated = array_sum(array_column($revenue_data, 'revenue'));
    $target_pct = $monthly_target > 0 ? round(($total_generated / $monthly_target) * 100, 1) : 0;

    return [
        'error' => 0,
        'total_staff' => $total_staff,
        'top_performer' => $top_performer,
        'highest_revenue' => $highest_revenue,
        'most_booked' => $most_booked,
        'target_pct' => $target_pct,
        'total_generated' => $total_generated,
        'monthly_target' => $monthly_target,
        'revenue_data' => $revenue_data // For charts
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
    $staff_sql = $filter_data['sql'];

    // Popular services
    $popular = select_array("SELECT s.service as service_name, COUNT(s.id) as count 
        FROM hr_invoice_service s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
        AND s.service != '' AND s.staff_id != 'Select Staff' AND s.staff_id != '' $staff_sql 
        GROUP BY s.service 
        ORDER BY count DESC LIMIT 10");

    // Heatmap / Peak hours (Using invoice creation time as proxy)
    $peak_hours = select_array("SELECT HOUR(i.invoice_date) as hour, COUNT(*) as count 
        FROM hr_invoice i
        JOIN hr_invoice_staff s ON s.invoice_id=i.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59' $staff_sql
        GROUP BY hour ORDER BY hour ASC");

    // Staff Service Breakdown (which staff worked on which service, count, and revenue)
    $breakdown = select_array("SELECT st.staff_name, s.service as service_name, 
           COUNT(s.id) as total_count,
           SUM(s.service_total_wth_gst) as total_revenue,
           SUM(IF(p.payment_mode IN ('wallet', 'pkg') OR s.pkg_id > 0, 1, 0)) as redemp_count,
           SUM(IF(p.payment_mode IN ('wallet', 'pkg') OR s.pkg_id > 0, s.service_total_wth_gst, 0)) as redemp_revenue,
           SUM(IF((p.payment_mode IS NULL OR p.payment_mode NOT IN ('wallet', 'pkg')) AND (s.pkg_id IS NULL OR s.pkg_id = '' OR s.pkg_id = 0), 1, 0)) as other_count,
           SUM(IF((p.payment_mode IS NULL OR p.payment_mode NOT IN ('wallet', 'pkg')) AND (s.pkg_id IS NULL OR s.pkg_id = '' OR s.pkg_id = 0), s.service_total_wth_gst, 0)) as other_revenue
        FROM hr_invoice_service s
        JOIN hr_invoice i ON i.invoice_id = s.invoice_id
        JOIN hr_staff st ON st.staff_id = s.staff_id
        LEFT JOIN (
            SELECT invoice_id, MAX(payment_mode) as payment_mode 
            FROM hr_invoice_payment 
            WHERE payment_mode IN ('wallet', 'pkg') 
            GROUP BY invoice_id
        ) p ON p.invoice_id = i.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
        AND s.service != '' AND s.staff_id != 'Select Staff' AND s.staff_id != '' $staff_sql 
        GROUP BY s.staff_id, s.service
        ORDER BY st.staff_name ASC, total_count DESC");

    return ['error' => 0, 'popular_services' => $popular, 'peak_hours' => $peak_hours, 'breakdown' => $breakdown];
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
    $staff_ids_str = !empty($staff_list) ? implode(',', array_column($staff_list, 'staff_id')) : '0';

    // 1. Clients
    $q_clients = select_array("SELECT s.staff_id, COUNT(DISTINCT i.cust_id) as count 
        FROM hr_invoice_staff s 
        JOIN hr_invoice i ON i.invoice_id=s.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
        GROUP BY s.staff_id");
    if($q_clients) {
        foreach($q_clients as $row) { 
            if(isset($report[$row['staff_id']])) $report[$row['staff_id']]['clients'] = $row['count']; 
        }
    }

    // 2. Services Rev & Products Rev (from hr_invoice_service)
    $q_services = select_array("SELECT s.staff_id, s.service_cat, SUM(s.service_total_wth_gst) as rev 
        FROM hr_invoice_service s
        JOIN hr_invoice i ON i.invoice_id=s.invoice_id
        WHERE i.salon_id='$salon_id' AND i.invoice_date BETWEEN '$from 00:00:00' AND '$to 23:59:59'
        GROUP BY s.staff_id, s.service_cat");
    if($q_services) {
        foreach($q_services as $row) {
            $sid = $row['staff_id'];
            if(!isset($report[$sid])) continue;
            
            $rev = floatval($row['rev']);
            if(stripos($row['service_cat'], 'Product') !== false) {
                $report[$sid]['products_sold'] += $rev;
            } else if(stripos($row['service_cat'], 'Package') !== false) {
                // Ignore packages sold through invoices, rely on hr_customer_packages table
            } else {
                $report[$sid]['services_rev'] += $rev;
            }
        }
    }

    // 3. Package Sold (from hr_customer_packages)
    $q_pkgs = select_array("SELECT sold_by as staff_id, SUM(paid_amount) as rev 
        FROM hr_customer_packages
        WHERE salon_id='$salon_id' AND purchase_date BETWEEN '$from' AND '$to'
        GROUP BY sold_by");
    if($q_pkgs) {
        foreach($q_pkgs as $row) { if(isset($report[$row['staff_id']])) $report[$row['staff_id']]['packages_sold'] += floatval($row['rev']); }
    }

    // 4. Membership Sold (from hr_customer_membership)
    $q_mems = select_array("SELECT sold_by as staff_id, SUM(paid_amount) as rev 
        FROM hr_customer_membership
        WHERE salon_id='$salon_id' AND start_date BETWEEN '$from' AND '$to'
        GROUP BY sold_by");
    if($q_mems) {
        foreach($q_mems as $row) { if(isset($report[$row['staff_id']])) $report[$row['staff_id']]['memberships_sold'] += floatval($row['rev']); }
    }

    // 5. Redemptions (from hr_customer_package_usage)
    $q_redemps = select_array("SELECT used_by as staff_id, SUM(qty_used) as count 
        FROM hr_customer_package_usage
        WHERE used_at BETWEEN '$from 00:00:00' AND '$to 23:59:59'
        GROUP BY used_by");
    if($q_redemps) {
        foreach($q_redemps as $row) { if(isset($report[$row['staff_id']])) $report[$row['staff_id']]['redemptions'] += $row['count']; }
    }

    // Calculate Total Generated
    $final_report = [];
    foreach($report as $sid => $data) {
        $data['total_generated'] = $data['services_rev'] + $data['packages_sold'] + $data['memberships_sold'] + $data['products_sold'];
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

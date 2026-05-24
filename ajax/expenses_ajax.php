<?php
session_start();
include '../config.php';
include '../function.php';
include '../includes/ExpenseInsightsEngine.php';

$salon_id = get_session_data('salon_id') ?? 80;
$user_id = get_session_data('user_id') ?? 1;
$method = $_POST['method'] ?? '';

if ($method == 'get_dashboard_data') {
    $fromdate = $_POST['fromdate'] ?? date('01-m-Y');
    $todate = $_POST['todate'] ?? date('d-m-Y');
    
    // date comes as dd-mm-yyyy, convert to yyyy-mm-dd
    $from = date('Y-m-d', strtotime($fromdate));
    $to = date('Y-m-d', strtotime($todate));

    $data = [];
    
    // Total Expense
    $sql_total = "SELECT SUM(exp_total) as total FROM hr_expenses WHERE salon_id='$salon_id' AND DATE(exp_date) BETWEEN '$from' AND '$to' AND approval_status != 'rejected'";
    $res_total = mysqli_query($conn, $sql_total);
    $data['total_expense'] = ($res_total && $row = mysqli_fetch_assoc($res_total)) ? (float)$row['total'] : 0;

    // Previous month total for comparison
    $prev_from = date('Y-m-d', strtotime('-1 month', strtotime($from)));
    $prev_to = date('Y-m-d', strtotime('-1 month', strtotime($to)));
    $sql_prev = "SELECT SUM(exp_total) as total FROM hr_expenses WHERE salon_id='$salon_id' AND DATE(exp_date) BETWEEN '$prev_from' AND '$prev_to' AND approval_status != 'rejected'";
    $res_prev = mysqli_query($conn, $sql_prev);
    $prev_total = ($res_prev && $row = mysqli_fetch_assoc($res_prev)) ? (float)$row['total'] : 0;
    
    if ($prev_total > 0) {
        $data['last_month_compare'] = (($data['total_expense'] - $prev_total) / $prev_total) * 100;
    } else {
        $data['last_month_compare'] = 0;
    }

    // Daily Average
    $days = max(1, (strtotime($to) - strtotime($from)) / (60 * 60 * 24) + 1);
    $data['daily_average'] = $data['total_expense'] / $days;

    // Category Distribution (Pie Chart)
    $sql_cat = "SELECT c.category_name, SUM(e.exp_total) as total FROM hr_expenses e LEFT JOIN hr_expenses_category c ON e.exp_catId = c.exp_catId WHERE e.salon_id='$salon_id' AND DATE(e.exp_date) BETWEEN '$from' AND '$to' AND e.approval_status != 'rejected' GROUP BY e.exp_catId ORDER BY total DESC";
    $res_cat = mysqli_query($conn, $sql_cat);
    $cats = [];
    $highest_cat_name = '-';
    $highest_cat_val = 0;
    if ($res_cat) {
        while($row = mysqli_fetch_assoc($res_cat)) {
            $name = $row['category_name'] ?: 'General';
            $cats[] = ['name' => $name, 'total' => (float)$row['total']];
            if ($row['total'] > $highest_cat_val) {
                $highest_cat_val = $row['total'];
                $highest_cat_name = $name;
            }
        }
    }
    $data['category_distribution'] = $cats;
    $data['highest_category'] = $highest_cat_name;

    // Expense Trend (Line Graph)
    $sql_trend = "SELECT DATE_FORMAT(DATE(exp_date), '%d %b') as ddate, SUM(exp_total) as total FROM hr_expenses WHERE salon_id='$salon_id' AND DATE(exp_date) BETWEEN '$from' AND '$to' AND approval_status != 'rejected' GROUP BY DATE(exp_date) ORDER BY DATE(exp_date) ASC";
    $res_trend = mysqli_query($conn, $sql_trend);
    $trend = [];
    if ($res_trend) {
        while($row = mysqli_fetch_assoc($res_trend)) {
            $trend[] = ['date' => $row['ddate'], 'total' => (float)$row['total']];
        }
    }
    $data['expense_trend'] = $trend;

    // Payment Modes (Donut)
    $sql_pm = "SELECT payment_mode, SUM(exp_total) as total FROM hr_expenses WHERE salon_id='$salon_id' AND DATE(exp_date) BETWEEN '$from' AND '$to' AND approval_status != 'rejected' GROUP BY payment_mode";
    $res_pm = mysqli_query($conn, $sql_pm);
    $pms = [];
    $cash = 0; $online = 0;
    if ($res_pm) {
        while($row = mysqli_fetch_assoc($res_pm)) {
            $pm = $row['payment_mode'] ? ucfirst(strtolower($row['payment_mode'])) : 'Other';
            $pms[] = ['name' => $pm, 'total' => (float)$row['total']];
            if (strtolower(trim($row['payment_mode'])) === 'cash') $cash += $row['total'];
            else $online += $row['total'];
        }
    }
    $data['payment_modes'] = $pms;
    $data['cash_vs_online'] = ['cash' => $cash, 'online' => $online];

    // Top Vendor
    $sql_vendor = "SELECT v.name, SUM(e.exp_total) as total FROM hr_expenses e LEFT JOIN hr_vendors v ON e.vendor_id = v.id WHERE e.salon_id='$salon_id' AND DATE(e.exp_date) BETWEEN '$from' AND '$to' AND e.approval_status != 'rejected' AND e.vendor_id IS NOT NULL GROUP BY e.vendor_id ORDER BY total DESC LIMIT 1";
    $res_vendor = mysqli_query($conn, $sql_vendor);
    if($res_vendor && $row = mysqli_fetch_assoc($res_vendor)) {
        $data['top_vendor'] = $row['name'];
    } else {
        $data['top_vendor'] = '-';
    }

    // Pending Approvals count
    $sql_pending = "SELECT COUNT(*) as cnt FROM hr_expenses WHERE salon_id='$salon_id' AND approval_status = 'pending'";
    $res_pending = mysqli_query($conn, $sql_pending);
    $data['pending_approvals'] = ($res_pending && $row = mysqli_fetch_assoc($res_pending)) ? (int)$row['cnt'] : 0;

    // Insights
    $engine = new ExpenseInsightsEngine($conn, $salon_id);
    $data['insights'] = $engine->generateInsights($from, $to);

    echo json_encode($data);
    exit;
}

if ($method == 'get_reports_data') {
    $fromdate = $_POST['fromdate'] ?? date('01-m-Y');
    $todate = $_POST['todate'] ?? date('d-m-Y');
    $compare_type = $_POST['compare_type'] ?? 'mom'; // mom, yoy, wow
    
    // date comes as dd-mm-yyyy, convert to yyyy-mm-dd
    $from = date('Y-m-d', strtotime($fromdate));
    $to = date('Y-m-d', strtotime($todate));

    $data = [];

    // Current period total
    $sql_curr = "SELECT SUM(exp_total) as total FROM hr_expenses WHERE salon_id='$salon_id' AND DATE(exp_date) BETWEEN '$from' AND '$to' AND approval_status != 'rejected'";
    $res_curr = mysqli_query($conn, $sql_curr);
    $data['current_total'] = ($res_curr && $row = mysqli_fetch_assoc($res_curr)) ? (float)$row['total'] : 0;

    // Determine previous period
    if ($compare_type == 'mom') {
        $prev_from = date('Y-m-d', strtotime('-1 month', strtotime($from)));
        $prev_to = date('Y-m-d', strtotime('-1 month', strtotime($to)));
    } elseif ($compare_type == 'yoy') {
        $prev_from = date('Y-m-d', strtotime('-1 year', strtotime($from)));
        $prev_to = date('Y-m-d', strtotime('-1 year', strtotime($to)));
    } else { // wow
        $prev_from = date('Y-m-d', strtotime('-1 week', strtotime($from)));
        $prev_to = date('Y-m-d', strtotime('-1 week', strtotime($to)));
    }

    // Previous period total
    $sql_prev = "SELECT SUM(exp_total) as total FROM hr_expenses WHERE salon_id='$salon_id' AND DATE(exp_date) BETWEEN '$prev_from' AND '$prev_to' AND approval_status != 'rejected'";
    $res_prev = mysqli_query($conn, $sql_prev);
    $data['prev_total'] = ($res_prev && $row = mysqli_fetch_assoc($res_prev)) ? (float)$row['total'] : 0;

    // Calculate growth
    if ($data['prev_total'] > 0) {
        $data['growth_percent'] = (($data['current_total'] - $data['prev_total']) / $data['prev_total']) * 100;
    } else {
        $data['growth_percent'] = 0;
    }

    // Category comparison table
    $cat_curr = [];
    $sql_cat_curr = "SELECT c.category_name, SUM(e.exp_total) as total FROM hr_expenses e LEFT JOIN hr_expenses_category c ON e.exp_catId = c.exp_catId WHERE e.salon_id='$salon_id' AND DATE(e.exp_date) BETWEEN '$from' AND '$to' AND e.approval_status != 'rejected' GROUP BY e.exp_catId";
    $res_cat_curr = mysqli_query($conn, $sql_cat_curr);
    if($res_cat_curr) {
        while($row = mysqli_fetch_assoc($res_cat_curr)) {
            $cat_curr[$row['category_name'] ?: 'General'] = (float)$row['total'];
        }
    }

    $cat_prev = [];
    $sql_cat_prev = "SELECT c.category_name, SUM(e.exp_total) as total FROM hr_expenses e LEFT JOIN hr_expenses_category c ON e.exp_catId = c.exp_catId WHERE e.salon_id='$salon_id' AND DATE(e.exp_date) BETWEEN '$prev_from' AND '$prev_to' AND e.approval_status != 'rejected' GROUP BY e.exp_catId";
    $res_cat_prev = mysqli_query($conn, $sql_cat_prev);
    if($res_cat_prev) {
        while($row = mysqli_fetch_assoc($res_cat_prev)) {
            $cat_prev[$row['category_name'] ?: 'General'] = (float)$row['total'];
        }
    }

    $data['category_comparison'] = [];
    $all_cats = array_unique(array_merge(array_keys($cat_curr), array_keys($cat_prev)));
    foreach($all_cats as $c) {
        $curr_val = $cat_curr[$c] ?? 0;
        $prev_val = $cat_prev[$c] ?? 0;
        $diff = $curr_val - $prev_val;
        $grow = $prev_val > 0 ? ($diff / $prev_val) * 100 : 0;
        $data['category_comparison'][] = [
            'category' => $c,
            'current' => $curr_val,
            'previous' => $prev_val,
            'growth' => $grow
        ];
    }

    // Vendor comparison
    $vendor_curr = [];
    $sql_v_curr = "SELECT v.name, SUM(e.exp_total) as total FROM hr_expenses e LEFT JOIN hr_vendors v ON e.vendor_id = v.id WHERE e.salon_id='$salon_id' AND DATE(e.exp_date) BETWEEN '$from' AND '$to' AND e.approval_status != 'rejected' AND e.vendor_id IS NOT NULL GROUP BY e.vendor_id";
    $res_v_curr = mysqli_query($conn, $sql_v_curr);
    if($res_v_curr) {
        while($row = mysqli_fetch_assoc($res_v_curr)) {
            $vendor_curr[$row['name'] ?: 'Unknown'] = (float)$row['total'];
        }
    }
    
    $vendor_prev = [];
    $sql_v_prev = "SELECT v.name, SUM(e.exp_total) as total FROM hr_expenses e LEFT JOIN hr_vendors v ON e.vendor_id = v.id WHERE e.salon_id='$salon_id' AND DATE(e.exp_date) BETWEEN '$prev_from' AND '$prev_to' AND e.approval_status != 'rejected' AND e.vendor_id IS NOT NULL GROUP BY e.vendor_id";
    $res_v_prev = mysqli_query($conn, $sql_v_prev);
    if($res_v_prev) {
        while($row = mysqli_fetch_assoc($res_v_prev)) {
            $vendor_prev[$row['name'] ?: 'Unknown'] = (float)$row['total'];
        }
    }

    $data['vendor_comparison'] = [];
    $all_vendors = array_unique(array_merge(array_keys($vendor_curr), array_keys($vendor_prev)));
    foreach($all_vendors as $v) {
        $curr_val = $vendor_curr[$v] ?? 0;
        $prev_val = $vendor_prev[$v] ?? 0;
        $diff = $curr_val - $prev_val;
        $grow = $prev_val > 0 ? ($diff / $prev_val) * 100 : 0;
        $data['vendor_comparison'][] = [
            'vendor' => $v,
            'current' => $curr_val,
            'previous' => $prev_val,
            'growth' => $grow
        ];
    }

    echo json_encode($data);
    exit;
}

if ($method == 'get_pending_approvals') {
    $sql = "SELECT e.*, c.category_name, v.name as vendor_name FROM hr_expenses e LEFT JOIN hr_expenses_category c ON e.exp_catId = c.exp_catId LEFT JOIN hr_vendors v ON e.vendor_id = v.id WHERE e.salon_id='$salon_id' AND e.approval_status = 'pending' ORDER BY e.exp_id DESC";
    $res = mysqli_query($conn, $sql);
    $data = [];
    if($res) {
        while($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

if ($method == 'update_approval') {
    $exp_id = (int)($_POST['exp_id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $comments = mysqli_real_escape_string($conn, $_POST['comments'] ?? '');
    
    if ($exp_id > 0 && in_array($status, ['approved', 'rejected'])) {
        mysqli_query($conn, "UPDATE hr_expenses SET approval_status='$status', approved_by='$user_id' WHERE exp_id='$exp_id' AND salon_id='$salon_id'");
        mysqli_query($conn, "INSERT INTO hr_expense_approvals (exp_id, approver_id, status, comments) VALUES ('$exp_id', '$user_id', '$status', '$comments')");
        
        echo json_encode(['status' => 'success', 'msg' => 'Expense marked as ' . $status]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid parameters']);
    }
    exit;
}
?>

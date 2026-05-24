<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

$method = $_REQUEST["method"] ?? '';

if ($method && function_exists($method)) {
    echo json_encode($method());
} else {
    echo json_encode(['error' => 1, 'msg' => 'Method Not Found']);
}

function get_crm_kpis() {
    global $salon_id, $conn;
    extract($_REQUEST);

    $where = "c.salon_id = '".mysqli_real_escape_string($conn, $salon_id ?? '')."'";

    if (!empty($from_date) && !empty($to_date)) {
        $from = mysqli_real_escape_string($conn, $from_date . ' 00:00:00');
        $to = mysqli_real_escape_string($conn, $to_date . ' 23:59:59');
        $where .= " AND c.cust_added >= '$from' AND c.cust_added <= '$to'";
    }

    $sql = "SELECT 
                COUNT(DISTINCT c.cust_id) as total_customers,
                SUM(c.cust_wallet) as total_wallet,
                SUM(c.cust_outstanding) as base_debt
            FROM hr_customer c 
            WHERE $where";
    $stats = select_row($sql);

    // Calculate additional debt from memberships and packages efficiently
    $memb_debt = (float)select_row("SELECT SUM(m.remaining_amount) as total FROM hr_customer_membership m JOIN hr_customer c ON m.cust_id = c.cust_id WHERE $where")['total'];
    $pkg_debt = (float)select_row("SELECT SUM(p.remaining_amount) as total FROM hr_customer_packages p JOIN hr_customer c ON p.cust_id = c.cust_id WHERE $where")['total'];
    
    $total_debt = (float)($stats['base_debt'] ?? 0) + $memb_debt + $pkg_debt;

    // To get Average LTV (Lifetime Value), we need total revenue from these customers
    // We sum grand_total of all invoices belonging to these customers
    $ltv_sql = "SELECT SUM(i.grand_total) as total_revenue, COUNT(DISTINCT i.invoice_id) as total_visits
                FROM hr_invoice i 
                JOIN hr_customer c ON i.cust_id = c.cust_id
                WHERE $where AND i.delete_bill = 0";
    $ltv_stats = select_row($ltv_sql);

    $total_customers = (int)($stats['total_customers'] ?? 0);
    $total_revenue = (float)($ltv_stats['total_revenue'] ?? 0);
    $avg_ltv = $total_customers > 0 ? ($total_revenue / $total_customers) : 0;

    return [
        'error' => 0,
        'total_customers' => $total_customers,
        'total_wallet' => (float)($stats['total_wallet'] ?? 0),
        'total_debt' => $total_debt,
        'avg_ltv' => $avg_ltv,
        'total_revenue' => $total_revenue
    ];
}

function get_crm_data() {
    global $salon_id, $conn;
    extract($_REQUEST);

    $where = "c.salon_id = '".mysqli_real_escape_string($conn, $salon_id ?? '')."'";

    if (!empty($from_date) && !empty($to_date)) {
        $from = mysqli_real_escape_string($conn, $from_date . ' 00:00:00');
        $to = mysqli_real_escape_string($conn, $to_date . ' 23:59:59');
        $where .= " AND c.cust_added >= '$from' AND c.cust_added <= '$to'";
    }

    if (isset($search['value']) && $search['value'] != '') {
        $search_val = mysqli_real_escape_string($conn, $search['value']);
        $where .= " AND (c.cust_name LIKE '%$search_val%' OR c.cust_mobile LIKE '%$search_val%')";
    }

    // Determine ordering
    // 0: Name, 1: Mobile, 2: Joined, 3: Wallet, 4: Debt, 5: Visits, 6: Last Visit, 7: Total Spent, 8: Subs
    $order_column_index = $order[0]['column'] ?? 2;
    $order_dir = $order[0]['dir'] ?? 'DESC';

    $order_map = [
        0 => 'c.cust_name',
        1 => 'c.cust_mobile',
        2 => 'c.cust_added',
        3 => 'c.cust_wallet',
        4 => 'c.cust_outstanding',
        5 => 'total_visits',
        6 => 'last_visit',
        7 => 'total_spent'
    ];
    $order_by = $order_map[$order_column_index] ?? 'c.cust_added';

    if (!isset($start)) $start = 0;
    if (!isset($length)) $length = 10;

    // Get total count efficiently
    $count_sql = "SELECT COUNT(cust_id) as total FROM hr_customer c WHERE $where";
    $count_res = select_row($count_sql);
    $total_records = $count_res['total'] ?? 0;

    // Get paginated customers first
    $inner_sql = "SELECT cust_id, cust_name, cust_mobile, cust_gender, cust_added, cust_wallet, cust_outstanding 
                  FROM hr_customer c 
                  WHERE $where 
                  ORDER BY $order_by $order_dir";
                  
    if ($length > 0) {
        $inner_sql .= " LIMIT " . intval($start) . ", " . intval($length);
    }

    // Wrap with heavy subqueries so they only run for the limited rows
    $sql = "SELECT c.*,
                (SELECT COUNT(invoice_id) FROM hr_invoice i WHERE i.cust_id = c.cust_id AND i.delete_bill = 0) as total_visits,
                (SELECT MAX(invoice_date) FROM hr_invoice i WHERE i.cust_id = c.cust_id AND i.delete_bill = 0) as last_visit,
                (SELECT SUM(grand_total) FROM hr_invoice i WHERE i.cust_id = c.cust_id AND i.delete_bill = 0) as total_spent,
                (SELECT COUNT(cm_id) FROM hr_customer_membership cm WHERE cm.cust_id = c.cust_id AND cm.status = 'active') as active_memberships,
                (SELECT COUNT(cp_id) FROM hr_customer_packages cp WHERE cp.cust_id = c.cust_id AND cp.status = 'active') as active_packages,
                (SELECT SUM(remaining_amount) FROM hr_customer_membership cm WHERE cm.cust_id = c.cust_id) as memb_debt,
                (SELECT SUM(remaining_amount) FROM hr_customer_packages cp WHERE cp.cust_id = c.cust_id) as pkg_debt
            FROM ($inner_sql) c";

    $results = select_array($sql);
    $data = [];

    foreach ($results as $row) {
        // Calculate Churn Risk (days since last visit)
        $days_since_visit = 'Never';
        $churn_risk = 'high';
        $days = 9999;
        if ($row['last_visit']) {
            $diff = time() - strtotime($row['last_visit']);
            $days = floor($diff / (60 * 60 * 24));
            $days_since_visit = $days . ' days ago';
            if ($days <= 30) $churn_risk = 'low';
            else if ($days <= 90) $churn_risk = 'medium';
        }

        // Segment logic
        $total_spent_val = (float)$row['total_spent'];
        $total_visits_val = (int)$row['total_visits'];
        if ($total_spent_val >= 20000 || $total_visits_val >= 20) {
            $seg = 'VIP'; $seg_bg = '#fef3c7'; $seg_col = '#92400e'; $seg_icon = 'ph-crown-simple';
        } elseif ($days > 90) {
            $seg = 'Lapsed'; $seg_bg = '#fee2e2'; $seg_col = '#7f1d1d'; $seg_icon = 'ph-clock-counter-clockwise';
        } elseif ($total_visits_val >= 3) {
            $seg = 'Regular'; $seg_bg = '#dcfce7'; $seg_col = '#14532d'; $seg_icon = 'ph-check-circle';
        } else {
            $seg = 'New'; $seg_bg = '#e0e7ff'; $seg_col = '#3730a3'; $seg_icon = 'ph-star';
        }
        $segment_badge = '<span style="display:inline-flex;align-items:center;gap:4px;background:'.$seg_bg.';color:'.$seg_col.';padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;"><i class="ph-fill '.$seg_icon.'"></i>'.$seg.'</span>';

        $subs = [];
        if ($row['active_memberships'] > 0) $subs[] = '<span class="badge" style="background:#e0e7ff;color:#4f46e5;padding:4px 8px;border-radius:12px;font-size:11px;font-weight:600;"><i class="ph-fill ph-identification-badge"></i> '.$row['active_memberships'].' Memb</span>';
        if ($row['active_packages'] > 0) $subs[] = '<span class="badge" style="background:#fef3c7;color:#d97706;padding:4px 8px;border-radius:12px;font-size:11px;font-weight:600;"><i class="ph-fill ph-package"></i> '.$row['active_packages'].' Pkg</span>';

        $total_customer_debt = (float)$row['cust_outstanding'] + (float)$row['memb_debt'] + (float)$row['pkg_debt'];

        // Build WhatsApp message based on segment
        if ($seg === 'Lapsed') {
            $wa_text = 'Hello ' . $row['cust_name'] . '! 💆 We miss you at our salon. It\'s been a while – come visit us again and enjoy our services. We\'d love to see you! 😊';
        } elseif ($seg === 'VIP') {
            $wa_text = 'Hello ' . $row['cust_name'] . '! 👑 Thank you for being our valued VIP customer. We have exclusive offers waiting for you. Visit us soon!';
        } else {
            $wa_text = 'Hello ' . $row['cust_name'] . '! 🌸 Hope you\'re doing well! We\'d love to see you at our salon again soon. Book your appointment today!';
        }
        $wa_url = 'https://wa.me/91' . preg_replace('/\D/', '', $row['cust_mobile']) . '?text=' . urlencode($wa_text);
        $wa_btn = '<a href="'.$wa_url.'" target="_blank" style="display:inline-flex;align-items:center;gap:4px;background:#25D366;color:white;border:none;padding:6px 10px;border-radius:6px;font-size:13px;cursor:pointer;text-decoration:none;font-weight:600;" title="Send WhatsApp"><i class="ph-fill ph-whatsapp-logo"></i></a>';
        $view_btn = '<button type="button" class="btn-view modalButtonCommon" data-href="customer_membership_view.php?cust_id='.$row['cust_id'].'" title="View Profile" style="background:#e0e7ff;color:#4f46e5;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;"><i class="ph ph-user-circle"></i></button>';

        $data[] = [
            'cust_id' => $row['cust_id'],
            'customer_info' => '<div style="font-weight:700;color:var(--text-main);">'.htmlspecialchars($row['cust_name']).'</div>
                                <div style="font-size:12px;color:var(--text-muted);">'.($row['cust_gender']?htmlspecialchars($row['cust_gender']).' • ':'').'Acq: '.date('d M Y', strtotime($row['cust_added'])).'</div>',
            'mobile' => htmlspecialchars($row['cust_mobile']),
            'segment' => $segment_badge,
            'joined' => $row['cust_added'],
            'wallet' => (float)$row['cust_wallet'] > 0 ? '<span style="color:#059669;font-weight:600;">₹'.number_format($row['cust_wallet'], 2).'</span>' : '₹0.00',
            'debt' => $total_customer_debt > 0 ? '<span style="color:#dc2626;font-weight:600;">₹'.number_format($total_customer_debt, 2).'</span>' : '₹0.00',
            'visits' => '<div style="font-weight:600;">'.$total_visits_val.'</div>',
            'last_visit' => $row['last_visit'] ? '<div style="font-size:13px;">'.date('d M Y', strtotime($row['last_visit'])).'</div><div style="font-size:11px;color:'.($churn_risk=='low'?'#059669':($churn_risk=='medium'?'#d97706':'#dc2626')).';font-weight:600;">'.$days_since_visit.'</div>' : '<span style="color:var(--text-muted);font-style:italic;">No visits</span>',
            'total_spent' => '<div style="font-weight:700;color:var(--text-main);">₹'.number_format((float)$row['total_spent'], 2).'</div>',
            'subscriptions' => count($subs) > 0 ? implode(' ', $subs) : '<span style="color:var(--text-muted);font-size:12px;">None</span>',
            'action' => $wa_btn . ' ' . $view_btn
        ];
    }

    return [
        'draw' => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $total_records,
        'data' => $data
    ];
}
?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$user_id  = get_session_data('user_id');
$salon_id = get_session_data('salon_id');
$method   = $_REQUEST['method'] ?? '';

if ($method && function_exists($method)) {
    $result = $method();
    echo is_string($result) ? $result : json_encode($result);
} else {
    echo json_encode(['error' => 1, 'msg' => 'Method not found']);
}

// ─── Shared: date where clause ─────────────────────────────────────────────
function date_where(string $alias = 'i'): string {
    global $conn;
    $from = $_REQUEST['from'] ?? '';
    $to   = $_REQUEST['to']   ?? '';
    $w = '';
    if ($from) $w .= " AND {$alias}.invoice_date >= '".mysqli_real_escape_string($conn,$from)." 00:00:00'";
    if ($to)   $w .= " AND {$alias}.invoice_date <= '".mysqli_real_escape_string($conn,$to)." 23:59:59'";
    return $w;
}

// ─── date filter without alias (for plain table queries) ──────────────────────
function date_where_plain(string $col = 'invoice_date'): string {
    global $conn;
    $from = $_REQUEST['from'] ?? '';
    $to   = $_REQUEST['to']   ?? '';
    $w = '';
    if ($from) $w .= " AND {$col} >= '".mysqli_real_escape_string($conn,$from)." 00:00:00'";
    if ($to)   $w .= " AND {$col} <= '".mysqli_real_escape_string($conn,$to)." 23:59:59'";
    return $w;
}

// ═══════════════════════════════════════════════════
// CHURN KPIs  — always ALL-TIME (churn is about absolute last visit, not a date range)
// ═══════════════════════════════════════════════════
function churn_kpis(): array {
    global $salon_id;

    // All registered customers for this salon
    $total = (int)(select_row("SELECT COUNT(DISTINCT cust_id) as n FROM hr_customer WHERE salon_id='$salon_id'")['n']??0);

    // Last visit per customer — no date filter, we need lifetime last-visit
    $last_visits = select_array("SELECT cust_id, MAX(invoice_date) as lv
        FROM hr_invoice WHERE salon_id='$salon_id' AND delete_bill=0
        GROUP BY cust_id");

    $active = $at_risk = $lapsed = 0;
    $visited_count = count($last_visits);
    foreach ($last_visits as $r) {
        $days = (int)floor((time() - strtotime($r['lv'])) / 86400);
        if ($days <= 30)     $active++;
        elseif ($days <= 90) $at_risk++;
        else                 $lapsed++;
    }
    $never = max(0, $total - $visited_count);

    return compact('active','at_risk','lapsed','never');
}

// ═══════════════════════════════════════════════════
// CHURN LIST (DataTables)
// ═══════════════════════════════════════════════════
function churn_list(): array {
    global $salon_id, $conn;
    $days   = (int)($_REQUEST['days'] ?? 30);
    $start  = (int)($_REQUEST['start']  ?? 0);
    $length = (int)($_REQUEST['length'] ?? 15);

    $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

    // Subquery: last visit per customer (all-time, no date filter)
    $base = "FROM hr_invoice i
        JOIN hr_customer c ON i.cust_id = c.cust_id AND c.salon_id = '$salon_id'
        WHERE i.salon_id='$salon_id' AND i.delete_bill=0
        GROUP BY i.cust_id
        HAVING MAX(i.invoice_date) < '$cutoff'";

    $count = (int)(select_row("SELECT COUNT(*) as n FROM (SELECT i.cust_id $base) sub")['n']??0);

    $rows = select_array("SELECT c.cust_name, c.cust_mobile, i.cust_id,
        MAX(i.invoice_date) as last_visit,
        SUM(i.grand_total) as total_spent,
        DATEDIFF(NOW(), MAX(i.invoice_date)) as days_silent
        $base
        ORDER BY days_silent DESC
        LIMIT $start, $length");

    $data = [];
    foreach ($rows as $r) {
        $d = (int)$r['days_silent'];
        $col = $d > 180 ? '#dc2626' : ($d > 90 ? '#d97706' : '#f59e0b');
        $wa_phone = preg_replace('/\D/','',$r['cust_mobile']);
        if (strlen($wa_phone)===10) $wa_phone = '91'.$wa_phone;
        $wa_text = 'Hello '.$r['cust_name'].'! 💆 We miss you at our salon. It\'s been '.$d.' days since your last visit – come back and enjoy some self-care! 😊';
        $wa_url = 'https://wa.me/'.$wa_phone.'?text='.rawurlencode($wa_text);
        $data[] = [
            'customer_info' => '<div style="font-weight:700;">'.htmlspecialchars($r['cust_name']).'</div>',
            'mobile'        => htmlspecialchars($r['cust_mobile']),
            'last_visit'    => date('d M Y', strtotime($r['last_visit'])),
            'days_silent'   => '<span style="font-weight:800;color:'.$col.';">'.$d.' days</span>',
            'total_spent'   => '₹'.number_format((float)$r['total_spent'],0),
            'action'        => '<a href="'.$wa_url.'" target="_blank" style="display:inline-flex;align-items:center;gap:4px;background:#25D366;color:white;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;"><i class="ph-fill ph-whatsapp-logo"></i> Win Back</a>'
        ];
    }
    return ['draw'=>(int)($_REQUEST['draw']??0),'recordsTotal'=>$count,'recordsFiltered'=>$count,'data'=>$data];
}

// ═══════════════════════════════════════════════════
// P-MIX LIST
// ═══════════════════════════════════════════════════
function pmix_list(): array {
    global $salon_id, $conn;
    $dw     = date_where();
    $start  = (int)($_REQUEST['start']  ?? 0);
    $length = (int)($_REQUEST['length'] ?? 20);

    $base = "FROM hr_invoice_service s
        JOIN hr_invoice i ON s.invoice_id = i.invoice_id
        WHERE i.salon_id='$salon_id' AND i.delete_bill=0 $dw AND s.service != ''";

    $count_row = select_row("SELECT COUNT(DISTINCT s.service) as n $base");
    $count = (int)($count_row['n']??0);

    $all_rev = (float)(select_row("SELECT SUM(s.service_price * s.service_qty) as t $base")['t']??1);

    $rows = select_array("SELECT s.service, s.service_cat as category,
        SUM(s.service_qty) as times_sold,
        SUM(s.service_price * s.service_qty) as revenue,
        AVG(s.service_price) as avg_price
        $base
        GROUP BY s.service, s.service_cat
        ORDER BY revenue DESC
        LIMIT $start, $length");

    $data = [];
    $max_rev = $rows ? (float)$rows[0]['revenue'] : 1;
    foreach ($rows as $r) {
        $pct = $max_rev > 0 ? ($r['revenue']/$max_rev)*100 : 0;
        $perf_color = $pct > 60 ? '#22c55e' : ($pct > 25 ? '#f59e0b' : '#ef4444');
        $perf_label = $pct > 60 ? 'Top Seller' : ($pct > 25 ? 'Average' : 'Underperformer');
        $data[] = [
            'service'     => '<div style="font-weight:600;">'.htmlspecialchars($r['service']).'</div>',
            'category'    => '<span style="font-size:12px;color:#64748b;">'.htmlspecialchars($r['category']).'</span>',
            'times_sold'  => '<div style="font-weight:700;">'.(int)$r['times_sold'].'</div>',
            'revenue'     => '<div style="font-weight:800;color:var(--primary);">₹'.number_format((float)$r['revenue'],0).'</div>',
            'avg_price'   => '₹'.number_format((float)$r['avg_price'],0),
            'performance' => '<div style="display:flex;align-items:center;gap:8px;">
                <div style="flex:1;height:6px;background:#f1f5f9;border-radius:4px;overflow:hidden;"><div style="width:'.round($pct).'%;height:100%;background:'.$perf_color.';border-radius:4px;"></div></div>
                <span style="font-size:11px;font-weight:700;color:'.$perf_color.';white-space:nowrap;">'.$perf_label.'</span></div>'
        ];
    }
    return ['draw'=>(int)($_REQUEST['draw']??0),'recordsTotal'=>$count,'recordsFiltered'=>$count,'data'=>$data];
}

// ═══════════════════════════════════════════════════
// P-MIX CHARTS DATA
// ═══════════════════════════════════════════════════
function pmix_charts(): array {
    global $salon_id;
    $dw = date_where();

    $cat_rows = select_array("SELECT s.service_cat as name, SUM(s.service_price * s.service_qty) as revenue
        FROM hr_invoice_service s
        JOIN hr_invoice i ON s.invoice_id = i.invoice_id
        WHERE i.salon_id='$salon_id' AND i.delete_bill=0 $dw AND s.service != ''
        GROUP BY s.service_cat ORDER BY revenue DESC LIMIT 10");

    // Pareto split: top 20% services by count
    $all = select_array("SELECT SUM(s.service_price*s.service_qty) as revenue
        FROM hr_invoice_service s
        JOIN hr_invoice i ON s.invoice_id=i.invoice_id
        WHERE i.salon_id='$salon_id' AND i.delete_bill=0 $dw AND s.service!=''
        GROUP BY s.service ORDER BY revenue DESC");

    $total_services = count($all);
    $top20_count = max(1, (int)ceil($total_services * 0.20));
    $top20_rev = 0; $rest_rev = 0;
    foreach ($all as $i => $row) {
        if ($i < $top20_count) $top20_rev += (float)$row['revenue'];
        else $rest_rev += (float)$row['revenue'];
    }

    return ['categories' => $cat_rows, 'top20_revenue' => $top20_rev, 'rest80_revenue' => $rest_rev];
}

// ═══════════════════════════════════════════════════
// SENTIMENT DATA (works with both old & new feedback tables)
// ═══════════════════════════════════════════════════
function sentiment_data(): array {
    global $salon_id, $conn;
    $dw_date = '';
    $from = $_REQUEST['from'] ?? '';
    $to   = $_REQUEST['to']   ?? '';
    if ($from) $dw_date .= " AND created_date >= '".mysqli_real_escape_string($conn,$from)."'";
    if ($to)   $dw_date .= " AND created_date <= '".mysqli_real_escape_string($conn,$to)." 23:59:59'";

    // Old feedback table uses experience (1-5) and message; new uses rating and comments
    $cols = select_array("SHOW COLUMNS FROM hr_feedback");
    $col_names = array_column($cols, 'Field');

    $rating_col   = in_array('rating', $col_names)   ? 'rating'   : 'experience';
    $comment_col  = in_array('comments', $col_names) ? 'comments' : 'message';
    $name_col     = in_array('cust_name', $col_names) ? 'cust_name' : 'cust_name';
    $date_col     = in_array('created_at', $col_names) ? 'created_at' : 'created_date';

    $where = "salon_id='$salon_id' AND $rating_col > 0 $dw_date";
    $kpis  = select_row("SELECT COUNT(*) as total, AVG($rating_col) as avg_rating,
        SUM(CASE WHEN $rating_col >= 4 THEN 1 ELSE 0 END) as positive,
        SUM(CASE WHEN $rating_col <= 2 THEN 1 ELSE 0 END) as negative,
        SUM(CASE WHEN $rating_col = 1 THEN 1 ELSE 0 END) as r1,
        SUM(CASE WHEN $rating_col = 2 THEN 1 ELSE 0 END) as r2,
        SUM(CASE WHEN $rating_col = 3 THEN 1 ELSE 0 END) as r3,
        SUM(CASE WHEN $rating_col = 4 THEN 1 ELSE 0 END) as r4,
        SUM(CASE WHEN $rating_col = 5 THEN 1 ELSE 0 END) as r5
        FROM hr_feedback WHERE $where");

    $total = (int)($kpis['total']??0);
    $pos   = (int)($kpis['positive']??0);
    $neg   = (int)($kpis['negative']??0);

    $reviews = select_array("SELECT $name_col as cust_name, $rating_col as rating, $comment_col as comments,
        DATE_FORMAT($date_col, '%d %b %Y') as created_at
        FROM hr_feedback WHERE $where AND $comment_col != ''
        ORDER BY $date_col DESC LIMIT 20");

    return [
        'total'        => $total,
        'avg_rating'   => round((float)($kpis['avg_rating']??0), 1),
        'positive'     => $pos,
        'negative'     => $neg,
        'positive_pct' => $total > 0 ? round($pos/$total*100) : 0,
        'negative_pct' => $total > 0 ? round($neg/$total*100) : 0,
        'r1'=>(int)($kpis['r1']??0), 'r2'=>(int)($kpis['r2']??0), 'r3'=>(int)($kpis['r3']??0),
        'r4'=>(int)($kpis['r4']??0), 'r5'=>(int)($kpis['r5']??0),
        'reviews' => $reviews
    ];
}
?>

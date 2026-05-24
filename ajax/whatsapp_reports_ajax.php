<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$salon_id = get_session_data('salon_id');

$start  = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 25);
$daterange = $_POST['daterange'] ?? 'today';
$start_date = date('Y-m-d');
$end_date = date('Y-m-d');

if ($daterange === 'yesterday') {
    $start_date = date('Y-m-d', strtotime('-1 day'));
    $end_date = $start_date;
} elseif ($daterange === 'this_week') {
    $start_date = date('Y-m-d', strtotime('monday this week'));
    $end_date = date('Y-m-d', strtotime('sunday this week'));
} elseif ($daterange === 'this_month') {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
} elseif ($daterange === 'last_month') {
    $start_date = date('Y-m-01', strtotime('last month'));
    $end_date = date('Y-m-t', strtotime('last month'));
} elseif ($daterange === 'custom') {
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date'] ?? date('Y-m-d'));
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date'] ?? date('Y-m-d'));
}

$where = "l.salon_id='$salon_id' AND DATE(l.created_at) >= '$start_date' AND DATE(l.created_at) <= '$end_date'";

if (!empty($_POST['search']['value'])) {
    $sv = mysqli_real_escape_string($conn, $_POST['search']['value']);
    $where .= " AND (l.module LIKE '%$sv%' OR u.full_name LIKE '%$sv%' OR l.message LIKE '%$sv%')";
}

$count = select_row("SELECT COUNT(l.log_id) as n FROM hr_whatsapp_logs l LEFT JOIN hr_user u ON l.user_id = u.user_id WHERE $where")['n'] ?? 0;

$rows = select_array("SELECT l.*, u.full_name 
    FROM hr_whatsapp_logs l 
    LEFT JOIN hr_user u ON l.user_id = u.user_id 
    WHERE $where 
    ORDER BY l.log_id DESC 
    LIMIT $start, $length");

$data = [];
foreach ($rows as $r) {
    $data[] = [
        'created_at' => '<div style="font-size:13px;font-weight:600;">'.date('d M Y', strtotime($r['created_at'])).'</div><div style="font-size:12px;color:#94a3b8;">'.date('h:i A', strtotime($r['created_at'])).'</div>',
        'user'       => '<div style="font-weight:700;">'.htmlspecialchars($r['full_name'] ?? 'System').'</div>',
        'module'     => '<span style="background:#f1f5f9;color:#475569;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">'.htmlspecialchars($r['module']).'</span>',
        'message'    => '<div style="font-size:12px;color:#64748b;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="'.htmlspecialchars($r['message']).'">'.htmlspecialchars($r['message']).'</div>'
    ];
}

$summary_period = select_row("SELECT COUNT(*) as total FROM hr_whatsapp_logs l WHERE $where")['total'] ?? 0;
$summary_today  = select_row("SELECT COUNT(*) as total FROM hr_whatsapp_logs WHERE salon_id='$salon_id' AND DATE(created_at) = CURDATE()")['total'] ?? 0;
$summary_all    = select_row("SELECT COUNT(*) as total FROM hr_whatsapp_logs WHERE salon_id='$salon_id'")['total'] ?? 0;

echo json_encode([
    'draw' => (int)($_POST['draw'] ?? 0),
    'recordsTotal' => $count,
    'recordsFiltered' => $count,
    'data' => $data,
    'summary' => [
        'period' => number_format($summary_period),
        'today'  => number_format($summary_today),
        'all'    => number_format($summary_all)
    ]
]);

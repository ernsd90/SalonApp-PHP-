<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";
include "../loyalty_functions.php";

$user_id  = get_session_data('user_id');
$salon_id = get_session_data('salon_id');
$method   = $_REQUEST['method'] ?? '';

if ($method && function_exists($method)) {
    $result = $method();
    if (is_string($result)) echo $result; // HTML responses
    else echo json_encode($result);
} else {
    echo json_encode(['error' => 1, 'msg' => 'Method not found']);
}

function block_loyalty_customer() {
    $cust_id = (int)($_POST['cust_id'] ?? 0);
    $status = (int)($_POST['status'] ?? 0);
    update_query("UPDATE hr_customer SET loyalty_blocked='$status' WHERE cust_id='$cust_id'");
    return ['error'=>false, 'msg'=> $status ? 'Customer blocked from loyalty program.' : 'Customer unblocked.'];
}

// ─── Loyalty List (DataTables) ────────────────────────────────────────────────
function get_loyalty_list(): array {
    global $salon_id, $conn;
    extract($_REQUEST);

    $where = "c.salon_id = '$salon_id'";
    if (!empty($search['value'])) {
        $sv = mysqli_real_escape_string($conn, $search['value']);
        $where .= " AND (c.cust_name LIKE '%$sv%' OR c.cust_mobile LIKE '%$sv%')";
    }

    $count = select_row("SELECT COUNT(DISTINCT c.cust_id) as n FROM hr_customer c
        JOIN hr_customer_points p ON p.cust_id = c.cust_id WHERE $where")['n'] ?? 0;

    $start  = (int)($start ?? 0);
    $length = (int)($length ?? 25);

    $rows = select_array("SELECT c.cust_id, c.cust_name, c.cust_mobile, c.loyalty_blocked
        FROM hr_customer c
        JOIN hr_customer_points p ON p.cust_id = c.cust_id
        WHERE $where
        GROUP BY c.cust_id
        ORDER BY c.cust_name
        LIMIT $start, $length");

    $data = [];
    foreach ($rows as $r) {
        $lifetime = get_customer_lifetime_spend((int)$r['cust_id']);
        $tier     = get_customer_tier($lifetime);
        $balance  = get_customer_points_balance((int)$r['cust_id']);

        $tier_badge = '<span style="display:inline-flex;align-items:center;gap:4px;background:'.$tier['bg'].';color:'.$tier['color'].';padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;">
            <i class="ph-fill '.$tier['icon'].'"></i>'.$tier['name'].'</span>';

        $next_text = '';
        if ($tier['next']) {
            $gap = $tier['next'] - $lifetime;
            $next_text = '<div style="font-size:11px;color:#94a3b8;margin-top:3px;">₹'.number_format($gap,0).' more to next tier</div>';
        }

        $pts_color = $balance > 0 ? '#059669' : '#94a3b8';

        $salon_name_query = select_row("SELECT salon_name FROM hr_salon WHERE salon_id='$salon_id'");
        $s_name = $salon_name_query ? $salon_name_query['salon_name'] : 'Our Salon';
        
        $wa_msg = "Dear ".trim($r['cust_name']).",\n\nWe hope you are having a wonderful day! 🌟\n\nThis is a friendly update regarding your loyalty points at *".$s_name."*. You currently have *".number_format($balance, 0)." pts* (₹".number_format($balance, 0)." discount value) available to redeem on your next visit!\n\nThank you for choosing us, and we look forward to pampering you again soon! ✨\n\nWarm regards,\nThe ".$s_name." Team";
        $wa_link = "https://api.whatsapp.com/send?phone=91".trim($r['cust_mobile'])."&text=".urlencode($wa_msg);
        
        $wa_btn = '<a href="'.$wa_link.'" target="_blank" data-log-module="Loyalty Ledger" class="wa-track-click" style="background:#25D366;color:white;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px;" title="Send WhatsApp">
            <i class="ph ph-whatsapp-logo"></i> Send</a>';

        $data[] = [
            'customer_info' => '<div style="font-weight:700;">'.htmlspecialchars($r['cust_name']).'</div>',
            'mobile'        => htmlspecialchars($r['cust_mobile']),
            'tier'          => $tier_badge . $next_text,
            'points_display'=> '<div style="font-size:20px;font-weight:800;color:'.$pts_color.';">'.number_format($balance,0).'</div>
                                <div style="font-size:11px;color:#94a3b8;">≈ ₹'.number_format($balance,0).' discount value</div>',
            'lifetime_spend'=> '<div style="font-weight:700;">₹'.number_format($lifetime,0).'</div>',
            'action'        => '<div style="display:flex;gap:6px;">
                <button class="btn-view-history" data-cust="'.$r['cust_id'].'" style="background:#e0e7ff;color:#4f46e5;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-weight:600;"><i class="ph ph-list-numbers"></i> History</button>
                '.($r['loyalty_blocked'] ? 
                '<button class="btn-loyalty-unblock" data-cust="'.$r['cust_id'].'" style="background:#fef2f2;color:#dc2626;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-weight:600;"><i class="ph ph-lock-key"></i> Unblocked</button>' :
                '<button class="btn-loyalty-block" data-cust="'.$r['cust_id'].'" style="background:#f8fafc;color:#475569;border:1px solid #cbd5e1;padding:5px 12px;border-radius:6px;cursor:pointer;font-weight:600;"><i class="ph ph-lock-key-open"></i> Block</button>'
                ).'
                '.$wa_btn.'
                </div>'
        ];
    }

    return ['draw' => (int)($_REQUEST['draw']??0), 'recordsTotal' => $count, 'recordsFiltered' => $count, 'data' => $data];
}

// ─── Point History (HTML modal) ────────────────────────────────────────────────
function get_point_history(): string {
    global $conn, $salon_id;
    $cust_id = (int)($_REQUEST['cust_id'] ?? 0);
    if (!$cust_id) return '<div style="padding:20px;">Invalid customer.</div>';

    $cust = select_row("SELECT cust_name, cust_mobile FROM hr_customer WHERE cust_id='$cust_id'");
    $balance  = get_customer_points_balance($cust_id);
    $lifetime = get_customer_lifetime_spend($cust_id);
    $tier     = get_customer_tier($lifetime, $salon_id);

    $rows = select_array("SELECT * FROM hr_customer_points WHERE cust_id='$cust_id' ORDER BY created_at DESC LIMIT 50");

    $rows_html = '';
    foreach ($rows as $r) {
        if ($r['type'] === 'earn')   { $type_color = '#059669'; $type_icon = 'ph-plus-circle'; }
        elseif ($r['type'] === 'redeem') { $type_color = '#dc2626'; $type_icon = 'ph-minus-circle'; }
        elseif ($r['type'] === 'expire') { $type_color = '#94a3b8'; $type_icon = 'ph-clock'; }
        else { $type_color = '#475569'; $type_icon = 'ph-circle'; }
        $sign = ($r['type'] === 'earn') ? '+' : '-';
        $rows_html .= '
        <tr>
            <td style="font-size:12px;color:#475569;">'.date('d M Y', strtotime($r['created_at'])).'</td>
            <td><i class="ph-fill '.$type_icon.'" style="color:'.$type_color.';"></i> '.ucfirst($r['type']).'</td>
            <td style="font-weight:700;color:'.$type_color.';">'.$sign.number_format($r['points'],0).' pts</td>
            <td style="font-size:12px;color:#64748b;">'.htmlspecialchars($r['remark']).'</td>
            <td style="font-size:12px;color:#94a3b8;">'.($r['expiry_date'] ? date('d M Y', strtotime($r['expiry_date'])) : '—').'</td>
        </tr>';
    }

    $tier_badge = '<span style="background:'.$tier['bg'].';color:'.$tier['color'].';padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><i class="ph-fill '.$tier['icon'].'"></i> '.$tier['name'].'</span>';
    $next_note = $tier['next'] ? '₹'.number_format($tier['next']-$lifetime,0).' to next tier' : 'Maximum tier reached 👑';

    return '
    <div style="padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
            <div>
                <h3 style="margin:0;font-size:18px;font-weight:800;">'.htmlspecialchars($cust['cust_name']).'</h3>
                <div style="color:#64748b;font-size:13px;margin-top:4px;">'.htmlspecialchars($cust['cust_mobile']).' &nbsp;|&nbsp; '.$tier_badge.'</div>
            </div>
            <button class="close-modal" style="background:none;border:none;font-size:24px;cursor:pointer;color:#94a3b8;"><i class="ph ph-x"></i></button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:12px;color:#16a34a;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Points Balance</div>
                <div style="font-size:32px;font-weight:800;color:#14532d;">'.number_format($balance,0).'</div>
                <div style="font-size:12px;color:#16a34a;">≈ ₹'.number_format($balance,0).' discount value</div>
            </div>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:12px;color:#475569;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Lifetime Spend</div>
                <div style="font-size:32px;font-weight:800;color:#0f172a;">₹'.number_format($lifetime,0).'</div>
                <div style="font-size:12px;color:#94a3b8;">'.$next_note.'</div>
            </div>
        </div>
        <div style="max-height:300px;overflow-y:auto;border-radius:10px;border:1px solid #e2e8f0;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;">Date</th>
                    <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;">Type</th>
                    <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;">Points</th>
                    <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;">Remark</th>
                    <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;">Expires</th>
                </tr>
            </thead>
            <tbody>'.$rows_html.'</tbody>
        </table>
        </div>
    </div>';
}

// ─── Expire old points ────────────────────────────────────────────────────────
function run_expire_points(): array {
    expire_old_points();
    return ['error' => 0, 'msg' => 'Expired old points successfully. Ledger updated.'];
}

// ─── Tier CRUD ────────────────────────────────────────────────────────────────
function save_tier(): array {
    global $salon_id, $conn;
    $tier_id  = (int)($_POST['tier_id'] ?? 0);
    $name     = mysqli_real_escape_string($conn, trim($_POST['tier_name']  ?? ''));
    $min      = (float)($_POST['min_spend']        ?? 0);
    $pct      = (float)($_POST['cashback_percent'] ?? 0);
    $color    = mysqli_real_escape_string($conn, $_POST['color']   ?? '#9a3412');
    $bg       = mysqli_real_escape_string($conn, $_POST['bg_color'] ?? '#fff7ed');
    $icon     = mysqli_real_escape_string($conn, $_POST['icon']    ?? 'ph-star');

    if (!$name || $pct < 0 || $pct > 100)
        return ['error' => 1, 'msg' => 'Invalid input. Check tier name and cashback %.'];

    if ($tier_id > 0) {
        update_query("UPDATE hr_loyalty_tiers SET
            tier_name='$name', min_spend='$min', cashback_percent='$pct',
            color='$color', bg_color='$bg', icon='$icon'
            WHERE tier_id='$tier_id' AND salon_id='$salon_id'");
        return ['error' => 0, 'msg' => "Tier '$name' updated successfully."];
    } else {
        // auto sort_order = max + 1
        $max = (int)(select_row("SELECT MAX(sort_order) as m FROM hr_loyalty_tiers WHERE salon_id='$salon_id'")['m'] ?? 0);
        insert_query("INSERT INTO hr_loyalty_tiers SET
            salon_id='$salon_id', tier_name='$name', min_spend='$min',
            cashback_percent='$pct', color='$color', bg_color='$bg', icon='$icon',
            sort_order='".($max+1)."'");
        return ['error' => 0, 'msg' => "Tier '$name' created successfully."];
    }
}

function get_tier(): array {
    global $salon_id;
    $tier_id = (int)($_REQUEST['tier_id'] ?? 0);
    $t = select_row("SELECT * FROM hr_loyalty_tiers WHERE tier_id='$tier_id' AND salon_id='$salon_id'");
    return $t ?: ['error' => 1, 'msg' => 'Not found'];
}

function delete_tier(): array {
    global $salon_id;
    $tier_id = (int)($_REQUEST['tier_id'] ?? 0);
    update_query("DELETE FROM hr_loyalty_tiers WHERE tier_id='$tier_id' AND salon_id='$salon_id'");
    return ['error' => 0, 'msg' => 'Tier deleted.'];
}

// ─── POS: Get points balance for a customer ───────────────────────────────────
function get_points_for_pos(): array {
    global $salon_id;
    $cust_id = (int)($_REQUEST['cust_id'] ?? 0);
    if (!$cust_id) return ['balance' => 0, 'tier' => 'Bronze'];

    $cdata = select_row("SELECT loyalty_blocked FROM hr_customer WHERE cust_id='$cust_id'");
    if ($cdata && $cdata['loyalty_blocked'] == '1') {
        return ['balance' => 0, 'tier' => 'Blocked'];
    }

    $balance  = get_customer_points_balance($cust_id);
    $lifetime = get_customer_lifetime_spend($cust_id);
    $tier     = get_customer_tier_db($lifetime, (int)$salon_id);

    return [
        'balance'  => round($balance, 2),
        'tier'     => $tier['tier_name'] ?? 'Bronze',
        'cashback' => $tier['cashback_percent'] ?? 0,
    ];
}

// ─── Save Loyalty Settings ────────────────────────────────────────────────────
function save_loyalty_settings(): array {
    global $salon_id;
    $enabled = (int)($_POST['loyalty_enabled']      ?? 1);
    $pts     = (float)($_POST['profile_complete_points'] ?? 50);
    $enabled = max(0, min(1, $enabled));
    $pts     = max(0, min(10000, $pts));

    update_query("INSERT INTO hr_loyalty_settings (salon_id, loyalty_enabled, profile_complete_points)
        VALUES ('$salon_id', '$enabled', '$pts')
        ON DUPLICATE KEY UPDATE loyalty_enabled='$enabled', profile_complete_points='$pts'");

    $label = $enabled ? 'Loyalty program is now ACTIVE.' : 'Loyalty program has been DISABLED.';
    return ['error' => 0, 'msg' => $label];
}
?>

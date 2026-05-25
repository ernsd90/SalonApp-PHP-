<?php 
include "config.php";
include "function.php"; 

$salon_id = get_session_data('salon_id');

if(isset($_GET['view']) && $_GET['view'] == 1){
    $raw_id = $_GET['invoice_id'];
    $invoice_id = is_numeric($raw_id) ? intval($raw_id) : intval(base64_decode($raw_id));
    $inv_salon = select_row("SELECT salon_id FROM hr_invoice where invoice_id='".mysqli_real_escape_string($conn, $invoice_id)."' ");
    if($inv_salon) extract($inv_salon);
}else{
    $invoice_id = $_GET['invoice_id'];
}

if(is_numeric($invoice_id)){
    $salon = select_row("SELECT salon_name,salon_address,salon_contact,salon_gst,gst_percentage,include_gst,logo,firm_name FROM `hr_salon` WHERE `salon_id` = $salon_id");
    if($salon) extract($salon);
    
    $invoice_data = select_row("SELECT * FROM `hr_invoice` where invoice_id='".$invoice_id."' ");
    if($invoice_data != false){
        foreach($invoice_data as $var => $value){
            $$var = $value;
        }

        $totol_gst = $service_total_tax;
        $all_service = select_array("SELECT service,service_qty,service_price FROM `hr_invoice_service` where `invoice_id`='".$invoice_id."'");
        
        $wallet_data = select_row("SELECT cust_wallet FROM `hr_customer` where `salon_id`='".$salon_id."' and cust_id='".$cust_id."' ORDER BY `cust_wallet` DESC");
        if($wallet_data) extract($wallet_data);

    }else{
        die("Invalid Invoice!!!");
    }
}else{
    die("Invalid Invoice!!!");
}
?>

<?php
if (isset($_GET['wa']) && $_GET['wa'] == 1) {
    $wa_phone = $_GET['wa_phone'] ?? '';
    $wa_msg   = $_GET['wa_msg'] ?? '';
    
    if (empty($wa_phone) && !empty($invoice_data['cust_mob'])) {
        $wa_phone = $invoice_data['cust_mob'];
    }
    if (empty($wa_msg) && !empty($invoice_data)) {
        $share_token = function_exists('getInvoiceShareToken') ? getInvoiceShareToken($invoice_id) : $invoice_id;
        $invoice_url = DOMAIN_SOFTWARE . "inv.php?t=" . $share_token;
        $feedback_url = DOMAIN_SOFTWARE . "feedback.php?inv=" . $invoice_id;
        $s_name = $salon['salon_name'] ?? 'Our Salon';
        
        // ── Loyalty & Profile details fallback calculation ────────────────
        $pts_earned = 0;
        $pts_row = select_row("SELECT points FROM hr_customer_points WHERE invoice_id='$invoice_id' AND type='earn' AND remark LIKE 'Cashback%'");
        if ($pts_row) {
            $pts_earned = (float)$pts_row['points'];
        }
        
        $loyalty_on = true;
        $ls_row = select_row("SELECT loyalty_enabled, profile_complete_points FROM hr_loyalty_settings WHERE salon_id='$salon_id'");
        if ($ls_row && (int)$ls_row['loyalty_enabled'] === 0) $loyalty_on = false;
        
        $cdata = select_row("SELECT loyalty_blocked FROM hr_customer WHERE cust_id='".$invoice_data['cust_id']."'");
        if ($cdata && $cdata['loyalty_blocked'] == '1') $loyalty_on = false;
        
        $total_points = 0;
        $is_profile_complete = true;
        $profile_points = 0;
        if ($loyalty_on && !empty($invoice_data['cust_id'])) {
            require_once __DIR__.'/loyalty_functions.php';
            $total_points = get_customer_points_balance((int)$invoice_data['cust_id']);
            $cust_profile = select_row("SELECT cust_dob, cust_anniversary, cust_gender FROM hr_customer WHERE cust_id = '".$invoice_data['cust_id']."'");
            if ($cust_profile) {
                if (empty($cust_profile['cust_dob']) || $cust_profile['cust_dob'] == '0000-00-00' || $cust_profile['cust_dob'] == '1970-01-01') {
                    $is_profile_complete = false;
                }
                if (empty($cust_profile['cust_anniversary']) || $cust_profile['cust_anniversary'] == '0000-00-00' || $cust_profile['cust_anniversary'] == '1970-01-01') {
                    $is_profile_complete = false;
                }
                if (empty($cust_profile['cust_gender']) || trim($cust_profile['cust_gender']) == '') {
                    $is_profile_complete = false;
                }
            } else {
                $is_profile_complete = false;
            }
            if ($ls_row) {
                $profile_points = (float)($ls_row['profile_complete_points'] ?? 0);
            }
        }
        
        $c_first = ucfirst(strtolower(explode(' ', trim($invoice_data['cust_name'] ?? ''))[0]));
        $pm_mode = $invoice_data['payment_mode'] ?? 'cash';
        
        $wa_msg = "Dear {$c_first},\n\n";
        $wa_msg .= "Thank you for visiting *{$s_name}*! We loved having you.\n\n";
        if ($pm_mode == 'pkg') {
            $wallet_data = select_row("SELECT cust_wallet FROM `hr_customer` where `salon_id`='".$salon_id."' and cust_id='".$invoice_data['cust_id']."' ORDER BY `cust_wallet` DESC");
            $balance = $wallet_data ? (float)$wallet_data['cust_wallet'] : 0;
            
            $wa_msg .= "Your visit has been recorded.\n";
            $wa_msg .= "Amount  : *Rs." . number_format($invoice_data['grand_total'] ?? 0, 2) . "*\n";
            $wa_msg .= "Mode    : Package / Wallet\n";
            $wa_msg .= "Balance : *Rs." . number_format($balance, 2) . "* remaining\n\n";
        } else {
            $wa_msg .= "Your bill for *Rs." . number_format($invoice_data['grand_total'] ?? 0, 2) . "* has been generated.\n";
            $wa_msg .= "Mode   : " . ucfirst(strtolower($pm_mode)) . "\n\n";
        }
        $wa_msg .= "🧾 View Receipt: {$invoice_url}\n\n";
        
        if ($loyalty_on) {
            $wa_msg .= "💎 Loyalty Points:\n";
            $wa_msg .= "• Earned this visit: " . number_format($pts_earned, 0) . " pts\n";
            $wa_msg .= "• Total Balance: " . number_format($total_points, 0) . " pts\n\n";
            
            if (!$is_profile_complete && $profile_points > 0) {
                $complete_profile_url = DOMAIN_SOFTWARE . "complete_profile.php?inv=" . $invoice_id;
                $wa_msg .= "🎁 Complete your profile to get " . number_format($profile_points, 0) . " bonus points!\n🔗 Fill details here: {$complete_profile_url}\n\n";
            }
        }
        
        $wa_msg .= "⭐ We'd love your feedback! Please rate your experience here:\n{$feedback_url}";
    }
    
    $clean_phone = preg_replace('/\D/', '', $wa_phone);
    if (strlen($clean_phone) == 10) {
        $clean_phone = '91' . $clean_phone;
    }
    
    $wa_link = 'https://api.whatsapp.com/send?phone=' . $clean_phone . '&text=' . rawurlencode($wa_msg);
    header("Location: " . $wa_link);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #<?= $invoice_number ?> - <?= $salon_name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #f1f5f9;
            color: #0f172a;
            padding: 20px;
        }

        .receipt {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 10px 12px 16px 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header .salon-name {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header .firm-name {
            font-size: 11px;
            color: #475569;
            margin-top: 2px;
        }

        .header .salon-info {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.5;
        }

        .section-divider {
            border: none;
            border-top: 1px dashed #cbd5e1;
            margin: 8px 0;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .meta-row .label { color: #64748b; }
        .meta-row .value { font-weight: bold; text-align: right; max-width: 55%; }

        .cust-name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 11px;
        }

        table.items thead th {
            border-bottom: 1px solid #94a3b8;
            padding: 4px 2px;
            font-weight: bold;
            text-align: left;
        }

        table.items thead th.right,
        table.items tbody td.right {
            text-align: right;
        }

        table.items tbody td {
            padding: 5px 2px;
            border-bottom: 1px dotted #e2e8f0;
            vertical-align: top;
        }

        .totals {
            margin-top: 8px;
            font-size: 11px;
        }

        .totals .total-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }

        .totals .total-row.discount { color: #16a34a; }
        .totals .total-row.gst     { color: #475569; }

        .totals .grand-line {
            border-top: 2px dashed #0f172a;
            margin-top: 6px;
            padding-top: 6px;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .payment-badge {
            text-align: center;
            margin-top: 8px;
            padding: 4px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 10px;
            color: #64748b;
            border-top: 2px dashed #cbd5e1;
            padding-top: 10px;
            line-height: 1.7;
        }

        @media print {
            html, body {
                background: white;
                padding: 0;
                margin: 0;
                width: 80mm;
            }
            .receipt {
                box-shadow: none;
                width: 100%;
                padding: 4px 8px 8px 8px;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body <?= (!isset($_GET['view']) || $_GET['view'] != 1) ? 'onload="window.print()"' : '' ?>>

<div class="receipt">

    <!-- Header -->
    <div class="header">
        <?php if(!empty($logo)): ?>
            <img src="images/<?= $logo ?>" style="max-height:50px; margin-bottom:6px;" alt="Logo"><br>
        <?php endif; ?>
        <div class="salon-name"><?= htmlspecialchars($firm_name ?: $salon_name) ?></div>
        <?php if($firm_name && $firm_name != $salon_name): ?>
            <div class="firm-name"><?= htmlspecialchars($salon_name) ?></div>
        <?php endif; ?>
        <div class="salon-info">
            <?= htmlspecialchars($salon_address) ?><br>
            Tel: <?= htmlspecialchars($salon_contact) ?>
            <?php if(!empty($salon_gst)): ?>
                <br>GSTIN: <?= htmlspecialchars($salon_gst) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Invoice Meta -->
    <div class="meta-row"><span class="label">Invoice #</span><span class="value"><?= $invoice_number ?></span></div>
    <div class="meta-row"><span class="label">Date</span><span class="value"><?= date("d M Y, h:i A", strtotime($invoice_date)) ?></span></div>

    <hr class="section-divider">

    <!-- Customer Info -->
    <div class="cust-name"><?= htmlspecialchars($cust_name) ?></div>
    <div style="font-size:10px; color:#64748b;"><?= htmlspecialchars($cust_mob) ?></div>
    <?php if(!empty($cust_ref_by) && !in_array($cust_ref_by, ["","0","Staff","Instagram","Google Ads","Facebook","WalkIn"])): ?>
        <div style="font-size:10px; color:#64748b; margin-top:2px;">Ref: <?= htmlspecialchars($cust_ref_by) ?></div>
    <?php endif; ?>

    <hr class="section-divider">

    <!-- Items -->
    <table class="items">
        <thead>
            <tr>
                <th style="width:55%;">Item</th>
                <th style="width:10%; text-align:center;">Qty</th>
                <th class="right" style="width:35%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($all_service as $services): ?>
            <tr>
                <td><?= htmlspecialchars(ucwords(strtolower($services['service']))) ?></td>
                <td style="text-align:center;"><?= $services['service_qty'] ?></td>
                <td class="right">₹<?= number_format((float)$services['service_price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <?php
        $display_subtotal = $service_total;
        if(isset($include_gst) && $include_gst == 1 && $totol_gst > 0) {
            $display_subtotal = $service_total - $totol_gst;
        }
        ?>
        <div class="total-row"><span>Subtotal</span><span>₹<?= number_format((float)$display_subtotal, 2) ?></span></div>

        <?php if($totol_gst > 0): ?>
        <div class="total-row gst"><span>CGST (<?= number_format((float)$gst_percentage/2, 1) ?>%)</span><span>₹<?= number_format((float)$totol_gst/2, 2) ?></span></div>
        <div class="total-row gst"><span>SGST (<?= number_format((float)$gst_percentage/2, 1) ?>%)</span><span>₹<?= number_format((float)$totol_gst/2, 2) ?></span></div>
        <?php endif; ?>

        <?php if($discount > 0): ?>
        <div class="total-row discount"><span>Discount</span><span>-₹<?= number_format((float)$discount, 2) ?></span></div>
        <?php endif; ?>

        <?php if(!empty($extra_fee) && $extra_fee > 0): ?>
        <div class="total-row"><span>Additional Fee</span><span>₹<?= number_format((float)$extra_fee, 2) ?></span></div>
        <?php endif; ?>

        <div class="grand-line">
            <span>TOTAL</span>
            <span>₹<?= number_format((float)$grand_total, 2) ?></span>
        </div>
    </div>

    <!-- Payment Mode Badge -->
    <div class="payment-badge">
        Paid via: <?= strtoupper(htmlspecialchars($payment_mode)) ?>
    </div>

    <?php if($payment_mode == 'pkg' && isset($cust_wallet)): ?>
    <div style="text-align:center; margin-top:6px; font-size:11px; color:#4f46e5; font-weight:bold;">
        Wallet Balance: ₹<?= number_format((float)$cust_wallet, 2) ?>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        *** Thank You! ***<br>
        Visit us again at <?= htmlspecialchars($salon_name) ?><br>
        <?= htmlspecialchars($salon_contact) ?>

        <?php
        // Use DOMAIN_SOFTWARE constant to build the correct public feedback URL
        $feedback_url = rtrim(DOMAIN_SOFTWARE, '/') . '/feedback.php?inv=' . $invoice_id;
        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($feedback_url);

        // Loyalty points display on invoice
        $pts_balance = 0;
        $ls = select_row("SELECT loyalty_enabled FROM hr_loyalty_settings WHERE salon_id='$salon_id'");
        if ((!$ls || $ls['loyalty_enabled'] == 1) && !empty($cust_id)) {
            require_once __DIR__.'/loyalty_functions.php';
            $pts_balance = get_customer_points_balance((int)$cust_id);
        }
        ?>
        <div style="margin-top: 10px; border-top: 1px dashed #cbd5e1; padding-top: 10px;">
            <div style="font-size:9px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px;">⭐ Rate Your Visit</div>
            <img src="<?= $qr_url ?>" alt="Feedback QR" style="width:70px; height:70px;"><br>
            <div style="font-size:9px; color:#94a3b8; margin-top:3px;">Scan to share your feedback</div>
        </div>
        <?php if ($pts_balance > 0): ?>
        <div style="margin-top:8px;padding:6px 10px;background:#f5f3ff;border-radius:8px;font-size:10px;color:#5b21b6;font-weight:700;text-align:center;">
            💎 Your Loyalty Points Balance: <?= number_format($pts_balance, 0) ?> pts (≈ ₹<?= number_format($pts_balance, 0) ?> discount value)
        </div>
        <?php endif; ?>
    </div>

</div>

<?php
$wa_phone = $_GET['wa_phone'] ?? '';
$wa_msg   = $_GET['wa_msg'] ?? '';

if (empty($wa_phone) && !empty($invoice_data['cust_mob'])) {
    $wa_phone = $invoice_data['cust_mob'];
}
if (empty($wa_msg) && !empty($invoice_data)) {
    $share_token = function_exists('getInvoiceShareToken') ? getInvoiceShareToken($invoice_id) : $invoice_id;
    $invoice_url = DOMAIN_SOFTWARE . "inv.php?t=" . $share_token;
    $feedback_url = DOMAIN_SOFTWARE . "feedback.php?inv=" . $invoice_id;
    $s_name = $salon['salon_name'] ?? 'Our Salon';
    
    // ── Loyalty & Profile details fallback calculation ────────────────
    $pts_earned = 0;
    $pts_row = select_row("SELECT points FROM hr_customer_points WHERE invoice_id='$invoice_id' AND type='earn' AND remark LIKE 'Cashback%'");
    if ($pts_row) {
        $pts_earned = (float)$pts_row['points'];
    }
    
    $loyalty_on = true;
    $ls_row = select_row("SELECT loyalty_enabled, profile_complete_points FROM hr_loyalty_settings WHERE salon_id='$salon_id'");
    if ($ls_row && (int)$ls_row['loyalty_enabled'] === 0) $loyalty_on = false;
    
    $cdata = select_row("SELECT loyalty_blocked FROM hr_customer WHERE cust_id='".$invoice_data['cust_id']."'");
    if ($cdata && $cdata['loyalty_blocked'] == '1') $loyalty_on = false;
    
    $total_points = 0;
    $is_profile_complete = true;
    $profile_points = 0;
    if ($loyalty_on && !empty($invoice_data['cust_id'])) {
        require_once __DIR__.'/loyalty_functions.php';
        $total_points = get_customer_points_balance((int)$invoice_data['cust_id']);
        $cust_profile = select_row("SELECT cust_dob, cust_anniversary, cust_gender FROM hr_customer WHERE cust_id = '".$invoice_data['cust_id']."'");
        if ($cust_profile) {
            if (empty($cust_profile['cust_dob']) || $cust_profile['cust_dob'] == '0000-00-00' || $cust_profile['cust_dob'] == '1970-01-01') {
                $is_profile_complete = false;
            }
            if (empty($cust_profile['cust_anniversary']) || $cust_profile['cust_anniversary'] == '0000-00-00' || $cust_profile['cust_anniversary'] == '1970-01-01') {
                $is_profile_complete = false;
            }
            if (empty($cust_profile['cust_gender']) || trim($cust_profile['cust_gender']) == '') {
                $is_profile_complete = false;
            }
        } else {
            $is_profile_complete = false;
        }
        if ($ls_row) {
            $profile_points = (float)($ls_row['profile_complete_points'] ?? 0);
        }
    }
    
    $c_first = ucfirst(strtolower(explode(' ', trim($invoice_data['cust_name'] ?? ''))[0]));
    $pm_mode = $invoice_data['payment_mode'] ?? 'cash';
    
    $wa_msg = "Dear {$c_first},\n\n";
    $wa_msg .= "Thank you for visiting *{$s_name}*! We loved having you.\n\n";
    if ($pm_mode == 'pkg') {
        $wallet_data = select_row("SELECT cust_wallet FROM `hr_customer` where `salon_id`='".$salon_id."' and cust_id='".$invoice_data['cust_id']."' ORDER BY `cust_wallet` DESC");
        $balance = $wallet_data ? (float)$wallet_data['cust_wallet'] : 0;
        
        $wa_msg .= "Your visit has been recorded.\n";
        $wa_msg .= "Amount  : *Rs." . number_format($invoice_data['grand_total'] ?? 0, 2) . "*\n";
        $wa_msg .= "Mode    : Package / Wallet\n";
        $wa_msg .= "Balance : *Rs." . number_format($balance, 2) . "* remaining\n\n";
    } else {
        $wa_msg .= "Your bill for *Rs." . number_format($invoice_data['grand_total'] ?? 0, 2) . "* has been generated.\n";
        $wa_msg .= "Mode   : " . ucfirst(strtolower($pm_mode)) . "\n\n";
    }
    $wa_msg .= "🧾 View Receipt: {$invoice_url}\n\n";
    
    if ($loyalty_on) {
        $wa_msg .= "💎 Loyalty Points:\n";
        $wa_msg .= "• Earned this visit: " . number_format($pts_earned, 0) . " pts\n";
        $wa_msg .= "• Total Balance: " . number_format($total_points, 0) . " pts\n\n";
        
        if (!$is_profile_complete && $profile_points > 0) {
            $complete_profile_url = DOMAIN_SOFTWARE . "complete_profile.php?inv=" . $invoice_id;
            $wa_msg .= "🎁 Complete your profile to get " . number_format($profile_points, 0) . " bonus points!\n🔗 Fill details here: {$complete_profile_url}\n\n";
        }
    }
    
    $wa_msg .= "⭐ We'd love your feedback! Please rate your experience here:\n{$feedback_url}";
}

$clean_phone_bottom = preg_replace('/\D/', '', $wa_phone);
if (strlen($clean_phone_bottom) == 10) {
    $clean_phone_bottom = '91' . $clean_phone_bottom;
}

$wa_link   = 'https://api.whatsapp.com/send?phone=' . $clean_phone_bottom
           . '&text=' . rawurlencode($wa_msg);
$skip_url  = DOMAIN_SOFTWARE . 'invoices.php';
?>

<?php if(isset($_GET['type']) && $_GET['type'] == 'close'): ?>
<script>window.onfocus = function() { window.close(); }</script>
<?php elseif(!isset($_GET['view']) || $_GET['view'] != 1): ?>

<!-- ── WhatsApp Popup ─────────────────────────────────────────────── -->
<style>
#wa-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(3px);
    z-index: 9999;
    align-items: flex-end;
    justify-content: center;
}
#wa-overlay.show { display: flex; }

#wa-sheet {
    background: #fff;
    width: 100%;
    max-width: 420px;
    border-radius: 20px 20px 0 0;
    padding: 28px 24px 32px;
    text-align: center;
    animation: slideUp .35s cubic-bezier(.22,.61,.36,1) forwards;
    box-shadow: 0 -8px 40px rgba(0,0,0,.18);
}
@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.wa-icon {
    width: 64px; height: 64px;
    background: #25D366;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
    box-shadow: 0 4px 16px rgba(37,211,102,.4);
}
.wa-icon svg { width: 36px; height: 36px; fill: white; }

#wa-sheet h2 {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 18px; font-weight: 700;
    color: #0f172a; margin-bottom: 6px;
}
#wa-sheet p {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 13px; color: #64748b; margin-bottom: 22px; line-height: 1.5;
}

.wa-btn-send {
    display: block; width: 100%;
    padding: 14px;
    background: #25D366;
    color: white;
    border: none; border-radius: 12px;
    font-size: 15px; font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    margin-bottom: 10px;
    transition: background .2s;
}
.wa-btn-send:hover { background: #1ebe5d; }

.wa-btn-skip {
    display: block; width: 100%;
    padding: 12px;
    background: #f1f5f9;
    color: #475569;
    border: none; border-radius: 12px;
    font-size: 14px; font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .2s;
}
.wa-btn-skip:hover { background: #e2e8f0; }
</style>

<div id="wa-overlay">
    <div id="wa-sheet">
        <div class="wa-icon">
            <!-- WhatsApp SVG icon -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </div>
        <h2>Send Receipt on WhatsApp?</h2>
        <p>Share the invoice link with <strong><?= htmlspecialchars($_GET['wa_phone'] ?? '') ?></strong> via WhatsApp.</p>

        <a id="wa-send-btn" href="<?= $wa_link ?>" target="_blank" data-log-module="POS Print Invoice" class="wa-btn-send wa-track-click" onclick="skipToHome()">
            📲 Send on WhatsApp
        </a>
        <a href="<?= $skip_url ?>" class="wa-btn-skip">Skip, go back to billing</a>
    </div>
</div>

<script>
function showWaPopup() {
    document.getElementById('wa-overlay').classList.add('show');
}
function skipToHome() {
    var req = new XMLHttpRequest();
    req.open('POST', 'ajax/whatsapp_log_ajax.php', true);
    req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    req.send('module=POS Print Invoice&target_url=' + encodeURIComponent('<?= $wa_link ?>'));
    
    setTimeout(function(){ window.location.href = '<?= $skip_url ?>'; }, 1500);
}

// Show popup after print dialog closes (afterprint) or after 2s fallback
if (window.matchMedia) {
    var mql = window.matchMedia('print');
    mql.addEventListener('change', function(e) {
        if (!e.matches) showWaPopup();
    });
}
window.addEventListener('afterprint', showWaPopup);

// 2-second fallback in case afterprint doesn't fire (some browsers/printers)
setTimeout(showWaPopup, 2000);
</script>

<?php endif; ?>


</body>
</html>

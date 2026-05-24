<?php 
include_once "config.php";
include_once "function.php";
include_once "loyalty_functions.php";
$salon_id = get_session_data('salon_id');

$date_now = (new DateTime())->format("Y-m-d H:i:s");
$kk = 0;


$salon = select_row("SELECT salon_name, salon_address, salon_contact, salon_gst, msg_id as senderid, whatsapp_enable FROM `hr_salon` WHERE `salon_id` = $salon_id");
$salon_name = $salon['salon_name'];
$salon_address = $salon['salon_address'];
$salon_contact = $salon['salon_contact'];
$salon_gst = $salon['salon_gst'];
$senderid = $salon['senderid'];
$whatsapp_enable = $salon['whatsapp_enable'];

$staff_service_data = array();

if(isset($_POST['cust_mob'])){
    extract($_POST);

    // Safe defaults for optional fields not always submitted by the V3 form
    $cust_reffer  = isset($cust_reffer) ? mysqli_real_escape_string($conn, $cust_reffer) : '';
    $extra_tax    = floatval($extra_tax ?? 0);
    $cust_gender  = isset($cust_gender) ? $cust_gender : '';
    $billing_remark = isset($billing_remark) ? mysqli_real_escape_string($conn, $billing_remark) : '';
    $discount     = floatval($discount ?? 0);
    $grandTotal   = floatval($grandTotal ?? 0);
    $discount_mode = intval($discount_mode ?? 0);
    $job_card_id  = intval($job_card_id ?? 0);

    $date_invoice = new DateTime($_POST['invoice_date']);
    $date_now = $date_invoice->format('Y-m-d').' '.date('H:i:s');

    $cust_name = ucwords(strtolower(trim($cust_name)));

    $cust = select_row("SELECT cust_id FROM `hr_customer` WHERE cust_mobile = '".$cust_mob."' AND `salon_id` = '".$salon_id."' ORDER BY cust_wallet DESC");
    $cust_id = ($cust['cust_id'] > 0) ? $cust['cust_id'] : 0;

    if($cust_id > 0){
        $sql = "UPDATE `hr_customer` SET `cust_gender` = '".$cust_gender."' WHERE cust_id = '".$cust_id."'";
        update_query($sql);
    }else{
        $sql = "INSERT INTO `hr_customer` SET `salon_id` = '".$salon_id."', `user_id` = '".$user_id."', `cust_name` = '".$cust_name."', `cust_reffer` = '".$cust_reffer."', `cust_mobile` = '".$cust_mob."', `cust_gender` = '".$cust_gender."'";
        $cust_id = insert_query($sql);
    }

    $invoice_number = select_row("SELECT MAX(invoice_number) + 1 as invoice_number FROM `hr_invoice` WHERE `salon_id` = '".$salon_id."'");
    $invoice_number = $invoice_number['invoice_number'];

    $sql = "INSERT INTO `hr_invoice` SET `salon_id` = '".$salon_id."', `cust_ref_by` = '".$cust_reffer."', `invoice_number` = '".$invoice_number."', `billing_remark` = '".$billing_remark."', `user_id` = '".$user_id."', `cust_id` = '".$cust_id."', `cust_name` = '".$cust_name."', `cust_mob` = '".$cust_mob."', `extra_fee` = '".$extra_tax."', `invoice_date` = '".$date_now."'";
    $invoice_id = insert_query($sql);

    // Update existing job card
    if($job_card_id > 0){
        $sql = "UPDATE `hr_jobcard` SET jobcard_status='2',invoice_id='".$invoice_id."',`updated_at` = '".$date_now."' WHERE `job_card_id` = '".$job_card_id."'";
        update_query($sql);
    }

    // Fetch active customer GST setting for wallet payments
    $apply_gst_on_services = true;
    if($payment_mode === 'wallet' && intval($cust_id) > 0) {
        $cdata = select_row("SELECT COALESCE(hr_customer.active_membership_id, (SELECT plan_id FROM hr_customer_membership WHERE cust_id=hr_customer.cust_id AND status='active' ORDER BY cm_id DESC LIMIT 1)) as active_membership_id FROM hr_customer WHERE cust_id='$cust_id'");
        if(!empty($cdata['active_membership_id'])) {
            $pdata = select_row("SELECT gst_on_service FROM hr_membership_plans WHERE plan_id='".$cdata['active_membership_id']."'");
            if($pdata && $pdata['gst_on_service'] == '0') {
                $apply_gst_on_services = false;
            }
        }
    }

    $totol_gst = 0;
    $service_total_with_tax = 0;
    $service_total = 0;

    $total_discount = $discount;
    if($discount_mode == 1){
        $total_discount = ($grandTotal * $discount) / 100;
    }

    $total_grand_before_discount = $total_discount + $grandTotal;
    $persrvice_discount = ($total_grand_before_discount > 0) ? ($total_discount / $total_grand_before_discount) * 100 : 0;
    
    if($invoice_id > 0){
        foreach($service_catid as $key => $service_cat){
            if($service_cat != ''){
                $is_product = (strpos($sub_service[$key], 'p_') === 0);
                
                if($is_product) {
                    $pid = str_replace('p_', '', $sub_service[$key]);
                    $Scategory = select_row("SELECT p.product_name, b.brand_name FROM `hr_product` p LEFT JOIN `hr_product_brand` b ON p.brand_id = b.brand_id WHERE p.product_id = '$pid'");
                    $service_catName = $Scategory['brand_name'] ? "Product - " . $Scategory['brand_name'] : "Product";
                    $service_name = $Scategory['product_name'];
                } else {
                    $sid = str_replace('s_', '', $sub_service[$key]);
                    if(empty($sid)) $sid = $sub_service[$key]; // Fallback
                    
                    $Scategory = select_row("SELECT service_catName FROM `hr_servicesCategory` WHERE `service_catid` = '$service_cat'");
                    $service_catName = $Scategory['service_catName'];

                    $hr_services = select_row("SELECT `service_name` FROM `hr_services` WHERE `service_id` = '$sid'");
                    $service_name = $hr_services['service_name'];
                }

                $staff_name = '';
                $total_staff = 0;

                if (!isset($service_staff[$key]) || !is_array($service_staff[$key])) { $service_staff[$key] = []; }
                foreach($service_staff[$key] as $staff_id){
                    $hr_staff = select_row("SELECT `staff_name`, staff_mob, staff_id FROM `hr_staff` WHERE `staff_id` = $staff_id");
                    $staff_name .= $hr_staff['staff_name'].",";
                    $mystaff_id = $hr_staff['staff_id'];
                    $mystaff_detail[$mystaff_id]['name'] = $hr_staff['staff_name'];
                    $mystaff_detail[$mystaff_id]['mobile'] = $hr_staff['staff_mob'];
                    $total_staff++;
                }

                $staff_name = trim($staff_name,",");
                $staff_ids = implode(",",$service_staff[$key]);

                $ser_qty = $service_qty[$key];
                $ser_price = $service_price[$key];
                $ser_gst = $apply_gst_on_services ? floatval($service_gst[$key]) : 0;

                $itemTotal = ($ser_qty * $ser_price);
                $gst_total = round((($itemTotal * $ser_gst) / 100), 0);
                $totalTax_tax = $itemTotal + $gst_total;

                $service_total += $itemTotal;
                $service_total_with_tax += $totalTax_tax;
                $totol_gst += $gst_total;

                

                $service_name = ucwords($service_name);
                $sql = "INSERT INTO `hr_invoice_service` SET `invoice_id` = '".$invoice_id."', `service_cat` = '".$service_catName."', `service` = '".$service_name."', `staff_id` = '".$staff_ids."', `staff_name` = '".$staff_name."', `service_price` = '".$ser_price."', `service_qty` = '".$ser_qty."', `service_gst` = '".$ser_gst."', `service_total_wth_gst` = '".$totalTax_tax."',service_discount= '".$persrvice_discount."' ";
                $ser_id = insert_query($sql);

                //staff Record 60% 40% start from 1 aug 2023

                if($total_staff >1){
                    $first_staff_amt = $totalTax_tax * 60 / 100;
                    $all_other_staff_amt = ($totalTax_tax - $first_staff_amt) / ($total_staff - 1);
                }else{
                    $first_staff_amt = $totalTax_tax;
                }

                foreach($service_staff[$key] as $staff_index => $staff_id){
                    if($staff_index == 0){
                        $staff_total = $first_staff_amt;
                    }else{
                        $staff_total = $all_other_staff_amt;
                    }
                    $staff_service_data[$kk]['invoice_id'] = $invoice_id;
                    $staff_service_data[$kk]['staff_id'] = $staff_id;
                    $staff_service_data[$kk]['invoice_service'] = $ser_id;
                    $staff_service_data[$kk]['total_amt'] = $staff_total;
                    $kk++;
                }

                $all_service[$key]['service'] = $service_name;
                $all_service[$key]['qty'] = $ser_qty;
                $all_service[$key]['price'] = $itemTotal;
            }
        }

        $total_discount = $discount;

        if($discount_mode == 1){
            $total_discount = ($service_total * $discount) / 100;
        }
        
        $service_total_with_discount = $service_total_with_tax - $total_discount;

        $salon_data = select_row("SELECT round_off FROM hr_salon WHERE salon_id='$salon_id'");
        $do_round = isset($salon_data['round_off']) && $salon_data['round_off'] == 1;

        if($payment_mode != 'pkg' && $do_round){
            $grand_total = round($service_total_with_discount);
        }else{
            $grand_total = $service_total_with_discount;
        }

        $round_off = $grand_total - $service_total_with_discount;
        $grand_total = $grand_total + $extra_tax;

        $sql = "UPDATE `hr_invoice` SET `discount` = '".$total_discount."', `discount_mode` = '".$discount_mode."', `service_total` = '".$service_total."', `service_total_tax` = '".$totol_gst."', grand_total = '".$grand_total."', `round_off` = '".$round_off."', `payment_mode` = '".$payment_mode."' WHERE `invoice_id` = '".$invoice_id."'";
        update_query($sql);

        if($payment_mode == "split"){
            $part_cash      = isset($_POST['part_cash']) ? floatval($_POST['part_cash']) : 0;
            $part_cash_mode = isset($_POST['part_cash_mode']) ? mysqli_real_escape_string($conn, $_POST['part_cash_mode']) : 'cash';
            $part_cc        = isset($_POST['part_cc']) ? floatval($_POST['part_cc']) : 0;
            $part_cc_mode   = isset($_POST['part_cc_mode']) ? mysqli_real_escape_string($conn, $_POST['part_cc_mode']) : 'cc';
            
            if($part_cash > 0){
                $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."',grand_total = '".$part_cash."', `payment_mode` = '".$part_cash_mode."',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
                update_query($sql);
            }
            if($part_cc > 0){
                $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."', grand_total = '".$part_cc."', `payment_mode` = '".$part_cc_mode."',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
                update_query($sql);
            }

        }else{
            $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."', grand_total = '".$grand_total."', `payment_mode` = '".$payment_mode."',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
            update_query($sql);
        }
        

        $total_member = count($staff_service_data);
       
        foreach($staff_service_data as $service_data){
            if($payment_mode == 'pkg'){
                $staff_total = $service_data['total_amt'] / 2;
            }else{
                
                $total_grand_before_discount = $total_discount + $grand_total;
                $persrvice_discount = ($total_grand_before_discount > 0) ? ($total_discount / $total_grand_before_discount) * 100 : 0;
                $staff_work_price = $service_data['total_amt'];

                $staff_total = ($staff_work_price - (($staff_work_price * $persrvice_discount) / 100));
            }

            $invoice_ids = $service_data['invoice_id'];
            $staff_id = $service_data['staff_id'];
            $ser_id = $service_data['invoice_service'];
            
            insert_query("INSERT INTO `hr_invoice_staff`(`invoice_id`, `invoice_service`, `staff_id`,`total_amt`,`persrvice_discount`,`staff_work_price`,`grand_total`,`invoice_date`) VALUES ('".$invoice_ids."','".$ser_id."','".$staff_id."','".$staff_total."','".$persrvice_discount."','".$staff_work_price."','".$grand_total."','".$date_now."')");
        }

        if($payment_mode == 'pkg' || $payment_mode == 'wallet'){
            $check_old_balance = select_row("SELECT `cust_wallet` FROM `hr_customer` WHERE `cust_id` = '".$cust_id."'");
            $old_balance = $check_old_balance['cust_wallet'];
            $balance = $old_balance - $grand_total;

            $wallet_remark = mysqli_real_escape_string($conn, 'Invoice #'.$invoice_number.' - Wallet Payment');
            $sql = "INSERT INTO `hr_customer_wallet` SET `invoice_id` = '".$invoice_id."', `cust_id` = '".$cust_id."', `debit` = '".$grand_total."', `credit` = 0, `balance` = '".$balance."', `remark` = '".$wallet_remark."'";
            insert_query($sql);

            $sql = "UPDATE `hr_customer` SET `cust_wallet` = '".$balance."' WHERE `cust_id` = '".$cust_id."'";
            update_query($sql);

            // Audit log
            insert_query("INSERT INTO hr_wallet_audit_log SET cust_id='".$cust_id."', salon_id='".$salon_id."', user_id='".$user_id."',
                old_balance='".$old_balance."', new_balance='".$balance."', change_type='debit',
                reason='".$wallet_remark."', reference='invoice_".$invoice_id."'");

            $wallet_msg = " And Your Remaining Wallet Balance is ".$balance;
            $staff_wallet_msg = " And client Remaining Wallet Balance is ".$balance;

            // If package mode: deduct service counts from the customer's active package
            if($payment_mode == 'pkg' && isset($_POST['active_cp_id']) && !empty($_POST['active_cp_id'])) {
                $cp_ids = array_map('intval', explode(',', $_POST['active_cp_id']));
                $cp_id_to_use = $cp_ids[0]; // Use first active package
                // Deduct each billed service from package
                foreach($service_catid as $key => $service_cat){
                    if($service_cat != '') {
                        if(strpos($sub_service[$key], 'p_') === 0) {
                            continue; // Do not deduct products from package
                        }
                        $svc_id = intval(str_replace('s_', '', $sub_service[$key]));
                        $qty    = intval($service_qty[$key]);
                        // Check package has this service
                        $pkg_item = select_row("SELECT quantity FROM hr_package_items WHERE pkg_id=(SELECT pkg_id FROM hr_customer_packages WHERE cp_id='".$cp_id_to_use."') AND service_id='".$svc_id."'");
                        if($pkg_item) {
                            $used = select_row("SELECT COALESCE(SUM(qty_used),0) as u FROM hr_customer_package_usage WHERE cp_id='".$cp_id_to_use."' AND service_id='".$svc_id."'");
                            $remaining = intval($pkg_item['quantity']) - intval($used['u']);
                            $deduct = min($qty, $remaining);
                            if($deduct > 0) {
                                insert_query("INSERT INTO hr_customer_package_usage SET
                                    cp_id='".$cp_id_to_use."',
                                    pkg_id=(SELECT pkg_id FROM hr_customer_packages WHERE cp_id='".$cp_id_to_use."'),
                                    cust_id='".$cust_id."', service_id='".$svc_id."',
                                    qty_used='".$deduct."', invoice_id='".$invoice_id."', used_by='".$user_id."'");
                            }
                        }
                    }
                }
                // Check if fully used
                $pkg_rec = select_row("SELECT pkg_id FROM hr_customer_packages WHERE cp_id='".$cp_id_to_use."'");
                if($pkg_rec) {
                    $all_items = select_array("SELECT pi.quantity, COALESCE(SUM(u.qty_used),0) AS used
                        FROM hr_package_items pi
                        LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id='".$cp_id_to_use."'
                        WHERE pi.pkg_id='".$pkg_rec['pkg_id']."' GROUP BY pi.item_id");
                    $fully_used = true;
                    foreach($all_items as $ai) { if(intval($ai['used']) < intval($ai['quantity'])) { $fully_used = false; break; } }
                    if($fully_used) update_query("UPDATE hr_customer_packages SET status='fully_used' WHERE cp_id='".$cp_id_to_use."'");
                }
            }
        }

        if(!empty($grand_total)){
            $payment_msg = 'You Paid '.$grand_total.' by '.$payment_mode.'.';
                    $staff_payment_msg = 'Client Paid '.$grand_total.' by '.$payment_mode.'.';
        }else{
            $payment_msg = '';
        }

        // ── Loyalty Guard: check if loyalty is enabled for this salon ────────
        $loyalty_on = true;
        $ls_row = select_row("SELECT loyalty_enabled FROM hr_loyalty_settings WHERE salon_id='$salon_id'");
        if ($ls_row && (int)$ls_row['loyalty_enabled'] === 0) $loyalty_on = false;
        
        $cdata = select_row("SELECT loyalty_blocked FROM hr_customer WHERE cust_id='$cust_id'");
        if ($cdata && $cdata['loyalty_blocked'] == '1') $loyalty_on = false;

        // ── Loyalty Points Accrual ────────────────────────────────────────────
        // Only earn points on real cash-equivalent payments (not pkg/wallet)
        if ($loyalty_on && !in_array($payment_mode, ['pkg', 'wallet']) && $cust_id > 0 && $grand_total > 0) {
            $pts_earned = award_points($cust_id, $salon_id, $grand_total, $invoice_id);
            if ($pts_earned > 0) {
                $payment_msg .= ' You earned ' . $pts_earned . ' loyalty points!';
            }
        }

        // ── Loyalty Points Redemption ─────────────────────────────────────────
        $pts_to_redeem = floatval($_POST['redeem_points'] ?? 0);
        if ($loyalty_on && $pts_to_redeem > 0 && $cust_id > 0) {
            // Deduct from ledger
            redeem_points($cust_id, $salon_id, $pts_to_redeem, $invoice_id);
            // Apply as additional discount on the invoice record
            $current_discount = (float)(select_row("SELECT discount FROM hr_invoice WHERE invoice_id='$invoice_id'")['discount']??0);
            $new_discount     = $current_discount + $pts_to_redeem;
            $new_grand        = max(0, $grand_total - $pts_to_redeem);
            $remark_addon     = mysqli_real_escape_string($conn, 'Loyalty Points Redeemed: '.intval($pts_to_redeem).' pts = ₹'.intval($pts_to_redeem).' discount');
            update_query("UPDATE hr_invoice SET
                discount       = '$new_discount',
                grand_total    = '$new_grand',
                billing_remark = CONCAT(IFNULL(billing_remark,''), IF(billing_remark IS NULL OR billing_remark='','','  |  '), '$remark_addon')
                WHERE invoice_id='$invoice_id'");
            $payment_msg .= ' Redeemed '.intval($pts_to_redeem).' loyalty pts (₹'.intval($pts_to_redeem).' off).';
        }
        // ─────────────────────────────────────────────────────────────────────

        // ── Short, secure invoice URL ─────────────────────────────────────────
        $share_token   = getInvoiceShareToken($invoice_id);
        $short_inv_url = rtrim(DOMAIN_SOFTWARE, '/') . '/i.php?t=' . $share_token;

        $cust_first       = ucfirst(strtolower(explode(' ', trim($cust_name))[0]));
        $formatted_amount = 'Rs.' . number_format($grand_total, 2);
        $payment_label    = ucfirst(strtolower($payment_mode));

        // ── WhatsApp API message — emoji-free (ASCII-safe for all APIs) ───────
        $feedback_url = DOMAIN_SOFTWARE . "feedback.php?inv=" . $invoice_id;
        
        if($payment_mode == 'pkg'){
            $message  = "Dear {$cust_first},\n\n";
            $message .= "Thank you for visiting *{$salon_name}*! We loved having you.\n\n";
            $message .= "Your visit has been recorded.\n";
            $message .= "Amount  : *{$formatted_amount}*\n";
            $message .= "Mode    : Package / Wallet\n";
            $message .= "Balance : *Rs." . number_format($balance ?? 0, 2) . "* remaining\n\n";
            $message .= "View Receipt: {$short_inv_url}\n\n";
            $message .= "We'd love your feedback! Please rate your experience here:\n{$feedback_url}\n\n";
            $message .= "Warm regards,\nThe {$salon_name} Team";
        }else{
            $message  = "Dear {$cust_first},\n\n";
            $message .= "Thank you for visiting *{$salon_name}*! We loved having you.\n\n";
            $message .= "Your bill for *{$formatted_amount}* has been generated.\n";
            $message .= "Mode   : {$payment_label}\n\n";
            $message .= "View Receipt: {$short_inv_url}\n\n";
            $message .= "We'd love your feedback! Please rate your experience here:\n{$feedback_url}\n\n";
            $message .= "Warm regards,\nThe {$salon_name} Team";
        }
        // ─────────────────────────────────────────────────────────────────────

        if($salon_id != 80){
            if($whatsapp_enable == 1){
                SendWhatsAppSms($cust_mob, $message, $whatsapp_api);
            }else{
                //sendapisms($cust_mob,$message,$senderid);
            }
        }

        // ── Make.com webhook ──────────────────────────────────────────────────
        if (!empty($salon['make_enable'])) {
            sendBillToMake(
                ucfirst(strtolower($cust_name)),
                $cust_mob,
                $grand_total,
                $payment_mode,
                $short_inv_url,
                $salon_name,
                $salon['make_webhook_url'] ?? ''
            );
        }
        // ─────────────────────────────────────────────────────────────────────

        $ref_mobile = select_row("SELECT * FROM `hr_user_owner` WHERE user_name = '".$cust_reffer."' AND salon_id = ".$salon_id."");
        if(is_numeric($ref_mobile['mobile_no'])){
            $mobile_no = $ref_mobile['mobile_no'];
            $message = "*Reminder:* \n*".ucfirst(strtolower($cust_name))."* visited *{$salon_name}* and gave your reference. Thank you for recommending us!";
            SendWhatsAppSms($mobile_no, $message, $whatsapp_api);
        }
    }
}

if(isset($_POST['save_bill_print'])){
    // Your code for saving and printing the bill
}else{
    // Your code for other actions
}

$redirect_url = DOMAIN_SOFTWARE . "print_invoice.php";

if (isset($invoice_id)) {
    // WhatsApp popup message for print_invoice.php
    // Use emojis here — this goes via browser (wa.me), not through an API
    $wa_phone = preg_replace('/\D/', '', $cust_mob);
    if (strlen($wa_phone) === 10) $wa_phone = '91' . $wa_phone;

    // Short URL already generated above ($short_inv_url / $share_token)
    $cust_first_wa    = ucfirst(strtolower(explode(' ', trim($cust_name))[0]));
    $formatted_amt_wa = 'Rs.' . number_format((float)$grand_total, 2);
    $payment_lbl_wa   = ucfirst(strtolower($payment_mode));

    $feedback_url = DOMAIN_SOFTWARE . "feedback.php?inv=" . $invoice_id;

    if ($payment_mode == 'pkg') {
        $wa_msg  = "Dear {$cust_first_wa},\n\n";
        $wa_msg .= "Thank you for visiting *{$salon_name}*! We loved having you.\n\n";
        $wa_msg .= "Your visit has been recorded.\n";
        $wa_msg .= "Amount  : *{$formatted_amt_wa}*\n";
        $wa_msg .= "Mode    : Package / Wallet\n";
        $wa_msg .= "Balance : *Rs." . number_format($balance ?? 0, 2) . "* remaining\n\n";
        $wa_msg .= "🧾 View Receipt: {$short_inv_url}\n\n";
        $wa_msg .= "⭐ We'd love your feedback! Please rate your experience here:\n{$feedback_url}\n\n";
        $wa_msg .= "Warm regards,\nThe {$salon_name} Team";
    } else {
        $wa_msg  = "Dear {$cust_first_wa},\n\n";
        $wa_msg .= "Thank you for visiting *{$salon_name}*! We loved having you.\n\n";
        $wa_msg .= "Your bill for *{$formatted_amt_wa}* has been generated.\n";
        $wa_msg .= "Mode   : {$payment_lbl_wa}\n\n";
        $wa_msg .= "🧾 View Receipt: {$short_inv_url}\n\n";
        $wa_msg .= "⭐ We'd love your feedback! Please rate your experience here:\n{$feedback_url}\n\n";
        $wa_msg .= "Warm regards,\nThe {$salon_name} Team";
    }

    $redirect_url .= "?invoice_id=" . $invoice_id
                   . "&wa_phone=" . urlencode($wa_phone)
                   . "&wa_msg="   . urlencode($wa_msg);
} else {
    $redirect_url = DOMAIN_SOFTWARE . "invoices.php";
}

header("Location: " . $redirect_url);
exit;
?>
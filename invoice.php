<?php 
include_once "function.php";
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
    $sql = "UPDATE `hr_jobcard` SET jobcard_status='2',invoice_id='".$invoice_id."',`updated_at` = '".$date_now."' WHERE `job_card_id` = '".$job_card_id."'";
    update_query($sql);

    $totol_gst = 0;
    $service_total_with_tax = 0;
    $service_total = 0;

    $total_discount = $discount;
    if($discount_mode == 1){
        $total_discount = ($grandTotal * $discount) / 100;
    }

    $total_grand_before_discount = $total_discount + $grandTotal;
    $persrvice_discount = ($total_discount / $total_grand_before_discount) * 100;
    
    if($invoice_id > 0){
        foreach($service_catid as $key => $service_cat){
            if(is_numeric($service_cat)){
                $Scategory = select_row("SELECT service_catName FROM `hr_servicesCategory` WHERE `service_catid` = $service_cat");
                $service_catName = $Scategory['service_catName'];

                $hr_services = select_row("SELECT `service_name` FROM `hr_services` WHERE `service_id` = $sub_service[$key]");
                $service_name = $hr_services['service_name'];

                $staff_name = '';
                $total_staff = 0;

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
                $ser_gst = $service_gst[$key];

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

        if($payment_mode != 'pkg'){
            $grand_total = round($service_total_with_discount / 10) * 10;
        }else{
            $grand_total = $service_total_with_discount;
        }

        $round_off = $grand_total - $service_total_with_discount;
        $grand_total = $grand_total + $extra_tax;

        $sql = "UPDATE `hr_invoice` SET `discount` = '".$total_discount."', `discount_mode` = '".$discount_mode."', `service_total` = '".$service_total."', `service_total_tax` = '".$totol_gst."', grand_total = '".$grand_total."', `round_off` = '".$round_off."', `payment_mode` = '".$payment_mode."' WHERE `invoice_id` = '".$invoice_id."'";
        update_query($sql);

        if($payment_mode == "split"){
            $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."',grand_total = '".$part_cash."', `payment_mode` = 'cash',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
            update_query($sql);

            $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."', grand_total = '".$part_cc."', `payment_mode` = 'cc',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
            update_query($sql);

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
                $persrvice_discount = ($total_discount / $total_grand_before_discount) * 100;
                $staff_work_price = $service_data['total_amt'];

                $staff_total = ($staff_work_price - (($staff_work_price * $persrvice_discount) / 100));
            }

            $invoice_ids = $service_data['invoice_id'];
            $staff_id = $service_data['staff_id'];
            $ser_id = $service_data['invoice_service'];
            
            insert_query("INSERT INTO `hr_invoice_staff`(`invoice_id`, `invoice_service`, `staff_id`,`total_amt`,`persrvice_discount`,`staff_work_price`,`grand_total`,`invoice_date`) VALUES ('".$invoice_ids."','".$ser_id."','".$staff_id."','".$staff_total."','".$persrvice_discount."','".$staff_work_price."','".$grand_total."','".$date_now."')");
        }

        if($payment_mode == 'pkg'){
            $check_old_balance = select_row("SELECT `cust_wallet` FROM `hr_customer` WHERE `cust_id` = '".$cust_id."'");
            $old_balance = $check_old_balance['cust_wallet'];
            $balance = $old_balance - $grand_total;

            $sql = "INSERT INTO `hr_customer_wallet` SET `invoice_id` = '".$invoice_id."', `cust_id` = '".$cust_id."', `debit` = '".$grand_total."', `balance` = '".$balance."'";
            insert_query($sql);

            $sql = "UPDATE `hr_customer` SET `cust_wallet` = '".$balance."' WHERE `cust_id` = '".$cust_id."'";
            update_query($sql);

            $wallet_msg = " And Your Remaining Balance is ".$balance;
            $staff_wallet_msg = " And client Remaining Balance is ".$balance;
        }

        if(!empty($grand_total)){
            $payment_msg = 'You Paid '.$grand_total.' by '.$payment_mode.'.';
            $staff_payment_msg = 'Client Paid '.$grand_total.' by '.$payment_mode.'.';
        }else{
            $payment_msg = '';
        }

        $feedurl = str_replace('=','','https://salonapp.org/f/'.base64_encode($invoice_id));

        if($payment_mode == 'pkg'){
            $message = "Dear ".ucfirst(strtolower($cust_name)).",\nwe are pleased to inform you that your recent service at *".$salon_name."* has been paid for using your package. The amount of ".$grand_total." has been deducted from your package, leaving you with *".$balance."* remaining. Thank you for choosing *".$salon_name."* and we look forward to seeing you again soon.\n*Invoice* https://salonapp.org/paidbill/".base64_encode($invoice_id)."/1 \n\n*Appointment* ".$salon_contact." \n\n*Feedback* ".$feedurl;
        }else{
            $message = "Dear ".ucfirst(strtolower($cust_name)).",\nThank you for choosing *".$salon_name."* for your beauty needs. We would like to inform you that your payment of *Rs.".$grand_total."* has been received by ".$payment_mode.". We hope you enjoyed your experience with us and we look forward to your next visit.\n*Invoice* https://salonapp.org/paidbill/".base64_encode($invoice_id)."/1 \n\n*Appointment* ".$salon_contact." \n\n*Feedback* ".$feedurl;
        }

        if($whatsapp_enable == 1){
            SendWhatsAppSms($cust_mob, $message, $whatsapp_api);
        }else{
            //sendapisms($cust_mob,$message,$senderid);
        }

        $ref_mobile = select_row("SELECT * FROM `hr_user_owner` WHERE user_name = '".$cust_reffer."' AND salon_id = ".$salon_id."");
        
        if(is_numeric($ref_mobile['mobile_no'])){
            $mobile_no = $ref_mobile['mobile_no'];
            $message = "*Just Reminder* \n*".ucfirst(strtolower($cust_name))."* gave your reference while billing at *".$salon_name."*";
            SendWhatsAppSms($mobile_no, $message, $whatsapp_api);
        }
    }
}

if(isset($_POST['save_bill_print'])){
    // Your code for saving and printing the bill
}else{
    // Your code for other actions
}

header("refresh: 0; url=/print_invoice.php?invoice_id=".$invoice_id);

?>
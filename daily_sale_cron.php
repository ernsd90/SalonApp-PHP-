<?php
session_start();
include "function.php";


function summary_sale(){

    $date = date('Y-m-d');
    $date_where = " and DATE(invoice_date) LIKE '".$date."'";
    $exp_where = " and DATE(exp_date) LIKE '".$date."'";

    $fromdate = date("Y/m/1");
    $todate = date("Y/m/t");
    $date_month_where = " and (DATE(invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";
    $exp_month_where = " and (DATE(exp_date) BETWEEN '".$fromdate."' AND '".$todate."')";


    $all_salon = select_array("SELECT salon_id,salon_name,msg_id,whatsapp_enable,whatsapp_api FROM `hr_salon` where salon_id NOT IN (22,8)");
    //$all_salon = select_array("SELECT salon_id,salon_name,msg_id FROM `hr_salon` where salon_id IN (80)");

    foreach($all_salon as $salon) {

        foreach($salon as $var => $value){
            $$var = $value;
        }
    extract(select_row("SELECT sum(grand_total) as month_total FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1' and invoice_type!='2'  and payment_mode!='pkg' " . $date_month_where . " "));
    extract(select_row("SELECT sum(grand_total) as month_total_product FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1' and invoice_type='2'  and payment_mode!='pkg' " . $date_month_where . " "));
    extract(select_row("SELECT sum(exp_total) as month_total_expense FROM `hr_expenses` where salon_id='" . $salon_id . "' " . $exp_month_where . " "));

   // extract(select_row("SELECT sum(grand_total) as grand_total FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1' and invoice_type!=2  and payment_mode!='pkg' " . $date_where . " "));
    $sql = "SELECT sum(grand_total) as total_cash FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1' and invoice_type!='2'  and payment_mode LIKE 'cash' " . $date_where . " ";
    extract(select_row($sql));
    extract(select_row("SELECT sum(grand_total) as total_cc FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1' and invoice_type!='2'  and (payment_mode='paytm' || payment_mode='cc' || payment_mode='google_pay' || payment_mode='upi' || payment_mode='near_buy') " . $date_where . " "));

    $sql = "SELECT sum(grand_total) as total_procc FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1' and invoice_type='2' and (lower(payment_mode)='paytm' || lower(payment_mode)='cc' || lower(payment_mode)='google_pay' || lower(payment_mode)='upi' || lower(payment_mode)='near_buy') " . $date_where . " ";
    extract(select_row($sql));
    extract(select_row("SELECT sum(grand_total) as total_procash FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1' and invoice_type='2' and lower(payment_mode) LIKE 'cash' " . $date_where . " "));
   
    $sql = "SELECT sum(exp_total) as exp_cash FROM `hr_expenses` where salon_id='" . $salon_id . "' and lower(payment_mode)='cash' " . $exp_where . " ";
    extract(select_row($sql));
    extract(select_row("SELECT sum(exp_total) as exp_cc FROM `hr_expenses` where salon_id='" . $salon_id . "'  and lower(payment_mode)!='cash' " . $exp_where . " "));
    

        $grand_total = $total_cash+$total_cc;
        $grand_prototal = $total_procc+$total_procash;

        $data[$salon_id]['month_total'] = $month_total;
        $data[$salon_id]['month_total_product'] = $month_total_product;
        $data[$salon_id]['month_total_expense'] = $month_total_expense;


        $data[$salon_id]['today_total'] = $grand_total;
        $data[$salon_id]['total_cash'] = $total_cash;
        $data[$salon_id]['total_cc'] = $total_cc;

        $data[$salon_id]['today_pro_total'] = $grand_prototal;
        $data[$salon_id]['total_pro_cash'] = $total_procash;
        $data[$salon_id]['total_pro_cc'] = $total_procc;

        $exp_total = $exp_cash+$exp_cc;
        $data[$salon_id]['exp__cash'] = $exp_cash;
        $data[$salon_id]['exp__cc'] = $exp_cc;
        $data[$salon_id]['exp__total'] = $exp_total;

        $data[$salon_id]['salon_name'] = $salon_name;
        $data[$salon_id]['sender_id'] = $msg_id;
        $data[$salon_id]['salon_id'] = $salon_id;
        $data[$salon_id]['whatsapp_enable'] = $whatsapp_enable;
        $data[$salon_id]['whatsapp_api'] = $whatsapp_api;

    }
    return $data;
}

function remove_format($text){
    $text = str_replace(",", "", $text);
    return $text;
}

function get_staffrecord(){

    $i = 0;

    $date = date('Y-m-d');
    $date_where = " and DATE(i.invoice_date) LIKE '".$date."%'";

    $fromdate = date("Y/m/1");
    $todate = date("Y/m/t");
    $date_month_where = " and (DATE(i.invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";


    $all_salon = select_array("SELECT salon_id,salon_name,msg_id,whatsapp_enable,whatsapp_api FROM `hr_salon` where salon_id NOT IN (22,8)");
    foreach($all_salon as $salon) {
        foreach ($salon as $var => $value) {
            $$var = $value;
        }

        $sql = "SELECT * FROM `hr_staff` where salon_id='" . $salon_id . "' and staff_mob > 100  and staff_status=1 ";
        $record = select_array($sql);

        foreach ($record as $staff) {

            $staff_id = $staff['staff_id'];
            $where = " and s.staff_id LIKE '" . $staff_id . "'" . $date_where;
            $month_where = " and s.staff_id LIKE '" . $staff_id . "'" . $date_month_where;
           // echo "SELECT sum(s.total_amt) as total_service_sale FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='" . $salon_id . "' and delete_bill!='1'  and payment_mode!='pkg' " . $where . " <br><BR>";
            $month_service_sale = (select_row("SELECT sum(s.total_amt) as month_service_sale FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='" . $salon_id . "' and delete_bill!='1' and payment_mode!='pkg' " . $month_where . " "));

            $total_service_sale = (select_row("SELECT sum(s.total_amt) as total_service_sale FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='" . $salon_id . "' and delete_bill!='1' and payment_mode!='pkg' " . $where . " "));


            $product_total = (select_row("SELECT sum(s.total_amt) as product_total FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='" . $salon_id . "' and delete_bill!='1' and invoice_type='2'  and payment_mode!='pkg' " . $where . " "));
            $produc_sale = ($product_total['product_total']);



            $total_service_sale = remove_format(number_format($total_service_sale['total_service_sale']));
            $month_service_sale = remove_format(number_format($month_service_sale['month_service_sale']));



           if($total_service_sale > 0) {
               $data[$i]['salon_name'] = $salon_name;
               $data[$i]['sender_id'] = $msg_id;
               $data[$i]['salon_id'] = $salon_id;
               $data[$i]['staff_name'] = $staff['staff_name'];
               $data[$i]['staff_mob'] = $staff['staff_mob'];
               $data[$i]['product_total'] = remove_format(number_format($produc_sale));
               $data[$i]['total_sale'] = $total_service_sale;
               $data[$i]['month_sale'] = $month_service_sale;
               $data[$i]['whatsapp_enable'] = $whatsapp_enable;
               $data[$i]['whatsapp_api'] = $whatsapp_api;
               $i++;
           }
        }
    }

    return $data;
}


// Daily Sale Message to Owners
$saledata = summary_sale();

foreach($saledata as $mydata){

    foreach($mydata as $var => $value){
        $$var = $value;
    }

        $msg = "*".$salon_name." (".date('d F').")*

*Service Sale*
Cash: ".$total_cash."
Card: ".$total_cc."
Today: *".$today_total."*

*Product Sale*
Cash: ".$total_pro_cash."
Card: ".$total_pro_cc."
Total: *".$today_pro_total."*

*Expense*
Cash: ".$exp__cash."
Card: ".$exp__cc."
Total: *".$exp__total."*

*".date('F Y')."*
Total Service Sale: *Rs.".$month_total. "*
Total Product Sale: *Rs.".$month_total_product. "*
Total Expense: *Rs.".$month_total_expense. "*";


    $all_user = select_array("SELECT mobile_no FROM `hr_user_owner` where salon_id='".$salon_id."' and is_active=1");
    foreach($all_user as $user){
        echo "<br>>".$mobile_no = $user['mobile_no'];

        //echo "<br><br>>>".$msg;
        if($whatsapp_enable == 1){
            //echo "<br>>>>";
            SendWhatsAppSms($mobile_no,$msg,$whatsapp_api);
        }else{
            //sendapisms($mobile_no,$msg,$sender_id);
        }
    }
}

// Daily Sale Message to Staff
$salestaffdata = get_staffrecord();
foreach($salestaffdata as $mydata){

    foreach($mydata as $var => $value){
        $$var = $value;
    }

        $msg = "Hi " . $staff_name . "
Your today(" . date('d-M-y') . ") sale is Rs." . $total_sale . "
And Your " . date('F') . " month sale is Rs." . $month_sale . "
Thanks
".$salon_name;

        $mobile_no = $staff_mob;
        //echo "<BR><BR>> ".$salon_name." > ".$msg;
        //SendWhatsAppSms($mobile_no,$msg,$sender_id);
        if($whatsapp_enable == 1) {
            SendWhatsAppSms($mobile_no, $msg, $whatsapp_api);
        }


        


}



?>
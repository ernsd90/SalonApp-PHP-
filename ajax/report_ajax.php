<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE); 
if (session_status() === PHP_SESSION_NONE) session_start();

include "../config.php";
include "../function.php";

$user_id      = get_session_data('user_id');
$salon_id     = get_session_data('salon_id');
$cash_discount = get_session_data('cash_discount');
$role_id      = get_session_data('role_id');

$method = $_REQUEST["method"] ?? '';

if ($method && function_exists($method))
    echo json_encode($method());
else
    echo json_encode(['error' => 1, 'msg' => 'Method Not Found: ' . $method]);

function staff_summary_sale(){
    global $user_id, $salon_id;

    extract($_REQUEST);

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
        $date_where = " and (DATE(i.invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";

        $exp_where = " and (DATE(exp_date) BETWEEN '".$fromdate."' AND '".$todate."')";

    }else if($fromdate != '' && $todate == ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $date_where = " and (DATE(i.invoice_date) = '".$fromdate."')";
        $exp_where = " and (DATE(exp_date) = '".$fromdate."')";
    }else{
        $date = date('Y-m-d');
        $date_where = " and DATE(i.invoice_date) LIKE '".$date."'";
        $exp_where = " and DATE(exp_date) LIKE '".$date."'";
    }

    if($staff_id > 0){
        $date_where .= " and s.staff_id LIKE '".$staff_id."'";
    }

    if(isset($_REQUEST['service_id']) && is_array($_REQUEST['service_id']) && count($_REQUEST['service_id']) > 0) {
        $svc_list = implode(",", array_map('intval', $_REQUEST['service_id']));
        // note: i is prefixed here because staff_summary_sale usually aliases hr_invoice as i
        $date_where .= " AND i.invoice_id IN (SELECT invoice_id FROM hr_invoice_service WHERE service IN (SELECT service_name FROM hr_services WHERE service_id IN (".$svc_list.")))";
    }

    if($refrence_by != '0' && $refrence_by != '') {
        $date_where .= " and i.cust_ref_by = '".$refrence_by."'";
    }

    extract(select_row("SELECT sum(s.total_amt) as grand_total FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1'  and payment_mode!='pkg' and payment_mode!='wallet' ".$date_where." "));

    $prod_res = select_row("SELECT sum(sv.service_total_wth_gst) as product_total_cash FROM `hr_invoice` as i join hr_invoice_service as sv on sv.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and i.delete_bill!='1' and i.payment_mode!='pkg' and i.payment_mode!='wallet' and sv.service_cat LIKE 'Product%' ".$date_where." ");
    $product_total_cash = $prod_res['product_total_cash'] ? $prod_res['product_total_cash'] : 0;

    $total_customer = (num_rows("SELECT distinct(cust_mob) FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' ".$date_where." "));

    // Since we no longer separate invoice cash vs CC individually for products vs service, we will just sum all cash / cc
    extract(select_row("SELECT sum(s.total_amt) as total_cash FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' and payment_mode LIKE 'cash' ".$date_where." "));
    extract(select_row("SELECT sum(s.total_amt) as total_cc FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' and (payment_mode='paytm' || payment_mode='cc' || payment_mode='google_pay' || payment_mode='upi' ) ".$date_where." "));
    extract(select_row("SELECT sum(s.total_amt) as total_nearbuy FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' and (payment_mode='near_buy') ".$date_where." "));



    $data['grand_total'] = number_format($grand_total);
    $data['total_customer'] = $total_customer;
    $data['total_cash'] = number_format($total_cash);
    $data['total_cc'] = number_format($total_cc);
    $data['product_total'] = number_format($product_total_cash);
    $data['exp_total'] = 0;

    return $data;
}

function summary_sale(){

    global $user_id, $salon_id,$role_id,$cash_discount;
    extract($_REQUEST);

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
    }else if($fromdate != '' && $todate == ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($fromdate));
    }else{
        $fromdate = date('Y-m-d');
        $todate = date('Y-m-d');
    }

    $all_mnth = getMonthsInRange($fromdate, $todate);

    

    foreach ($all_mnth as $mnth_Sale){
        $sale_monthlY = summary_sale_monthly($mnth_Sale['fromdate'],$mnth_Sale['todate'],$refrence_by);

        foreach($sale_monthlY as $var => $sale) {
            $$var = $sale;
        }

        $data['total_customer_cash'] = ($data['total_customer_cash'] ?? 0) + ($total_customer ?? 0);
        $data['total_customer_pkg'] = ($data['total_customer_pkg'] ?? 0) + ($total_customer_pkg ?? 0);
        $data['total_customer'] = ($data['total_customer'] ?? 0) + ($total_customer_pkg ?? 0) + ($total_customer ?? 0);

        $data['total_cash'] = ($data['total_cash'] ?? 0) + ($total_cash ?? 0);
        $data['total_cc'] = ($data['total_cc'] ?? 0) + ($total_cc ?? 0);
        
        $data['service_total'] = ($data['service_total'] ?? 0) + ($pure_service_total ?? 0);
        $data['service_cash'] = ($data['service_cash'] ?? 0) + ($service_cash ?? 0);
        $data['service_cc'] = ($data['service_cc'] ?? 0) + ($service_cc ?? 0);

        $data['product_total'] = ($data['product_total'] ?? 0) + ($pure_product_total ?? 0);
        $data['product_cash'] = ($data['product_cash'] ?? 0) + ($product_cash ?? 0);
        $data['product_cc'] = ($data['product_cc'] ?? 0) + ($product_cc ?? 0);
        
        $data['discount_total'] = ($data['discount_total'] ?? 0) + ($discount_total ?? 0);

        $data['reduction_sale'] = ($data['reduction_sale'] ?? 0) + (($redemption_total ?? 0) + ($wallet_total ?? 0));
        $data['reduction_pkg'] = ($data['reduction_pkg'] ?? 0) + ($redemption_total ?? 0);
        $data['reduction_wallet'] = ($data['reduction_wallet'] ?? 0) + ($wallet_total ?? 0);

        $data['membership_pkg'] = ($data['membership_pkg'] ?? 0) + ($mem_pkg_total ?? 0);
        $data['membership_pkg_cash'] = ($data['membership_pkg_cash'] ?? 0) + ($mem_pkg_cash ?? 0);
        $data['membership_pkg_cc'] = ($data['membership_pkg_cc'] ?? 0) + ($mem_pkg_cc ?? 0);

        $data['exp_total_cash'] = ($data['exp_total_cash'] ?? 0) + ($exp_total ?? 0);
        $data['exp_total_cc'] = ($data['exp_total_cc'] ?? 0) + ($exp_total_cc ?? 0);
        $data['exp_total'] = ($data['exp_total'] ?? 0) + (($exp_total ?? 0) + ($exp_total_cc ?? 0));

        $data['grand_cash'] = ($data['grand_cash'] ?? 0) + ($total_cash ?? 0);
        $data['grand_cc'] = ($data['grand_cc'] ?? 0) + ($total_cc ?? 0);
        $data['grand_total'] = ($data['grand_total'] ?? 0) + ($grand_total ?? 0);
    }
   
    return $data;
}

function summary_sale_monthly($fromdate,$todate,$refrence_by){

    global $user_id, $salon_id,$role_id,$cash_discount;

    $discountwhere = '';


    // if($fromdate <= '2025-03-31' || $todate <= '2025-03-31') {
    //     $fromdate = date("2027-03-31",strtotime($fromdate));
    //     $todate = date("202-03-31",strtotime($todate));
    // }
   

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
        $date_where = " and (DATE(invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";
        $exp_where = " and (DATE(exp_date) BETWEEN '".$fromdate."' AND '".$todate."')";

        $fromdate_month = date("m",strtotime($fromdate));
        $fromdate_year = date("Y",strtotime($fromdate));
        $discountwhere = " and (MONTH(month_discount) = '".$fromdate_month."') and (YEAR(month_discount) = '".$fromdate_year."')";

    }

    if($refrence_by != '0' && $refrence_by != '') {
        $date_where .= " and cust_ref_by = '".$refrence_by."'";
    }

    if(isset($_REQUEST['service_id']) && is_array($_REQUEST['service_id']) && count($_REQUEST['service_id']) > 0) {
        $svc_list = implode(",", array_map('intval', $_REQUEST['service_id']));
        $date_where .= " AND i.invoice_id IN (SELECT invoice_id FROM hr_invoice_service WHERE service IN (SELECT service_name FROM hr_services WHERE service_id IN (".$svc_list.")))";
    }
   
    $mysql = "SELECT cash_discount,month_discount FROM `hr_salon_cashdiscount` where salon_id='".$salon_id."' ".$discountwhere;
    $alldiscount  = select_array($mysql);

    extract(select_row("SELECT sum(i.grand_total) as grand_total, sum(i.discount) as discount_total FROM `hr_invoice` as i where i.salon_id='".$salon_id."' and i.delete_bill!='1'  and i.payment_mode!='pkg' and i.payment_mode!='wallet' ".$date_where." "));

    $mydata = (select_row("SELECT sum(p.grand_total) as total_cash FROM `hr_invoice` as i JOIN  `hr_invoice_payment` as p on p.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and delete_bill!='1' and p.payment_mode LIKE 'cash' ".$date_where." "));
    $total_cash = $mydata['total_cash'];
    extract(select_row("SELECT sum(p.grand_total) as total_cc FROM `hr_invoice` as i JOIN  `hr_invoice_payment` as p on p.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and delete_bill!='1' and p.payment_mode !='cash' and p.payment_mode !='near_buy' and p.payment_mode !='pkg' and p.payment_mode !='wallet' ".$date_where." "));

    extract(select_row("SELECT sum(i.grand_total) as total_nearbuy FROM `hr_invoice` as i where i.salon_id='".$salon_id."' and i.delete_bill!='1' and (i.payment_mode='near_buy') ".$date_where." "));

    $total_customer = (num_rows("SELECT i.cust_mob FROM `hr_invoice` as i where i.salon_id='".$salon_id."' and i.delete_bill!='1' and i.invoice_type!='2' and i.payment_mode!='pkg' and i.payment_mode!='wallet' ".$date_where." "));
    $total_customer_pkg = (num_rows("SELECT i.cust_mob FROM `hr_invoice` as i where i.salon_id='".$salon_id."' and i.delete_bill!='1' and i.invoice_type!='2' and (i.payment_mode='pkg' or i.payment_mode='wallet') ".$date_where." "));
    
    extract(select_row("SELECT sum(exp_total) as exp_cash FROM `hr_expenses` where salon_id='".$salon_id."' and payment_mode='cash' ".$exp_where." "));
    extract(select_row("SELECT sum(exp_total) as exp_cc FROM `hr_expenses` where salon_id='".$salon_id."' and payment_mode='cc' ".$exp_where." "));

    if($role_id != 3) {
        $cash_discount = get_cash_discount($alldiscount,$fromdate);
        $total_cash = $total_cash - (($total_cash * $cash_discount) / 100);
    }else{
        $total_cash = $total_cash;
    }

    $safe_date_where = $date_where;
    if(stripos($safe_date_where, 'i.invoice_date') === false) {
        $safe_date_where = str_replace("invoice_date", "i.invoice_date", $safe_date_where);
    }
    if(stripos($safe_date_where, 'i.cust_ref_by') === false) {
        $safe_date_where = str_replace("cust_ref_by", "i.cust_ref_by", $safe_date_where);
    }

    // --- Total Services (Invoice Type 0 or filtering out Packages/Memberships/Products) ---
    $service_res = select_row("
        SELECT SUM(
            (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * i.grand_total
        ) as st 
        FROM hr_invoice i 
        JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id 
        WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND sv.service_cat NOT LIKE 'Product%' AND sv.service_cat NOT LIKE 'Membership%' AND sv.service_cat NOT LIKE 'Package%' AND i.payment_mode!='pkg' AND i.payment_mode!='wallet' ".$safe_date_where);
    $service_total = $service_res['st'] ? $service_res['st'] : 0;

    $service_cash_res = select_row("
        SELECT SUM(
            (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * p.grand_total
        ) as st_cash 
        FROM hr_invoice i 
        JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id 
        JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id 
        WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND sv.service_cat NOT LIKE 'Product%' AND sv.service_cat NOT LIKE 'Membership%' AND sv.service_cat NOT LIKE 'Package%' AND p.payment_mode='cash' ".$safe_date_where);
    $service_cash = $service_cash_res['st_cash'] ? $service_cash_res['st_cash'] : 0;
    
    // Check for splits
    $split_service_res = select_row("
        SELECT SUM(p.grand_total) as st_cc 
        FROM hr_invoice i 
        JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id 
        WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND i.invoice_type='0' AND p.payment_mode!='cash' AND p.payment_mode!='pkg' AND p.payment_mode!='wallet' ".$date_where);
    $service_cc = $service_total - $service_cash;
    // (We simply deduct cash from the precise grand_total to ensure it always maths out)

    // --- Total Products (Invoice Type 2 or filtering by Product%) ---
    $product_res = select_row("
        SELECT SUM(
            (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * i.grand_total
        ) as pt 
        FROM hr_invoice i 
        JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id 
        WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND sv.service_cat LIKE 'Product%' AND i.payment_mode!='pkg' AND i.payment_mode!='wallet' ".$safe_date_where);
    $product_total = $product_res['pt'] ? $product_res['pt'] : 0;
    
    $product_cash_res = select_row("
        SELECT SUM(
            (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * p.grand_total
        ) as pt_cash 
        FROM hr_invoice i 
        JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id 
        JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id 
        WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND sv.service_cat LIKE 'Product%' AND p.payment_mode='cash' ".$safe_date_where);
    $product_cash = $product_cash_res['pt_cash'] ? $product_cash_res['pt_cash'] : 0;
    
    $product_cc = $product_total - $product_cash;

    $redemption_res = select_row("SELECT SUM(grand_total) as rt FROM hr_invoice i WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND i.payment_mode='pkg' ".$safe_date_where);
    $redemption_total = $redemption_res['rt'] ? $redemption_res['rt'] : 0;
    
    $wallet_res = select_row("SELECT SUM(grand_total) as wt FROM hr_invoice i WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND i.payment_mode='wallet' ".$safe_date_where);
    $wallet_total = $wallet_res['wt'] ? $wallet_res['wt'] : 0;
    
    // Check for split payments involving pkg or wallet
    $split_pkg_res = select_row("SELECT SUM(p.grand_total) as sqt FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND i.payment_mode='split' AND p.payment_mode='pkg' ".$safe_date_where);
    $redemption_total += $split_pkg_res['sqt'] ? $split_pkg_res['sqt'] : 0;
    
    $split_wallet_res = select_row("SELECT SUM(p.grand_total) as swt FROM hr_invoice i JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND i.payment_mode='split' AND p.payment_mode='wallet' ".$safe_date_where);
    $wallet_total += $split_wallet_res['swt'] ? $split_wallet_res['swt'] : 0;

    // --- Total Memberships & Packages (Invoice Type 1 or Service Cat matches) ---
    // Note: We need to pull the service_total_wth_gst proportion since Package sales don't strictly use invoice_type=1
    $mem_pkg_res = select_row("
        SELECT SUM(
            (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * i.grand_total
        ) as mt 
        FROM hr_invoice i 
        JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id 
        WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND (sv.service_cat LIKE 'Membership%' OR sv.service_cat LIKE 'Package%') AND i.payment_mode!='pkg' AND i.payment_mode!='wallet' ".$safe_date_where);
    $mem_pkg_total = $mem_pkg_res['mt'] ? $mem_pkg_res['mt'] : 0;
    
    $mem_pkg_cash_res = select_row("
        SELECT SUM(
            (sv.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0)) * p.grand_total
        ) as mt_cash 
        FROM hr_invoice i 
        JOIN hr_invoice_service sv ON sv.invoice_id=i.invoice_id 
        JOIN hr_invoice_payment p ON p.invoice_id=i.invoice_id 
        WHERE i.salon_id='".$salon_id."' AND i.delete_bill!=1 AND (sv.service_cat LIKE 'Membership%' OR sv.service_cat LIKE 'Package%') AND p.payment_mode='cash' ".$safe_date_where);
    $mem_pkg_cash = $mem_pkg_cash_res['mt_cash'] ? $mem_pkg_cash_res['mt_cash'] : 0;

    // ADDITION: Include dedicated Membership and Package sales tables
    // 1. Memberships (only non-refunded parent memberships)
    $ext_mem_cash = select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND mp.payment_mode='cash' AND cm.status!='refunded' AND (DATE(mp.created_at) BETWEEN '$fromdate' AND '$todate')")['total'] ?? 0;
    $ext_mem_cc = select_row("SELECT SUM(mp.amount) as total FROM hr_membership_payments mp JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id WHERE mp.salon_id='$salon_id' AND mp.payment_mode!='cash' AND cm.status!='refunded' AND (DATE(mp.created_at) BETWEEN '$fromdate' AND '$todate')")['total'] ?? 0;
    
    // 2. Packages (excluding refunded)
    $ext_pkg_cash = select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND payment_mode='cash' AND status!='refunded' AND (purchase_date BETWEEN '$fromdate' AND '$todate')")['total'] ?? 0;
    $ext_pkg_cc = select_row("SELECT SUM(purchase_price) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND payment_mode!='cash' AND status!='refunded' AND (purchase_date BETWEEN '$fromdate' AND '$todate')")['total'] ?? 0;

    $mem_pkg_total += ($ext_mem_cash + $ext_mem_cc + $ext_pkg_cash + $ext_pkg_cc);
    $mem_pkg_cash += ($ext_mem_cash + $ext_pkg_cash);
    $total_cash += ($ext_mem_cash + $ext_pkg_cash);
    $total_cc += ($ext_mem_cc + $ext_pkg_cc);
    
    $mem_pkg_cc = $mem_pkg_total - $mem_pkg_cash;

    $data['grand_total'] = $total_cash+$total_cc+$total_nearbuy;
    $data['total_customer'] = $total_customer;
    $data['total_customer_pkg'] = $total_customer_pkg;
    $data['total_cash'] = $total_cash;
    $data['total_cc'] = $total_cc;
    $data['pure_product_total'] = $product_total;
    $data['product_cash'] = $product_cash;
    $data['product_cc'] = $product_cc;
    $data['pure_service_total'] = $service_total;
    $data['service_cash'] = $service_cash;
    $data['service_cc'] = $service_cc;
    $data['discount_total'] = $discount_total;
    $data['redemption_total'] = $redemption_total;
    $data['wallet_total'] = $wallet_total;
    $data['mem_pkg_total'] = $mem_pkg_total;
    $data['mem_pkg_cash'] = $mem_pkg_cash;
    $data['mem_pkg_cc'] = $mem_pkg_cc;
    $data['product_total'] = 0;
    $data['product_total_cc'] = 0;
    $data['exp_total'] = $exp_cash;
    $data['exp_total_cc'] = $exp_cc;
    $data['fromdate'] = $fromdate."-".$todate;
    $data['cash_discount'] = $cash_discount;
    return $data;
}


function get_salerecord_old(){

    global $user_id, $salon_id;

    extract($_REQUEST);

    if (isset($start)) { $page  = $start; } else { $page=1; }; 
    $start_from = $start; 

    if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and (cust_name LIKE '%".$search_value."%' OR invoice_id LIKE '%".$search_value."%' OR service LIKE '%".$search_value."%')";
    }

    if($fromdate != '' && $todate == ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $where .= " and (DATE(invoice_date) = '".$fromdate."')";
    }

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
        $where .= " and (DATE(invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";
    }



     $sql = "SELECT invoice_id,invoice_date,cust_name,payment_type FROM `hr_invoices_old` WHERE 1=1  ".$where;

    $total_records = num_rows($sql); 
    $sql .= " group by invoice_id order by invoice_id DESC LIMIT $start_from, $length";
    $record = select_array($sql);

    $data_record = array();
    $i=0;
    foreach($record as $sale){
        
        $sql = "SELECT c.cust_mobile FROM `customer_invoices` as i join hr_customer as c on c.cust_id=i.cust_id where `invoice_id`='".$sale['invoice_id']."'";
        extract(select_row($sql));

        $all_service = '';
        $all_staff = '';
        $all_product = '';
        $all_total_cost = '';
        $data_detail = select_array("SELECT `service`,staff_name,product,membership,total_cost  FROM `hr_invoices_old` where invoice_id='".$sale['invoice_id']."'");
        foreach($data_detail as $old_detail){
            extract($old_detail);

            if($service != ''){
                $all_service .= $service.",";
            }elseif($product != ''){
                $all_service .= $product.",";
            }else{
                $all_service .= $membership.",";
            }
            $all_staff .= $staff_name.",";;
            $all_total_cost += $total_cost;
        }

        $data_record['all_staff'] = trim($all_staff,",");
        $data_record['cust_name'] = $sale['cust_name'];
        $data_record['cust_mob'] = $cust_mobile;
        $data_record['payment_mode'] = $sale['payment_type']; 
        $data_record['grand_total'] = $all_total_cost;
        $data_record['tips'] = $sale['extra_fee'];
        $data_record['delete_bill'] = $sale['delete_bill']; 

        $data_record['invoice_date'] = date('d-m-Y',strtotime($sale['invoice_date']));

        
        $data_record['all_service'] = $all_service;
        
        
       // var_dump(check_user_permission("report","report_view",$user_id));
       // exit;
        if(check_user_permission("report","view",$user_id)){
            $edit_btn = "";
        }

        if(check_user_permission("report","delete",$user_id)){
            $del_btn = "";
        }

        $userdata[$i] = $data_record;
        $userdata[$i]['user_role'] = $role_name;

        $i++;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;

}



function get_servicerecord(){
    global $user_id, $salon_id;

    extract($_REQUEST);

    if (isset($start)) { $page  = $start; } else { $page=1; };
    $start_from = $start;

    if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and (cust_name LIKE '%".$search_value."%' OR cust_mob LIKE '%".$search_value."%')";
    }

   
    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
        $date_where = " and (DATE(i.invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";
    }else if($fromdate != '' && $todate == ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $date_where = " and (DATE(i.invoice_date) = '".$fromdate."')";
    }else{
        $date = date('Y-m-d');
        $date_where = " and DATE(i.invoice_date) LIKE '".$date."'";
    }

    if($pkg_service != 1){
        $where .= " and (i.payment_mode !='pkg' and i.payment_mode !='wallet') ";
    }

    if($membership_include == 1){
       // $where .= " and (i.invoice_type='0' OR i.invoice_type='1') ";
    }else{
       // $where .= " and i.invoice_type='0' ";
    }
    $where .= " and i.invoice_type='0' ";

    $dir = $order[0]['dir'];
    $order_by = "service_grand desc";

    if($order[0]['column'] == 1){
        $order_by = "service_price ".$dir;
    }
    if($order[0]['column'] == 2){
        $order_by = "service_qty ".$dir;
    }
    if($order[0]['column'] == 3){
        $order_by = "service_grand ".$dir;
    }


// JOIN hr_invoice_staff as k on k.invoice_service=s.id
    $sql = "SELECT s.service as service_name,SUM(s.service_price*s.service_qty) as service_price,SUM(service_qty) as service_qty,SUM(service_total_wth_gst) as service_grand FROM hr_invoice as i Join `hr_invoice_service` as s on s.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and delete_bill='0' ".$where. $date_where."  group by service ";
    $total_records = num_rows($sql);
    //echo $sql;
    $sql .= " order by ".$order_by." LIMIT $start_from, $length";


    $record = select_array($sql);


    $data_record = array();
    $userdata = array();
    $i=0;

    foreach($record as $service_sale){

        foreach ($service_sale as $var => $sale){
            $$var = $sale;
        }

        $data['sevice_name'] = ($service_name);
        $data['service_price'] = number_format($service_price);
        $data['service_qty'] = number_format($service_qty);
        $data['service_grand'] = number_format($service_grand);

        $ttl_service_price += $service_price;
        $ttl_service_qty += $service_qty;
        $total_service_grand += $service_grand;

        $userdata[$i] = $data;

        $i++;
    }

    if(check_user_permission("report","delete",$user_id)){

        $data['sevice_name'] = "<h3>Grand Total</h3>";
        $data['service_price'] = number_format($ttl_service_price);
        $data['service_qty'] = number_format($ttl_service_qty);
        $data['service_grand'] = number_format($total_service_grand);
        $userdata[$i] = $data;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;
 
    return $data;
}




function get_staffrecord(){
    global $user_id, $salon_id;

    extract($_REQUEST);

    if (isset($start)) { $page  = $start; } else { $page=1; };
    $start_from = $start;

    if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and (staff_name LIKE '%".$search_value."%')";
    }

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
        $date_where = " and (DATE(i.invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";
    }else if($fromdate != '' && $todate == ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $date_where = " and (DATE(i.invoice_date) = '".$fromdate."')";
    }else{
        $date = date('Y-m-d');
        $date_where = " and DATE(i.invoice_date) LIKE '".$date."'";
    }

    $dir = $order[0]['dir'];
    $order_by = "staff_name ".$dir;

    if($order[0]['column'] == 1){
        $order_by = "staff_salary ".$dir;
    }


    $sql = "SELECT * FROM `hr_staff` where salon_id='".$salon_id."' and staff_status=1 ".$where;

    $total_records = num_rows($sql);
    $sql .= " order by ".$order_by." LIMIT $start_from, $length";
    $record = select_array($sql);

    $data_record = array();
    $userdata = array();
    $i=0;

    $salary_multiply = 5;
    $staff_incentive = 3;

    foreach($record as $staff){

        $staff_id = $staff['staff_id'];

        $where = " and s.staff_id LIKE '".$staff_id."'". $date_where;
        //echo "SELECT sum(s.total_amt) as total_pkg FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1'  and invoice_type!='2'  and payment_mode LIKE 'pkg' ".$date_where." ";
        $total_customer = (num_rows("SELECT distinct(cust_mob) FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' and invoice_type!='2' ".$where." "));
       

        
        $ratio = "(ss.service_total_wth_gst / NULLIF((SELECT SUM(service_total_wth_gst) FROM hr_invoice_service WHERE invoice_id=i.invoice_id), 0))";
        $common_subquery = "(ss.service_total_wth_gst - ($ratio * i.discount))";
        
        $total_service_sale = (select_row("SELECT sum($common_subquery) as total_service_sale FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id join hr_invoice_service as ss on ss.id=s.invoice_service where i.salon_id='".$salon_id."' and ss.service NOT LIKE 'Outstanding%' and ss.service_cat NOT LIKE 'Product%' and ss.service_cat NOT LIKE 'Membership%' and ss.service_cat NOT LIKE 'Package%' and i.delete_bill!='1' and i.payment_mode!='pkg' and i.payment_mode!='wallet' ".$where." "));

        $total_pkg_sale = (select_row("SELECT sum($common_subquery) as total_pkgsell FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id join hr_invoice_service as ss on ss.id=s.invoice_service where i.salon_id='".$salon_id."' and i.delete_bill!='1' and ss.service_cat LIKE 'Package%' and i.payment_mode!='pkg' and i.payment_mode!='wallet' ".$where." "));

        $total_mem_sale = (select_row("SELECT sum($common_subquery) as total_memsell FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id join hr_invoice_service as ss on ss.id=s.invoice_service where i.salon_id='".$salon_id."' and i.delete_bill!='1' and ss.service_cat LIKE 'Membership%' and i.payment_mode!='pkg' and i.payment_mode!='wallet' ".$where." "));

        $total_pkg_services = (select_row("SELECT sum($common_subquery) as total_pkg_service FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id join hr_invoice_service as ss on ss.id=s.invoice_service where i.salon_id='".$salon_id."' and i.delete_bill!='1' and ss.service NOT LIKE 'Outstanding%' and ss.service_cat NOT LIKE 'Product%' and ss.service_cat NOT LIKE 'Membership%' and ss.service_cat NOT LIKE 'Package%' and (i.payment_mode='pkg' or i.payment_mode='wallet') ".$where." "));

        $product_total = (select_row("SELECT sum($common_subquery) as product_total FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id join hr_invoice_service as ss on ss.id=s.invoice_service where i.salon_id='".$salon_id."' and i.delete_bill!='1' and ss.service_cat LIKE 'Product%' and i.payment_mode!='pkg' and i.payment_mode!='wallet' ".$where." "));

        $produc_sale = $product_total['product_total'] ? floatval($product_total['product_total']) : 0;
        $mem_sale = $total_mem_sale['total_memsell'] ? floatval($total_mem_sale['total_memsell']) : 0;
        $pkg_sale = $total_pkg_sale['total_pkgsell'] ? floatval($total_pkg_sale['total_pkgsell']) : 0;

        $ttl_salary += $staff['staff_salary'];
        $ttl_customer += $total_customer;
        $total_pkgsell += $pkg_sale;
        $ttl_memsell += $mem_sale;
        $ttl_produt += $produc_sale;
        $ttl_sale += ($total_service_sale['total_service_sale']);
        $total_pkg_service += ($total_pkg_services['total_pkg_service']);
        $grand_ttl += ($total_service_sale['total_service_sale']+$total_pkg_services['total_pkg_service']+$produc_sale+$pkg_sale+$mem_sale);


        $target_sale = $staff['staff_salary']*$salary_multiply;

        $grand_target_sale += $target_sale;

        $incentive_sale = $total_service_sale['total_service_sale']-($target_sale);
        if($incentive_sale > 0){
            $incentive = ($total_service_sale['total_service_sale']*$staff_incentive)/100;
            $incentive_sale = " (".number_format($total_service_sale['total_service_sale']).")";
        }else{
            $incentive = 0;
            $incentive_sale ='';
        }
        $ttl_incentive += ($incentive);


        $data['staff_name'] = $staff['staff_name'];
        $data['staff_salary'] = $staff['staff_salary'];
        $data['total_customer'] = $total_customer;

        $data['total_pkgsell'] = number_format($pkg_sale);
        $data['total_memsell'] = number_format($mem_sale);
        $data['service_sale'] = "<b>".number_format($total_service_sale['total_service_sale'])."</b>";
        $data['target_sale'] = "<b>".number_format($target_sale)."</b>";
        $data['incentive_sale'] = "<b>".number_format($incentive)."</b>";

        $data['product_total'] = number_format($produc_sale);
        $data['total_pkg_service'] = number_format($total_pkg_services['total_pkg_service']);
        $data['grand_total'] = number_format($total_service_sale['total_service_sale']+$total_pkg_services['total_pkg_service']+$produc_sale+$pkg_sale+$mem_sale);

        $userdata[$i] = $data;

        $i++;
    }

    if(check_user_permission("report","delete",$user_id)){

        $data['staff_name'] = "<h3>Grand Total</h3>";
        $data['staff_salary'] = number_format($ttl_salary);
        $data['total_customer'] = number_format($ttl_customer);
        $data['total_pkgsell'] = number_format($total_pkgsell);
        $data['total_memsell'] = number_format($ttl_memsell);
        $data['service_sale'] = number_format($ttl_sale);
        $data['target_sale'] = number_format($grand_target_sale);
        $data['incentive_sale'] = number_format($ttl_incentive);
        $data['product_total'] = number_format($ttl_produt);
        $data['total_pkg_service'] = number_format($total_pkg_service);
        $data['grand_total'] = number_format($grand_ttl);
        $userdata[$i] = $data;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;
}

function get_salerecord(){
    global $user_id, $salon_id,$role_id,$cash_discount,$payment_method;

    extract($_REQUEST);

    $where = "";
    $discountwhere = "";

    if (isset($start)) { $page  = $start; } else { $page=1; }; 
    $start_from = $start;

    if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and (cust_name LIKE '%".$search_value."%' OR cust_mob LIKE '%".$search_value."%' OR invoice_number LIKE '%".$search_value."%' OR payment_mode LIKE '".$search_value."')";
    }

    if($refrence_by != '0' && $refrence_by != '') {
        $where .= " and cust_ref_by = '".$refrence_by."'";
    }


    
    if($fromdate == '' && $todate == '') {
        $fromdate = date("Y-m-d");
        $where .= " and (DATE(invoice_date) = '".$fromdate."')";

        $fromdate_month = date("m",strtotime($fromdate));
        $fromdate_year = date("Y",strtotime($fromdate));
        $discountwhere .= " and (MONTH(month_discount) = '".$fromdate_month."') and (YEAR(month_discount) = '".$fromdate_year."')";

    } else if($fromdate != '' && $todate != '') {

        $fromdate = date("Y-m-d",strtotime($fromdate));
        $todate = date("Y-m-d",strtotime($todate));
        $where .= " and (DATE(invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";

        $discountwhere .= " and (DATE(month_discount) BETWEEN '".date("Y-m-d",strtotime($fromdate))."' AND '".date("Y-m-t",strtotime($todate))."')";

    } else if($fromdate != '' && $todate == '') {

        $fromdate = date("Y-m-d",strtotime($fromdate));

        $fromdate_month = date("m",strtotime($fromdate));
        $fromdate_year = date("Y",strtotime($fromdate));

        $where .= " and (DATE(invoice_date) = '".$fromdate."')";
        $discountwhere .= " and (MONTH(month_discount) = '".$fromdate_month."') and (YEAR(month_discount) = '".$fromdate_year."')";
    }

    if(isset($staff_id)){
        if($staff_id > 0){
            $where .= " and invoice_id IN (SELECT invoice_id FROM `hr_invoice_staff` where staff_id ='".$staff_id."')";
        }
    }

    if(isset($_REQUEST['service_id']) && is_array($_REQUEST['service_id']) && count($_REQUEST['service_id']) > 0) {
        $svc_list = implode(",", array_map('intval', $_REQUEST['service_id']));
        $where .= " AND invoice_id IN (SELECT invoice_id FROM hr_invoice_service WHERE service IN (SELECT service_name FROM hr_services WHERE service_id IN (".$svc_list.")))";
    }
   

    $mysql = "SELECT cash_discount,month_discount FROM `hr_salon_cashdiscount` where salon_id='".$salon_id."' ".$discountwhere;
    $alldiscount  = select_array($mysql);

    $sql = "SELECT cust_ref_by,`invoice_id`,invoice_number, `invoice_type`,delete_bill,delete_reason, `cust_name`, `cust_mob`, `discount`, `discount_mode`, `service_total`, `service_total_tax`, `round_off`, `grand_total`, `outstanding`, `payment_mode`, `billing_remark`, `invoice_date` FROM `hr_invoice` WHERE salon_id='".$salon_id."'  ".$where;

    $total_records = num_rows($sql); 
    
    // Handle Ordering
    $order_by = "invoice_id DESC"; // Default
    if(isset($_REQUEST['order']) && isset($_REQUEST['order'][0])) {
        $col_idx = $_REQUEST['order'][0]['column'];
        $dir = $_REQUEST['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
        
        switch ($col_idx) {
            case 0: $order_by = "invoice_id " . $dir; break;
            case 1: $order_by = "cust_name " . $dir; break;
            case 2: $order_by = "cust_mob " . $dir; break;
            case 3: $order_by = "payment_mode " . $dir; break;
            case 5: $order_by = "grand_total " . $dir; break;
            case 6: $order_by = "invoice_date " . $dir; break;
            default: $order_by = "invoice_id DESC"; break;
        }
    }
    
    $sql .= " order by " . $order_by . " LIMIT $start_from, $length";

   
    $record = select_array($sql);

    $data_record = array();
    $i=0;
    $userdata = array();

    foreach($record as $sale){

        $data['mysql'] = $mysql;
        $data_record['invoice_id'] = $sale['invoice_id'];        // real DB primary key for View/Print/Delete
        $data_record['invoice_number'] = $sale['invoice_number']; // display number shown in the table
        $data_record['cust_name'] = $sale['cust_name'];
        $data_record['cust_mob'] = $sale['cust_mob'];
        $ref_by = $sale['cust_ref_by'];
        $data_record['discount'] = "<span data-toggle='tooltip'  tabindex='0' data-placement='top' title='".$ref_by."'>".$sale['discount']."</span>";
        $mode = strtolower($sale['payment_mode']);
        if($mode == 'split'){
            // get splits
            $split_qry = "SELECT payment_mode FROM `hr_invoice_payment` WHERE invoice_id='".$sale['invoice_id']."'";
            $split_res = select_array($split_qry);
            $split_modes = [];
            if($split_res){
                foreach($split_res as $s){
                    $m = strtolower($s['payment_mode']);
                    $split_modes[] = isset($payment_method) && isset($payment_method[$m]) ? $payment_method[$m] : ucfirst($m);
                }
            }
            $mode = 'Split<br><small class="text-muted">('.implode(' + ', $split_modes).')</small>';
        }else{
            $mode = isset($payment_method) && isset($payment_method[$mode]) ? $payment_method[$mode] : ucfirst($mode);
        }

        $grand_total = $sale['grand_total'];

        $data_record['invoice_date'] = date('d-m-Y h:i A',strtotime($sale['invoice_date']));


        if((($mode == "cash" || $mode == "Cash") && $grand_total > 100) && $role_id != 3) {

            $cash_discount = get_cash_discount($alldiscount,$data_record['invoice_date']);

            $data_record['grand_total'] = $grand_total - (($grand_total * $cash_discount) / 100);
        }else{
            $data_record['grand_total'] = $grand_total;
        }

        $data_record['delete_bill'] = $sale['delete_bill']; 



        if($sale['invoice_type'] == 1){
            $data_record['invoice_type'] = "Package";
        }elseif($sale['invoice_type'] == 2){
            $data_record['invoice_type'] = "Product";
        }else{
            $data_record['invoice_type'] = "Service" ;
        }

        $data_record['payment_mode'] = ($sale['delete_bill'] == 1) ?$sale['delete_reason']:$mode;
        
       // var_dump(check_user_permission("report","report_view",$user_id));
       // exit;
        $all_btn = '';
        if(check_user_permission("report","view",$user_id)){
            $all_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="invoice_view.php?invoice_id='.$sale['invoice_id'].'"> <i class="fa fa-eye"></i> </button>';
        }
        $edit_btn='';
        if(check_user_permission("report","edit",$user_id) && $mode != 'pkg'){
            $all_btn .= '<button type="button" class="btn btn-gradient-info btn-xs modalButtonCommon" data-toggle="modal" data-href="model/invoice_edit.php?invoice_id='.$sale['invoice_id'].'"><i class="fa fa-pencil"></i> </button>';
        }

        if(check_user_permission("report","delete",$user_id)){
            $all_btn .= '<button type="button" class="btn btn-gradient-danger btn-xs modalButtonCommon" data-toggle="modal" data-href="invoice_del.php?invoice_id='.$sale['invoice_id'].'"><i class="fa fa-trash "></i> </button>';
        }

        $userdata[$i] = $data_record;
        $userdata[$i]['action'] = $all_btn;
        $userdata[$i]['user_role'] = '';//$role_name;

        $i++;
    }

    $data['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;
    
    return $data;
}

function update_invoice(){

    global $user_id, $salon_id, $conn;
    extract($_REQUEST);

    // Get invoice details first to sync payment table
    $inv = select_row("SELECT grand_total, invoice_date FROM `hr_invoice` WHERE `invoice_id`='".mysqli_real_escape_string($conn, $invoice_id)."'");
    
    update_query("UPDATE `hr_invoice` SET `payment_mode` = '".mysqli_real_escape_string($conn, $payment_mode)."' WHERE `invoice_id`='".mysqli_real_escape_string($conn, $invoice_id)."'");
    
    if($inv){
        $grand_total = $inv['grand_total'];
        $invoice_date = $inv['invoice_date'];
        
        // Update/Sync hr_invoice_payment table for reporting tiles
        update_query("DELETE FROM `hr_invoice_payment` WHERE `invoice_id`='".mysqli_real_escape_string($conn, $invoice_id)."'");
        update_query("INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."', grand_total = '".$grand_total."', payment_mode = '".mysqli_real_escape_string($conn, $payment_mode)."', invoice_id = '".mysqli_real_escape_string($conn, $invoice_id)."', created_date = '".$invoice_date."'");
    }

    return array("msg" => "Payment Method Changed","error"=>"0");
}


function invoice_delete(){
    global $user_id, $salon_id;
    extract($_REQUEST);

    if($delete_pwd == "delete" && $delete_reason != ''){
        $sql = update_query("UPDATE `hr_invoice` SET `delete_bill` = '1',`delete_reason` = '".$delete_reason."' WHERE `invoice_id`='".$invoice_id."'");
        return array("msg" => "Invoice Mark as Deleted","error"=>"0");
    }else{
        return array("msg" => "Password Incorrect","error"=>"1");
    }

}

function get_feedback(){
    global $user_id, $salon_id;

    extract($_REQUEST);
    $login_user = get_session_data('user_id');


    if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and (experience LIKE '%".$search_value."%')";
    }

    if (isset($start)) { $page  = $start; } else { $page=1; }; 
    $start_from = $start; 

    $sql = "SELECT * FROM `hr_feedback` WHERE salon_id='".$salon_id."' ".$where;

    $total_records = num_rows($sql); 
    $sql .= " order by created_date DESC LIMIT $start_from, $length";
    $record = select_array($sql);

    $userdata = array();
    $data_record = array();
    $i=0;
    foreach($record as $sale){

        $data_record['invoice_id'] = $sale['invoice_id'];
        $data_record['cust_name'] = $sale['cust_name'];
        $data_record['cust_mob'] = $sale['cust_mob'];
        $data_record['experience'] = $sale['experience']; 
        $data_record['message'] = $sale['message']; 
        $data_record['created_date'] = date('d-m-y h:i A',strtotime($sale['created_date']));

        
        if(check_user_permission("record","record_view",$user_id)){
            $edit_btn = '<button type="button" class="btn btn-gradient-info btn-xs modalButtonCommon" data-toggle="modal" data-href="invoice_view.php?invoice_id='.$sale['invoice_id'].'">View</button>';
        }

        $userdata[$i] = $data_record;
        $userdata[$i]['action'] = $edit_btn.$del_btn;

        $i++;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;
}

function get_attendencerecord(){

    global $user_id, $salon_id;

    extract($_REQUEST);

    if (isset($start)) { $page  = $start; } else { $page=1; };
    $start_from = $start;

    if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and (name LIKE '%".$search_value."%' )";
    }

    if($fromdate != '' && $todate == ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $where .= " and (DATE(user_date) = '".$fromdate."')";
    }

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
        $where .= " and (DATE(user_date) BETWEEN '".$fromdate."' AND '".$todate."')";
    }

    if(isset($staff_id) && $staff_id != '' && $staff_id != '0'){
        $where .= " and name='".$staff_id."'";
    }

    $order_by = "user_date DESC ";
    if($order[0]['column'] == 1){
        $dir = $order[0]['dir'];
        $order_by = "working_hr ".$dir;
    }



    //$sql = "SELECT `invoice_id`, `invoice_type`,delete_bill,delete_reason, `cust_name`, `cust_mob`, `discount`, `discount_mode`, `service_total`, `service_total_tax`, `round_off`, `grand_total`, `outstanding`, `payment_mode`, `billing_remark`, `invoice_date` FROM `hr_invoice` WHERE salon_id='".$salon_id."'  ".$where;
    $sql = "SELECT * FROM `hr_attendance`  WHERE 1=1 ".$where;
    $total_records = num_rows($sql);
    $sql .= " order by ".$order_by." LIMIT $start_from, $length";
    $record = select_array($sql);

    $i=0;
    foreach($record as $sale){
        $userdata[$i] = $sale;
        $i++;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;

}


function summary_attendance(){
    global $user_id, $salon_id;

    extract($_REQUEST);

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
        $date_where = " and (DATE(user_date) BETWEEN '".$fromdate."' AND '".$todate."')";


    }else if($fromdate != '' && $todate == ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $date_where = " and (DATE(user_date) = '".$fromdate."')";
    }else{
        $date = date('Y-m-d');
        $date_where = " and DATE(user_date) LIKE '".$date."'";
    }

    if($staff_id != ''){
        $date_where .= " and name LIKE '".$staff_id."'";
    }


    $total_days = (num_rows("SELECT (working_hr) as total_hr FROM `hr_attendance` WHERE 1=1 ".$date_where));
    extract(select_row("SELECT sum(working_hr) as total_hr FROM `hr_attendance` WHERE 1=1 ".$date_where));

    $data['total_hr'] = number_format($total_hr,2);
    $data['total_working'] = $total_days;

    return $data;
}

function get_pnl_report() {
    global $conn;
    $salon_id = get_session_data('salon_id');
    extract($_REQUEST);

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y-m-d",strtotime($fromdate));
        $todate = date("Y-m-d",strtotime($todate));
    }else if($fromdate != '' && $todate == ''){
        $fromdate = date("Y-m-d",strtotime($fromdate));
        $todate = $fromdate;
    }else{
        $fromdate = date('Y-m-d');
        $todate = date('Y-m-d');
    }

    $income = [ 'service' => 0, 'product' => 0, 'membership' => 0, 'package' => 0 ];

    // 1 & 2. Service and Product Sales
    $inv_q = "SELECT i.invoice_type, sum(p.grand_total) as amount FROM `hr_invoice` as i JOIN `hr_invoice_payment` as p on p.invoice_id=i.invoice_id WHERE i.salon_id='$salon_id' AND i.delete_bill!='1' AND p.payment_mode!='pkg' AND DATE(i.invoice_date) BETWEEN '$fromdate' AND '$todate' GROUP BY i.invoice_type";
    $inv_data = select_array($inv_q);
    if($inv_data) {
        foreach($inv_data as $row) {
            if($row['invoice_type'] == 2) {
                $income['product'] += floatval($row['amount']);
            } else {
                $income['service'] += floatval($row['amount']);
            }
        }
    }

    // 3. Memberships
    $mem_amt = select_row("SELECT sum(paid_amount) as amount FROM hr_customer_membership WHERE salon_id='$salon_id' AND status!='refunded' AND DATE(created_at) BETWEEN '$fromdate' AND '$todate'");
    if($mem_amt) { $income['membership'] += floatval($mem_amt['amount']); }

    // 4. Packages
    $pkg_amt = select_row("SELECT sum(purchase_price) as amount FROM hr_customer_packages WHERE salon_id='$salon_id' AND status!='refunded' AND DATE(purchase_date) BETWEEN '$fromdate' AND '$todate'");
    if($pkg_amt) { $income['package'] += floatval($pkg_amt['amount']); }

    $total_income = array_sum($income);

    // Expenses
    $expenses = [];
    $total_expense = 0;
    
    $exp_q = "SELECT c.category_name, sum(e.exp_total) as amount FROM hr_expenses e LEFT JOIN hr_expenses_category c ON e.exp_catId = c.exp_catId WHERE e.salon_id='$salon_id' AND DATE(e.exp_date) BETWEEN '$fromdate' AND '$todate' GROUP BY c.category_name ORDER BY amount DESC";
    $exp_data = select_array($exp_q);
    
    if($exp_data) {
        foreach($exp_data as $row) {
            $cat = $row['category_name'] ?: 'Uncategorized';
            $amt = floatval($row['amount']);
            $expenses[] = ['category' => $cat, 'amount' => $amt];
            $total_expense += $amt;
        }
    }

    $net_profit = $total_income - $total_expense;

    return [
        'error' => 0,
        'income' => $income,
        'total_income' => $total_income,
        'expenses' => $expenses,
        'total_expense' => $total_expense,
        'net_profit' => $net_profit
    ];
}
?>
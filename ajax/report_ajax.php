<?php 

include "../function.php";

$method=$_REQUEST["method"];

if(function_exists($method))
echo json_encode($method());
else
echo "Method Not Found";


$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');
$cash_discount = get_session_data('cash_discount');
$role_id = get_session_data('role_id');


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

    if($refrence_by != '0' && $refrence_by != '') {
        $date_where .= " and i.cust_ref_by = '".$refrence_by."'";
    }

    extract(select_row("SELECT sum(s.total_amt) as grand_total FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1'  and payment_mode!='pkg' ".$date_where." "));
    extract(select_row("SELECT sum(s.total_amt) as product_total_cash FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' and payment_mode='cash'  and invoice_type='2'  and payment_mode!='pkg' ".$date_where." "));
    extract(select_row("SELECT sum(s.total_amt) as product_total_cc FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' and payment_mode!='cash'  and invoice_type='2'  and payment_mode!='pkg' ".$date_where." "));
    $total_customer = (num_rows("SELECT distinct(cust_mob) FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' and invoice_type!='2' ".$date_where." "));
    extract(select_row("SELECT sum(s.total_amt) as total_cash FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1'  and invoice_type!='2'  and payment_mode LIKE 'cash' ".$date_where." "));
    extract(select_row("SELECT sum(s.total_amt) as total_cc FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1'  and invoice_type!='2'  and (payment_mode='paytm' || payment_mode='cc' || payment_mode='google_pay' || payment_mode='upi' ) ".$date_where." "));
    extract(select_row("SELECT sum(s.total_amt) as total_nearbuy FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1'  and invoice_type!='2'  and (payment_mode='near_buy') ".$date_where." "));



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
        foreach($sale_monthlY as $var => $sale)
            $$var = $sale;

        $data['total_customer_cash'] += $total_customer;
        $data['total_customer_pkg'] += $total_customer_pkg;
        $data['total_customer'] += $total_customer_pkg+$total_customer;

        $data['total_cash'] += $total_cash;
        $data['total_cc'] += $total_cc;
        $data['service_total'] += $total_cc+$total_cash;


        $data['product_total_cash'] += $product_total;
        $data['product_total_cc'] += $product_total_cc;
        $data['product_total'] += $product_total_cc+$product_total;

        $data['exp_total_cash'] += $exp_total;
        $data['exp_total_cc'] += $exp_total_cc;
        $data['exp_total'] += $exp_total+$exp_total_cc;

        $data['grand_cash'] += $product_total+$total_cash;
        $data['grand_cc'] += $product_total_cc+$total_cc;
        $data['grand_total'] += $grand_total;
    }
    return $data;
}

function summary_sale_monthly($fromdate,$todate,$refrence_by){

    global $user_id, $salon_id,$role_id,$cash_discount;

    $discountwhere = '';
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


    $mysql = "SELECT cash_discount,month_discount FROM `hr_salon_cashdiscount` where salon_id='".$salon_id."' ".$discountwhere;
    $alldiscount  = select_array($mysql);

    //echo ("SELECT sum(grand_total) as grand_total FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1'  and payment_mode!='pkg' ".$date_where." ");
    extract(select_row("SELECT sum(grand_total) as grand_total FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1'  and payment_mode!='pkg' ".$date_where." "));

    extract(select_row("SELECT sum(p.grand_total) as product_total_cash FROM `hr_invoice` as i JOIN  `hr_invoice_payment` as p on p.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and delete_bill!='1' and p.payment_mode='cash'  and invoice_type='2' ".$date_where." "));
    extract(select_row("SELECT sum(p.grand_total) as product_total_cc FROM `hr_invoice` as i JOIN  `hr_invoice_payment` as p on p.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and delete_bill!='1' and p.payment_mode!='cash'  and invoice_type='2' ".$date_where." "));

    $total_customer = (num_rows("SELECT cust_mob FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1' and invoice_type!='2' and payment_mode!='pkg' ".$date_where." "));
    $total_customer_pkg = (num_rows("SELECT cust_mob FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1' and invoice_type!='2' and payment_mode='pkg' ".$date_where." "));
    
    $mydata = (select_row("SELECT sum(p.grand_total) as total_cash FROM `hr_invoice` as i JOIN  `hr_invoice_payment` as p on p.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and delete_bill!='1' and invoice_type!='2'  and p.payment_mode LIKE 'cash' ".$date_where." "));
    $total_cash = $mydata['total_cash'];
    extract(select_row("SELECT sum(p.grand_total) as total_cc FROM `hr_invoice` as i JOIN  `hr_invoice_payment` as p on p.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and delete_bill!='1' and invoice_type!='2'  and (p.payment_mode='paytm' || p.payment_mode='cc' || p.payment_mode='google_pay' || p.payment_mode='upi' ) ".$date_where." "));

    extract(select_row("SELECT sum(grand_total) as total_nearbuy FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1' and invoice_type!='2'  and (payment_mode='near_buy') ".$date_where." "));

    extract(select_row("SELECT sum(exp_total) as exp_cash FROM `hr_expenses` where salon_id='".$salon_id."' and payment_mode='cash' ".$exp_where." "));
    extract(select_row("SELECT sum(exp_total) as exp_cc FROM `hr_expenses` where salon_id='".$salon_id."' and payment_mode='cc' ".$exp_where." "));


    if($role_id != 3) {
        $cash_discount = get_cash_discount($alldiscount,$fromdate);
        $total_cash = $total_cash - (($total_cash * $cash_discount) / 100);
    }else{
        $total_cash = $total_cash;
    }

    $data['grand_total'] = $total_cash+$product_total_cash+$product_total_cc+$total_cc+$total_nearbuy;
    $data['total_customer'] = $total_customer;
    $data['total_customer_pkg'] = $total_customer_pkg;
    $data['total_cash'] = $total_cash;
    $data['total_cc'] = $total_cc;
    $data['product_total'] = $product_total_cash;
    $data['product_total_cc'] = $product_total_cc;
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
        $where .= " and (i.payment_mode !='pkg') ";
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
       

        
        $total_service_sale = (select_row("SELECT sum(s.total_amt) as total_service_sale FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id join hr_invoice_service as ss on ss.id=s.invoice_service   where salon_id='".$salon_id."' and ss.service NOT LIKE 'Outstanding%' and delete_bill!='1' and payment_mode!='pkg'  and invoice_type='0' ".$where." "));

        $total_pkg_sale = (select_row("SELECT sum(s.total_amt) as total_pkgsell FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1'   and invoice_type='1' ".$where." "));

        $total_pkg_services = (select_row("SELECT sum(s.total_amt) as total_pkg_service FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1'   and payment_mode LIKE 'pkg' ".$where." "));

        $product_total = (select_row("SELECT sum(s.total_amt) as product_total FROM `hr_invoice` as i join hr_invoice_staff as s on s.invoice_id=i.invoice_id  where salon_id='".$salon_id."' and delete_bill!='1' and invoice_type='2'  and payment_mode!='pkg' ".$where." "));

        $produc_sale = ($product_total['product_total']);

        $ttl_salary += $staff['staff_salary'];
        $ttl_customer += $total_customer;
        $total_pkgsell += ($total_pkg_sale['total_pkgsell']);
        $ttl_produt += $produc_sale;
        $ttl_sale += ($total_service_sale['total_service_sale']);
        $total_pkg_service += ($total_pkg_services['total_pkg_service']);
        $grand_ttl += ($total_service_sale['total_service_sale']+$total_pkg_service['total_pkg_service']+$produc_sale+$total_pkg_sale['total_pkgsell']);


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

        $data['total_pkgsell'] = number_format($total_pkg_sale['total_pkgsell']);
        $data['service_sale'] = "<b>".number_format($total_service_sale['total_service_sale'])."</b>";
        $data['target_sale'] = "<b>".number_format($target_sale)."</b>";
        $data['incentive_sale'] = "<b>".number_format($incentive)."</b>";

        $data['product_total'] = number_format($produc_sale);
        $data['total_pkg_service'] = number_format($total_pkg_services['total_pkg_service']);
        $data['grand_total'] = number_format($total_service_sale['total_service_sale']+$total_pkg_services['total_pkg_service']+$produc_sale+$total_pkg_sale['total_pkgsell']);

        $userdata[$i] = $data;

        $i++;
    }

    if(check_user_permission("report","delete",$user_id)){

        $data['staff_name'] = "<h3>Grand Total</h3>";
        $data['staff_salary'] = number_format($ttl_salary);
        $data['total_customer'] = number_format($ttl_customer);
        $data['total_pkgsell'] = number_format($total_pkgsell);
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
        $fromdate = date("Y/m/d");
        $where .= " and (DATE(invoice_date) = '".$fromdate."')";

        $fromdate_month = date("m",strtotime($fromdate));
        $fromdate_year = date("Y",strtotime($fromdate));
        $discountwhere .= " and (MONTH(month_discount) = '".$fromdate_month."') and (YEAR(month_discount) = '".$fromdate_year."')";
    }
    if($fromdate != '' && $todate == ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $where .= " and (DATE(invoice_date) = '".$fromdate."')";


        $fromdate_month = date("m",strtotime($fromdate));
        $fromdate_year = date("Y",strtotime($fromdate));
        $discountwhere .= " and (MONTH(month_discount) = '".$fromdate_month."') and (YEAR(month_discount) = '".$fromdate_year."')";
    }

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y/m/d",strtotime($fromdate));
        $todate = date("Y/m/d",strtotime($todate));
        $where .= " and (DATE(invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";

        $discountwhere .= " and (DATE(month_discount) BETWEEN '".date("Y/m/1",strtotime($fromdate))."' AND '".date("Y/m/31",strtotime($todate))."')";
    }

    if($staff_id != '' && $staff_id != ''){
        $where .= " and invoice_id IN (select invoice_id from hr_invoice_staff where staff_id='".$staff_id."' ".$where.")";
    }


    $mysql = "SELECT cash_discount,month_discount FROM `hr_salon_cashdiscount` where salon_id='".$salon_id."' ".$discountwhere;
    $alldiscount  = select_array($mysql);


    $sql = "SELECT cust_ref_by,`invoice_id`,invoice_number, `invoice_type`,delete_bill,delete_reason, `cust_name`, `cust_mob`, `discount`, `discount_mode`, `service_total`, `service_total_tax`, `round_off`, `grand_total`, `outstanding`, `payment_mode`, `billing_remark`, `invoice_date` FROM `hr_invoice` WHERE salon_id='".$salon_id."'  ".$where;

    $total_records = num_rows($sql); 
    $sql .= " order by invoice_date DESC LIMIT $start_from, $length";

    //echo $sql;
    $record = select_array($sql);

    $data_record = array();
    $i=0;
    $userdata = array();

    foreach($record as $sale){

        $data_record['invoice_id'] = $sale['invoice_number'];
        $data_record['cust_name'] = $sale['cust_name'];
        $data_record['cust_mob'] = $sale['cust_mob'];
        $ref_by = $sale['cust_ref_by'];
        $data_record['discount'] = "<span data-toggle='tooltip'  tabindex='0' data-placement='top' title='".$ref_by."'>".$sale['discount']."</span>";
        $mode = strtolower($sale['payment_mode']);
        $mode = $payment_method[$mode];

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

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;
}

function update_invoice(){

    global $user_id, $salon_id;
    extract($_REQUEST);

    update_query("UPDATE `hr_invoice` SET `payment_mode` = '".$payment_mode."' WHERE `invoice_id`='".$invoice_id."'");
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

    if($staff_id != '' && $staff_id != ''){
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
?>
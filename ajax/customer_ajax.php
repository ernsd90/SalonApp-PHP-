<?php 

include "../function.php";

$method=$_REQUEST["method"];

if(function_exists($method))
echo json_encode($method());
else
echo "Method Not Found";


$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

function update_staff(){
    
    extract($_POST);

	$sql = "UPDATE `hr_staff` SET `staff_mob`='".$staff_mob."',`staff_name`='".$staff_name."',`joining_date`='".$joining_date."',`staff_salary`='".$staff_salary."',`staff_status`='".$staff_status."' Where staff_id = '".$staff_id."'";
    update_query($sql);
    $msg = "User Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}


function customer_update(){
    extract($_POST);

    $password = $salon_id;//strtolower($salon_id.str_replace(" ","",$cust_name));
    if($cust_password != $password){
        return array("msg" => "Enter a Correct password","error"=>1);
    }

    extract(select_row("SELECT balance FROM `hr_customer_wallet` WHERE `cust_id` = '".$cust_id."' order by wallet_id desc"));

    if($balance != $cust_wallet){

        if($balance > $cust_wallet){
            $debit = $balance-$cust_wallet;
            $credit = 0;
        }else{
            $credit = $cust_wallet-$balance;
            $debit = 0;
        }
        update_query("INSERT INTO  `hr_customer_wallet` SET cust_id = '".$cust_id."',debit = '".$debit."',credit = '".$credit."',balance='".$cust_wallet."',`remark` = 'changed from customer edit'");
      // echo  "INSERT INTO  `hr_customer_wallet` SET cust_id = '".$cust_id."',debit = '".$debit."',credit = '".$credit."',balance='".$cust_wallet."',`remark` = 'changed from customer edit'";
    }

    $sql = "UPDATE `hr_customer` SET `cust_name`='".$cust_name."',`cust_mobile`='".$cust_mobile."',`cust_wallet`='".$cust_wallet."',`cust_outstanding`='".$cust_outstanding."' Where cust_id = '".$cust_id."'";

    update_query($sql);
    $msg = "Customer Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}

function get_customer_details(){

     global $user_id, $salon_id;
    
        extract($_REQUEST);

        $fields = array('created_date','credit','debit','balance');

        $order_field = $fields[$order[0]['column']];
        $order_dir = $order[0]['dir'];

        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT cust_id,credit,debit,balance,created_date,invoice_id FROM `hr_customer_wallet` where  `cust_id`='".$cust_id."' order by $order_field $order_dir ";

        $total_records = num_rows($sql);
    
        $sql .= " LIMIT $start_from, $length";
    
        $user = select_array($sql);
        $userdata = array();
        $data = array();

        $i=0;
        foreach($user as $users){
            extract($users);

            $users['created_date'] = date("d-m-Y h:i A",strtotime($created_date));
            if(check_user_permission("customer","edit",$user_id) && $invoice_id > 0){
                $view_btn = '<a class="btn btn-gradient-success btn" href="/print_invoice.php?invoice_id='.$invoice_id.'&type=close" target="_blank">view</a>';
            }else{
                $view_btn = "Edited by Admin";
            }

            $userdata[$i] = $users;
            $userdata[$i]['action'] = $view_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }

function get_customer(){

     global $user_id, $salon_id;
    
        extract($_REQUEST);

        $fields = array('cust_id','cust_name','cust_mobile','cust_wallet','cust_outstanding');

        $order_field = $fields[$order[0]['column']];

        $order_dir = $order[0]['dir'];

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and (cust_name LIKE '%".$search_value."%' or cust_mobile LIKE '%".$search_value."%')";
        }
        if($search['value'] == 'only_member'){
            $where = " and cust_wallet > '0'";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

         $sql = "SELECT cust_id,cust_name,cust_mobile,cust_wallet,cust_outstanding FROM `hr_customer` where  `salon_id`='".$salon_id."'  $where  ORDER BY $order_field $order_dir";

       $total_records = num_rows($sql);
    
		$sql .= " LIMIT $start_from, $length";
	
        $user = select_array($sql);
    $userdata = array();
    $data = array();
        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("customer","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-gradient-info btn  modalButtonCommon" data-toggle="modal" data-href="add-customers.php?cust_id='.$cust_id.'">Edit</button>';

                $view_btn = '<button type="button" class="btn btn-gradient-success btn  modalButtonCommon" data-toggle="modal" data-href="model/customer_details_view.php?cust_id='.$cust_id.'">view</button>';
            }
           

            $userdata[$i] = $users;
            
            if ($cust_wallet != 0) {

                $userdata[$i]['action'] = $view_btn." ".$edit_btn;

            }
            else{
                $userdata[$i]['action'] = $edit_btn;
            }
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }




function get_customer_from_mobile(){
    
    extract($_REQUEST);

    global $user_id, $salon_id;


    $where = " and (cust_name LIKE '%".$cust_mob."%' or cust_mobile LIKE '%".$cust_mob."%')";


    $get_fields = "cust_mobile,cust_id";
    if($detail == 1){
        $where = " and cust_mobile LIKE '".$cust_mob."'";

        
        extract(select_row("SELECT billing_remark FROM `hr_invoice` where cust_mob='".$cust_mob."' and billing_remark!='' ORDER BY `invoice_id`  DESC"));

        $get_fields = "cust_id,cust_name,cust_mobile,cust_gender,cust_wallet,cust_outstanding,cust_reffer";

    }

    $sql = "SELECT $get_fields FROM `hr_customer` where  `salon_id`='".$salon_id."' $where ORDER BY cust_wallet desc";
    $sql .= " LIMIT 10";
    $user = select_array($sql);


    if($detail == 1){

        $user[0]['billing_remark'] = $billing_remark;
        return $user;
    }
    $data = array();
    foreach($user as  $key=>$users){
        $data[] = $users['cust_mobile'];
    }
    return $data;
}

?>
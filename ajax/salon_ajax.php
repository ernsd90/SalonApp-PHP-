<?php 

include "../function.php";

$method=$_REQUEST["method"];



if(function_exists($method)){
    echo json_encode($method());
}
else{
    echo "Method Not Found";
}

$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');



/*----Services Category--------*/

function create_services_cat(){
    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);

    $sql = "SELECT * FROM `hr_servicesCategory` WHERE `service_catName`='".$service_catName."' and `salon_id`='".$salon_id."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Category Already Exist";
        $error = 1;
    }else{
         $sql = "INSERT INTO `hr_servicesCategory` SET `service_catName`='".$service_catName."',`salon_id`='".$salon_id."',`user_id`='".$user_id."'";
        insert_query($sql);
        $msg = "Service Category Added Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}
function update_services_cat(){
    
    extract($_POST);

	$sql = "UPDATE `hr_servicesCategory` SET `service_catName`='".$service_catName."'  Where service_catid = '".$service_catid."'";
    update_query($sql);
    $msg = "User Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}
function delete_services_cat(){
    
    extract($_POST);

	 $sql = "DELETE FROM `hr_servicesCategory` WHERE service_catid  = '".$id."' ";
	 
    if(update_query($sql)){
		 $msg = "Category Delete Successfully";
	}else{
		 $msg = "Error While Delete (Services Uses This Category)";
		 $error = 1;
	}
    return array("msg" => $msg,"error"=>$error);
}


function delete_inventorybill(){

    extract($_POST);

    $sql = "DELETE FROM `hr_bill` WHERE bill_id  = '".$id."' ";
    if(update_query($sql)){

        update_query("UPDATE `hr_vendor_payment` SET `bill_deleted`=1 WHERE `bill_id`='".$id."'");

        $msg = "Bill Delete Successfully";
    }else{
        $msg = "Error While Delete (Services Uses This Category)";
        $error = 1;
    }
    return array("msg" => $msg,"error"=>$error);
}




function get_serviceCat(){
    
        extract($_REQUEST);
    
        global $user_id, $salon_id;
        

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and service_catName LIKE '%".$search_value."%'";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT service_catid,service_catName FROM `hr_servicesCategory` where  `salon_id`='".$salon_id."' $where ORDER BY service_catid desc";
        $total_records = num_rows($sql); 
    
		$sql .= " LIMIT $start_from, $length";
	
        $user = select_array($sql);
    $userdata = array();
        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("cataloge","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info  modalButtonCommon" data-toggle="modal" data-href="services_cat_edit.php?service_catid='.$service_catid.'"><i class="fa fa-edit "></i></button>';
            }
			if(check_user_permission("cataloge","delete",$user_id)){
                $del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?service_catid='.$service_catid.'"><i class="fa fa-trash "></i> </button>';
            }

            $userdata[$i] = $users;
            $userdata[$i]['action'] = $edit_btn.$del_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }
	


    function get_package(){
    
        extract($_REQUEST);
    
        global $user_id, $salon_id;

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and package_name LIKE '%".$search_value."%' ";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT pkg_id,package_name,pakage_validity,customer_pay,customer_get,package_status FROM `hr_packages`  where  `salon_id`='".$salon_id."' $where ORDER BY pkg_id desc";
        $total_records = num_rows($sql); 
    
		$sql .= " LIMIT $start_from, $length";
	
        $user = select_array($sql);
        $userdata = array();
        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("cataloge","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="package_add.php?pkg_id='.$pkg_id.'"> <i class="fa fa-edit"></i> </button>';
            } 
			 if(check_user_permission("cataloge","delete",$user_id)){
                $del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?pkg_id='.$pkg_id.'"><i class="fa fa-trash "></i> </button>';
            }
			$status_btn = '<label class="badge badge-danger">Inactive</label>';
            if($users['package_status'] == 1){
				$status_btn = '<label class="badge badge-success">Active</label>';
                
            }

            $userdata[$i] = $users;
            $userdata[$i]['package_status'] = $status_btn;
            $userdata[$i]['action'] = $edit_btn.$del_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }
function create_package(){
        global $user_id, $salon_id;
        $error = 0; 
        extract($_POST);
    
        $sql = "SELECT pkg_id FROM `hr_packages` WHERE `package_name`='".$package_name."'  and  `salon_id`='".$salon_id."'"; 
         $ttl = num_rows($sql);
        if($ttl > 0){
            $msg = "Package Already Exist";
            $error = 1;
        }else{
            $pakage_validity = $pakage_validity*30;
              $sql = "INSERT INTO `hr_packages` SET `package_name`='".$package_name."',`pakage_validity`='".$pakage_validity."',`customer_pay`='".$customer_pay."', `customer_get`='".$customer_get."',`package_status`='".$package_status."',`user_id`='".$user_id."',`salon_id`='".$salon_id."'";
            insert_query($sql);
            $msg = "Package Added Successfully";
        }
        
        return array("msg" => $msg,"error"=>$error);
    }
	
	/*----services--------*/
	
function create_services(){
    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);

    $sql = "SELECT service_id FROM `hr_services` WHERE `service_name`='".$service_name."' and `service_catid`='".$service_catid."'"; 
     $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Service Already Exist";
        $error = 1;
    }else{
        $service_name = ucwords(strtolower(trim($service_name)));
         $sql = "INSERT INTO `hr_services` SET `service_name`='".$service_name."',`salon_id`='".$salon_id."',`service_price`='".$service_price."',`service_time`='".$service_time."' ,`service_status`='".$service_status."',`service_reminder`='".$service_reminder."', `service_catid`='".$service_catid."',`user_id`='".$user_id."'";
        insert_query($sql);
        $msg = "Service Added Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}
function update_services(){
    
    extract($_POST);
    $service_name = ucwords(strtolower(trim($service_name)));
    $sql = "UPDATE `hr_services` SET `service_name`='".$service_name."', `service_price`='".$service_price."',`service_status`='".$service_status."' ,`service_time`='".$service_time."',`service_catid`='".$service_catid."',`service_reminder`='".$service_reminder."'  Where service_id = '".$service_id."'";
	 
    update_query($sql);
    $msg = "Service Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}
function delete_service(){
    
    extract($_POST);
	$sql = "DELETE FROM `hr_services` WHERE service_id  = '".$id."' ";
    update_query($sql);
    $msg = "Service Delete Successfully";
    return array("msg" => $msg,"error"=>$error);
}
function delete_package(){
    
    extract($_POST);
	$sql = "DELETE FROM `hr_packages` WHERE pkg_id  = '".$id."' ";
    update_query($sql);
    $msg = "Package Delete Successfully";
    return array("msg" => $msg,"error"=>$error);
}
function get_service(){
    
        extract($_REQUEST);
    
        global $user_id, $salon_id;

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and service_name LIKE '%".$search_value."%' ";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT * FROM `hr_services`  where  `salon_id`='".$salon_id."' $where ORDER BY service_id desc";
        $total_records = num_rows($sql); 
    
		$sql .= " LIMIT $start_from, $length";
	
        $user = select_array($sql);
    $userdata = array();
        $i=0;
        foreach($user as $users){
            extract($users);
            // if(check_user_permission("cataloge","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="services_edit.php?service_id='.$service_id.'"> <i class="fa fa-edit"></i> </button>';
            // }
			 if(check_user_permission("cataloge","delete",$user_id)){
                $del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?service_id='.$service_id.'"><i class="fa fa-trash "></i> </button>';
            }
			$status_btn = '<label class="badge badge-danger">Inactive</label>';
            if($users['service_status'] == 1){
				$status_btn = '<label class="badge badge-success">Active</label>';
                
            }
            $sql = "SELECT * FROM `hr_servicesCategory` where service_catid = '".$service_catid."'";
			$service_catName = select_row($sql); 

            $userdata[$i] = $users;
            $userdata[$i]['service_catid'] = $service_catName['service_catName'];
            $userdata[$i]['service_status'] = $status_btn;
            $userdata[$i]['action'] = $edit_btn.$del_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }
	
	
	
	/*----product--------*/
	
function create_product_brand(){
    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);

    $sql = "SELECT product_id FROM `hr_product_brand` WHERE `brand_name`='".$brand_name."' and `salon_id`='".$salon_id."'"; 
     $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Brand Already Exist";
        $error = 1;
    }else{
        $brand_name = ucwords(strtolower(trim($brand_name)));
         $sql = "INSERT INTO `hr_product_brand` SET `salon_id`='".$salon_id."', `user_id`='".$user_id."', `brand_name`='".$brand_name."', `brand_status` = '".$brand_status."'";
        insert_query($sql);
        $msg = "Brand Added Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}
function update_product_brand(){
    
    extract($_POST);
    $brand_name = ucwords(strtolower(trim($brand_name)));
	 $sql = "UPDATE `hr_product_brand` SET `brand_name`='".$brand_name."',`brand_status`='".$brand_status."'  Where brand_id = '".$brand_id."'";
	 
    update_query($sql);
    $msg = "Brand Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}
function delete_product_brand(){
    
    extract($_POST);

	 $sql = "DELETE FROM `hr_product_brand` WHERE brand_id  = '".$id."' ";
	 
    update_query($sql);
    $msg = "Brand Delete Successfully";

    return array("msg" => $msg,"error"=>$error);
}
function get_product_brand(){
    
        extract($_REQUEST);
    
        global $user_id, $salon_id;

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and brand_name LIKE '%".$brand_name."%'";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT * FROM `hr_product_brand`  where  `salon_id`='".$salon_id."' $where ORDER BY brand_id desc";
        $total_records = num_rows($sql); 
    
		$sql .= " LIMIT $start_from, $length";
	
        $user = select_array($sql);
    $userdata = array();
        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("cataloge","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="product_brand_edit.php?brand_id='.$brand_id.'"> <i class="fa fa-edit"></i> </button>';
			
            } 
			 if(check_user_permission("cataloge","delete",$user_id)){
                $del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?brand_id='.$brand_id.'"><i class="fa fa-trash "></i> </button>';
            }
			$status_btn = '<label class="badge badge-danger">Inactive</label>';
            if($users['brand_status'] == 1){
				$status_btn = '<label class="badge badge-success">Active</label>';
                
            }


            $userdata[$i] = $users;
            $userdata[$i]['brand_status'] = $status_btn;
            $userdata[$i]['action'] = $edit_btn.$del_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }




function create_product(){
    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);


    $sql = "SELECT product_id FROM `hr_product` WHERE `product_name`='".$product_name."' and `brand_id`='".$brand_id."' and `salon_id`='".$salon_id."'"; 
     $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Product Already Exist";
        $error = 1;
    }else{
        $product_name = ucwords(strtolower(trim($product_name)));
        $sql = "INSERT INTO `hr_product` SET `salon_id`='".$salon_id."', `user_id`='".$user_id."', `brand_id`='".$brand_id."', `product_price`='".$product_price."', `product_name`='".$product_name."', `product_status` = '".$product_status."'";
        insert_query($sql);
        $msg = "Product Added Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}
function update_product(){
    
    extract($_POST);

    $sql = "SELECT product_id FROM `hr_product` WHERE `product_name`='".$product_name."' and `brand_id`='".$brand_id."' and `salon_id`='".$salon_id."' and product_id != '".$product_id."'"; 
    $ttl = num_rows($sql);
    if($ttl > 0){
       $msg = "Product Already Exist";
       $error = 1;
    }else{
        $product_name = ucwords(strtolower(trim($product_name)));
        $sql = "UPDATE `hr_product` SET `product_name`='".$product_name."', `product_price`='".$product_price."',`brand_id`='".$brand_id."',`product_status`='".$product_status."'  Where product_id = '".$product_id."'";
        
        update_query($sql);
        $msg = "Product Updated Successfully";
    }
    return array("msg" => $msg,"error"=>$error);
}
function delete_product(){
    
    extract($_POST);

	$sql = "DELETE FROM `hr_product` WHERE product_id='".$id."' ";
    update_query($sql);
    $msg = "Product Delete Successfully";

    return array("msg" => $msg,"error"=>$error);
}


function get_product(){
    
    extract($_REQUEST);

    global $user_id, $salon_id;

   if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and b.brand_name LIKE '%".$search_value."%' OR p.product_name LIKE '%".$search_value."%'";
    }
    
    if (isset($start)) { $page  = $start; } else { $page=1; }; 
    $start_from = $start; 

    $sql = "SELECT p.*,b.brand_name FROM `hr_product` as p join `hr_product_brand` as b on b.brand_id=p.brand_id  where  p.salon_id='".$salon_id."'  $where ORDER BY product_id desc";
    $total_records = num_rows($sql); 

    $sql .= " LIMIT $start_from, $length";

    $user = select_array($sql);
    $userdata = array();
    $i=0;
    foreach($user as $users){
        extract($users);

        if(check_user_permission("product","edit",$user_id)){
            $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="product_edit.php?product_id='.$product_id.'"> <i class="fa fa-edit"></i> </button>';
        
        } 
         if(check_user_permission("product","delete",$user_id)){
            $del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?product_id='.$product_id.'"><i class="fa fa-trash "></i> </button>';
        }
        $status_btn = '<label class="badge badge-danger">Inactive</label>';
        if($users['product_status'] == 1){
            $status_btn = '<label class="badge badge-success">Active</label>';
            
        }


        $userdata[$i] = $users;
        $userdata[$i]['product_status'] = $status_btn;
        $userdata[$i]['action'] = $edit_btn.$del_btn;
       

        $i++;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;
}
	
	
	/*----Expenses Cat--------*/
	
function create_expenses_cat(){
    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);

    $sql = "SELECT exp_catId FROM `hr_expenses_category` WHERE `category_name`='".$category_name."' and `salon_id`='".$salon_id."'"; 
     $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Expenses Category Already Exist";
        $error = 1;
    }else{
         $sql = "INSERT INTO `hr_expenses_category` SET `salon_id`='".$salon_id."', `user_id`='".$user_id."', `category_name`='".$category_name."'";
        insert_query($sql);
        $msg = "Expenses Category Added Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}
function update_expenses_cat(){
    
    extract($_POST);

	 $sql = "UPDATE `hr_expenses_category` SET `category_name`='".$category_name."'  Where exp_catId = '".$exp_catId."'";
	 
    update_query($sql);
    $msg = "Expenses Category Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}
function delete_expenses_cat(){
    
    extract($_POST);

	 $sql = "DELETE FROM `hr_expenses_category` WHERE exp_catId  = '".$id."' ";
	 
    update_query($sql);
    $msg = "Expenses Category Delete Successfully";

    return array("msg" => $msg,"error"=>$error);
}
function get_expensesCat(){
    
        extract($_REQUEST);
    
        global $user_id, $salon_id;

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and category_name LIKE '%".$category_name."%'";
        }



        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT * FROM `hr_expenses_category`  where  `salon_id`='".$salon_id."' $where ORDER BY exp_catId desc";
        $total_records = num_rows($sql); 
    
		$sql .= " LIMIT $start_from, $length";
	
        $user = select_array($sql);
    $userdata  =array();
        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("expenses","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="expenses_cat_edit.php?exp_catId='.$exp_catId.'"> <i class="fa fa-edit"></i> </button>';
			
            } 
			 if(check_user_permission("expenses","delete",$user_id)){
                $del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?exp_catId='.$exp_catId.'"><i class="fa fa-trash "></i> </button>';
            }


            $userdata[$i] = $users;
            $userdata[$i]['action'] = $edit_btn.$del_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }


	
	
	/*----Expenses--------*/

	
function create_expenses(){
    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);
        
    $exp_name = ucwords($exp_name);
  
         $sql = "INSERT INTO `hr_expenses` SET `exp_name`='".$exp_name."',`salon_id`='".$salon_id."',`payment_mode`='".$payment_mode."',`exp_vendor`='".$exp_vendor."',`exp_total`='".$exp_total."',`exp_note`='".$exp_note."', `exp_catId`='".$exp_catId."',`user_id`='".$user_id."'";
        insert_query($sql);
        $msg = "Expenses Added Successfully";
    

    return array("msg" => $msg,"error"=>$error);
}
 function update_expenses(){
    
    extract($_POST);

	 $sql = "UPDATE `hr_expenses` SET  `exp_name`='".$exp_name."',`salon_id`='".$salon_id."',`exp_vendor`='".$exp_vendor."',`exp_total`='".$exp_total."',`exp_note`='".$exp_note."', `exp_catId`='".$exp_catId."', `exp_date`='".$exp_date."'  Where exp_id = '".$exp_id."'";
	 
    update_query($sql);
    $msg = "User Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}
function delete_expenses(){
    
    extract($_POST);

	 $sql = "DELETE FROM `hr_expenses` WHERE exp_id  = '".$id."' ";
	 
    update_query($sql);
    $msg = "Expenses Delete Successfully";

    return array("msg" => $msg,"error"=>$error);
}

function expenses_summary(){

    global $user_id, $salon_id;

    extract($_REQUEST);

    if($fromdate != '' && $todate != ''){
        $fromdate = date("Y-m-d",strtotime($fromdate));
        $todate = date("Y-m-d",strtotime($todate));
        $date_where = " and (DATE(exp_date) BETWEEN '".$fromdate."' AND '".$todate."')";
    }else if($fromdate != '' && $todate == ''){
        $fromdate = date("Y-m-d",strtotime($fromdate));
        $date_where = " and (DATE(exp_date) = '".$fromdate."')";
    }else{
        $date = date('Y-m-d');
        $date_where = " and DATE(exp_date) LIKE '".$date."'";
    }

    if($category_id != '' && $category_id != ''){
        $where .= " and e.exp_catId='".$category_id."'";
    }

   $sql = " FROM `hr_expenses` as e join hr_expenses_category as c on c.exp_catId=e.exp_catId  where  e.`salon_id`='".$salon_id."' and e.`payment_mode`='".$payment_mode."' $where $date_where ORDER BY exp_id desc";

    extract(select_row("SELECT sum(e.exp_total) as grand_total ".$sql));

    $total_exp = (num_rows("SELECT (e.exp_total) as grand_total ".$sql));

    $data['exp_number'] = $total_exp;
    $data['exp_total'] = number_format($grand_total);

    return $data;

}
function get_expenses(){
    
        extract($_REQUEST);
    
        global $user_id, $salon_id;

        $loginuser_id = $user_id;
       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and (exp_name LIKE '%".$search_value."%' OR exp_note LIKE '%".$search_value."%' OR c.category_name LIKE '%".$search_value."%')";
       }

        if($fromdate != '' && $todate == ''){
            $fromdate = date("Y/m/d",strtotime($fromdate));
            $where .= " and (DATE(exp_date) = '".$fromdate."')";
        }

        if($fromdate != '' && $todate != ''){
            $fromdate = date("Y/m/d",strtotime($fromdate));
            $todate = date("Y/m/d",strtotime($todate));
            $where .= " and (DATE(exp_date) BETWEEN '".$fromdate."' AND '".$todate."')";
        }

        if($category_id != '' && $category_id != ''){
            $where .= " and e.exp_catId='".$category_id."'";
        }


        $order_by = "exp_id desc";
        if($order[0]['column'] == 3){
            $dir = $order[0]['dir'];
            $order_by = "exp_total ".$dir;
        } if($order[0]['column'] == 5){
            $dir = $order[0]['dir'];
            $order_by = "modify_date ".$dir;
        }

        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT e.*,c.category_name FROM `hr_expenses` as e join hr_expenses_category as c on c.exp_catId=e.exp_catId  where  e.`salon_id`='".$salon_id."' and e.`payment_mode`='".$payment_mode."' $where ORDER BY ".$order_by;
        $total_records = num_rows($sql); 
    
		$sql .= " LIMIT $start_from, $length";
	
        $user = select_array($sql);
    $userdata = array();
        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("expenses","edit",$loginuser_id)){
                $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="expenses_edit.php?exp_id='.$exp_id.'"> <i class="fa fa-edit"></i> </button>';
			
            } 
			 if(check_user_permission("expenses","delete",$loginuser_id)){
                $del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?exp_id='.$exp_id.'"><i class="fa fa-trash "></i> </button>';
            }
		

            $userdata[$i] = $users;
            $userdata[$i]['exp_note'] = '<div class="set_td" data-toggle="tooltip" title="'.$users['exp_note'].'" >'.$users['exp_note'].'</div>';
            $userdata[$i]['exp_date'] = date("d-m-Y",strtotime($exp_date));
            $userdata[$i]['exp_catId'] = $category_name;
            $userdata[$i]['action'] = $edit_btn.$del_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }
	 
    
    
    function get_sub_service(){

        extract($_REQUEST);


        $sql = "SELECT service_id,service_name FROM `hr_services` where service_catid='".$service_catid."' ORDER BY service_id ASC";
        $service = select_array($sql);
        $html = "<option value=''>Select Service</option>";
        foreach($service as $serv){
            $html .= "<option value='".$serv['service_id']."'>".$serv['service_name']."</option>";
        }
        return $html;
    }

    function get_sub_service_detail(){

        global $user_id, $salon_id;

        extract($_REQUEST);
        $sql = "SELECT * FROM `hr_services` where service_id='".$service_id."'";
        $service = select_row($sql);


        return $service;
    }



function get_monthly_discount(){

    extract($_REQUEST);

    global $user_id, $salon_id,$role_id;

    if($role_id != 3){
        return true;
    }

    if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and p.month_discount LIKE '%".$search_value."%'";
    }

    if (isset($start)) { $page  = $start; } else { $page=1; };
    $start_from = $start;

    $sql = "SELECT p.* FROM `hr_salon_cashdiscount` as p where  p.salon_id='".$salon_id."'  $where ORDER BY month_discount desc";
    $total_records = num_rows($sql);

    $sql .= " LIMIT $start_from, $length";

    $user = select_array($sql);
    $userdata = array();
    $i=0;
    foreach($user as $users){

        foreach($users as $var => $sale)
            $$var = $sale;

        $users['discount_month'] = date("F-Y",strtotime($month_discount));
        $users['total_discount'] = $cash_discount."%";


        if(check_user_permission("product","edit",$user_id)){
            $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="model/monthly_discount.php?cash_discount_id='.$id.'"> <i class="fa fa-edit"></i> </button>';

        }
        if(check_user_permission("product","delete",$user_id)){
            //$del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?cash_discount_id='.$id.'"><i class="fa fa-trash "></i> </button>';
        }

        $userdata[$i] = $users;
        $userdata[$i]['action'] = $edit_btn.$del_btn;
        $i++;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;
}
function update_cash_discount(){

    extract($_POST);

    $sql = "UPDATE `hr_salon_cashdiscount` SET  `cash_discount`='".$cash_discount."'  Where id = '".$cash_discount_id."'";

    update_query($sql);
    $msg = "Discount Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}

function create_cash_discount(){

    global $user_id, $salon_id;
    extract($_POST);

    $discountwhere = " and (MONTH(month_discount) = '".date("m",strtotime($month_discount))."') and (YEAR(month_discount) = '".date("Y",strtotime($month_discount))."')";

    $mysql = "SELECT month_discount FROM `hr_salon_cashdiscount` where salon_id='".$salon_id."' ".$discountwhere;
    $alldiscount  = select_array($mysql);
    if($alldiscount > 0){
        $msg = "Month already exist";
        return array("msg" => $msg,"error"=>1);
    }
    $sql = "INSERT INTO `hr_salon_cashdiscount` SET  `salon_id`='".$salon_id."',`cash_discount`='".$cash_discount."',`month_discount`='".$month_discount."'  ";
    update_query($sql);
    $msg = "Discount Inserted Successfully";

    return array("msg" => $msg,"error"=>$error);
}

    function get_inventory_product(){
        global $user_id, $salon_id;
        $error = 0;
        extract($_POST);


        $sql = "SELECT * FROM `hr_bill_product` where  `product_name` LIKE '%".$pro_name."%' group by product_name ORDER BY id desc  LIMIT 100";
        $user = select_array($sql);
        $data = array();
        foreach($user as  $key=>$users){
            $data[]['label'] = $users['product_name'];
        }

        return $data;
    }


    function inventory_inuse()
    {
        extract($_REQUEST);
        global $user_id, $salon_id;
        $loginuser_id = $user_id;


        $sql = "INSERT INTO `hr_bill_product` SET salon_id='".$salon_id."',`product_type`='store',`product_name`='".$product_name."',`qty_out`='".$qty_out."'";
        update_query($sql);
        $msg = "Inventory Updated Successfully";

        $error = 0;
        return array("msg" => $msg,"error"=>$error);

    }


    function get_inventory_compare()
    {
        extract($_REQUEST);
        global $user_id, $salon_id;
        $loginuser_id = $user_id;

        if ($search['value'] != '') {
            $search_value = $search['value'];
            $where = " and (product_name LIKE '%" . $search_value . "%' )";
        }

        if (isset($start)) { $page  = $start; } else { $page=1; };
        $start_from = $start;

        //$sql = "SELECT product_name,(sum(qty)) as ttl_qty,(sum(qty)-sum(qty_out)) as store_qty,sum(qty_out) as out_qty,product_type FROM `hr_bill_product` where 1=1 ".$where."  GROUP by product_name";
        $sql = "SELECT product_name,product_type,max(bill_id) as new_bill,max(id) as id FROM `hr_bill_product`  where bill_id!=0 and 1=1 ".$where."  GROUP BY product_name ";
        $total_records = num_rows($sql);
        $sql .= " order by id desc LIMIT $start_from, $length";
        $data = select_array($sql);
        $i=0;
        $userdata = array();
        foreach($data as $datas){


             $new_data = select_row("SELECT (qty) as ttl_qty,grand_total as new_price FROM `hr_bill_product`  where id='".$datas['id']."'");

            $old_data = select_row("SELECT (qty) as ttl_qty,grand_total as old_price,bill_id as old_bill,id FROM `hr_bill_product`  where  bill_id!=0 and product_name='".$datas['product_name']."' and id!='".$datas['id']."' order by id desc LIMIT 1");
            if($old_data['id'] > 0) {

                $old_btn = '<a href="#" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="model/inventory_view.php?inventorybill_id='.$old_data['old_bill'].'">'.$old_data['old_bill'].'</a>';
                $new_btn = '<a href="#" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="model/inventory_view.php?inventorybill_id='.$datas['new_bill'].'">'.$datas['new_bill'].'</a>';

                $old_price = number_format($old_data['old_price'] / $old_data['ttl_qty']);
                $new_price = number_format($new_data['new_price'] / $new_data['ttl_qty']);
                if($old_price < $new_price){
                    $new_price = "<span style='color:red'>".$new_price."</span>";
                    $datas['product_name'] = "<span style='color:red'>".$datas['product_name']."</span>";
                }

                $userdata[$i]['product_name'] = $datas['product_name'];
                $userdata[$i]['product_type'] = $datas['product_type'];
                $userdata[$i]['bill_number'] = $old_btn . "-" . $new_btn;
                $userdata[$i]['old_price'] = $old_price;
                $userdata[$i]['new_price'] = $new_price;
                $i++;
            }
        }


        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;

        return $data;

    }


    function get_inventory()
    {
        extract($_REQUEST);
        global $user_id, $salon_id;
        $loginuser_id = $user_id;
        $where = '';

        if ($search['value'] != '') {
            $search_value = $search['value'];
            $where = " and (product_name LIKE '%" . $search_value . "%' )";
        }

        if($fromdate != '' && $todate == ''){
            $fromdate = date("Y/m/d",strtotime($fromdate));
            $where .= " and (DATE(created_date) = '".$fromdate."')";
        }

        if($fromdate != '' && $todate != ''){
            $fromdate = date("Y/m/d",strtotime($fromdate));
            $todate = date("Y/m/d",strtotime($todate));
            $where .= " and (DATE(created_date) BETWEEN '".$fromdate."' AND '".$todate."')";
        }

        if($category_id != ''){
            $where .= " and product_name='".$category_id."'";
        }


        $dir = $order[0]['dir'];
        $order_by = "product_name ".$dir;
        if($order[0]['column'] == 2){
            $order_by = "ttl_qty ".$dir;
        }

        if (isset($start)) { $page  = $start; } else { $page=1; };
        $start_from = $start;

        $sql = "SELECT product_name,(sum(qty)) as ttl_qty,(sum(qty)-sum(qty_out)) as store_qty,sum(qty_out) as out_qty,product_type FROM `hr_bill_product` where  bill_id!=0 and salon_id='".$salon_id."' ".$where."  GROUP by product_name";
        $total_records = num_rows($sql);

        $sql .= " order by ".$order_by." LIMIT $start_from, $length";
        $datad = select_array($sql);
        $i=0;
        $userdata = array();
        foreach($datad as $datas){
            $datas = array_map('utf8_encode', $datas);
            $product_name = $datas['product_name'];
            $btn = '<button type="button" class="btn-sm  btn btn-success modalButtonCommon" data-toggle="modal" data-href="model/inventory_inuse.php?product_name='.base64_encode($product_name).'"><i class="fa fa-plus-circle"></i> Issue Product</button>';
           // $btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="model/inventory_inuse.php?product_name='.$datas['product_name'].'"> <i class="fa fa-edit"></i> Mark Inuse/sold </button>';
            $userdata[$i] = $datas;
            $userdata[$i]['action'] =$btn;
            $i++;
        }


        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;

       //$data =  json_encode($userdata);
       //print_R($data);
        //$data = array_map('utf8_encode', $data);
        return $data;

    }
    function inventory_summary(){

        global $user_id, $salon_id;

        extract($_REQUEST);

        if($fromdate != '' && $todate != ''){
            $fromdate = date("Y-m-d",strtotime($fromdate));
            $todate = date("Y-m-d",strtotime($todate));
            $date_where = " and (DATE(created_date) BETWEEN '".$fromdate."' AND '".$todate."')";
        }else if($fromdate != '' && $todate == ''){
            $fromdate = date("Y-m-d",strtotime($fromdate));
            $date_where = " and (DATE(created_date) = '".$fromdate."')";
        }else{
            $date = date('Y-m-d');
            $date_where = " and DATE(created_date) LIKE '".$date."'";
        }

        if($product_id != ''){
            $where .= " and product_name='".$product_id."'";
        }


        $sql = " FROM `hr_bill_product`  where bill_id!=0 and `salon_id`='".$salon_id."'  $where $date_where";

        extract(select_row("SELECT sum(grand_total) as grand_total ".$sql));

        extract(select_row("SELECT sum(qty) as total_qty ".$sql));
        //echo "SELECT sum(qty) as total_qty ".$sql;
        $data['product_number'] = $total_qty;
        $data['product_total'] = number_format($grand_total);

        return $data;

    }

    function get_inventory_bill(){

        extract($_REQUEST);

        global $user_id, $salon_id;

        $loginuser_id = $user_id;
        if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and (vendor_name LIKE '%".$search_value."%' OR invoice_date LIKE '%".$search_value."%')";
        }

        if (isset($start)) { $page  = $start; } else { $page=1; };
        $start_from = $start;

        $sql = "SELECT b.*,c.vendor_name FROM `hr_bill` as b join hr_vendor as c on c.id=b.vendor  where  b.`salon_id`='".$salon_id."' $where ORDER BY bill_id desc";
        $total_records = num_rows($sql);

        $sql .= " LIMIT $start_from, $length";

        $user = select_array($sql);
        $userdata = array();
        $data = array();

        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("inventory","edit",$loginuser_id)){
                $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="model/inventory_view.php?inventorybill_id='.$bill_id.'"> <i class="fa fa-eye"></i> </button>';
            }
            if(check_user_permission("inventory","delete",$loginuser_id)){
                $del_btn = '<button type="button" class="btn btn-xs btn-outline-danger modalButtonCommon" data-toggle="modal" data-href="common_delete.php?inventorybill_id='.$bill_id.'"><i class="fa fa-trash "></i> </button>';
            }

            $userdata[$i] = $users;
            $userdata[$i]['mrp'] = 0; //$mrp/$qty;
            $userdata[$i]['invoice_date'] = date("d-m-Y",strtotime($invoice_date));
            $userdata[$i]['action'] = $edit_btn.$del_btn;


            $i++;
        }

        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;

        return $data;

    }


function vendor_payment(){

    global $user_id, $salon_id;
    extract($_POST);

    $sql = "INSERT INTO `hr_vendor_payment` SET salon_id='".$salon_id."',`vendor_id`='".$vendor_id."',`payment_mode`='".$payment_mode."',`amt_out`='".$amt_out."',`vendor_remark`='".$vendor_remark."' ";
    if(update_query($sql)){

        $sql = "SELECT vendor_name FROM `hr_vendor` where `id`='".$vendor_id."'";
        extract(select_row($sql));
        $msql = "SELECT (SUM(amt_in)-sum(amt_out)) as pending_payment FROM `hr_vendor_payment` where salon_id='".$salon_id."' and  vendor_id='".$vendor_id."' and  bill_deleted!=1 order by id desc limit 1";
        extract(select_row($msql));

        $message = "*Product Bill Payment*\n\n*".$vendor_name."*\nPaid Amount: *Rs".$amt_out."* by ".ucfirst(str_ireplace("_"," ",$payment_mode))."\nRemaining: *Rs".$pending_payment."*";
        sendsmstoowner($message);

        extract(select_row("SELECT exp_catId  FROM `hr_expenses_category` WHERE `salon_id` = 80 AND `category_name` LIKE 'Product Bill'"));

        $mode = "cash";
        if($payment_mode == "bank_transfer"){
            $mode = "cc";
        }

        if($payment_mode != "credit_note"){
            $exp_name = ucwords($vendor_name)." Bill";
            $sql = "INSERT INTO `hr_expenses` SET `exp_name`='".$exp_name."',`salon_id`='".$salon_id."',`payment_mode`='".$mode."',`exp_vendor`='".$vendor_name."',`exp_total`='".$amt_out."',`exp_note`='added from payment record', `exp_catId`='".$exp_catId."',`user_id`='".$user_id."'";
            update_query($sql);
        }
       

        $msg = "Payment Successfully";
    }else{
        $msg = "Error While payment";
        $error = 1;
    }
    return array("msg" => $msg,"error"=>$error);

}

function get_vendor_credit_debit(){

    extract($_REQUEST);

    global $user_id, $salon_id;

    $loginuser_id = $user_id;
    if($search['value'] != ''){
        $search_value = $search['value'];
        $where = " and (vendor_name LIKE '%".$search_value."%')";
    }

    if (isset($start)) { $page  = $start; } else { $page=1; };
    $start_from = $start;

    $sql = "SELECT v.vendor_name,p.amt_in,p.amt_out,payment_mode,created_date,bill_id,p.vendor_id FROM `hr_vendor_payment` as p join hr_vendor as v on v.id=p.vendor_id  where  p.`salon_id`='".$salon_id."' and p.bill_deleted!=1 $where  order by p.id desc";
    $total_records = num_rows($sql);

    $sql .= " LIMIT $start_from, $length";

    $user = select_array($sql);
    $data = array();
    $userdata = array();
    $i=0;
    foreach($user as $users){
        extract($users);
        $edit_btn = '';
        if(check_user_permission("inventory","edit",$loginuser_id) && $bill_id > 0){
            $edit_btn = '<button type="button" class="btn btn-xs btn-outline-info modalButtonCommon" data-toggle="modal" data-href="model/inventory_view.php?inventorybill_id='.$bill_id.'"> <i class="fa fa-eye"></i> </button>';
        }

        $msql = "SELECT (SUM(amt_in)-sum(amt_out)) as pending_payment FROM `hr_vendor_payment` where salon_id='".$salon_id."' and  vendor_id='".$vendor_id."' and  bill_deleted!=1 and created_date <= '".$created_date."'";
        extract(select_row($msql));
        $userdata[$i] = $users;
        $userdata[$i]['created_date'] = date("d-m-Y",strtotime($created_date));
        $userdata[$i]['balance'] = number_format($pending_payment);
        $userdata[$i]['action'] = $edit_btn;


        $i++;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;

}

function get_vendor_payment(){

    global $user_id, $salon_id;

    extract($_REQUEST);


    extract(select_row("SELECT (SUM(amt_in)-sum(amt_out)) as pending_payment FROM `hr_vendor_payment` where salon_id='".$salon_id."' and  bill_deleted!=1 and  vendor_id='".$vendor_id."'"));

    $data['pending_payment'] = number_format($pending_payment, 0, '', '');;

    return $data;

}

function jobcard_service_delete(){

    extract($_POST);

    $sql = "UPDATE `hr_jobcardservice` SET `delete_status` = 'deleted' WHERE `job_card_service_id` = '".$jobcard_service_id."' and `job_card_id` = '".$jobcard_id."' ";
    update_query($sql);
    $msg = "Service Delete Successfully";

    return array("msg" => $msg,"error"=>$error);
}



?>
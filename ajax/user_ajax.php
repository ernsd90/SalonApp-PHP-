<?php 

include "../function.php";

$method=$_REQUEST["method"];

if(function_exists($method))
echo json_encode($method());
else
echo "Method Not Found";


$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

function get_user(){
    
    extract($_REQUEST);

    $login_user = get_session_data('user_id');

    if (isset($start)) { $page  = $start; } else { $page=1; }; 
    $start_from = $start; 

    $sql = "SELECT * FROM `hr_user` as u join hr_user_role as r on r.role_id=u.role_id where user_id!=1 ORDER BY user_id desc LIMIT $start_from, $length";
    $user = select_array($sql);
    $total_records = num_rows($sql); 

    $i=0;
    foreach($user as $users){
        extract($users);

      
            $edit_btn = '<button type="button" class="btn btn-gradient-info btn-xs modalButtonCommon" data-toggle="modal" data-href="user_edit.php?user_id='.$user_id.'">Edit</button>';
     
            $del_btn = '<button type="button" class="btn btn-gradient-danger btn-xs modalButtonCommon" data-toggle="modal" data-href="user_delete.php?user_id='.$user_id.'">Delete</button>';
       

        $userdata[$i] = $users;
        $userdata[$i]['action'] = $edit_btn.$del_btn;
        $userdata[$i]['user_role'] = $role_name;

        $i++;
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;
}
function get_role(){
    
    extract($_REQUEST);

    if (isset($start)) { $page  = $start; } else { $page=1; }; 
    $start_from = $start; 

    $sql = "SELECT * FROM `hr_user_role` ORDER BY role_id desc LIMIT $start_from, $length";
    $role = select_array($sql);
    $total_records = num_rows($sql); 

    $i=0;
    foreach($role as $roles){
        extract($roles);


        $del_btn = '<button type="button" class="btn btn-gradient-danger btn-xs modalButtonCommon" data-toggle="modal" data-href="user_role_delete.php?role_id='.$role_id.'">Delete</button>';

       $userdata[$i] = $roles;
        $userdata[$i]['action'] = $del_btn;

        $i++;	
    }

    $data['recordsTotal'] = $total_records;
    $data['recordsFiltered'] = $total_records;
    $data['data'] = $userdata;

    return $data;
}


function user_login(){
    
    $error = 0; 
    extract($_POST);

    $sql = "SELECT * FROM `hr_user` WHERE `username`='".$user_email."' and password='".$user_password."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $data1 = select_row($sql);
        $data2 = select_row("SELECT include_gst,salon_name,salon_address,logo,gst_enable,whatsapp_api FROM `hr_salon` where salon_id='".$data1['salon_id']."'");
        $data = array_merge($data1,$data2);

        $role_id = $data['role_id'];
        $_SESSION['userdata'] = json_encode($data);
        setcookie('userdata', json_encode($data), time() + (86400 * 90), "/");
        //$sql = "SELECT * FROM `hr_user_role` WHERE role_id='".$role_id."'";
        //$hr_user_role = select_row($sql);

       // $_SESSION['userpermission'] = json_encode($hr_user_role);

        $msg = "User Login In Successfully. Redirecting.......";
    }else{
        $msg = "Email And Password Does Not Match";
        $error = 1;
    }

    return array("msg" => $msg,"error"=>$error);
}

function create_user(){
    
    $error = 0; 
    extract($_POST);

    $sql = "SELECT * FROM `hr_user` WHERE `username`='".$username."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "User Already Exist";
        $error = 1;
    }else{
        $sql = "INSERT INTO  `hr_user` SET `salon_id`='".$salon_id."',`username`='".$username."',`email`='".$user_email."',`full_name`='".$full_name."',`password`='".$user_password."',`role_id`='".$role_id."',`user_mobile`='".$user_mobile."' ";
        insert_query($sql);
        $msg = "User Successfully Added";
    }

    return array("msg" => $msg,"error"=>$error);
}


function update_user(){
    
    extract($_POST);

    $sql = "SELECT * FROM `hr_user` WHERE `username`='".$username."' and user_id != '".$user_id."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Username Already Exist";
        $error = 1;
    }else{

        if($user_password != ''){
            $pass_update = ",`password`='".$user_password."'";
        }
        $sql = "UPDATE `hr_user` SET `username`='".$username."',`full_name`='".$full_name."',`role_id`='".$role_id."',`salon_id`='".$salon_id."',`user_mobile`='".$user_mobile."' ".$pass_update." where user_id='".$user_id."'";

        update_query($sql);
        $msg = "User Updated Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}


function create_role(){
    
    $error = 0; 
    extract($_POST);

    $sql = "SELECT * FROM `hr_user_role` WHERE `role_name`='".$role_name."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Role Already Exist";
        $error = 1;
    }else{
        $sql = "INSERT INTO `hr_user_role` SET `role_name`='".$role_name."' ";
        insert_query($sql);
        $msg = "Role Successfully Added";
    }

    return array("msg" => $msg,"error"=>$error);
}

function delete_user(){
    
    extract($_POST);

    if($user_id != ''){
        $sql = "DELETE FROM `hr_user` WHERE user_id='".$user_id."'";
        update_query($sql);
        $msg = "User Deleted Successfully";
    }
    else{
        $error = 1;
        $msg = "Something Went Wrong";
    }
    

    return array("msg" => $msg,"error"=>$error);
}

function delete_role(){
    
    extract($_POST);

    if($role_id != ''){
        $sql = "DELETE FROM `hr_user_role` WHERE role_id='".$role_id."'";
        update_query($sql);
        $msg = "Role Deleted Successfully";
    }
    else{
        $error = 1;
        $msg = "Something Went Wrong";
    }
    

    return array("msg" => $msg,"error"=>$error);
}


function group_permission_update(){

    extract($_POST);

    $permission = json_encode($_POST['data']);

    $sql = "UPDATE `hr_user_role` SET `role_permission`='$permission' WHERE `role_id`='".$role_id."'";
    $data = update_query($sql);
    
    if($ttl > 0){
        $msg = "Something Went Wrong";
        $error = 1;
    }else{
      
        $msg = "Permissions Updated";
    }

    return array("msg" => $msg,"error"=>$error);

}


function delete_staff(){
    
    extract($_POST);
	 $sql = "DELETE FROM `hr_staff` WHERE staff_id  = '".$id."' ";

    update_query($sql);
    $msg = "Staff Member Deleted Successfully";

    return array("msg" => $msg,"error"=>$error);
}

function create_staff(){
    

    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);

    $sql = "SELECT * FROM `hr_staff` WHERE `staff_mob`='".$staff_mob."' and `salon_id`='".$salon_id."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Staff Already Exist";
        $error = 1;
    }else{
        $staff_name = ucwords($staff_name);
         $sql = "INSERT INTO `hr_staff` SET `staff_mob`='".$staff_mob."',`staff_name`='".$staff_name."',`joining_date`='".$joining_date."',`salon_id`='".$salon_id."'";
        insert_query($sql);
        $msg = "Staff Added Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}


function update_staff(){
    
    extract($_POST);

    $staff_name = ucwords($staff_name);

	$sql = "UPDATE `hr_staff` SET `staff_mob`='".$staff_mob."',`staff_name`='".$staff_name."',`joining_date`='".$joining_date."',`staff_salary`='".$staff_salary."',`staff_status`='".$staff_status."' Where staff_id = '".$staff_id."'";
    update_query($sql);
    $msg = "User Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}


function get_staff(){
        global $user_id, $salon_id;
        extract($_REQUEST);
    
        $login_user = get_session_data('user_id');

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and  staff_name LIKE '%".$search_value."%'";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT * FROM `hr_staff` where  `salon_id`='".$salon_id."' $where ORDER BY staff_status desc";
    $total_records = num_rows($sql);
        $sql .= " LIMIT $start_from, $length";
    $user = select_array($sql);

    $userdata = array();
        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("staff","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-gradient-info btn-xs  modalButtonCommon" data-toggle="modal" data-href="staff_edit.php?staff_id='.$staff_id.'">Edit</button>';
            }
            if(check_user_permission("staff","delete",$user_id)){
                $del_btn = '<button type="button" class="btn btn-gradient-danger btn-xs modalButtonCommon" data-toggle="modal" data-href="common_delete.php?staff_id='.$staff_id.'">Delete</button>';
            }

            $status_btn = '<label class="badge badge-danger">Inactive</label>';
            if($users['staff_status'] == 1){
				$status_btn = '<label class="badge badge-success">Active</label>';
                
            }
    
            $userdata[$i] = $users;
            $userdata[$i]['staff_status'] = $status_btn;
            $userdata[$i]['action'] = $edit_btn.$del_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }

    function get_appointment(){
        global $user_id, $salon_id;
        extract($_REQUEST);
    
        $login_user = get_session_data('user_id');

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and  staff_name LIKE '%".$search_value."%'";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT a.*,s.staff_name FROM `hr_appointment` as a join `hr_staff` as s on s.staff_id=a.staff_id where  a.`salon_id`='".$salon_id."' $where ORDER BY appointment_date desc";
    $total_records = num_rows($sql);
        $sql .= " LIMIT $start_from, $length";
    $user = select_array($sql);

    $userdata = array();
        $i=0;
        foreach($user as $users){
            extract($users);


            $users['appointment_date'] = date("d-m-Y", strtotime($appointment_date)); 
            $users['appointment_time'] = date("h:i A", strtotime($appointment_time)); 
            if(check_user_permission("staff","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-gradient-info btn-xs  modalButtonCommon" data-toggle="modal" data-href="appointment_edit.php?staff_id='.$staff_id.'">Edit</button>';
                
            }
            if(check_user_permission("staff","delete",$user_id)){
                $del_btn = '<button type="button" class="btn btn-gradient-danger btn-xs modalButtonCommon" data-toggle="modal" data-href="appointment_delete.php?id='.$id.'">Delete</button>';
            }

    
            $userdata[$i] = $users;
            $userdata[$i]['action'] = $del_btn;
           
    
            $i++;
        }
    
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }

    function get_vendor(){
        global $user_id, $salon_id;
        extract($_REQUEST);
    
        $login_user = get_session_data('user_id');

       if($search['value'] != ''){
            $search_value = $search['value'];
            $where = " and  vendor_name LIKE '%".$search_value."%'";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        $sql = "SELECT * FROM `hr_vendor` where  1=1 $where ORDER BY id desc";
        $total_records = num_rows($sql);
        $sql .= " LIMIT $start_from, $length";
    $user = select_array($sql);

    $userdata = array();
        $i=0;
        foreach($user as $users){
            extract($users);

            if(check_user_permission("staff","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-gradient-info btn-xs  modalButtonCommon" data-toggle="modal" data-href="vendor_add.php?id='.$id.'">Edit</button>';
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

    function update_vendor(){
    
    extract($_POST);

    $vendor_name = ucwords($vendor_name);

    $sql = "UPDATE `hr_vendor` SET `vendor_name`='".$vendor_name."' Where id = '".$id."'";
    update_query($sql);
    $msg = "Vendor Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
    }

    function create_vendor(){
    

    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);

    $sql = "SELECT * FROM `hr_vendor` WHERE `vendor_name`='".$vendor_name."' and `id`='".$id."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Vendor Already Exist";
        $error = 1;
    }else{
        $staff_name = ucwords($vendor_name);
         $sql = "INSERT INTO `hr_vendor` SET `vendor_name`='".$vendor_name."',`id`='".$id."'";
        insert_query($sql);
        $msg = "Vendor Added Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}

function create_appointment(){
    

    global $user_id, $salon_id;
    $error = 0; 
    extract($_POST);

    // $sql = "SELECT * FROM `hr_customer` WHERE `Cust_id`='".$Cust_id."'";
    // $ttl = num_rows($sql);
    // if($ttl > 0){
    //     $msg = "Customer Already Exist";
    //     $error = 1;
    // }else{
        $staff_name = ucwords($staff_name);
          $sql = "INSERT INTO `hr_appointment` SET `salon_id`='".$salon_id."',`staff_id`='".$staff_id."',`cust_name`='".$cust_name."',`cust_mobile`='".$cust_mobile."',`appointment_date`='".$appointment_date."',`appointment_time`='".$appointment_time."'";
        insert_query($sql);
        $msg = "Appointment Added Successfully";
    // }

    return array("msg" => $msg,"error"=>$error);
}
function delete_appointment(){
    
    extract($_POST);
     $sql = "DELETE FROM `hr_appointment` WHERE id  = '".$id."' ";

    update_query($sql);
    $msg = "Appointment Deleted Successfully";

    return array("msg" => $msg,"error"=>$error);
}

?>
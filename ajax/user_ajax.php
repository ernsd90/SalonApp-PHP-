<?php 
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE); 
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

$method = $_REQUEST["method"] ?? '';

if($method && function_exists($method)) {
    echo json_encode($method());
} else {
    echo "Method Not Found";
}

function get_user(){
    
    extract($_REQUEST);
    global $salon_id, $user_id;

    $login_user = get_session_data('user_id');

    if (isset($_REQUEST['start'])) { $page = $_REQUEST['start']; } else { $page=0; }; 
    $start_from = $page;
    $length = isset($_REQUEST['length']) ? $_REQUEST['length'] : 10;

    $base_sql = "SELECT * FROM `hr_user` as u join hr_user_role as r on r.role_id=u.role_id where user_id!=1 and u.salon_id='".$salon_id."'";
    $sql = $base_sql . " ORDER BY user_id desc LIMIT $start_from, $length";
    $user = select_array($sql);
    $total_records = num_rows($base_sql); 

    $session_userdata = json_decode($_SESSION['userdata'], true);
    $is_superadmin = (isset($session_userdata['user_type']) && ($session_userdata['user_type'] == 1 || $session_userdata['user_type'] == '1'));

    $i=0;
    foreach($user as $users){
        extract($users);

      
        $edit_btn = '<button type="button" class="btn btn-gradient-info btn-xs modalButtonCommon" data-toggle="modal" data-href="user_edit.php?user_id='.$user_id.'">Edit</button>';
     
        $del_btn = '<button type="button" class="btn btn-gradient-danger btn-xs modalButtonCommon" data-toggle="modal" data-href="user_delete.php?user_id='.$user_id.'">Delete</button>';
   
        $login_btn = '';
        if($is_superadmin) {
            $login_btn = '<button type="button" class="btn-login" onclick="loginAsUser('.$user_id.')">Login</button>';
        }

        $userdata[$i] = $users;
        $userdata[$i]['action'] = '<div style="display: flex; gap: 6px; align-items: center;">' . $edit_btn . $del_btn . $login_btn . '</div>';
        $userdata[$i]['user_role'] = $role_name;

        $i++;
    }

    $data['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
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

    $data['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
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
        if($data1['user_type'] == 1 && $data1['salon_id'] == 0){
            $default_salon = select_row("SELECT salon_id FROM hr_salon LIMIT 1");
            if($default_salon){
                $data1['salon_id'] = $default_salon['salon_id'];
            }
        }
        $data2 = select_row("SELECT include_gst,salon_name,salon_address,logo,gst_enable,whatsapp_api FROM `hr_salon` where salon_id='".$data1['salon_id']."'");
        if(!$data2) { $data2 = array(); }
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

function login_as_user(){
    extract($_POST);
    $error = 0;
    
    // Check if the current user is a superadmin.
    $current_userdata = json_decode($_SESSION['userdata'], true);
    if(!isset($current_userdata['user_type']) || ($current_userdata['user_type'] != 1 && $current_userdata['user_type'] != '1')){
        return array("msg" => "Permission Denied", "error" => 1);
    }
    
    // login logic for the provided user_id
    $sql = "SELECT * FROM `hr_user` WHERE `user_id`='".$login_user_id."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $data1 = select_row($sql);
        if($data1['user_type'] == 1 && $data1['salon_id'] == 0){
            $default_salon = select_row("SELECT salon_id FROM hr_salon LIMIT 1");
            if($default_salon){
                $data1['salon_id'] = $default_salon['salon_id'];
            }
        }
        $data2 = select_row("SELECT include_gst,salon_name,salon_address,logo,gst_enable,whatsapp_api FROM `hr_salon` where salon_id='".$data1['salon_id']."'");
        if(!$data2) { $data2 = array(); }
        $data = array_merge($data1,$data2);

        $_SESSION['userdata'] = json_encode($data);
        setcookie('userdata', json_encode($data), time() + (86400 * 90), "/");

        $msg = "Logged in successfully as " . $data['username'] . ". Redirecting.......";
    } else {
        $msg = "User not found";
        $error = 1;
    }

    return array("msg" => $msg, "error" => $error);
}

function switch_salon(){
    $error = 0;
    extract($_POST);
    $userdata = json_decode($_SESSION['userdata'], true);
    if($userdata['user_type'] == 1 || $userdata['user_type'] == '1'){
        $data2 = select_row("SELECT include_gst,salon_name,salon_address,logo,gst_enable,whatsapp_api FROM `hr_salon` where salon_id='".$new_salon_id."'");
        if($data2){
            $userdata['salon_id'] = $new_salon_id;
            foreach($data2 as $k => $v){
                $userdata[$k] = $v;
            }
            $_SESSION['userdata'] = json_encode($userdata);
            setcookie('userdata', json_encode($userdata), time() + (86400 * 90), "/");
            $msg = "Salon Switched Successfully";
        }else{
            $msg = "Salon not found";
            $error = 1;
        }
    }else{
        $msg = "Permission Denied";
        $error = 1;
    }
    return array("msg" => $msg, "error" => $error);
}

function create_user(){
    $error = 0; 
    extract($_POST);

    global $salon_id;
    $userdata = json_decode($_SESSION['userdata'], true);
    if(!isset($userdata['user_type']) || $userdata['user_type'] != 1){
        $post_salon = $salon_id;
        $post_user_type = 3; // Default staff
    } else {
        $post_salon = $_POST['salon_id'];
        $post_user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 3;
    }

    $sql = "SELECT * FROM `hr_user` WHERE `username`='".$username."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "User Already Exist";
        $error = 1;
    }else{
        $sql = "INSERT INTO  `hr_user` SET `salon_id`='".$post_salon."',`username`='".$username."',`email`='".$user_email."',`full_name`='".$full_name."',`password`='".$user_password."',`role_id`='".$role_id."',`user_mobile`='".$user_mobile."', `user_type`='".$post_user_type."' ";
        insert_query($sql);
        $msg = "User Successfully Added";
    }

    return array("msg" => $msg,"error"=>$error);
}


function update_user(){
    extract($_POST);

    global $salon_id;
    $userdata = json_decode($_SESSION['userdata'], true);
    if(!isset($userdata['user_type']) || $userdata['user_type'] != 1){
        $post_salon = $salon_id;
        $post_user_type_update = "";
    } else {
        $post_salon = $_POST['salon_id'];
        $ut = isset($_POST['user_type']) ? $_POST['user_type'] : 3;
        $post_user_type_update = ", `user_type`='".$ut."'";
    }

    $sql = "SELECT * FROM `hr_user` WHERE `username`='".$username."' and user_id != '".$user_id."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Username Already Exist";
        $error = 1;
    }else{

        $pass_update = "";
        if($user_password != ''){
            $pass_update = ",`password`='".$user_password."'";
        }
        $sql = "UPDATE `hr_user` SET `username`='".$username."',`full_name`='".$full_name."',`role_id`='".$role_id."',`salon_id`='".$post_salon."',`user_mobile`='".$user_mobile."' ".$post_user_type_update." ".$pass_update." where user_id='".$user_id."'";

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
    global $user_id, $salon_id, $conn;
    $error = 0; 
    extract($_POST);

    $sql = "SELECT * FROM `hr_staff` WHERE `staff_mob`='".$staff_mob."' and `salon_id`='".$salon_id."'";
    $ttl = num_rows($sql);
    if($ttl > 0){
        $msg = "Staff Already Exist";
        $error = 1;
    }else{
        $staff_name = ucwords($staff_name);
        $staff_role = mysqli_real_escape_string($conn, $staff_role ?? '');
        $department = mysqli_real_escape_string($conn, $department ?? '');
        $gender = mysqli_real_escape_string($conn, $gender ?? '');
        $seniority = mysqli_real_escape_string($conn, $seniority ?? 'Junior');
        
        $sql = "INSERT INTO `hr_staff` SET `staff_mob`='".$staff_mob."',`staff_name`='".$staff_name."',`joining_date`='".$joining_date."',`staff_salary`='".$staff_salary."',`staff_status`='".$staff_status."',`salon_id`='".$salon_id."',`staff_role`='".$staff_role."',`department`='".$department."',`gender`='".$gender."',`seniority`='".$seniority."'";
        insert_query($sql);
        $msg = "Stylist Added Successfully";
    }

    return array("msg" => $msg,"error"=>$error);
}


function update_staff(){
    global $conn;
    extract($_POST);

    $staff_name = ucwords($staff_name);
    $staff_role = mysqli_real_escape_string($conn, $staff_role ?? '');
    $department = mysqli_real_escape_string($conn, $department ?? '');
    $gender = mysqli_real_escape_string($conn, $gender ?? '');
    $seniority = mysqli_real_escape_string($conn, $seniority ?? 'Junior');

	$sql = "UPDATE `hr_staff` SET `staff_mob`='".$staff_mob."',`staff_name`='".$staff_name."',`joining_date`='".$joining_date."',`staff_salary`='".$staff_salary."',`staff_status`='".$staff_status."',`staff_role`='".$staff_role."',`department`='".$department."',`gender`='".$gender."',`seniority`='".$seniority."' Where staff_id = '".$staff_id."'";
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

            $edit_btn = '';
            if(check_user_permission("staff","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-gradient-info btn-xs  modalButtonCommon" data-toggle="modal" data-href="staff_edit.php?staff_id='.$staff_id.'">Edit</button>';
            }
    
            $userdata[$i] = $users;
            $userdata[$i]['staff_status'] = $users['staff_status'];
            $userdata[$i]['action'] = $edit_btn;
           
    
            $i++;
        }
    
        $data['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
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
    
        $data['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
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
    
        $data['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
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
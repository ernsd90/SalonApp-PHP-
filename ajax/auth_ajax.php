<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../config.php";
include "../function.php";

$method=$_REQUEST["method"] ?? '';

if(function_exists($method)){
    echo json_encode($method());
} else {
    echo json_encode(["msg" => "Method Not Found", "error" => 1]);
}

function switch_salon(){
    $error = 0;
    extract($_POST);
    
    $userdata = json_decode($_SESSION['userdata'], true);
    if($userdata['user_type'] == 1){
        $data2 = select_row("SELECT include_gst,salon_name,salon_address,logo,gst_enable,whatsapp_api FROM `hr_salon` where salon_id='".mysqli_real_escape_string($GLOBALS['conn'], $new_salon_id)."'");
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
?>

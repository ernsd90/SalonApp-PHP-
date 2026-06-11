<?php 
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

function update_staff(){
    
    extract($_POST);

	$sql = "UPDATE `hr_staff` SET `staff_mob`='".$staff_mob."',`staff_name`='".$staff_name."',`joining_date`='".$joining_date."',`staff_salary`='".$staff_salary."',`staff_status`='".$staff_status."' Where staff_id = '".$staff_id."'";
    update_query($sql);
    $msg = "User Updated Successfully";

    return array("msg" => $msg,"error"=>$error);
}


function customer_create() {
    global $salon_id, $user_id, $conn;
    extract($_POST);

    $cust_wallet = empty($cust_wallet) ? 0 : (float)$cust_wallet;
    $cust_outstanding = empty($cust_outstanding) ? 0 : (float)$cust_outstanding;
    $cust_gender = mysqli_real_escape_string($conn, $cust_gender ?? '');
    $cust_dob = !empty($cust_dob) ? "'".$cust_dob."'" : 'NULL';
    $cust_anniversary = !empty($cust_anniversary) ? "'".$cust_anniversary."'" : 'NULL';

    // Check if mobile already exists in this salon
    $check = select_row("SELECT cust_id FROM `hr_customer` WHERE cust_mobile='".mysqli_real_escape_string($conn, $cust_mobile)."' AND salon_id='".$salon_id."'");
    if($check) {
        return array("msg" => "A customer with this mobile number already exists in your outlet.", "error" => 1);
    }

    $sql = "INSERT INTO `hr_customer` (`salon_id`, `user_id`, `cust_name`, `cust_mobile`, `cust_gender`, `cust_dob`, `cust_anniversary`, `cust_wallet`, `cust_outstanding`, `cust_added`) 
            VALUES ('".$salon_id."', '".$user_id."', '".mysqli_real_escape_string($conn, $cust_name)."', '".mysqli_real_escape_string($conn, $cust_mobile)."', '$cust_gender', $cust_dob, $cust_anniversary, '".$cust_wallet."', '".$cust_outstanding."', '".date('Y-m-d H:i:s')."')";
    
    $new_id = insert_query($sql);

    if($new_id) {
        if($cust_wallet > 0) {
            insert_query("INSERT INTO `hr_customer_wallet` SET cust_id = '".$new_id."', debit = 0, credit = '".$cust_wallet."', balance='".$cust_wallet."', remark = 'Initial Credit Balance'");
        }
        return array("msg" => "Customer Created Successfully!", "error" => 0, "cust_id" => $new_id);
    } else {
        return array("msg" => "Failed to create customer.", "error" => 1);
    }
}

function customer_update(){
     global $salon_id, $user_id, $conn;
    extract($_POST);

    $password = $salon_id;//strtolower($salon_id.str_replace(" ","",$cust_name));
    if($cust_password != $password){
        return array("msg" => "Enter a Correct password","error"=>1, "salon_id" => $salon_id);
    }

    $wallet_row = select_row("SELECT balance FROM `hr_customer_wallet` WHERE `cust_id` = '".$cust_id."' order by wallet_id desc");
    $balance = $wallet_row ? (float)$wallet_row['balance'] : 0;

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

    global $conn;
    $cust_gender_esc = mysqli_real_escape_string($conn, $cust_gender ?? '');
    $cust_dob_val = !empty($cust_dob) ? "'".$cust_dob."'" : 'NULL';
    $cust_anniversary_val = !empty($cust_anniversary) ? "'".$cust_anniversary."'" : 'NULL';

    $sql = "UPDATE `hr_customer` SET 
        `cust_name`='".mysqli_real_escape_string($conn,$cust_name)."',
        `cust_mobile`='".mysqli_real_escape_string($conn,$cust_mobile)."',
        `cust_gender`='$cust_gender_esc',
        `cust_dob`=$cust_dob_val,
        `cust_anniversary`=$cust_anniversary_val,
        `cust_wallet`='$cust_wallet',
        `cust_outstanding`='$cust_outstanding'
        WHERE cust_id='$cust_id'";

    update_query($sql);
    $msg = "Customer Updated Successfully";

    // ── Profile Completion Bonus ─────────────────────────────────────────────
    // Award loyalty points once when DOB + Anniversary + Gender are all filled
    $profile_bonus_msg = '';
    if (!empty($cust_dob) && !empty($cust_anniversary) && !empty($cust_gender)) {
        $cust_rec = select_row("SELECT loyalty_profile_bonus_given FROM hr_customer WHERE cust_id='$cust_id'");
        if ($cust_rec && !(int)($cust_rec['loyalty_profile_bonus_given'] ?? 0)) {
            // Check if loyalty is enabled and get bonus points
            $ls = select_row("SELECT loyalty_enabled, profile_complete_points FROM hr_loyalty_settings WHERE salon_id='$salon_id'");
            if ($ls && (int)$ls['loyalty_enabled'] === 1 && (float)$ls['profile_complete_points'] > 0) {
                require_once dirname(__DIR__).'/loyalty_functions.php';
                $bonus_pts = (float)$ls['profile_complete_points'];
                // Insert as earn into ledger
                $expiry = date('Y-m-d', strtotime('+365 days'));
                insert_query("INSERT INTO hr_customer_points SET
                    cust_id='$cust_id', salon_id='$salon_id', points='$bonus_pts',
                    type='earn', remark='Profile completion bonus',
                    expiry_date='$expiry', created_at=NOW()");
                // Mark bonus as given
                update_query("UPDATE hr_customer SET loyalty_profile_bonus_given=1 WHERE cust_id='$cust_id'");
                $profile_bonus_msg = ' Bonus: '.intval($bonus_pts).' loyalty points awarded for completing your profile! 🎁';
            }
        }
    }
    // ─────────────────────────────────────────────────────────────────────────

    return array("msg" => $msg . $profile_bonus_msg, "error" => 0);

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
    
        $data['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
        $data['recordsTotal'] = $total_records;
        $data['recordsFiltered'] = $total_records;
        $data['data'] = $userdata;
    
        return $data;
    }

function get_customer(){

     global $user_id, $salon_id, $conn;
    
        extract($_REQUEST);

        $fields = array('c.cust_name', 'c.cust_wallet', 'c.cust_outstanding', 'last_visit', 'lifetime_spend', 'loyalty_points');

        $order_field = $fields[$order[0]['column']] ?? 'c.cust_name';

        $order_dir = $order[0]['dir'] ?? 'DESC';

        $where = "";
        if(isset($search['value']) && $search['value'] != ''){
            $search_value = mysqli_real_escape_string($conn, $search['value']);
            $where = " and (c.cust_name LIKE '%".$search_value."%' or c.cust_mobile LIKE '%".$search_value."%')";
        }
        if(isset($search['value']) && $search['value'] == 'only_member'){
            $where = " and c.cust_wallet > '0'";
        }
        
        if (isset($start)) { $page  = $start; } else { $page=1; }; 
        $start_from = $start; 

        // Get total count efficiently
        $count_sql = "SELECT COUNT(c.cust_id) as total FROM `hr_customer` c WHERE c.salon_id='".$salon_id."' $where";
        $count_res = select_row($count_sql);
        $total_records = $count_res['total'] ?? 0;

        $sql = "SELECT c.cust_id, c.cust_name, c.cust_mobile, c.cust_wallet, c.cust_outstanding,
                       (SELECT MAX(invoice_date) FROM hr_invoice WHERE cust_id = c.cust_id AND delete_bill = 0) as last_visit,
                       (SELECT SUM(grand_total) FROM hr_invoice WHERE cust_id = c.cust_id AND delete_bill = 0) as lifetime_spend,
                       (SELECT COALESCE(SUM(CASE WHEN type='earn' THEN points ELSE 0 END) - SUM(CASE WHEN type IN ('redeem','expire') THEN points ELSE 0 END), 0) FROM hr_customer_points WHERE cust_id = c.cust_id) as loyalty_points
                FROM `hr_customer` c 
                WHERE c.salon_id='".$salon_id."' $where 
                ORDER BY $order_field $order_dir 
                LIMIT $start_from, $length";
	
        $user = select_array($sql);
        $userdata = array();
        $data = array();
        $i=0;
        foreach($user as $users){
            extract($users);

            $edit_btn = '';
            $view_btn = '';
            $wa_btn   = '';
            $mem_btn  = '';
            $followup_btn = '';

            if(check_user_permission("customer","edit",$user_id)){
                $edit_btn = '<button type="button" class="btn btn-gradient-info btn modalButtonCommon" data-href="customer_edit.php?cust_id='.$cust_id.'" title="Edit Customer"><i class="ph ph-pencil-simple"></i></button>';
                $view_btn = '<button type="button" class="btn btn-gradient-success btn modalButtonCommon" data-href="model/customer_details_view.php?cust_id='.$cust_id.'" title="View Details"><i class="ph ph-eye"></i></button>';
            }
            
            // Follow-up button — always show first
            $followup_btn = '<button type="button" class="btn-followup modalButtonCommon" data-href="customer_followup.php?cust_id='.$cust_id.'" title="Log Follow-up"><i class="ph ph-phone-call"></i></button>';

            // Membership button — always show
            $mem_btn = '<button class="btn-view modalButtonCommon" data-href="customer_membership_view.php?cust_id=' . $cust_id . '" title="Membership & Wallet" style="background:#e0e7ff;color:#4f46e5;margin-right:4px;"><i class="ph ph-identification-badge"></i></button>';

            // WhatsApp button — always show
            $wa_msg = urlencode('Hello ' . $cust_name . ', we would love to see you at our salon again! 😊');
            $wa_btn = '<a href="https://wa.me/91'.$cust_mobile.'?text='.$wa_msg.'" target="_blank" class="btn-wa" title="WhatsApp"><i class="ph-fill ph-whatsapp-logo"></i></a>';

            $userdata[$i] = $users;
            
            // Format Last Visit Days
            if ($last_visit) {
                $diff = time() - strtotime($last_visit);
                $days = floor($diff / (60 * 60 * 24));
                $days_text = ($days == 0) ? "Today" : (($days == 1) ? "1 day ago" : $days . " days ago");
                $userdata[$i]['last_visit'] = '<div style="font-weight:600;">' . $days_text . '</div><div style="font-size:11px;color:var(--text-muted);">' . date('d M Y', strtotime($last_visit)) . '</div>';
            } else {
                $userdata[$i]['last_visit'] = '<span style="color:var(--text-muted);font-style:italic;">Never</span>';
            }

            // Format Lifetime Spend
            $userdata[$i]['lifetime_spend'] = '<div style="font-weight:700;color:var(--text-main);">₹' . number_format((float)$lifetime_spend, 2) . '</div>';

            // Format Loyalty Points Balance
            $userdata[$i]['loyalty_points'] = number_format($loyalty_points, 0) . ' pts';

            // Follow-up is first button in actions list, followed by Membership, WhatsApp, Edit, and View
            $userdata[$i]['action'] = $followup_btn . ' ' . $mem_btn . ' ' . $wa_btn . ' ' . $edit_btn . ' ' . $view_btn;
           
            $i++;
        }
    
        $data['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
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
        $get_fields = "cust_id,cust_name,cust_mobile,cust_gender,cust_wallet,cust_outstanding,cust_reffer,active_membership_id, (SELECT gst_on_service FROM hr_membership_plans WHERE plan_id=COALESCE(hr_customer.active_membership_id, (SELECT plan_id FROM hr_customer_membership WHERE cust_id=hr_customer.cust_id AND status='active' ORDER BY cm_id DESC LIMIT 1))) as gst_on_service";
    } else if($detail == 2){
        $get_fields = "cust_id,cust_name,cust_mobile,cust_gender,cust_wallet,cust_outstanding,cust_reffer,active_membership_id, (SELECT gst_on_service FROM hr_membership_plans WHERE plan_id=COALESCE(hr_customer.active_membership_id, (SELECT plan_id FROM hr_customer_membership WHERE cust_id=hr_customer.cust_id AND status='active' ORDER BY cm_id DESC LIMIT 1))) as gst_on_service";
    }

    $sql = "SELECT $get_fields FROM `hr_customer` where  `salon_id`='".$salon_id."' $where ORDER BY cust_wallet desc";
    $sql .= " LIMIT 10";
    $user = select_array($sql);


    if($detail == 1){
        $user[0]['billing_remark'] = $billing_remark;
        return $user;
    }
    if($detail == 2){
        return $user;
    }
    $data = array();
    foreach($user as  $key=>$users){
        $data[] = $users['cust_mobile'];
    }
    return $data;
}

function get_customer_history(){
    extract($_REQUEST);
    global $salon_id;

    if(!isset($cust_id) || empty($cust_id)) return [];

    $sql = "SELECT i.invoice_date, s.service, s.staff_name, s.service_total_wth_gst 
            FROM hr_invoice_service s 
            JOIN hr_invoice i ON s.invoice_id = i.invoice_id 
            WHERE i.cust_id = '".mysqli_real_escape_string($GLOBALS['conn'], $cust_id)."' 
            AND i.delete_bill = 0 
            ORDER BY i.invoice_date DESC LIMIT 10";

    $history = select_array($sql);
    $data = [];
    foreach($history as $row) {
        $row['invoice_date'] = date("d-m-Y H:i", strtotime($row['invoice_date']));
        $data[] = $row;
    }
    return $data;
}

function save_followup() {
    global $conn, $salon_id, $user_id;
    extract($_POST);

    $cust_id = (int)$cust_id;
    $type = mysqli_real_escape_string($conn, $type ?? 'Call');
    $notes = mysqli_real_escape_string($conn, $notes ?? '');
    $response = mysqli_real_escape_string($conn, $response ?? '');
    $status = mysqli_real_escape_string($conn, $status ?? 'Pending');
    $followup_date = !empty($followup_date) ? "'" . mysqli_real_escape_string($conn, $followup_date) . "'" : 'NULL';

    if (!$cust_id) {
        return array("msg" => "Invalid Customer ID", "error" => 1);
    }

    $sql = "INSERT INTO `hr_customer_followups` (`salon_id`, `cust_id`, `user_id`, `type`, `notes`, `response`, `followup_date`, `status`)
            VALUES ('$salon_id', '$cust_id', '$user_id', '$type', '$notes', '$response', $followup_date, '$status')";

    $res = insert_query($sql);
    if ($res) {
        return array("msg" => "Follow-up logged successfully!", "error" => 0);
    } else {
        return array("msg" => "Failed to log follow-up.", "error" => 1);
    }
}

function get_followup_history() {
    global $conn, $salon_id;
    $cust_id = (int)$_REQUEST['cust_id'];
    
    $sql = "SELECT f.*, u.user_name 
            FROM hr_customer_followups f
            LEFT JOIN hr_user u ON f.user_id = u.user_id
            WHERE f.cust_id = '$cust_id' AND f.salon_id = '$salon_id'
            ORDER BY f.created_at DESC";
            
    $history = select_array($sql);
    return array("error" => 0, "data" => $history);
}

?>
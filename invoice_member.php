<?php 
include_once "function.php";
$salon_id = get_session_data('salon_id');
$date_now = date("Y-m-d H:i:s");

$salon = select_row("SELECT salon_name,salon_address,salon_contact,salon_gst,logo,msg_id as senderid,whatsapp_enable,whatsapp_api FROM `hr_salon`  WHERE `salon_id` = $salon_id");
extract($salon);

if($_POST['cust_mob']){
	extract($_POST);
    
    $cust_name = ucwords($cust_name);

    $cust = select_row("SELECT cust_id FROM `hr_customer` where cust_mobile='".$cust_mob."' and `salon_id` = '".$salon_id."' order by cust_wallet desc");
    if($cust['cust_id'] > 0){
        $cust_id = $cust['cust_id'];
    }

    
	if($cust_id > 0){
        $sql = "UPDATE `hr_customer` SET `cust_gender`='".$cust_gender."' where cust_id='".$cust_id."'";
        update_query($sql);
       
	}else{
		$sql = "INSERT INTO `hr_customer` SET `salon_id`='".$salon_id."',`cust_reffer`='".$cust_reffer."',`user_id`='".$user_id."',`cust_name`='".$cust_name."',`cust_mobile`='".$cust_mob."',`cust_gender`='".$cust_gender."'";
        $cust_id = insert_query($sql);
    }
    extract(select_row("SELECT MAX(invoice_number)+1 as invoice_number FROM `hr_invoice` WHERE `salon_id` ='".$salon_id."'"));

    $sql = "INSERT INTO `hr_invoice` SET `salon_id`='".$salon_id."',`invoice_number`='".$invoice_number."',`invoice_type`='1',`billing_remark`='".$billing_remark."',`user_id`='".$user_id."',`cust_id`='".$cust_id."',`cust_name`='".$cust_name."',`cust_mob`='".$cust_mob."',`outstanding`='".$amount_due."',`service_total`='".$subTotal."',`service_total_tax`='".$taxTotal."',`grand_total`='".$customer_paying."',`payment_mode`='".$payment_mode."',`invoice_date`='".$date_now."'";
  
	$invoice_id = insert_query($sql);
    $totol_gst = '';
    $service_total_with_tax = '';
    $service_total ='';
	if($invoice_id > 0){

        $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."',grand_total = '".$customer_paying."', `payment_mode` = '".$payment_mode."',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
        update_query($sql);

        if(is_numeric($package)){
            $staff_name = '';
            $total_staff = 0;
            foreach($service_staff as $staff_id){
                $hr_staff = select_row("SELECT `staff_name` FROM `hr_staff` WHERE `staff_id` = $staff_id");
                $staff_name .= $hr_staff['staff_name'].",";
                $total_staff++;
            }
            $staff_name = trim($staff_name,",");
            $staff_ids = implode(",",$service_staff);
            $sql = "INSERT  INTO `hr_invoice_service` SET `invoice_id`='".$invoice_id."',`pkg_id`='".$package."',`service_cat`='Package',`service`='".$package_name."',`staff_id`='".$staff_ids."',`staff_name`='".$staff_name."',`service_price`='".$subTotal."',`service_qty`='1',`service_gst`='".$taxTotal."',`service_total_wth_gst`='".$customer_paying."'";
            $service_idsss = insert_query($sql);

            foreach($service_staff as $staff_id){
                $staff_total = $customer_paying/$total_staff;
                insert_query("INSERT INTO `hr_invoice_staff`(`invoice_id`, `invoice_service`, `staff_id`,`total_amt`) VALUES ('".$invoice_id."','".$service_idsss."','".$staff_id."','".$staff_total."')");
            }
            

        }

        $check_old_balance = select_row("SELECT `cust_wallet` FROM `hr_customer` WHERE `cust_id` = '".$cust_id."'");
        $old_balance = $check_old_balance['cust_wallet'];
        $balance = $customer_get+$old_balance;

        $sql = "INSERT  INTO `hr_customer_wallet` SET `invoice_id`='".$invoice_id."',`cust_id`='".$cust_id."',`credit`='".$customer_get."',`balance`='".$balance."'";
        insert_query($sql);

        if($cust_id > 0){
            if($amount_due == '')
            $amount_due = 0;

            $sql = "UPDATE `hr_customer` SET  `cust_wallet`='".$balance."',`cust_outstanding`=(`cust_outstanding`+".$amount_due.") where `cust_id`='".$cust_id."' ";
            update_query($sql);


            $pkg_expired = Date('Y-m-d', strtotime('+'.$pakage_validity.' days'));
            $sql = "INSERT INTO `hr_customer_pkg` SET `cust_id`='".$cust_id."',`pkg_id`='".$package."',`pkg_expired`='".$pkg_expired."'";
            insert_query($sql);
        }



        $feedurl = str_ireplace('=','','http://salonapp.com/f'.base64_encode($invoice_id).'/');
        if(!empty($grand_total)){ $total1 = 'You Paid '.$grand_total.' by '.$payment_mode.'.';} else { $total1 ='';};

       // $message = 'Hi '.strtoupper($cust_name).', Thanks for visit at Hair Raiserz Sector 20 Panchkula, '.$total1.', Your Wallet Amt is '.$balance.' For Appointment 7888815146 ,Feedback '.$feedurl;
        
        $message = 'Hi '.strtoupper($cust_name).', Thanks for visit at '.$salon_name.', '.$total1.', Your Wallet Amount is '.$balance.' For Appointment '.$salon_contact;
        $message = 'Hi '.ucfirst(strtolower($cust_name)).', Thanks for purchasing the package from '.$salon_name.'., '.$total1.', Your Wallet Amount is '.$balance.' To book your appointments, just give us a call at '.$salon_contact.'. We appreciate your business!';


        if($whatsapp_enable == 1){
            SendWhatsAppSms($cust_mob,$message,$whatsapp_api);
        }

        //$data  = sendapisms($cust_mob,$message,$senderid);
     
	}


}


?>
<!doctype html>
<html>
<head>

<meta charset="utf-8">
<title>Hair Raiserz</title>
    
    <style>
    .invoice-box{
    max-width: 100mm;
    margin: auto;
    padding: 3px;
    font-size: 12px;
    font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    color: #010101;
    font-weight: normal;
    }
    
    .invoice-box table{
        width:100%;
        line-height:inherit;
        text-align:left;
    }
    
    .invoice-box table td{
        padding:3px;
        vertical-align:top;
    }
    
    .invoice-box table tr td:nth-child(2){
        text-align:right;
    }
    
    .invoice-box table tr.top table td{
        padding-bottom:20px;
    }
    
   
    
    .invoice-box table tr.information table td{
        padding-bottom:40px;
    }
    
    .invoice-box table tr.heading td{
        font-weight:bold;
    }
    
    .invoice-box table tr.details td{
        padding-bottom:20px;
    }
    
    .invoice-box table tr.item td{
        border-bottom:1px solid #eee;
    }
    
    .invoice-box table tr.item.last td{
        border-bottom:none;
    }
    
    .invoice-box table tr.total td:nth-child(2){
        border-top:2px solid #eee;
        font-weight:bold;
    }
    
    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td{
            width:100%;
            display:block;
            text-align:center;
        }
        
        .invoice-box table tr.information table td{
            width:100%;
            display:block;
            text-align:center;
        }
    }
    </style>
</head>
<body  onload="window.print()">
<div class="invoice-box">
      <center><img src="images/<?php echo $logo; ?>" style="width:70%; max-width:300px;"></center>
        
        <p> <right>
            <strong>
            Invoice #: <?=$invoice_number; ?><br>
            Created: <?php print date("j F Y");?></strong>
            </right>
        </p>
           <table width="100%">
                <tr>
                    <td width="50%">
                    <p>
                            <span style="font-size: 12px">
                            <?php echo $salon_address; ?><br>
                            <?php echo $salon_contact; ?>  <br><br>
                            GST No. <?php echo $salon_gst; ?>  <br><br>
                            </span>
                        </p>
                    </td>
                    
                    <td width="50%"><p>
                        <span style="text-align: right"><strong><?php print strtoupper($cust_name);?></strong><br><br>
                        <?php print $cust_mob;?></span><br></p>
                    </td>
                </tr>
            </table>
            <table width="100%">   
            <tr class="heading">
                <td colspan="3">
                    Package
                </td>
                <td>
                    Tax
                </td>
                <td width="24%" style="text-align:right">
                    Amount
                </td>
            </tr>

            <tr class="item">
                <td colspan="3"><?php echo ucwords(strtolower($package_name)); ?></td>
                <td>
                    18
                </td>
                <td style="text-align:right">
                    <?php echo ucwords(strtolower($subTotal)); ?>
                </td>
            </tr>


            <tr>
                <td colspan="3">
                <td width="36%">
                   Sub Total:
                </td>
                 <td style="text-align:right">
                   <?php print $subTotal;?>
                </td>
            </tr>
            <?php if($taxTotal > 0){ ?>
            <tr>
                <td colspan="3">
                <td>
                   CGST 9%:
                </td>
                 <td style="text-align:right">
                   <?php echo $taxTotal/2; ?>
                </td>
            </tr>
            <tr >
                <td colspan="3">
                <td>
                   SGST 9%:
                </td>
                 <td style="text-align:right">
                   <?php echo $taxTotal/2; ?>
                </td>
            </tr>
            <?php } ?>
            <tr >
                <td colspan="3">
                <td>
                  <strong> Grand Total:</strong>
                </td>
                 <td style="text-align:right">
                 <strong><?php print $customer_pay;?></strong>
                </td>
            </tr>

            <?php if($amount_due > 0) { ?>
            <tr>
                <td colspan="3">
                <td>
                   Amount Due
                </td>
                 <td style="text-align:right">
                   <?php echo $amount_due; ?>
                </td>
            </tr>
           
        
            <tr>
                <td colspan="3">
                <td>
                  <strong> You Paid:</strong>
                </td>
                 <td style="text-align:right">
                 <strong><?php print $customer_paying;?></strong>
                </td>
            </tr>
            <?php } ?>
        </table>
        <p>Payment Method : <strong><?php echo $payment_mode; ?></strong></p>
        <p>&nbsp;</p>
        <p style="text-align: center; font-size: 12px;">*** Thanks for using Hair Raiserz Services ***</p></div>
        <meta http-equiv="refresh" content="0; url=/billing_membership.php" >

</body>
</html>

<?php 
include_once "function.php";
$salon_id = get_session_data('salon_id');
$date_now = date("Y-m-d H:i:s");

$salon = select_row("SELECT salon_name,salon_address,salon_contact,salon_gst,logo,whatsapp_enable,whatsapp_api FROM `hr_salon`  WHERE `salon_id` = $salon_id");
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
		$sql = "INSERT INTO `hr_customer` SET `salon_id`='".$salon_id."',`user_id`='".$user_id."',`cust_reffer`='".$cust_reffer."',`cust_name`='".$cust_name."',`cust_mobile`='".$cust_mob."',`cust_gender`='".$cust_gender."'";
        $cust_id = insert_query($sql);
	}

    extract(select_row("SELECT MAX(invoice_number)+1 as invoice_number FROM `hr_invoice` WHERE `salon_id` ='".$salon_id."'"));

    $sql = "INSERT INTO `hr_invoice` SET `salon_id`='".$salon_id."',`invoice_number`='".$invoice_number."',`billing_remark`='".$billing_remark."',`user_id`='".$user_id."',`cust_id`='".$cust_id."',`cust_name`='".$cust_name."',`cust_mob`='".$cust_mob."',`invoice_type`='2',`grand_total`='".$grandtotal."',`service_total_tax`='".$grandtotal."',`service_total`='".$grandtotal."',`payment_mode`='".$payment_mode."',`invoice_date`='".$date_now."'";
	$invoice_id = insert_query($sql);

    


    $totol_gst = '';
	if($invoice_id > 0){

        if($payment_mode == "split"){
            $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."',grand_total = '".$part_cash."', `payment_mode` = 'cash',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
            update_query($sql);
    
            $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."',grand_total = '".$part_cc."', `payment_mode` = 'cc',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
            update_query($sql);
    
        }else{
            $sql = "INSERT INTO `hr_invoice_payment` SET salon_id = '".$salon_id."',grand_total = '".$grandtotal."', `payment_mode` = '".$payment_mode."',`invoice_id` = '".$invoice_id."',created_date = '".$date_now."'";
            update_query($sql);
        }


		foreach($product_id as $key => $product_ids){

			if(is_numeric($product_ids) && $product_ids > 0){
				
				$product = select_row("SELECT product_name,brand_name FROM `hr_product` as p join hr_product_brand as b on b.brand_id=p.brand_id WHERE `product_id` = $product_ids");
                $product_name = $product['product_name'];
				$brand_name = $product['brand_name'];

                $staff_name = '';
                $total_staff = 0;
				foreach($product_staff[$key] as $staff_id){
                    $hr_staff = select_row("SELECT `staff_name` FROM `hr_staff` WHERE `staff_id` = $staff_id");
                    $staff_name .= $hr_staff['staff_name'].",";
                    $total_staff++;
                }

                $staff_name = trim($staff_name,",");
                $staff_ids = implode(",",$product_staff[$key]);
				$qty = $service_qty[$key];
				$mrp = $service_price[$key];

				$itemTotal   = ($qty*$mrp);

				$sql = "INSERT  INTO `hr_invoice_service` SET `invoice_id`='".$invoice_id."',`pkg_id`='".$product_ids."',`service_cat`='".$brand_name."',`service`='".$product_name."',`staff_id`='".$staff_ids."',`staff_name`='".$staff_name."',`service_price`='".$mrp."',`service_qty`='".$qty."',`service_gst`='0',`service_total_wth_gst`='".$itemTotal."'";
                $service_idsss = insert_query($sql);

                foreach($product_staff[$key] as $staff_id){
                    $staff_total = $itemTotal/$total_staff;
                    insert_query("INSERT INTO `hr_invoice_staff`(`invoice_id`, `invoice_service`, `staff_id`,`total_amt`) VALUES ('".$invoice_id."','".$service_idsss."','".$staff_id."','".$staff_total."')");
                }
               
                if($service_idsss > 0){
                    $sql = "UPDATE `hr_product` SET `product_qty`=(product_qty-$qty) where `product_id`='".$product_ids."' ";
                    update_query($sql);
                }
                $all_service[$key]['service'] = $product_name;
                $all_service[$key]['qty'] = $qty;
                $all_service[$key]['price'] = $mrp;
				
			}
			
		}

        if(!empty($grandtotal)){ $payment_msg = 'You Paid '.$grandtotal.' by '.$payment_mode.'.';} else { $payment_msg ='';};
        
        $feedurl = str_ireplace('=','','http://salonapp.com/f/'.base64_encode($invoice_id).'/');
        
        //$message = 'Hi '.strtoupper($cust_name).', Thanks for visit at Hair Raiserz Sector 20 Panchkula, '.$payment_msg.$wallet_msg.' For Appointment 7888815146 ,Feedback <br>'.$feedurl;
        
        $message = 'Hi '.strtoupper($cust_name).', Thanks for visit at '.$salon_name.', '.$payment_msg.$wallet_msg.' For Appointment '.$salon_contact;

        $message = 'Hi '.ucwords(strtolower($cust_name)).', Thank you for your purchase at '.$salon_name.'. Your payment of '.$grandtotal.' has been received by '.$payment_mode.'. We appreciate your business and look forward to serving you again soon!';

        //$data  = sendapisms($cust_mob,$message);
        if($whatsapp_enable == 1){
            SendWhatsAppSms($cust_mob,$message,$whatsapp_api);
        }


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
                    Services
                </td>
                <td>
                    Qty
                </td>
                <td width="24%" style="text-align:right">
                    Amount
                </td>
            </tr>

            <?php foreach($all_service as $services){ ?>
            <tr class="item">
                <td colspan="3"><?php echo ucwords(strtolower($services['service'])); ?></td>
                <td>
                    <?php echo $services['qty']; ?>
                </td>
                <td style="text-align:right">
                    <?php echo ucwords(strtolower($services['price'])); ?>
                </td>
            </tr>
            <?php } ?>
        
            <tr >
                <td colspan="3">
                <td>
                  <strong> Total:</strong>
                </td>
                 <td style="text-align:right">
                 <strong><?php print $grandtotal;?></strong>
                </td>
            </tr>
        </table>
        <p>Payment Method : <strong><?php echo $payment_mode; ?></strong></p>
        <p>&nbsp;</p>
        <p style="text-align: center; font-size: 12px;">*** Thanks for using Hair Raiserz Services ***</p></div>
        <meta http-equiv="refresh" content="0; url=/billing_product.php" >

</body>
</html>

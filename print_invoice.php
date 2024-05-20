<?php include "function.php"; 

$salon_id = get_session_data('salon_id');

if($_GET['view'] == 1){
    //echo "SELECT salon_id FROM hr_invoice where invoice_id='".$_GET['invoice_id']."' ";
    $invoice_id = base64_decode($_GET['invoice_id']);
    extract(select_row("SELECT salon_id FROM hr_invoice where invoice_id='".$invoice_id."' "));

}else{
    $invoice_id = $_GET['invoice_id'];
}

if(is_numeric($invoice_id)){

    $salon = select_row("SELECT salon_name,salon_address,salon_contact,salon_gst,logo,firm_name FROM `hr_salon`  WHERE `salon_id` = $salon_id");
    extract($salon);
    

    $invoice_data = select_row("SELECT * FROM `hr_invoice` where invoice_id='".$invoice_id."' ");
    if($invoice_data != false){
        
        foreach($invoice_data as $var => $value){
            $$var = $value;
        }

        $totol_gst = $service_total_tax;
        $all_service = select_array("SELECT service,service_qty,service_price FROM `hr_invoice_service` where `invoice_id`='".$invoice_id."'");
        
        extract(select_row("SELECT cust_wallet  FROM `hr_customer` where `salon_id`='".$salon_id."' and cust_id='".$cust_id."' ORDER BY `cust_wallet` DESC"));

    }else{
        die("Invalid Invoice!!!");
    }
    
}else{
    die("Invalid Invoice!!!");
}
?>

<!doctype html>
<html>
<head>

<meta charset="utf-8">
<title><?php echo $salon_name; ?></title>
    
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body <?php if($_GET['view'] != 1){ ?> onload="window.print()" <?php } ?> >

<div class="invoice-box">
      <center>
          <img src="images/<?php echo $logo; ?>" style="width:70%; max-width:300px;"><br>
          A Unit of <?php echo $firm_name; ?>
      </center>
        
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
                            <?php if($salon_gst != '') { ?>

                            GST No. <?php echo $salon_gst; ?>  <br><br>
                            <?php } ?>
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

            <?php 
            foreach($all_service as $services){ 
                foreach($services as $var => $value){
                    $$var = $value;
                }    
            
            ?>
            <tr class="item">
                <td colspan="3"><?php echo ucwords(strtolower($service)); ?></td>
                <td>
                    <?php echo $service_qty; ?>
                </td>
                <td style="text-align:right">
                    <?php echo ucwords(strtolower($service_price)); ?>
                </td>
            </tr>
            <?php } ?>


            <tr>
                <td colspan="3">
                <td width="36%">
                   Sub Total:
                </td>
                 <td style="text-align:right">
                   <?php echo $service_total;?>
                </td>
            </tr>
            <?php if($totol_gst > 0){ ?>
            <tr>
                <td colspan="3">
                <td>
                   CGST 9%:
                </td>
                 <td style="text-align:right">
                   <?php echo $totol_gst/2; ?>
                </td>
            </tr>
            <tr >
                <td colspan="3">
                <td>
                   SGST 9%:
                </td>
                 <td style="text-align:right">
                   <?php echo $totol_gst/2; ?>
                </td>
            </tr>
            <?php } ?>
            <?php if($discount > 0) { ?>
            <tr>
                <td colspan="3">
                <td>
                   Discount
                </td>
                 <td style="text-align:right">
                   <?php echo $discount; ?>
                   <?php// echo ($discount_mode == 1 ? $discount."%":"Rs ".$discount); ?>
                </td>
            </tr>
            <?php } ?>

            <?php if($extra_fee > 0) { ?>
            <tr>
                <td colspan="3">
                <td>
                   Covid-19 Fee
                </td>
                 <td style="text-align:right">
                   <?php echo ($extra_fee); ?>
                </td>
            </tr>
            <?php } ?>

            <!--tr>
                <td colspan="3">
                <td>
                  <strong> Round Off:</strong>
                </td>
                 <td style="text-align:right">
                 <strong><?php print $round_off;?></strong>
                </td>
            </tr-->
            
            <tr>
                <td colspan="3">
                <td>
                  <strong> Total:</strong>
                </td>
                 <td style="text-align:right">
                 <strong><?php print $grand_total;?></strong>
                </td>
            </tr>
        </table>
        <?php if($payment_mode == 'pkg'){ ?>
            <p>Your Remaining Wallet Balance is <strong><?php echo $cust_wallet; ?></strong></p>
        <?php } else { ?>
        <p>Payment Method : <strong><?php echo $payment_mode; ?></strong></p>
        <?php } ?>
        <p>&nbsp;</p>
        <?php $ref_not = array("","0","Staff","Instagram","Google Ads","Facebook","WalkIn") ?>
        <?php if(!in_array($cust_ref_by,$ref_not)){ ?>
        <p style="text-align: center; font-size: 12px;">* Reference By <?php echo $cust_ref_by; ?> *</p>
        <?php } ?>
        <p style="text-align: center; font-size: 12px;">*** Thanks for using <?php echo $salon_name; ?> Services ***</p>
</div>
        
            <?php if($_GET['type'] == 'close') { ?>
            <script type="text/javascript">
                window.onfocus=function(){ window.close();}
            </script>
            <?php } else {
            if ($_GET['view'] != 1) { ?>
                <meta http-equiv="refresh" content="0; url=/billing_service.php" >
            <?php } }

            ?>
        

</body>
</html>
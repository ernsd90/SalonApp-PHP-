<?php include "function.php"; 

$salon_id = get_session_data('salon_id');

function displayDates($date1, $date2, $format = 'Y-m-d' ) {
    $dates = array();
    $current = strtotime($date1);
    $date2 = strtotime($date2);
    $stepVal = '+1 day';
    while( $current <= $date2 ) {
        $dates[] = date($format, $current);
        $current = strtotime($stepVal, $current);
    }
    return $dates;
}

foreach($_REQUEST as $var => $value) {
    $$var = $value;
}


if($search_fromdate != '' && $search_todate != ''){

       $fromdate = date("Y-m-d",strtotime($search_fromdate));
       $todate = date("Y-m-d",strtotime($search_todate));
       $all_dates = displayDates($search_fromdate,$search_todate);


    if($staff_id > 0){
        $date_where .= " and s.staff_id LIKE '".$staff_id."'";
    }

    foreach($all_dates as $date) {

        $date_where = " and (DATE(invoice_date) = '".$date."')";

        $exp_where = " and (DATE(exp_date) = '".$date."')";


        $a = (select_row("SELECT sum(grand_total) as grand_total FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1'  and payment_mode!='pkg' ".$date_where." "));
        $grand_total = $a['grand_total'];

        $b = (select_row("SELECT sum(grand_total) as total_cash FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1'  and payment_mode LIKE 'cash' ".$date_where." "));
        $total_cash = $b['total_cash'];

        $c = (select_row("SELECT sum(grand_total) as total_cc FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1' and (payment_mode='paytm' || payment_mode='cc' || payment_mode='google_pay' || payment_mode='upi' ||payment_mode='near_buy' ) ".$date_where." "));
        $total_cc = $c['total_cc'];

        $total_customer = (num_rows("SELECT cust_mob FROM `hr_invoice` where salon_id='".$salon_id."' and delete_bill!='1' ".$date_where." "));

        $d = (select_row("SELECT sum(exp_total) as exp_total FROM `hr_expenses` where salon_id='".$salon_id."' ".$exp_where." "));
        $exp_total = $d['exp_total'];

        $data[$date]['grand_total'] = ($grand_total);
        $data[$date]['total_customer'] = $total_customer;
        $data[$date]['total_cash'] = ($total_cash);
        $data[$date]['total_cc'] = ($total_cc);
        $data[$date]['date'] = date("d M Y",strtotime($date));
        $data[$date]['exp_total'] = ($exp_total);

    }

}else{
    die("Invalid Invoice!!!");
}
?>

<!doctype html>
<html>
<head>

<meta charset="utf-8">
<title>Hair Raiserz</title>
    
    <style>
    .invoice-box{
    max-width: 85%;
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
    .heading{
        font-size: 18px;
    }
   .item{
       font-size: 15px;
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
<body onload="window.print()">

<div class="invoice-box">
      <center>
          <img src="https://www.tresslounge.com/hairraiserz/wp-content/uploads/2015/11/HR-LOGO.png" style="width:50%; max-width:200px;">
      </center>

        <p> <right>
            <strong>
            Created: <?php print date("j F Y");?></strong>
            </right>
        </p>

        <table border="1">
            <tr class="heading">
                <td>
                    Date
                </td>
                <td>
                    Customer
                </td>
                <td>
                    Cash
                </td>
                <td >
                    CC
                </td>
                <td >
                    Expence
                </td>
                <td >
                    Total Sale
                </td>
            </tr>

            <?php 
            foreach($data as $alldata){
                foreach($alldata as $var => $value){
                    $$var = $value;
                }    
            
            ?>
            <tr class="item">
                <td><?php echo $date; ?></td>
                <td>
                    <?php  $grandcust += $total_customer; echo $total_customer; ?>
                </td>
                <td>
                    <?php  $grandcash += $total_cash; echo $total_cash; ?>
                </td>
                <td >
                    <?php  $grandcc += $total_cc; echo $total_cc; ?>
                </td>
                <td>
                    <?php  $grandexp += $exp_total; echo $exp_total; ?>
                </td>
                <td>
                    <?php $grandttl += $grand_total; echo $grand_total; ?>
                </td>
            </tr>
            <?php } ?>
            <tr class="item">
                <td></td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
            </tr>
            <tr class="heading">
                <td style="font-size: 19px">Total</td>
                <td>
                    <?php echo $grandcust; ?>
                </td>
                <td>
                    <?php echo $grandcash; ?>
                </td>
                <td style="text-align:right">
                    <?php echo $grandcc; ?>
                </td>
                <td>
                    <?php echo $grandexp; ?>
                </td>
                <td>
                    <?php echo $grandttl; ?>
                </td>
            </tr>
        </table>


        

</body>
</html>
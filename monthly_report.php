<?php

session_start();

include "function.php";
if($_POST){

    $fromdate = date("Y/m/d",strtotime($_POST['search_fromdate']));
    $todate = date("Y/m/d",strtotime($_POST['search_todate']));
}else{
    $fromdate = date("Y/m/1");
    $todate = date("Y/m/t");
}


if($salon_id < 1){
    die(":(");
}
function summary_sale(){

    global $fromdate,$todate,$salon_id;


    $exp_where = " and (DATE(exp_date) BETWEEN '".$fromdate."' AND '".$todate."')";
    $date_month_where = " and (DATE(invoice_date) BETWEEN '".$fromdate."' AND '".$todate."')";

    //$all_salon = select_array("SELECT salon_id,salon_name,msg_id FROM `hr_salon` where salon_id NOT IN (22,8)");
    $all_salon = select_array("SELECT salon_id,salon_name,msg_id FROM `hr_salon` where salon_id IN ($salon_id)");

    foreach($all_salon as $salon) {

        foreach($salon as $var => $value){
            $$var = $value;
        }
        extract(select_row("SELECT sum(grand_total) as month_total FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1'  and payment_mode!='pkg' " . $date_month_where . " "));
        extract(select_row("SELECT sum(grand_total) as total_cash FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1'  and payment_mode LIKE 'cash' " . $date_month_where . " "));
        extract(select_row("SELECT sum(grand_total) as total_cc FROM `hr_invoice` where salon_id='" . $salon_id . "' and delete_bill!='1'  and (payment_mode='paytm' || payment_mode='cc' || payment_mode='google_pay' || payment_mode='upi' || payment_mode='near_buy') " . $date_month_where . " "));
        extract(select_row("SELECT sum(exp_total) as exp_total FROM `hr_expenses` where salon_id='" . $salon_id . "' " . $exp_where . " "));
        extract(select_row("SELECT sum(exp_total) as exp_cc FROM `hr_expenses` where salon_id='" . $salon_id . "' and payment_mode='cc' " . $exp_where . " "));
        extract(select_row("SELECT sum(exp_total) as exp_cash FROM `hr_expenses` where salon_id='" . $salon_id . "' and payment_mode!='cc' " . $exp_where . " "));

        $exp_total = $exp_cc+$exp_cash;
        $data[$salon_id]['month_total'] = $month_total;
        $data[$salon_id]['total_cash'] = $total_cash;
        $data[$salon_id]['total_cc'] = $total_cc;
        $data[$salon_id]['expense_cash'] = $exp_cash;
        $data[$salon_id]['expense_cc'] = $exp_cc;
        $data[$salon_id]['expense_total'] = $exp_total;
        $data[$salon_id]['available_after_exp'] = $month_total-$exp_total;
        $data[$salon_id]['salon_name'] = $salon_name;
        $data[$salon_id]['sender_id'] = $msg_id;
        $data[$salon_id]['salon_id'] = $salon_id;
    }
    return $data;
}

function expenses_summary(){

    global $fromdate,$todate,$salon_id;

    $fromdate = date("Y-m-d", strtotime($fromdate));
    $todate = date("Y-m-d", strtotime($todate));
    $date_where = " and (DATE(exp_date) BETWEEN '" . $fromdate . "' AND '" . $todate . "')";

    $sql2 = "SELECT * FROM `hr_expenses_category` where salon_id='".$salon_id."'  order by category_name asc";
    $expenses_cat = select_array($sql2);
    foreach($expenses_cat as $i => $name){

        $exp_catId = $name['exp_catId'];
        $category_name = ucwords(strtolower($name['category_name']));
        $where = " and e.exp_catId='".$exp_catId."'";

        $sql = " FROM `hr_expenses` as e join hr_expenses_category as c on c.exp_catId=e.exp_catId  where  e.`salon_id`='".$salon_id."' and e.`payment_mode`='cash' $where $date_where ORDER BY exp_id desc";
        $sql2 = " FROM `hr_expenses` as e join hr_expenses_category as c on c.exp_catId=e.exp_catId  where  e.`salon_id`='".$salon_id."' and e.`payment_mode`!='cash' $where $date_where ORDER BY exp_id desc";

        //echo "<br><BR>SELECT sum(e.exp_total) as grand_total ".$sql;
        $exp_detail = select_row("SELECT sum(e.exp_total) as grand_cash ".$sql);
        $exp_detail2 = select_row("SELECT sum(e.exp_total) as grand_cc ".$sql2);
        $exp_cash = $exp_detail['grand_cash'];
        $exp_cc = $exp_detail2['grand_cc'];

        $ttl_exp = $exp_cc+$exp_cash;
        if($ttl_exp > 0) {
            $data[$i]['cat_name'] = $category_name;
            $data[$i]['exp_cc'] = ($exp_cc);
            $data[$i]['exp_cash'] = ($exp_cash);
        }
    }

    return $data;

}
?>

<!doctype html>
<html>
<head>

    <meta charset="utf-8">
    <title>Hair Raiserz</title>

    <style>
       div table.monthly_report{
           width: 40%;
           margin: 10px 15px 21px 32px;
           display: inline-table;
       }
        }
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

<?php



$expdata = expenses_summary();

$saledata = summary_sale();


foreach($saledata as $mydata) {

    foreach ($mydata as $var => $value) {
        $$var = $value;
    }

    ?>

    <center><h2><?php echo $salon_name; ?> Monthly Report(<?php echo date("d-M",strtotime($fromdate ))." To ".date("d-M",strtotime($todate )) ?>)</h2></center>


    <table  border="1" class="monthly_report">
        <tr>
            <th colspan="2">Sale Summary</th>
        </tr>
        <tr>
            <th>Total CC</th>
            <td><?php echo $total_cc; ?></td>
        </tr>
        <tr>
            <th>Total Cash</th>
            <td><?php echo $total_cash; ?></td>
        </tr>
        <tr>
            <th>Total Sale</th>
            <td><?php echo $month_total; ?></td>
        </tr>

        <tr>
            <th>Total Expense</th>
            <td><?php echo $expense_total; ?></td>
        </tr>
        <tr>
            <th>Total Profit/loss</th>
            <td><?php echo $available_after_exp; ?></td>
        </tr>
    </table>
    <table  border="1" class="monthly_report">
        <tr>
            <th colspan="2">Total Exp</th>
        </tr>
        <tr>
            <th>Exp CC</th>
            <td><?php echo $expense_cc; ?></td>
        </tr>
        <tr>
            <th>Exp Cash</th>
            <td><?php echo $expense_cash; ?></td>
        </tr>
        <tr>
            <th>Total Exp</th>
            <td><?php echo $expense_total; ?></td>
        </tr>

    </table>



    <table  border="1" class="expmonthly_report">
        <tr>
            <th colspan="4" style="text-align: center;">Expense Detail</th>
        </tr>
        <tr style="font-size:20px">
            <th>Category</th>
            <th>CC Expense</th>
            <th>Cash Expense</th>
            <th>Total Expense</th>
        </tr>
<?php
}

$expdata = expenses_summary();
foreach($expdata as $mydata) {

    foreach ($mydata as $var => $value) {
        $$var = $value;
    }
?>
        <tr>
            <th><?php echo $cat_name; ?></th>
            <td><?php echo $exp_cc; ?></td>
            <td><?php echo $exp_cash; ?></td>
            <th><?php echo $exp_cc+$exp_cash; ?></th>
        </tr>

<?php
}
?>
        <tr>
            <th>Total Expenses</th>
            <td><?php echo $expense_cc; ?></td>
            <td><?php echo $expense_cash; ?></td>
            <th><?php echo $expense_total; ?></th>
        </tr>
    </table>

    <p> <right>
            <strong>
                Created: <?php print date("j F Y");?></strong>
        </right>
    </p>

</div>
</body>
</html>

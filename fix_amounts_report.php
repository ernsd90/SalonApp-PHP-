<?php
$data = file_get_contents('ajax/report_ajax.php');

// Fix Total CC to include anything not cash
$search1 = "extract(select_row(\"SELECT sum(p.grand_total) as total_cc FROM `hr_invoice` as i JOIN  `hr_invoice_payment` as p on p.invoice_id=i.invoice_id where i.salon_id='\".\$salon_id.\"' and delete_bill!='1' and invoice_type!='2'  and (p.payment_mode='paytm' || p.payment_mode='cc' || p.payment_mode='google_pay' || p.payment_mode='upi' ) \".\$date_where.\" \"));";
$replace1 = "extract(select_row(\"SELECT sum(p.grand_total) as total_cc FROM `hr_invoice` as i JOIN  `hr_invoice_payment` as p on p.invoice_id=i.invoice_id where i.salon_id='\".\$salon_id.\"' and delete_bill!='1' and invoice_type!='2'  and p.payment_mode !='cash' and p.payment_mode !='near_buy' \".\$date_where.\" \"));";

if(strpos($data, $search1) !== false) {
    $data = str_replace($search1, $replace1, $data);
    file_put_contents('ajax/report_ajax.php', $data);
    echo "Total CC Fixed.\n";
} else {
    echo "Total CC not found.\n";
}

?>

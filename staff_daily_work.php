<?php
session_start();
include "function.php";


exit;

$staff_service = select_array("SELECT persrvice_discount,staff_work_price,invoice_id,invoice_service,id as updating_id,COUNT(*) AS total_staff FROM `hr_invoice_staff` where  `invoice_date` BETWEEN '2022-01-01 00:00:00.000000' AND '2022-01-23 01:00:00.000000' GROUP BY invoice_id,invoice_service HAVING COUNT(*)>0");
foreach($staff_service as $staff_ser){

    foreach($staff_ser as $var => $value){
        $$var = $value;
    }

    $invoice = select_row("SELECT discount,grand_total FROM `hr_invoice` WHERE `invoice_id` ='".$invoice_id."'  and  `salon_id` = 80");
    foreach($invoice as $var => $value){
        $$var = $value;
    }
    if($grand_total > 0){

        $total_amt = (($staff_work_price-(($staff_work_price*$persrvice_discount)/100))/$total_staff);

        echo "<BR>".$sql = "UPDATE `hr_invoice_staff` SET `total_amt` = '".$total_amt."' WHERE `invoice_id` ='".$invoice_id."' and `invoice_service` ='".$invoice_service."' ";
        //update_query($sql);
    }



}

exit;

$salon = select_row("SELECT salon_name,salon_address,salon_contact,salon_gst,msg_id as senderid FROM `hr_salon`  WHERE `salon_id` = 80");
extract($salon);


$all_invoice = select_array("SELECT invoice_id,discount,grand_total FROM `hr_invoice` WHERE `salon_id` = 80");
foreach($all_invoice as $invoice){

    foreach($invoice as $var => $value){
        $$var = $value;
    }

    $invoice_service = select_array("SELECT staff_id,id as service_id FROM `hr_invoice_service` WHERE `invoice_id` ='".$invoice_id."' ");
    foreach($invoice_service as $service){

        foreach($service as $var => $value){
            $$var = $value;
        }

        $service = "";


    }




}




?>
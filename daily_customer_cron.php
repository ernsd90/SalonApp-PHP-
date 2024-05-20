<?php
session_start();
include "function.php";

//Service Reminder

    $all_salon = select_array("SELECT salon_id,salon_name,msg_id,whatsapp_enable,whatsapp_api,salon_contact FROM `hr_salon` where salon_id NOT IN (22,8)");
    foreach($all_salon as $salon) {

        foreach ($salon as $var => $value) {
            $$var = $value;
        }

        $sql =  select_array("SELECT service_name,service_reminder FROM `hr_services` WHERE `salon_id` = '".$salon_id."' and service_reminder > 5");
        foreach($sql as $data) {
            foreach ($data as $var => $value) {
                $$var = $value;
            }

            $last_date = date("Y-m-d",strtotime("-".$service_reminder." Days"));
            $customer = select_array("SELECT i.invoice_id,i.invoice_date,cust_name,cust_mob FROM hr_invoice as i join `hr_invoice_service` as s on s.invoice_id=i.invoice_id where i.salon_id='".$salon_id."' and lower(s.service)='".strtolower($service_name)."' and DATE(i.invoice_date) = '".$last_date."' ");
            foreach($customer as $data) {
                foreach ($data as $var => $value) {
                    $$var = $value;
                }

               echo "<br>".$message = "Hi ".ucfirst(strtolower($cust_name))."!\nThis is a friendly reminder from *".$salon_name."* that you have a *".$service_name."* service due today. We can't wait to pamper you! Please schedule a time that works for you by calling us at *".$salon_contact."*. See you soon!";


                SendWhatsAppSms($cust_mob,$message,$whatsapp_api);
                //$cust_mob = "9914500270";
                //SendWhatsAppSms($cust_mob,$message,$whatsapp_api);


            }
        }

}
?>
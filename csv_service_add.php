<?php
die("open file");
include "function.php";
$row = 1;
if (($handle = fopen("service_phase11.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
  
    $row++;
    $category = ucwords($data[0]);
    $service = ucwords($data[1]);
    $price = ucwords($data[2]);
    $salon_id = ucwords($data[3]);

    $check_cat = select_row("SELECT service_catid FROM `hr_servicesCategory` WHERE `salon_id` = '".$salon_id."' AND `service_catName` LIKE '".$category."'");
    if($check_cat['service_catid'] > 0){
        $cat_id = $check_cat['service_catid'];
    }else{
        $cat_id = insert_query("INSERT INTO `hr_servicesCategory` (`salon_id`, `user_id`, `service_catName`) VALUES ('".$salon_id."', '1', '".$category."') ");
    }

    $check_service = select_row("SELECT service_id FROM `hr_services` WHERE `salon_id` = '".$salon_id."' and `service_catid` = '".$cat_id."'  AND `service_name` LIKE '".$service."'");
   if($check_service['service_id'] > 0){

   }else{
        $sql = "INSERT INTO `hr_services` (`salon_id`, `user_id`, `service_catid`, `service_name`, `service_price`, `service_status`) VALUES ('".$salon_id."', '1', '".$cat_id."', '".$service."', '".$price."', '1')";
        insert_query($sql);
   }
   


  }
  fclose($handle);
}


?>
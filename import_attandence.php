<?php 

$csv = "attendence/Sep2021.csv";
 
$fieldsneed = array(3,6,9);
$row = 1;
if (($handle = fopen($csv, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);

    $row++;
    for ($c=0; $c < $num; $c++) {

        if(in_array($c,$fieldsneed)){
            if($c == 9){
                $a = explode(" ",$data[$c]);
               $time = $a[1];
               $date = str_replace("/","-",$a[0]);

                 $date = str_replace("-21","-2021",$date);

                $attan[$row]['time'] = $time;
                $attan[$row]['date'] = date("Y-m-d",strtotime($date));
            }elseif($c == 6)
                $attan[$row]['type'] = $data[$c];
            else
            $attan[$row]['name'] = $data[$c];
        }
    }
  }
  fclose($handle);
}



include "function.php";
foreach($attan as $types){
    
    foreach($types as $var => $value){
        $$var = $value;
    }

    $user = select_row("SELECT *  FROM `hr_attendance` WHERE `name` = '".$name."' AND `user_date` = '".$date."'");

    if($user['name'] != ''){

        echo "<br>update => ".$name." ".$user['duty_in'];
        if($user['duty_in'] == '00:00:00' && $type == "DutyOn"){
            insert_query("UPDATE `hr_attendance` SET `duty_in`='".$time."' where `id`='".$user['id']."'");
        }
        if($user['duty_out'] == '00:00:00' && $type == "DutyOff"){
            //echo "<BR>UPDATE `hr_attendance` SET `duty_out`='".$time."' where `id`='".$user['id']."'";
            insert_query("UPDATE `hr_attendance` SET `duty_out`='".$time."' where `id`='".$user['id']."'");
        }
        $data = select_row("SELECT *  FROM `hr_attendance` WHERE `name` = '".$name."' AND `user_date` = '".$date."'");
        
        if($data['duty_out'] == '00:00:00' || $data['duty_in'] == '00:00:00'){
            $total_hr = 8;
        }else{
            //$total_hr = str_replace(":",".",$data['duty_out'])-str_replace(":",".",$data['duty_in']);
            $starttimestamp = strtotime($data['duty_in']);
            $endtimestamp = strtotime($data['duty_out']);
            $total_hr = abs($endtimestamp - $starttimestamp)/3600;
            


        }
        //echo "<br><br>".$difference;
        insert_query("UPDATE `hr_attendance` SET `working_hr`='".$total_hr."' where `id`='".$data['id']."'");


    }else{

        echo "<br>insert => ".$name;
        $user_id = insert_query("INSERT INTO `hr_attendance` SET `name`='".$name."',`user_date`='".$date."'");

        if($type == "DutyOn"){
            insert_query("UPDATE `hr_attendance` SET `duty_in`='".$time."' where `id`='".$user_id."'");
        }
        if($type == "DutyOff"){
            insert_query("UPDATE `hr_attendance` SET `duty_out`='".$time."' where `id`='".$user_id."'");
        }

    }
    

}




?>
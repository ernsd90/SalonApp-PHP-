<?php 
header('Content-Type: application/json');

include "../function.php";

$method=$_REQUEST["method"];



if(function_exists($method)){
    echo json_encode($method(),JSON_NUMERIC_CHECK);
}
else{
    echo "Method Not Found";
}

$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');


function top_categories() {
    global $salon_id;

    $selectedMonth = $_REQUEST['selectedMonth'];
    if($selectedMonth == ''){
        $selectedMonth = date('Y-m');
    }
    

    $sql = "SELECT s.service_cat, SUM(i.grand_total) AS total_revenue 
            FROM `hr_invoice` AS i 
            JOIN hr_invoice_service AS s ON i.invoice_id = s.invoice_id 
            WHERE i.`salon_id` = '".$salon_id."' 
            AND i.`delete_bill` != 1 
            AND i.`invoice_date` BETWEEN '".$selectedMonth."-01' and '".$selectedMonth."-31'
            GROUP BY service_cat 
            ORDER BY total_revenue DESC 
            LIMIT 10";
    
    $data = select_array($sql);
   // $final[0][] = "Category";
    //$final[0][] = "Revenue";
    
    foreach($data as $i => $datas) {
        $final[$i+1][] = $datas['service_cat'];
        $final[$i+1][] = $datas['total_revenue'];
    }
    
    $data2 = array(array("Cat","total"));
    $final = array_merge($data2, $final);
    
    return $final;
}


function top_services() {
    global $salon_id;

    $selectedMonth = $_REQUEST['selectedMonth'];
    if($selectedMonth == ''){
        $selectedMonth = date('Y-m');
    }
    

    $sql = "SELECT s.service, SUM(i.grand_total) AS total_revenue 
            FROM `hr_invoice` AS i 
            JOIN hr_invoice_service AS s ON i.invoice_id = s.invoice_id 
            WHERE i.`salon_id` = '".$salon_id."' 
            AND i.`delete_bill` != 1 
            AND i.`invoice_date` BETWEEN '".$selectedMonth."-01' and '".$selectedMonth."-31'
            GROUP BY service 
            ORDER BY total_revenue DESC 
            LIMIT 10";
    
    $data = select_array($sql);
   // $final[0][] = "Category";
    //$final[0][] = "Revenue";
    
    foreach($data as $i => $datas) {
        $final[$i+1][] = $datas['service'];
        $final[$i+1][] = $datas['total_revenue'];
    }
    
    $data2 = array(array("Cat","total"));
    $final = array_merge($data2, $final);
    
    return $final;
}

function monthly_recap(){

    global $salon_id;
    $current_month = select_array("SELECT sum(grand_total) as day_sale,DATE(invoice_date) as full_date,DAY(invoice_date) as month_day FROM `hr_invoice` where salon_id = '".$salon_id."' and delete_bill != 1 and payment_mode != 'pkg' and  MONTH(invoice_date) = MONTH(CURRENT_DATE()) group by DATE(invoice_date) ORDER BY DATE(invoice_date)  ASC");

    
    foreach($current_month as $datas){
        
        extract($datas);
        //$data = array();

        $day = date("j",strtotime($full_date));
        $month = date("M",strtotime($full_date));
        
        $data[$month_day]['day'] = $day;
        $data[$month_day]['sale'] = $day_sale;
        
        //$final_data[] = $data;
    }

    $last_month = select_array("SELECT sum(grand_total) as day_sales,DAY(invoice_date) as month_day FROM `hr_invoice` where salon_id = '".$salon_id."' and delete_bill != 1 and payment_mode != 'pkg' and  MONTH(invoice_date) = MONTH(CURRENT_DATE())-1 group by DATE(invoice_date) ORDER BY DATE(invoice_date)  ASC");
    foreach($last_month as $datas){
        extract($datas);
        //$data = array();
        $data2[$month_day]['sale_next'] = $day_sales;
        //$final_data2[] = $data;
    }

    $d=strtotime("-1 Months");
    $last_month =  date("M", $d);




    $last2_month = select_array("SELECT sum(grand_total) as day_sales,DAY(invoice_date) as month_day FROM `hr_invoice` where salon_id = '".$salon_id."' and delete_bill != 1 and payment_mode != 'pkg' and  MONTH(invoice_date) = (MONTH(CURRENT_DATE())-2) group by DATE(invoice_date) ORDER BY DATE(invoice_date)  ASC");
    foreach($last2_month as $datas){
        extract($datas);
        //$data = array();
        $data2[$month_day]['sale_2next'] = $day_sales;
        //$final_data2[] = $data;
    }

    $d=strtotime("-2 Months");
    $last2_month =  date("M", $d);



    $final_data[] = array("Day",date("M"),$last_month,$last2_month);

    for($i=1;$i<=31;$i++){
        $final = array();

        $sale_next = $data2[$i]['sale_next'];
        $sale_2next = $data2[$i]['sale_2next'];
        $sale = $data[$i]['sale'];

        $final[] = $i;
        $final[] = is_null($sale) ? 0:$sale;
        $final[] = is_null($sale_next) ? 0:$sale_next;
        $final[] = is_null($sale_2next) ? 0:$sale_2next;
        $final_data[] = $final;
    }

    return ($final_data);

}

?>
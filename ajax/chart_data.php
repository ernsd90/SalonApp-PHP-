<?php 
header('Content-Type: application/json');

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE); 
if (session_status() === PHP_SESSION_NONE) session_start();

include "../config.php";
include "../function.php";

$method = $_REQUEST["method"] ?? '';

if($method && function_exists($method)){
    echo json_encode($method(), JSON_NUMERIC_CHECK);
} else {
    echo json_encode(['error' => 1, 'msg' => 'Method Not Found']);
}

$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

function top_services() {
    global $salon_id;

    $selectedMonth = $_REQUEST['selectedMonth'] ?? date('Y-m');
    $start_date = $selectedMonth . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));

    $sql = "SELECT s.service, SUM(i.grand_total) AS total_revenue 
            FROM `hr_invoice` AS i 
            JOIN hr_invoice_service AS s ON i.invoice_id = s.invoice_id 
            WHERE i.`salon_id` = '".$salon_id."' 
            AND i.`delete_bill` != 1 
            AND i.`invoice_date` BETWEEN '".$start_date."' and '".$end_date."'
            GROUP BY service 
            ORDER BY total_revenue DESC 
            LIMIT 10";
    
    $data = select_array($sql);
    $final = [];
    
    foreach($data as $i => $datas) {
        $final[$i+1][] = $datas['service'];
        $final[$i+1][] = (float)$datas['total_revenue'];
    }
    
    $data2 = array(array("Cat","total"));
    $final = array_merge($data2, $final);
    
    return $final;
}

function monthly_recap(){
    global $salon_id;

    $selectedMonth = $_REQUEST['selectedMonth'] ?? date('Y-m');
    
    // We want to show daily data for the selected month, compared against the two prior months.
    $date1 = $selectedMonth . '-01';
    $month1_name = date("M", strtotime($date1));
    $month1_num = date("m", strtotime($date1));
    $year1 = date("Y", strtotime($date1));

    $date2 = date('Y-m-d', strtotime('-1 month', strtotime($date1)));
    $month2_name = date("M", strtotime($date2));
    $month2_num = date("m", strtotime($date2));
    $year2 = date("Y", strtotime($date2));

    $date3 = date('Y-m-d', strtotime('-2 months', strtotime($date1)));
    $month3_name = date("M", strtotime($date3));
    $month3_num = date("m", strtotime($date3));
    $year3 = date("Y", strtotime($date3));

    $current_month = select_array("SELECT sum(grand_total) as day_sale, DATE(invoice_date) as full_date, DAY(invoice_date) as month_day FROM `hr_invoice` where salon_id = '".$salon_id."' and delete_bill != 1 and payment_mode != 'pkg' and MONTH(invoice_date) = '$month1_num' and YEAR(invoice_date) = '$year1' group by DATE(invoice_date) ORDER BY DATE(invoice_date) ASC");

    $data = [];
    $data2 = [];

    if ($current_month) {
        foreach($current_month as $datas){
            extract($datas);
            $day = date("j", strtotime($full_date));
            $month = date("M", strtotime($full_date));
            $data[$month_day]['sale'] = $day_sale;
        }
    }

    $last_month = select_array("SELECT sum(grand_total) as day_sales, DAY(invoice_date) as month_day FROM `hr_invoice` where salon_id = '".$salon_id."' and delete_bill != 1 and payment_mode != 'pkg' and MONTH(invoice_date) = '$month2_num' and YEAR(invoice_date) = '$year2' group by DATE(invoice_date) ORDER BY DATE(invoice_date) ASC");
    
    if ($last_month) {
        foreach($last_month as $datas){
            extract($datas);
            $data2[$month_day]['sale_next'] = $day_sales;
        }
    }

    $last2_month = select_array("SELECT sum(grand_total) as day_sales, DAY(invoice_date) as month_day FROM `hr_invoice` where salon_id = '".$salon_id."' and delete_bill != 1 and payment_mode != 'pkg' and MONTH(invoice_date) = '$month3_num' and YEAR(invoice_date) = '$year3' group by DATE(invoice_date) ORDER BY DATE(invoice_date) ASC");
    
    if ($last2_month) {
        foreach($last2_month as $datas){
            extract($datas);
            $data2[$month_day]['sale_2next'] = $day_sales;
        }
    }

    $final_data[] = array("Day", $month1_name, $month2_name, $month3_name);

    $days_in_month = date('t', strtotime($date1));

    for($i=1; $i<=$days_in_month; $i++){
        $final = array();

        $sale_next = isset($data2[$i]['sale_next']) ? $data2[$i]['sale_next'] : 0;
        $sale_2next = isset($data2[$i]['sale_2next']) ? $data2[$i]['sale_2next'] : 0;
        $sale = isset($data[$i]['sale']) ? $data[$i]['sale'] : 0;

        $final[] = (string)$i;
        $final[] = (float)$sale;
        $final[] = (float)$sale_next;
        $final[] = (float)$sale_2next;
        
        $final_data[] = $final;
    }

    return $final_data;
}
?>

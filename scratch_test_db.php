<?php
include "config.php";
include "function.php";

$salon_id = 80;
$from = '2026-05-01';
$to = '2026-05-27';

echo "--- Membership payments grouped by sold_by ---\n";
$mems_pay = select_array("SELECT cm.sold_by as staff_id, SUM(mp.amount) as rev 
    FROM hr_membership_payments mp
    JOIN hr_customer_membership cm ON cm.cm_id = mp.cm_id
    WHERE mp.salon_id = $salon_id AND DATE(mp.created_at) BETWEEN '$from' AND '$to' AND cm.status != 'refunded'
    GROUP BY cm.sold_by");
print_r($mems_pay);

echo "--- Packages sum grouped by sold_by ---\n";
$pkgs_pay = select_array("SELECT sold_by as staff_id, SUM(purchase_price) as rev 
    FROM hr_customer_packages
    WHERE salon_id = $salon_id AND purchase_date BETWEEN '$from' AND '$to' AND status != 'refunded'
    GROUP BY sold_by");
print_r($pkgs_pay);
?>

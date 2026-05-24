<?php
include 'config.php';

$sql1 = "ALTER TABLE hr_expenses ADD INDEX idx_salon_date (salon_id, exp_date)";
$sql2 = "ALTER TABLE hr_expenses ADD INDEX idx_catId (exp_catId)";
$sql3 = "ALTER TABLE hr_expenses ADD INDEX idx_payment (payment_mode)";

mysqli_query($conn, $sql1);
mysqli_query($conn, $sql2);
mysqli_query($conn, $sql3);

echo "Indexes added.\n";
?>

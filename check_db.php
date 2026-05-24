<?php
include 'config.php';

$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM hr_vendors");
$row = mysqli_fetch_assoc($res);
echo "Vendors count: " . $row['cnt'] . "\n";

$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM hr_expenses WHERE vendor_id IS NOT NULL");
$row = mysqli_fetch_assoc($res);
echo "Expenses with vendor_id count: " . $row['cnt'] . "\n";
?>

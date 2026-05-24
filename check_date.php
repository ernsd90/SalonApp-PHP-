<?php
include 'config.php';

$res = mysqli_query($conn, "SELECT exp_date FROM hr_expenses LIMIT 10");
while($row = mysqli_fetch_assoc($res)) {
    echo "Date: " . $row['exp_date'] . "\n";
}
?>

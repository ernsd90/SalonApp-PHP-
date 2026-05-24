<?php
include 'config.php';

$res = mysqli_query($conn, "SELECT approval_status, COUNT(*) as cnt FROM hr_expenses GROUP BY approval_status");
while($row = mysqli_fetch_assoc($res)) {
    echo "Status: " . ($row['approval_status'] === null ? 'NULL' : $row['approval_status']) . " | Count: " . $row['cnt'] . "\n";
}
?>

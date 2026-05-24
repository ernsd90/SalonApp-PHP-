<?php
session_start();
include 'config.php';
include 'function.php';

$salon_id = get_session_data('salon_id') ?? 80;
$type = $_GET['type'] ?? 'csv';
$fromdate = $_GET['fromdate'] ?? date('01-m-Y');
$todate = $_GET['todate'] ?? date('d-m-Y');

$from = date('Y-m-d', strtotime($fromdate));
$to = date('Y-m-d', strtotime($todate));

$sql = "SELECT e.*, c.category_name, v.name as vendor_name FROM hr_expenses e LEFT JOIN hr_expenses_category c ON e.exp_catId = c.exp_catId LEFT JOIN hr_vendors v ON e.vendor_id = v.id WHERE e.salon_id='$salon_id' AND DATE(e.exp_date) BETWEEN '$from' AND '$to' AND e.approval_status != 'rejected' ORDER BY e.exp_id DESC";
$res = mysqli_query($conn, $sql);

if ($type === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Expenses_Report_' . $fromdate . '_to_' . $todate . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Date', 'Category', 'Description', 'Amount', 'Payment Mode', 'Vendor', 'Note', 'Status'));
    
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            fputcsv($output, array(
                $row['exp_id'],
                $row['exp_date'],
                $row['category_name'] ?: 'General',
                $row['exp_name'],
                $row['exp_total'],
                strtoupper($row['payment_mode']),
                $row['vendor_name'] ?: $row['exp_vendor'],
                $row['exp_note'],
                ucfirst($row['approval_status'])
            ));
        }
    }
    fclose($output);
    exit;
} elseif ($type === 'print') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Expense Report (<?= $fromdate ?> to <?= $todate ?>)</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ccc; padding: 10px; text-align: left; font-size: 14px; }
            th { background: #f4f4f4; }
            .header { text-align: center; margin-bottom: 30px; }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <div class="header">
            <h2>Expense Report</h2>
            <p>Period: <?= $fromdate ?> to <?= $todate ?></p>
        </div>
        <button class="no-print" onclick="window.print()" style="padding:10px 20px; cursor:pointer; background:#4f46e5; color:white; border:none; border-radius:5px; margin-bottom:20px;">Print Document</button>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Vendor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                if ($res) {
                    while ($row = mysqli_fetch_assoc($res)) {
                        $total += $row['exp_total'];
                        echo "<tr>";
                        echo "<td>{$row['exp_date']}</td>";
                        echo "<td>".($row['category_name'] ?: 'General')."</td>";
                        echo "<td>{$row['exp_name']}</td>";
                        echo "<td>₹".number_format($row['exp_total'], 2)."</td>";
                        echo "<td>".strtoupper($row['payment_mode'])."</td>";
                        echo "<td>".($row['vendor_name'] ?: $row['exp_vendor'])."</td>";
                        echo "<td>".ucfirst($row['approval_status'])."</td>";
                        echo "</tr>";
                    }
                }
                ?>
                <tr>
                    <th colspan="3" style="text-align:right;">Total</th>
                    <th colspan="4">₹<?= number_format($total, 2) ?></th>
                </tr>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}
?>

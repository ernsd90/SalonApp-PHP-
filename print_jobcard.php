<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config.php";
include "function.php";

$salon_id = get_session_data('salon_id');

$job_card_id = isset($_GET['job_card_id']) ? $_GET['job_card_id'] : '';
if (isset($_GET['view']) && $_GET['view'] == 1) {
    $job_card_id = base64_decode($_GET['job_card_id']);
}

if(!is_numeric($job_card_id)) die("Invalid Job Card!!!");

$salon = select_row("SELECT salon_name, salon_address, salon_contact, firm_name FROM `hr_salon` WHERE `salon_id` = $salon_id");
extract($salon);

$job_card_data = select_row("SELECT * FROM `hr_jobcard` WHERE job_card_id='" . $job_card_id . "' ");
if (!$job_card_data) die("Job Card not found.");
extract($job_card_data);

$customer = select_row("SELECT * FROM `hr_customer` WHERE `salon_id`='" . $salon_id . "' AND cust_id='" . $cust_id . "' ORDER BY `cust_wallet` DESC");

// Fetch ALL assigned staff members for this job card
$assigned_staff = select_array("
    SELECT DISTINCT st.staff_id, st.staff_name 
    FROM hr_jobcardstaff js
    JOIN hr_staff st ON js.staff_id = st.staff_id
    WHERE js.job_card_id = '".$job_card_id."' AND js.delete_status = 'active'
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Internal Job Card #<?= $job_card_id ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #0f172a;
        }

        .print-page {
            max-width: 80mm; /* Standard thermal receipt width */
            margin: 0 auto 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border-radius: 8px;
            page-break-after: always;
        }

        .print-page:last-child {
            page-break-after: avoid;
            margin-bottom: 0;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .header h1 { margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; }
        .header p { margin: 5px 0 0 0; font-size: 12px; color: #64748b; }
        
        .meta-info {
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .staff-label {
            background: #e0e7ff;
            color: #4f46e5;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
        }

        .service-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .service-item {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .service-name {
            font-weight: 600;
            font-size: 14px;
        }

        .service-remark {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            font-style: italic;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #94a3b8;
        }

        @media print {
            body { background: white; padding: 0; }
            .print-page { box-shadow: none; border-radius: 0; margin: 0; max-width: 100%; padding: 0 10px; }
        }
    </style>
</head>
<body <?php if(!isset($_GET['view']) || $_GET['view'] != 1) echo 'onload="window.print()"'; ?>>

<?php 
if(empty($assigned_staff)) {
    echo "<div class='print-page'><p style='text-align:center;'>No staff assigned to this Job Card.</p></div>";
} else {
    // Generate a dedicated printout block for EACH staff member
    foreach($assigned_staff as $staff) {
        
        // Fetch only services assigned to *this specific staff member* for *this job card*
        $services = select_array("
            SELECT hs.service_name, js.service_remark 
            FROM hr_jobcardstaff jst
            JOIN hr_jobcardservice js ON jst.job_card_service_id = js.job_card_service_id
            JOIN hr_services hs ON js.service_id = hs.service_id
            WHERE jst.job_card_id = '".$job_card_id."' 
              AND jst.staff_id = '".$staff['staff_id']."' 
              AND jst.delete_status = 'active' 
              AND js.delete_status = 'active'
        ");

        if(!$services) continue; // Skip if they have no active services
?>
        <div class="print-page">
            <div class="header">
                <h1><?= htmlspecialchars($firm_name ?: $salon_name) ?></h1>
                <p>INTERNAL JOB CARD</p>
            </div>

            <div class="meta-info">
                <strong>Card #:</strong> <?= $job_card_id ?><br>
                <strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($created_at)) ?><br>
                <strong>Customer:</strong> <?= htmlspecialchars($customer['cust_name']) ?>
                <!-- Note: Mobile Number and Prices explicitly omitted for privacy/security -->
            </div>

            <div class="staff-label">
                Assigned To: <?= htmlspecialchars($staff['staff_name']) ?>
            </div>

            <ul class="service-list">
                <?php foreach($services as $srv): ?>
                    <li class="service-item">
                        <div class="service-name"><?= htmlspecialchars($srv['service_name']) ?></div>
                        <?php if(!empty($srv['service_remark'])): ?>
                            <div class="service-remark">Note: <?= htmlspecialchars($srv['service_remark']) ?></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="footer">
                Please complete assigned services and hand this card to the front desk.
            </div>
        </div>
<?php 
    } 
}
?>

<?php if(isset($_GET['type']) && $_GET['type'] == 'close'): ?>
<script type="text/javascript">
    window.onfocus = function() { window.close(); }
</script>
<?php elseif(!isset($_GET['view']) || $_GET['view'] != 1): ?>
    <meta http-equiv="refresh" content="1; url=<?= DOMAIN_SOFTWARE ?>job_card_list.php" >
<?php endif; ?>

</body>
</html>

<?php
include "function.php";

$salon_id = get_session_data('salon_id');

if ($_GET['view'] == 1) {
    $job_card_id = base64_decode($_GET['job_card_id']);
    extract(select_row("SELECT salon_id FROM hr_jobcard WHERE job_card_id='" . $job_card_id . "' "));
} else {
    $job_card_id = $_GET['job_card_id'];
}

if (is_numeric($job_card_id)) {
    $salon = select_row("SELECT salon_name, salon_address, salon_contact, salon_gst, logo, firm_name FROM `hr_salon` WHERE `salon_id` = $salon_id");
    extract($salon);

    $job_card_data = select_row("SELECT * FROM `hr_jobcard` WHERE job_card_id='" . $job_card_id . "' ");
    if ($job_card_data != false) {
        foreach ($job_card_data as $var => $value) {
            $$var = $value;
        }

        $all_services = select_array("
            SELECT hs.service_name,js.service_remark, GROUP_CONCAT(s.staff_name SEPARATOR ', ') as staff_names 
            FROM `hr_jobcardservice` js
            JOIN `hr_jobcardstaff` jst ON js.job_card_service_id = jst.job_card_service_id AND jst.delete_status = 'active'
            JOIN `hr_staff` s ON jst.staff_id = s.staff_id
            JOIN `hr_services` hs ON js.service_id = hs.service_id
            WHERE js.job_card_id = '" . $job_card_id . "' AND js.`delete_status` = 'active'
            GROUP BY js.service_id
        ");

        extract(select_row("SELECT * FROM `hr_customer` WHERE `salon_id`='" . $salon_id . "' AND cust_id='" . $cust_id . "' ORDER BY `cust_wallet` DESC"));
    } else {
        die("Invalid Job Card!!!");
    }
} else {
    die("Invalid Job Card!!!");
}

// Function to partially mask the mobile number
function maskMobileNumber($number) {
    return substr($number, 0, 4) . '******' . substr($number, -2);
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $salon_name; ?></title>
<style>
.invoice-box {
    max-width: 100mm;
    margin: auto;
    padding: 3px;
    font-size: 12px;
    font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    color: #010101;
    font-weight: normal;
}
.invoice-box table {
    width: 100%;
    line-height: inherit;
    text-align: left;
}
.invoice-box table td {
    padding: 3px;
    vertical-align: top;
}
.invoice-box table tr td:nth-child(2) {
    text-align: right;
}
.invoice-box table tr.top table td {
    padding-bottom: 20px;
}
.invoice-box table tr.information table td {
    padding-bottom: 40px;
}
.invoice-box table tr.heading td {
    font-weight: bold;
}
.invoice-box table tr.details td {
    padding-bottom: 20px;
}
.invoice-box table tr.item td {
    border-bottom: 1px solid #eee;
}
.invoice-box table tr.item.last td {
    border-bottom: none;
}
.invoice-box table tr.total td:nth-child(2) {
    border-top: 2px solid #eee;
    font-weight: bold;
}
@media only screen and (max-width: 600px) {
    .invoice-box table tr.top table td {
        width: 100%;
        display: block;
        text-align: center;
    }
    .invoice-box table tr.information table td {
        width: 100%;
        display: block;
        text-align: center;
    }
}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body <?php if ($_GET['view'] != 1) { ?> onload="window.print()" <?php } ?> >

<div class="invoice-box">
    <center>
        <img src="images/<?php echo $logo; ?>" style="width:70%; max-width:300px;"><br>
        A Unit of <?php echo $firm_name; ?>
    </center>
    
    <p><right>
        <strong>
        Job Card #: <?=$job_card_id; ?><br>
        Created: <?php echo date("j F Y", strtotime($created_at)); ?></strong>
        </right>
    </p>
    
    <table width="100%">
        <tr>
            <td width="50%">
                <p>
                    <span style="font-size: 12px">
                    <?php echo $salon_address; ?><br>
                    <?php echo $salon_contact; ?><br><br>
                    <?php if ($salon_gst != '') { ?>
                    GST No. <?php echo $salon_gst; ?><br><br>
                    <?php } ?>
                    </span>
                </p>
            </td>
            
            <td width="50%">
                <p>
                    <span style="text-align: right"><strong><?php echo strtoupper($cust_name); ?></strong><br><br>
                    <?php echo maskMobileNumber($cust_mobile); ?></span><br>
                </p>
            </td>
        </tr>
    </table>
    
    <table width="100%">   
        <tr class="heading">
            <td>Service</td>
            <td>Staff</td>
            <td>Remark</td>
        </tr>
        
        <?php 
        foreach ($all_services as $services) { 
        ?>
        <tr class="item">
            <td><?php echo ucwords(strtolower($services['service_name'])); ?></td>
            <td><?php echo ucwords(strtolower($services['staff_names'])); ?></td>
            <td><?php echo ucwords(strtolower($services['service_remark'])); ?></td>
        </tr>
        <?php } ?>
    </table>
    
    <p>&nbsp;</p>
    <p style="text-align: center; font-size: 12px;">*** Thanks for using <?php echo $salon_name; ?> Services ***</p>
</div>

<?php if ($_GET['type'] == 'close') { ?>
<script type="text/javascript">
    window.onfocus = function() { window.close(); }
</script>
<?php } else { if ($_GET['view'] != 1) { ?>
    <meta http-equiv="refresh" content="0; url=<?php echo DOMAIN_SOFTWARE ?>jobcard_view.php" >
<?php } } ?>

</body>
</html>

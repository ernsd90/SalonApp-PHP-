<?php
include_once "function.php";
$salon_id = get_session_data('salon_id');



$date_now = (new DateTime())->format("Y-m-d H:i:s");

$salon = select_row("SELECT salon_name, salon_address, salon_contact, salon_gst, msg_id as senderid, whatsapp_enable FROM `hr_salon` WHERE `salon_id` = $salon_id");
$salon_name = $salon['salon_name'];
$salon_address = $salon['salon_address'];
$salon_contact = $salon['salon_contact'];
$salon_gst = $salon['salon_gst'];
$senderid = $salon['senderid'];
$whatsapp_enable = $salon['whatsapp_enable'];

$staff_service_data = array();

if (isset($_POST['cust_mob'])) {
    extract($_POST);

    $cust_name = ucwords(strtolower(trim($cust_name)));

    // Check if the customer exists
    $cust = select_row("SELECT cust_id FROM `hr_customer` WHERE cust_mobile = '".$cust_mob."' AND `salon_id` = '".$salon_id."' ORDER BY cust_wallet DESC");
    $cust_id = ($cust['cust_id'] > 0) ? $cust['cust_id'] : 0;

    if ($cust_id > 0) {
        // Update existing customer
        $sql = "UPDATE `hr_customer` SET `cust_gender` = '".$cust_gender."' WHERE cust_id = '".$cust_id."'";
        update_query($sql);
    } else {
        // Insert new customer
        $sql = "INSERT INTO `hr_customer` SET `salon_id` = '".$salon_id."', `user_id` = '".$user_id."', `cust_name` = '".$cust_name."', `cust_reffer` = '".$cust_reffer."', `cust_mobile` = '".$cust_mob."', `cust_gender` = '".$cust_gender."'";
        $cust_id = insert_query($sql);
    }

    if (isset($_POST['job_card_id']) && is_numeric($_POST['job_card_id'])) {
        // Update existing job card
        $job_card_id = $_POST['job_card_id'];

        // Check if the job card exists
        $job_card = select_row("SELECT job_card_id FROM `hr_jobcard` WHERE `job_card_id` = '".$job_card_id."' AND `salon_id` = '".$salon_id."'");
        if ($job_card['job_card_id'] > 0) {
            $job_card_id = $job_card['job_card_id'];

            // Update the job card (if necessary)
            $sql = "UPDATE `hr_jobcard` SET `updated_at` = NOW() WHERE `job_card_id` = '".$job_card_id."'";
            update_query($sql);

            // Loop through services and update or insert
            foreach ($sub_service as $key => $service_id) {
                if ($service_id == 0) continue; // Skip if service is not selected

                // Ensure $service_remark is an array

                $service_remarks = is_array($service_remark) ? $service_remark[$key] : '';
                $existing_service = select_row("SELECT job_card_service_id, service_id, status FROM `hr_jobcardservice` WHERE `job_card_id` = '".$job_card_id."' AND `service_id` = '".$service_id."'");

                if ($existing_service) {
                    $old_status = $existing_service['status'];
                    $new_status = 'updated';

                    // Update existing job card service
                     $sql = "UPDATE `hr_jobcardservice` SET `status` = '".$new_status."', `service_remark` = '".$service_remarks."', `added_by` = '".$user_id."', `added_at` = NOW() WHERE `job_card_service_id` = '".$existing_service['job_card_service_id']."'";
                    update_query($sql);
                    $job_card_service_id = $existing_service['job_card_service_id'];

                    // Mark existing staff links for this service as deleted
                    $sql = "UPDATE `hr_jobcardstaff` SET `delete_status` = 'deleted' WHERE `job_card_service_id` = '".$job_card_service_id."'";
                    update_query($sql);

                } else {
                    // Insert new job card service
                    $sql = "INSERT INTO `hr_jobcardservice` SET `salon_id` = '".$salon_id."', `job_card_id` = '".$job_card_id."', `service_id` = '".$service_id."', `added_by` = '".$user_id."', `added_at` = NOW(), `status` = 'initial', `service_remark` = '".$service_remarks."'";
                    $job_card_service_id = insert_query($sql);
                }

                // Link staff with services
                foreach ($service_staff[$key] as $staff_id) {
                    $existing_staff = select_row("SELECT job_card_staff_id FROM `hr_jobcardstaff` WHERE `job_card_id` = '".$job_card_id."' AND `staff_id` = '".$staff_id."' AND `job_card_service_id` = '".$job_card_service_id."'");

                    if ($existing_staff) {
                        // Update existing job card staff
                        $sql = "UPDATE `hr_jobcardstaff` SET `updated_at` = NOW(), `delete_status` = 'active' WHERE `job_card_staff_id` = '".$existing_staff['job_card_staff_id']."'";
                        update_query($sql);
                        $job_card_staff_id = $existing_staff['job_card_staff_id'];
                    } else {
                        // Insert new job card staff
                        $sql = "INSERT INTO `hr_jobcardstaff` SET `salon_id` = '".$salon_id."', `job_card_id` = '".$job_card_id."', `staff_id` = '".$staff_id."', `job_card_service_id` = '".$job_card_service_id."', `assigned_at` = NOW(), `delete_status` = 'active'";
                        $job_card_staff_id = insert_query($sql);
                    }
                }
            }

            echo "Job card updated successfully with ID: " . $job_card_id;
        } else {
            echo "Job card not found.";
        }
    } else {
        // Create a new job card
        $sql = "INSERT INTO `hr_jobcard` SET `salon_id` = '".$salon_id."', `cust_id` = '".$cust_id."', `created_by` = '".$user_id."', `created_at` = NOW()";
        $job_card_id = insert_query($sql);

        if ($job_card_id > 0) {
            foreach ($sub_service as $key => $service_id) {
                if ($service_id == 0) continue; // Skip if service is not selected

                // Insert job card service
                $sql = "INSERT INTO `hr_jobcardservice` SET `salon_id` = '".$salon_id."', `job_card_id` = '".$job_card_id."', `service_id` = '".$service_id."', `added_by` = '".$user_id."', `added_at` = NOW(), `status` = 'initial', `service_remark` = '".$service_remark[$key]."'";
                $job_card_service_id = insert_query($sql);

                // Link staff with services
                foreach ($service_staff[$key] as $staff_id) {
                    // Insert job card staff
                    $sql = "INSERT INTO `hr_jobcardstaff` SET `salon_id` = '".$salon_id."', `job_card_id` = '".$job_card_id."', `staff_id` = '".$staff_id."', `job_card_service_id` = '".$job_card_service_id."', `assigned_at` = NOW(), `delete_status` = 'active'";
                    $job_card_staff_id = insert_query($sql);
                }
            }

            echo "Job card created successfully with ID: " . $job_card_id;
        } else {
            echo "Failed to create job card.";
        }
    }
}

if (isset($_POST['save_bill_print'])) {
    // Your code for saving and printing the bill
} else {
    // Your code for other actions
}

header("refresh: 0; url=".DOMAIN_SOFTWARE."print_jobcard.php?job_card_id=".$job_card_id);

?>

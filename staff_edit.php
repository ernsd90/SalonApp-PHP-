<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config.php";
include "function.php";

$user_action = "create";
$staff_name = $staff_mob = $joining_date = $staff_salary = "";
$staff_status = 1;

if(isset($_REQUEST['staff_id']) && is_numeric($_REQUEST['staff_id'])){
    $user_action = "edit";
    $sql = "SELECT * FROM `hr_staff` WHERE `staff_id`='".mysqli_real_escape_string($conn, $_REQUEST['staff_id'])."'";
    $staff = select_row($sql);
    if($staff) {
        $staff_id = $staff['staff_id'];
        $staff_name = $staff['staff_name'];
        $staff_mob = $staff['staff_mob'];
        $staff_role = $staff['staff_role'] ?? '';
        $department = $staff['department'] ?? '';
        $gender = $staff['gender'] ?? '';
        $seniority = $staff['seniority'] ?? 'Junior';
        
        $joining_date = "";
        if(!empty($staff['joining_date']) && $staff['joining_date'] != '0000-00-00' && $staff['joining_date'] != '0000-00-00 00:00:00') {
            $joining_date = date('Y-m-d', strtotime($staff['joining_date']));
        }

        $staff_salary = $staff['staff_salary'];
        $staff_status = $staff['staff_status'];
        $notify_daily_sale = isset($staff['notify_daily_sale']) ? intval($staff['notify_daily_sale']) : 0;
    }
} else {
    $staff_role = $department = $gender = '';
    $seniority = 'Junior';
    $notify_daily_sale = 0;
}
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0;"><?= $user_action == 'create' ? 'Register New Stylist' : 'Edit Stylist Profile' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/user_ajax.php" method="post" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $user_action == 'create' ? 'create_staff' : 'update_staff' ?>">
    <?php if($user_action == 'edit') echo '<input name="staff_id" type="hidden" value="'.$staff_id.'">'; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div class="form-group" style="grid-column: span 2;">
            <label>Full Name</label>
            <input required name="staff_name" type="text" class="form-control" placeholder="Jane Doe" value="<?= htmlspecialchars($staff_name) ?>">
        </div>
        
        <div class="form-group">
            <label>Mobile Number</label>
            <input required name="staff_mob" type="text" class="form-control" placeholder="9876543210" value="<?= htmlspecialchars($staff_mob) ?>">
        </div>

        <div class="form-group">
            <label>Gender</label>
            <select name="gender" class="form-control">
                <option value="">Select...</option>
                <option value="Male" <?= $gender == 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $gender == 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $gender == 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label>Department</label>
            <input name="department" type="text" class="form-control" placeholder="e.g. Hair, Skin, Nails" value="<?= htmlspecialchars($department) ?>">
        </div>

        <div class="form-group">
            <label>Job Role</label>
            <input name="staff_role" type="text" class="form-control" placeholder="e.g. Hair Stylist, Makeup Artist" value="<?= htmlspecialchars($staff_role) ?>">
        </div>

        <div class="form-group">
            <label>Seniority Level</label>
            <select name="seniority" class="form-control">
                <option value="Junior" <?= $seniority == 'Junior' ? 'selected' : '' ?>>Junior</option>
                <option value="Senior" <?= $seniority == 'Senior' ? 'selected' : '' ?>>Senior</option>
                <option value="Master" <?= $seniority == 'Master' ? 'selected' : '' ?>>Master / Expert</option>
            </select>
        </div>

        <div class="form-group">
            <label>Joining Date</label>
            <input required name="joining_date" type="date" class="form-control" value="<?= htmlspecialchars($joining_date) ?>">
        </div>

        <div class="form-group">
            <label>Base Salary / Commission (%)</label>
            <div style="position: relative;">
                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 600; color: var(--text-muted);">₹</span>
                <input required name="staff_salary" type="text" class="form-control" style="padding-left: 36px;" placeholder="0.00" value="<?= htmlspecialchars($staff_salary) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Employment Status</label>
            <select required name="staff_status" class="form-control">
                <option value="1" <?= $staff_status == 1 ? 'selected' : '' ?>>Active / Working</option>
                <option value="0" <?= $staff_status == 0 ? 'selected' : '' ?>>Inactive / Let Go</option>
            </select>
        </div>

        <div class="form-group" style="grid-column: span 2;">
            <label style="display:flex; align-items:center; justify-content:space-between;">
                <span>Send Daily Sale Notification (WhatsApp)</span>
                <label class="toggle-switch" style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; margin:0;">
                    <input type="hidden" name="notify_daily_sale" value="0">
                    <input type="checkbox" name="notify_daily_sale" value="1" id="notify_daily_sale_chk" <?= $notify_daily_sale == 1 ? 'checked' : '' ?> style="display:none;">
                    <span id="toggle_knob" onclick="toggleNotify()" style="display:inline-flex; width:44px; height:24px; background:<?= $notify_daily_sale ? '#6366f1' : '#cbd5e1' ?>; border-radius:12px; padding:2px; transition:background 0.2s; cursor:pointer;">
                        <span style="width:20px; height:20px; background:white; border-radius:50%; transition:transform 0.2s; transform:<?= $notify_daily_sale ? 'translateX(20px)' : 'translateX(0)' ?>;"></span>
                    </span>
                    <span id="toggle_label" style="font-size:13px; font-weight:500; color:#64748b;"><?= $notify_daily_sale ? 'Enabled' : 'Disabled' ?></span>
                </label>
            </label>
            <p style="font-size:12px; color:#94a3b8; margin-top:4px;">Staff will receive their daily sales report via WhatsApp at end of day.</p>
        </div>

    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;"><?= $user_action == 'create' ? 'Add Stylist' : 'Save Changes' ?></button>
    </div>
</form>
<script>
function toggleNotify() {
    var chk = document.getElementById('notify_daily_sale_chk');
    var knob = document.getElementById('toggle_knob');
    var label = document.getElementById('toggle_label');
    chk.checked = !chk.checked;
    knob.style.background = chk.checked ? '#6366f1' : '#cbd5e1';
    knob.querySelector('span').style.transform = chk.checked ? 'translateX(20px)' : 'translateX(0)';
    label.textContent = chk.checked ? 'Enabled' : 'Disabled';
}
</script>


<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "header.php";

$permissiontype = array(
    "dashboard",
    "job_cards",
    "billing",
    "invoices",
    "expenses",
    "customer",
    "cataloge",
    "product",
    "inventory",
    "vendors",
    "purchase_bills",
    "vendor_ledger",
    "report",
    "membership_plans",
    "packages",
    "sell_membership",
    "sell_package",
    "membership_reports",
    "staff",
    "users",
    "payment_methods"
);
$roles_query = "SELECT * FROM `hr_user_role`";
if(isset($_GET['role_id']) && is_numeric($_GET['role_id'])){
    $roles_query .= " WHERE role_id = '".mysqli_real_escape_string($conn, $_GET['role_id'])."'";
}
$roles = select_array($roles_query);
?>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Role Permissions</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Define granular access controls and authorizations for different administrative roles.</p>
</div>

<div class="roles-matrix-container" style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 40px;">

    <?php foreach($roles as $role): 
        $role_id = $role['role_id'];
        $role_name = $role['role_name'];
        $raw_permissions = $role['role_permission'];
        $permissions = json_decode($raw_permissions, true) ?: [];
    ?>
    <div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
        
        <div class="role-header" style="padding: 20px 24px; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="ph-fill ph-shield-check"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 600; margin: 0;"><?= htmlspecialchars($role_name) ?></h3>
            </div>
            <i class="ph ph-caret-down toggle-icon" style="font-size: 20px; color: var(--text-muted); transition: transform 0.3s ease;"></i>
        </div>

        <div class="role-body" style="padding: 24px; <?= isset($_GET['role_id']) ? '' : 'display: none;' ?>">
            <form class="ajax-form" data-action-url="ajax/user_ajax.php" method="post">
                <input type="hidden" name="role_id" value="<?= $role_id; ?>" />
                <input type="hidden" name="method" value="group_permission_update" />
                
                <div class="table-responsive">
                    <table class="table-permissions" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 12px 16px; border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase;">Module</th>
                                <th style="text-align: center; padding: 12px 16px; border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase;">View</th>
                                <th style="text-align: center; padding: 12px 16px; border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase;">Create</th>
                                <th style="text-align: center; padding: 12px 16px; border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase;">Edit</th>
                                <th style="text-align: center; padding: 12px 16px; border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase;">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($permissiontype as $p_type): 
                                $mod_perms = isset($permissions[$p_type]) ? $permissions[$p_type] : ['view'=>0,'create'=>0,'edit'=>0,'delete'=>0];
                            ?>
                            <tr>
                                <td style="padding: 16px; border-bottom: 1px solid var(--border-color); font-weight: 500; text-transform: capitalize;">
                                    <?= str_replace('_', ' ', $p_type) ?>
                                </td>
                                
                                <?php foreach(['view', 'create', 'edit', 'delete'] as $action): ?>
                                <td style="padding: 16px; border-bottom: 1px solid var(--border-color); text-align: center;">
                                    <label class="custom-checkbox">
                                        <input type="checkbox" name="data[<?= $p_type ?>][<?= $action ?>]" value="1" <?= (isset($mod_perms[$action]) && $mod_perms[$action] == 1) ? 'checked' : '' ?>>
                                        <span class="checkmark"></span>
                                    </label>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-primary" style="width: auto; padding: 10px 24px; display: flex; align-items: center; gap: 8px;">
                        <i class="ph-bold ph-floppy-disk"></i> Save Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<style>
/* Custom styled checkboxes for the permissions matrix */
.custom-checkbox {
    display: inline-block;
    position: relative;
    padding-left: 24px;
    cursor: pointer;
    user-select: none;
}
.custom-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}
.checkmark {
    position: absolute;
    top: -8px;
    left: 0;
    height: 20px;
    width: 20px;
    background-color: transparent;
    border: 2px solid var(--border-color);
    border-radius: 6px;
    transition: all 0.2s ease;
}
.custom-checkbox:hover input ~ .checkmark {
    border-color: var(--primary-light);
}
.custom-checkbox input:checked ~ .checkmark {
    background-color: var(--primary);
    border-color: var(--primary);
}
.checkmark:after {
    content: "";
    position: absolute;
    display: none;
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
.custom-checkbox input:checked ~ .checkmark:after {
    display: block;
}

.table-permissions tbody tr:hover {
    background: #f8fafc;
}

.role-header.open .toggle-icon {
    transform: rotate(180deg);
}
</style>

<script>
$(document).ready(function(){
    $('.role-header').click(function(){
        $(this).toggleClass('open');
        $(this).next('.role-body').slideToggle(300);
    });

    // Default open state processing if URL parameter exists
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('role_id')) {
        $('.role-header').addClass('open').find('.toggle-icon').css('transform', 'rotate(180deg)');
    }

    $(document).on('submit', 'form.ajax-form', function(e){
        e.preventDefault();
        var form = $(this);
        var targetUrl = form.attr('data-action-url');
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);

        $.ajax({
            type: "POST", url: targetUrl, data: form.serialize(),
            success: function(res) {
                var obj = JSON.parse(res);
                if (obj.error == 1) {
                    alert("Error: " + obj.msg);
                    submitBtn.html(originalText).prop('disabled', false);
                } else {
                    alert("Permissions Updated Successfully!");
                    submitBtn.html(originalText).prop('disabled', false);
                }
            }
        });
    });
});
</script>

<?php include "footer.php"; ?>
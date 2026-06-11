<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config.php";
include "function.php";

$salon_action = "create";
$salon_name = $salon_address = $salon_contact = $whatsapp_api = $whatsapp_api_url = $whatsapp_sender = $salon_gst = $make_webhook_url = $logo = "";
$gst_percentage = "18.00";
$gst_enable = 0;
$include_gst = 0;
$whatsapp_enable = 0;
$make_enable = 0;
$salon_status = 1;
$round_off = 0;
$google_review_link = "";

if(isset($_REQUEST['salon_id']) && is_numeric($_REQUEST['salon_id'])){
    $salon_action = "edit";
    $sql = "SELECT * FROM `hr_salon` WHERE `salon_id`='".mysqli_real_escape_string($conn, $_REQUEST['salon_id'])."'";
    $salon = select_row($sql);
    if($salon) {
        $salon_id = $salon['salon_id'];
        $salon_name = $salon['salon_name'];
        $salon_address = $salon['salon_address'];
        $salon_contact = $salon['salon_contact'];
        $salon_gst = $salon['salon_gst'];
        $gst_percentage = $salon['gst_percentage'];
        $gst_enable = $salon['gst_enable'];
        $include_gst = $salon['include_gst'];
        $whatsapp_enable = $salon['whatsapp_enable'] ?? 0;
        $whatsapp_api = $salon['whatsapp_api'];
        $whatsapp_api_url = $salon['whatsapp_api_url'] ?? '';
        $whatsapp_sender = $salon['whatsapp_sender'] ?? '';
        $make_enable = $salon['make_enable'] ?? 0;
        $make_webhook_url = $salon['make_webhook_url'] ?? '';
        $salon_status = $salon['salon_status'];
        $round_off = $salon['round_off'] ?? 0;
        $google_review_link = $salon['google_review_link'] ?? '';
        $logo = $salon['logo'] ?? '';
    }
}
?>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
    <h3 style="font-size: 18px; font-weight: 600; margin: 0;"><?= $salon_action == 'create' ? 'Register New Outlet' : 'Edit Outlet Configuration' ?></h3>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
</div>

<form class="ajax-form" data-action-url="ajax/salon_ajax.php" method="post" enctype="multipart/form-data" style="padding: 24px;">
    
    <input name="method" type="hidden" value="<?= $salon_action == 'create' ? 'create_salon' : 'update_salon' ?>">
    <?php if($salon_action == 'edit') echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">'; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <!-- Logo Upload Section -->
        <div class="form-group" style="grid-column: span 2; display: flex; align-items: center; gap: 24px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed var(--border-color); margin-bottom: 8px;">
            <div style="position: relative; width: 100px; height: 80px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <?php if(!empty($logo) && file_exists($logo)): ?>
                    <img id="logo-preview" src="<?= htmlspecialchars($logo) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    <div id="logo-placeholder" style="font-size: 32px; color: #94a3b8; display: none;">
                        <i class="ph ph-storefront"></i>
                    </div>
                <?php else: ?>
                    <img id="logo-preview" src="" style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;">
                    <div id="logo-placeholder" style="font-size: 32px; color: #94a3b8; display: flex; align-items: center; justify-content: center;">
                        <i class="ph ph-storefront"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; font-size: 14px; margin-bottom: 4px; display: block;">Outlet Brand Logo</label>
                <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 12px; line-height: 1.4;">Upload a brand logo for this branch. Recommended square aspect ratio (1:1), PNG/JPG/WEBP format.</p>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <label class="btn-primary" style="width: auto; margin: 0; padding: 8px 16px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; background: var(--primary);">
                        <i class="ph ph-upload-simple" style="font-size: 16px;"></i> Upload Photo
                        <input type="file" name="logo" id="logo-input" accept="image/*" style="display: none;">
                    </label>
                    <button type="button" id="btn-remove-logo" class="btn-deactivate" style="padding: 8px 16px; font-size: 13px; display: <?= !empty($logo) ? 'inline-flex' : 'none' ?>; align-items: center; gap: 6px; border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444;">
                        <i class="ph ph-trash" style="font-size: 16px;"></i> Remove
                    </button>
                    <input type="hidden" name="remove_logo" id="remove-logo-flag" value="0">
                </div>
            </div>
        </div>
        
        <div class="form-group" style="grid-column: span 2;">
            <label>Outlet Name</label>
            <input required name="salon_name" type="text" class="form-control" placeholder="e.g. Elegance Studio - Downtown" value="<?= htmlspecialchars($salon_name) ?>">
        </div>
        
        <div class="form-group" style="grid-column: span 2;">
            <label>Physical Address</label>
            <textarea required name="salon_address" class="form-control" style="resize: vertical; min-height: 80px;" placeholder="Full street address..."><?= htmlspecialchars($salon_address) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Contact Mobile</label>
            <input required name="salon_contact" type="text" class="form-control" value="<?= htmlspecialchars($salon_contact) ?>" placeholder="+91">
        </div>

        <div class="form-group">
            <label>Outlet Status</label>
            <select required name="salon_status" class="form-control">
                <option value="1" <?= $salon_status == 1 ? 'selected' : '' ?>>Active & Operating</option>
                <option value="0" <?= $salon_status == 0 ? 'selected' : '' ?>>Inactive / Closed</option>
            </select>
        </div>

        <div class="form-group" style="grid-column: span 2; padding: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; margin-top: 10px; margin-bottom: 10px;">
            <h4 style="margin: 0 0 16px 0; font-size: 15px; color: #166534;"><i class="ph ph-whatsapp-logo" style="vertical-align: middle;"></i> WhatsApp API Configuration</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>WhatsApp Notification</label>
                    <select name="whatsapp_enable" class="form-control">
                        <option value="0" <?= $whatsapp_enable == 0 ? 'selected' : '' ?>>Disabled</option>
                        <option value="1" <?= $whatsapp_enable == 1 ? 'selected' : '' ?>>Enabled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>API Endpoint URL</label>
                    <input name="whatsapp_api_url" type="url" class="form-control" value="<?= htmlspecialchars($whatsapp_api_url) ?>" placeholder="https://whatsapp.tressloungetdi.com/send-button">
                </div>

                <div class="form-group">
                    <label>API Key</label>
                    <input name="whatsapp_api" type="text" class="form-control" value="<?= htmlspecialchars($whatsapp_api) ?>" placeholder="Your API Key">
                </div>

                <div class="form-group">
                    <label>Sender Number</label>
                    <input name="whatsapp_sender" type="text" class="form-control" value="<?= htmlspecialchars($whatsapp_sender) ?>" placeholder="Your device sender ID">
                </div>
            </div>
            <small style="display:block; margin-top:8px; color: #15803d;">If enabled, bills will automatically trigger a WhatsApp message with View Receipt and Give Feedback buttons.</small>
        </div>

        <div class="form-group">
            <label>Make.com Bill Notification</label>
            <select name="make_enable" class="form-control">
                <option value="0" <?= $make_enable == 0 ? 'selected' : '' ?>>Disabled</option>
                <option value="1" <?= $make_enable == 1 ? 'selected' : '' ?>>Enabled</option>
            </select>
            <small style="color:var(--text-muted); display:block; margin-top:4px;">Send bill details to Make.com webhook on every invoice.</small>
        </div>

        <div class="form-group" style="grid-column: span 2;">
            <label>Make.com Webhook URL <small style="color:var(--text-muted);">(Paste your hook.us2.make.com URL)</small></label>
            <input name="make_webhook_url" type="url" class="form-control"
                   value="<?= htmlspecialchars($make_webhook_url) ?>"
                   placeholder="https://hook.us2.make.com/xxxxxxxxxxxxxx">
        </div>

        <div class="form-group" style="grid-column: span 2;">
            <label>Google Review Link <small style="color:var(--text-muted);">(Used to redirect highly satisfied customers)</small></label>
            <input name="google_review_link" type="url" class="form-control"
                   value="<?= htmlspecialchars($google_review_link) ?>"
                   placeholder="https://g.page/r/XXXXXXXXXXXX/review">
        </div>

        <div class="form-group">
            <label>Enable GST Tracking?</label>
            <select name="gst_enable" class="form-control">
                <option value="0" <?= $gst_enable == 0 ? 'selected' : '' ?>>Disabled</option>
                <option value="1" <?= $gst_enable == 1 ? 'selected' : '' ?>>Enabled</option>
            </select>
        </div>

        <div class="form-group">
            <label>Prices Include GST?</label>
            <select name="include_gst" class="form-control">
                <option value="0" <?= $include_gst == 0 ? 'selected' : '' ?>>Base + GST Applies Extra</option>
                <option value="1" <?= $include_gst == 1 ? 'selected' : '' ?>>Inclusive (Inside listed prices)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Auto Round-Off Final Bill?</label>
            <select name="round_off" class="form-control">
                <option value="0" <?= $round_off == 0 ? 'selected' : '' ?>>No (Exact decimals)</option>
                <option value="1" <?= $round_off == 1 ? 'selected' : '' ?>>Yes (Nearest whole number)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Registered GST Number</label>
            <input name="salon_gst" type="text" class="form-control" value="<?= htmlspecialchars($salon_gst) ?>" placeholder="22AAAAA0000A1Z5">
        </div>

        <div class="form-group">
            <label>GST Rate (%)</label>
            <div style="position: relative;">
                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 600; color: var(--text-muted);">%</span>
                <input name="gst_percentage" type="text" class="form-control" style="padding-left: 36px;" value="<?= htmlspecialchars($gst_percentage) ?>" placeholder="18.00">
            </div>
        </div>

    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 24px;">Save Outlet Details</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#logo-input').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#logo-preview').attr('src', e.target.result).show();
                $('#logo-placeholder').hide();
                $('#remove-logo-flag').val('0');
                $('#btn-remove-logo').css('display', 'inline-flex');
            }
            reader.readAsDataURL(file);
        }
    });

    $('#btn-remove-logo').on('click', function(e) {
        e.preventDefault();
        $('#logo-preview').attr('src', '').hide();
        $('#logo-placeholder').show();
        $('#logo-input').val('');
        $('#remove-logo-flag').val('1');
        $(this).hide();
    });
});
</script>

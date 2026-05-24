<?php
// Membership Plan Add/Edit Form (loads in modal)
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'config.php';
include_once 'function.php';
$salon_id = get_session_data('salon_id');
$plan_id = intval($_GET['plan_id'] ?? 0);
$plan = $plan_id ? select_row("SELECT * FROM hr_membership_plans WHERE plan_id='$plan_id'") : [];
$validity_months = $plan ? round($plan['validity_days'] / 30) : 3;
// Outlet GST
$salon_info = select_row("SELECT gst_percentage, gst_enable FROM hr_salon WHERE salon_id='$salon_id'");
$outlet_gst  = $salon_info ? floatval($salon_info['gst_percentage']) : 0;
$gst_enabled = $salon_info ? intval($salon_info['gst_enable']) : 0;
?>
<div style="padding:24px 28px 8px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h3 style="font-size:17px;font-weight:700;margin:0;color:var(--text-main);">
            <i class="ph-fill ph-identification-badge" style="color:var(--primary);margin-right:8px;"></i>
            <?= $plan_id ? 'Edit Membership Plan' : 'New Membership Plan' ?>
        </h3>
        <button type="button" class="close-modal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-muted);"><i class="ph ph-x"></i></button>
    </div>
</div>

<form class="ajax-form" data-action-url="ajax/membership_ajax.php" style="padding:0 28px 28px;">
    <input type="hidden" name="method" value="<?= $plan_id ? 'update_membership_plan' : 'create_membership_plan' ?>">
    <?php if($plan_id): ?><input type="hidden" name="plan_id" value="<?= $plan_id ?>"><<?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group" style="grid-column:1/-1;">
            <label>Plan Name <span style="color:var(--danger);">*</span></label>
            <input type="text" name="plan_name" class="form-control" required placeholder="e.g. Gold Membership"
                value="<?= htmlspecialchars($plan['plan_name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Membership Price (₹) <span style="color:var(--danger);">*</span></label>
            <div style="position:relative;">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--text-muted);">₹</span>
                <input type="number" name="plan_price" class="form-control" required min="1" step="any" placeholder="3000"
                    value="<?= $plan['plan_price'] ?? '' ?>" style="padding-left:32px;">
            </div>
            <small style="color:var(--text-muted);font-size:12px;">Amount customer pays to buy this plan</small>
        </div>

        <div class="form-group">
            <label>Wallet Credit Value (₹) <span style="color:var(--danger);">*</span></label>
            <div style="position:relative;">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--success);">₹</span>
                <input type="number" name="wallet_credit" class="form-control" required min="1" step="any" placeholder="5000"
                    value="<?= $plan['wallet_credit'] ?? '' ?>" style="padding-left:32px;">
            </div>
            <small style="color:var(--text-muted);font-size:12px;">Wallet balance credited to customer on activation</small>
        </div>

        <div class="form-group">
            <label>Validity (Months) <span style="color:var(--danger);">*</span></label>
            <select name="validity_days" class="form-control">
                <?php foreach([1,2,3,6,12] as $m): ?>
                    <option value="<?= $m ?>" <?= $validity_months == $m ? 'selected' : '' ?>>
                        <?= $m ?> Month<?= $m > 1 ? 's' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1" <?= ($plan['status'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= ($plan['status'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <!-- GST -->
        <div class="form-group" style="grid-column:1/-1;">
            <div style="display:flex;align-items:center;gap:12px;">
                <label style="margin:0;display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="hidden" name="gst_applicable" value="0">
                    <input type="checkbox" name="gst_applicable" id="gst_applicable_plan" value="1"
                        <?= !empty($plan['gst_applicable']) ? 'checked' : '' ?> style="width:18px;height:18px;">
                    <span>Apply GST on membership sale</span>
                </label>
            </div>
        </div>

        <div class="form-group" id="gst_percent_wrap_plan" style="<?= empty($plan['gst_applicable']) ? 'display:none;' : '' ?>">
            <label>GST % <small style="color:var(--text-muted);">(outlet default: <?= $outlet_gst ?>%)</small></label>
            <input type="number" name="gst_percent" class="form-control" step="0.01" min="0" max="100"
                value="<?= $plan_id ? ($plan['gst_percent'] ?? $outlet_gst) : $outlet_gst ?>" placeholder="<?= $outlet_gst ?>">
        </div>

        <div class="form-group" style="grid-column:1/-1;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="hidden" name="gst_on_service" value="0">
                <input type="checkbox" name="gst_on_service" value="1" style="width:18px;height:18px;"
                    <?= ($plan_id ? ($plan['gst_on_service'] ?? 1) : 1) ? 'checked' : '' ?>>
                <span>Charge GST on services when paying via this Wallet <small style="color:var(--text-muted);">(uncheck if GST is already collected upfront on membership sale)</small></span>
            </label>
        </div>

        <div class="form-group" style="grid-column:1/-1;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="hidden" name="allow_discount" value="0">
                <input type="checkbox" name="allow_discount" value="1" style="width:18px;height:18px;"
                    <?= !empty($plan['allow_discount']) ? 'checked' : '' ?>>
                <span>Allow additional discounts when paying via Wallet <small style="color:var(--text-muted);">(uncheck to prevent wallet+discount stacking)</small></span>
            </label>
        </div>

        <div class="form-group" style="grid-column:1/-1;">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="2" placeholder="Optional plan details..."><?= htmlspecialchars($plan['description'] ?? '') ?></textarea>
        </div>
    </div>

    <div style="margin-top:8px;padding:16px;background:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0;" id="savings_preview_plan">
        <div style="font-size:13px;color:var(--text-muted);">Customer pays <strong id="pp_price">—</strong>, gets <strong id="pp_credit" style="color:#059669;">—</strong> in wallet.
        That's <strong id="pp_savings" style="color:#059669;">—</strong> extra value!</div>
    </div>

    <div style="margin-top:20px;display:flex;gap:12px;justify-content:flex-end;">
        <button type="button" class="close-modal" style="background:#f1f5f9;color:var(--text-main);border:none;padding:12px 24px;border-radius:10px;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" class="btn-primary" style="width:auto;padding:12px 28px;margin:0;">
            <i class="ph ph-floppy-disk"></i> <?= $plan_id ? 'Update Plan' : 'Create Plan' ?>
        </button>
    </div>
</form>

<script>
$('#gst_applicable_plan').change(function(){ $('#gst_percent_wrap_plan').toggle(this.checked); });
function updatePlanPreview() {
    var p = parseFloat($('input[name=plan_price]').val()) || 0;
    var c = parseFloat($('input[name=wallet_credit]').val()) || 0;
    $('#pp_price').text('₹' + p.toLocaleString('en-IN'));
    $('#pp_credit').text('₹' + c.toLocaleString('en-IN'));
    var savings = c - p;
    $('#pp_savings').text(savings > 0 ? '₹' + savings.toLocaleString('en-IN') : '₹0');
}
$('input[name=plan_price], input[name=wallet_credit]').on('keyup change', updatePlanPreview);
updatePlanPreview();
</script>

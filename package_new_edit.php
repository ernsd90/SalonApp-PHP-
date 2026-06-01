<?php
// Package Add/Edit Form - loads in modal
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'config.php';
include_once 'function.php';

$salon_id = get_session_data('salon_id');
$pkg_id   = intval($_GET['pkg_id'] ?? 0);
$pkg      = $pkg_id ? select_row("SELECT * FROM hr_packages_new WHERE pkg_id='$pkg_id' AND salon_id='$salon_id'") : [];
$pkg_items = $pkg_id ? select_array("SELECT * FROM hr_package_items WHERE pkg_id='$pkg_id'") : [];
$validity_months = $pkg ? round($pkg['validity_days'] / 30) : 3;

// Outlet GST
$salon_info = select_row("SELECT gst_percentage, gst_enable, salon_gst, firm_name FROM hr_salon WHERE salon_id='$salon_id'");
$outlet_gst  = $salon_info ? floatval($salon_info['gst_percentage']) : 0;
$gst_enabled = $salon_info ? intval($salon_info['gst_enable']) : 0;

// Existing items for edit mode — we'll pass them as JSON to JS for re-population
$existing_items_json = '[]';
if ($pkg_items) {
    $existing_items_json = json_encode(array_values(array_map(function($item) {
        return [
            'service_id'    => (int)$item['service_id'],
            'service_price' => (float)$item['service_price'],
            'quantity'      => (int)$item['quantity'],
        ];
    }, $pkg_items)));
}
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
.pkg-qty-input { width:70px;text-align:center;padding:6px; }
.service-row-table { width:100%;border-collapse:collapse; }
.service-row-table th { padding:10px 12px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);text-align:left;background:#f8fafc; }
.service-row-table td { padding:8px 12px;border-bottom:1px solid var(--border-color);vertical-align:middle; }
.service-row-table tbody tr:hover td { background:#fafbfc; }
/* Select2 inside modal */
.select2-container { width:100% !important; }
.select2-container--default .select2-selection--single {
    height:42px !important;
    border:1px solid var(--border-color) !important;
    border-radius:8px !important;
    padding:5px 12px !important;
    font-size:13px;
    background:#f9fafb;
    display:flex; align-items:center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height:1.4 !important; padding:0 !important; font-size:13px; color:var(--text-main); }
.select2-container--default .select2-selection--single .select2-selection__arrow { top:9px !important; right:10px !important; }
.select2-dropdown { border:1px solid var(--border-color) !important; border-radius:10px !important; box-shadow:0 8px 24px rgba(0,0,0,.12) !important; overflow:hidden; }
.select2-search--dropdown { padding:10px !important; }
.select2-search--dropdown .select2-search__field { border:1px solid var(--border-color) !important; border-radius:8px !important; padding:8px 12px !important; font-size:13px; width:100%; }
.select2-results__option { padding:9px 14px !important; font-size:13px; }
.select2-results__option--highlighted { background:var(--primary) !important; color:white !important; }
.select2-results__group { padding:6px 14px !important; font-size:11px !important; font-weight:700 !important; text-transform:uppercase; color:var(--text-muted) !important; letter-spacing:.5px; background:#f8fafc !important; }
/* Variation select */
.var-select-wrap { display:flex; align-items:center; gap:6px; min-width:160px; }
.var-select-wrap select.form-control { font-size:13px; padding:6px 10px; }
.no-var-badge { font-size:11px; color:var(--text-muted); font-style:italic; white-space:nowrap; }
</style>

<div style="padding:22px 26px 6px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="font-size:17px;font-weight:700;margin:0;">
            <i class="ph-fill ph-package" style="color:var(--primary);margin-right:8px;"></i>
            <?= $pkg_id ? 'Edit Package' : 'New Service Package' ?>
        </h3>
        <button type="button" class="close-modal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-muted);line-height:1;"><i class="ph ph-x"></i></button>
    </div>
</div>

<form class="ajax-form" data-action-url="ajax/membership_ajax.php" id="pkg_form_main" style="padding:0 26px 26px;">
    <input type="hidden" name="method" value="<?= $pkg_id ? 'update_package_new' : 'create_package_new' ?>">
    <?php if($pkg_id): ?><input type="hidden" name="pkg_id" value="<?= $pkg_id ?>"><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
        <div class="form-group" style="grid-column:1/-1;margin-bottom:0;">
            <label>Package Name <span style="color:var(--danger);">*</span></label>
            <input type="text" name="package_name" class="form-control" required placeholder="e.g. 10 Haircuts Bundle"
                value="<?= htmlspecialchars($pkg['package_name'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label>Validity</label>
            <select name="validity_months" class="form-control">
                <?php foreach([1,2,3,6,12] as $m): ?>
                    <option value="<?= $m ?>" <?= $validity_months==$m?'selected':'' ?>><?= $m ?> Month<?= $m>1?'s':'' ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1" <?= ($pkg['status']??1)==1?'selected':'' ?>>Active</option>
                <option value="0" <?= ($pkg['status']??1)==0?'selected':'' ?>>Inactive</option>
            </select>
        </div>

        <!-- GST (from outlet, pre-filled but editable) -->
        <div class="form-group" style="margin-bottom:0;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="hidden" name="gst_applicable" value="0">
                <input type="checkbox" name="gst_applicable" id="gst_pkg_chk" value="1" style="width:16px;height:16px;"
                    <?= (!empty($pkg['gst_applicable']) || (!$pkg_id && $gst_enabled)) ? 'checked' : '' ?>>
                <span>Apply GST</span>
            </label>
        </div>

        <div class="form-group" id="gst_pkg_wrap" style="margin-bottom:0;<?= (empty($pkg['gst_applicable']) && ($pkg_id || !$gst_enabled)) ? 'display:none;' : '' ?>">
            <label>GST % <small style="color:var(--text-muted);">(outlet default: <?= $outlet_gst ?>%)</small></label>
            <input type="number" name="gst_percent" class="form-control" step="0.01" min="0" max="100"
                value="<?= $pkg_id ? ($pkg['gst_percent']??$outlet_gst) : $outlet_gst ?>" placeholder="<?= $outlet_gst ?>">
        </div>

        <div class="form-group" style="grid-column:1/-1;margin-bottom:0;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="hidden" name="allow_discount" value="0">
                <input type="checkbox" name="allow_discount" value="1" style="width:16px;height:16px;"
                    <?= !empty($pkg['allow_discount'])?'checked':'' ?>>
                <span style="font-size:14px;">Allow additional discounts when using package sessions</span>
            </label>
        </div>
    </div>

    <!-- Services Table -->
    <div style="margin-top:4px;margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <label style="font-weight:600;font-size:14px;margin:0;">Services Included <span style="color:var(--danger);">*</span>
                <small style="color:var(--text-muted);font-weight:400;font-size:12px;"> — add one row per service</small>
            </label>
            <button type="button" id="btn_add_svc_row" style="background:var(--primary);color:white;border:none;padding:7px 16px;border-radius:8px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;font-weight:600;">
                <i class="ph ph-plus"></i> Add Service
            </button>
        </div>
        <div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
            <table class="service-row-table">
                <thead>
                    <tr>
                        <th style="min-width:200px;">Service</th>
                        <th style="min-width:150px;">Variation</th>
                        <th style="width:80px;text-align:center;">Qty</th>
                        <th style="width:110px;text-align:right;">Unit Price</th>
                        <th style="width:110px;text-align:right;">Subtotal</th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="svc_rows_body">
                    <!-- rows injected by JS after services load -->
                </tbody>
            </table>
        </div>
        <!-- Loading / error state -->
        <div id="svc_loading" style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px;">
            <i class="ph ph-spinner ph-spin" style="font-size:20px;"></i> Loading services…
        </div>
    </div>

    <!-- Totals -->
    <div style="background:#f8fafc;border-radius:12px;padding:16px 18px;border:1px solid var(--border-color);margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:10px;">
            <span style="color:var(--text-muted);">MRP Total (auto-calculated):</span>
            <strong id="pkg_mrp_display">₹0.00</strong>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;margin-bottom:10px;">
            <label style="color:var(--text-muted);margin:0;">Selling Price (₹): <span style="color:var(--danger);">*</span></label>
            <div style="position:relative;width:160px;">
                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--text-muted);">₹</span>
                <input type="number" name="selling_price" id="pkg_selling_price" class="form-control" required
                    min="0" step="any" placeholder="0"
                    value="<?= $pkg['selling_price'] ?? '' ?>" style="padding-left:28px;text-align:right;">
            </div>
        </div>
        <div style="height:1px;background:var(--border-color);margin:8px 0;"></div>
        <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:700;color:#059669;">
            <span>Customer Saves:</span>
            <span id="pkg_savings_display">₹0.00</span>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button type="button" class="close-modal" style="background:#f1f5f9;color:var(--text-main);border:none;padding:12px 22px;border-radius:10px;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" id="pkg_submit_btn" class="btn-primary" style="width:auto;padding:12px 26px;margin:0;" disabled>
            <i class="ph ph-floppy-disk"></i> <?= $pkg_id ? 'Update Package' : 'Create Package' ?>
        </button>
    </div>
</form>

<script>
$('#gst_pkg_chk').change(function(){ $('#gst_pkg_wrap').toggle(this.checked); });

// ── Data store (loaded fresh via AJAX) ─────────────────────────
var pkgServices   = [];  // [{service_id, service_name, service_price, service_catName, variations:[{var_id,var_name,var_price}]}]
var servicesReady = false;

// ── Existing items from edit mode ──────────────────────────────
var existingItems = <?= $existing_items_json ?>;

// ── Load services with variations via AJAX ─────────────────────
$.ajax({
    url: 'ajax/membership_ajax.php',
    type: 'POST',
    data: { method: 'get_services_with_variations' },
    success: function(res) {
        try {
            pkgServices = JSON.parse(res);
        } catch(e) { pkgServices = []; }
        servicesReady = true;
        $('#svc_loading').hide();
        $('#pkg_submit_btn').prop('disabled', false);

        // Populate existing rows (edit mode) or add a blank row (new mode)
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(function(item) {
                addSvcRow(item.service_id, item.quantity, item.service_price);
            });
        } else {
            addSvcRow();
        }
        recalcTotals();
    },
    error: function() {
        $('#svc_loading').html('<span style="color:var(--danger);"><i class="ph ph-warning"></i> Failed to load services. Please close and reopen.</span>');
    }
});

// ── Build select options HTML for a given selected service_id ──
function buildServiceOptions(selected_id) {
    var html = '<option value="">— Select Service —</option>';
    var lastCat = '';
    pkgServices.forEach(function(s) {
        var cat = s.service_catName || 'General';
        if (cat !== lastCat) {
            if (lastCat !== '') html += '</optgroup>';
            html += '<optgroup label="' + escHtml(cat) + '">';
            lastCat = cat;
        }
        html += '<option value="' + s.service_id + '"' +
            (selected_id == s.service_id ? ' selected' : '') +
            '>' + escHtml(s.service_name) + ' (₹' + parseInt(s.service_price).toLocaleString('en-IN') + ')</option>';
    });
    if (lastCat !== '') html += '</optgroup>';
    return html;
}

// ── Build variation dropdown for a service ─────────────────────
// Returns the HTML for the variation cell content.
// selectedVarId: pre-select a var (edit mode)
// selectedPrice: the saved price, used to find the matching var
function buildVariationCell(service_id, selectedVarId, savedPrice) {
    var svc = pkgServices.find(function(s){ return s.service_id == service_id; });
    if (!svc || !svc.variations || svc.variations.length === 0) {
        return '<input type="hidden" name="var_id[]" value="0">' +
               '<input type="hidden" name="var_unit_price[]" value="' + (svc ? svc.service_price : 0) + '">' +
               '<span class="no-var-badge">— No variations</span>';
    }
    // Try to auto-select the variation by saved price if no var_id saved
    var preselect = selectedVarId || 0;
    if (!preselect && savedPrice) {
        var found = svc.variations.find(function(v){ return Math.abs(v.var_price - savedPrice) < 0.01; });
        if (found) preselect = found.var_id;
    }

    var html = '<div class="var-select-wrap">' +
               '<select name="var_id[]" class="form-control var-select" data-service-id="' + service_id + '" style="font-size:13px;">' +
               '<option value="0">— Base price —</option>';
    svc.variations.forEach(function(v) {
        html += '<option value="' + v.var_id + '" data-price="' + v.var_price + '"' +
            (preselect == v.var_id ? ' selected' : '') +
            '>' + escHtml(v.var_name) + ' — ₹' + v.var_price.toLocaleString('en-IN', {minimumFractionDigits:2}) + '</option>';
    });
    html += '</select>' +
            '<input type="hidden" name="var_unit_price[]" class="var-unit-price-hidden" value="">' +
            '</div>';
    return html;
}

// ── Add a service row ──────────────────────────────────────────
function addSvcRow(selected_id, qty, savedPrice) {
    selected_id = selected_id || 0;
    qty         = qty || 1;

    var tr = $('<tr class="svc-row"></tr>');

    // Service cell (Select2)
    var $sel = $('<select name="service_id[]" class="form-control svc-select"></select>')
                    .html(buildServiceOptions(selected_id));
    var $tdSvc = $('<td></td>').append($sel);
    tr.append($tdSvc);

    // Variation cell (will be populated on service change)
    var $tdVar = $('<td class="var-cell"></td>');
    tr.append($tdVar);

    // Qty
    tr.append($('<td></td>').append(
        '<input type="number" name="qty[]" class="form-control svc-qty pkg-qty-input" min="1" value="' + qty + '">'
    ));

    // Unit price (display only)
    tr.append($('<td style="text-align:right;"><span class="svc-unit-price" style="font-weight:600;">₹0.00</span></td>'));

    // Subtotal (display only)
    tr.append($('<td style="text-align:right;"><span class="svc-subtotal" style="font-weight:700;color:var(--primary);">₹0.00</span></td>'));

    // Remove button
    tr.append($('<td></td>').append(
        '<button type="button" class="svc-remove" style="background:#fee2e2;color:#dc2626;border:none;width:28px;height:28px;border-radius:6px;cursor:pointer;"><i class="ph ph-trash"></i></button>'
    ));

    $('#svc_rows_body').append(tr);

    // Initialize Select2 on this row's service select
    $sel.select2({
        placeholder: '— Select Service —',
        allowClear: false,
        dropdownParent: $('#pkgModalContent'),
        width: '100%'
    });

    // If pre-selected (edit mode), populate variation cell now
    if (selected_id) {
        var varHtml = buildVariationCell(selected_id, 0, savedPrice);
        $tdVar.html(varHtml);
        // Sync price
        updateRowPrice(tr, selected_id, 0, savedPrice);
    } else {
        $tdVar.html('<span class="no-var-badge" style="color:var(--text-muted);font-size:12px;font-style:italic;">— pick service first</span>');
    }
}

// ── Update row price display ───────────────────────────────────
function updateRowPrice(tr, service_id, var_id, savedPrice) {
    var svc = pkgServices.find(function(s){ return s.service_id == service_id; });
    if (!svc) { recalcTotals(); return; }

    var price = svc.service_price;
    if (var_id && svc.variations) {
        var found = svc.variations.find(function(v){ return v.var_id == var_id; });
        if (found) price = found.var_price;
    }
    // If savedPrice given and no var match, use savedPrice
    if (!var_id && savedPrice && savedPrice > 0) price = savedPrice;

    tr.find('.svc-unit-price').text('₹' + parseFloat(price).toFixed(2));
    // Update hidden var_unit_price field
    tr.find('.var-unit-price-hidden').val(price);
    var qty = parseInt(tr.find('.svc-qty').val()) || 1;
    tr.find('.svc-subtotal').text('₹' + (price * qty).toFixed(2));
    recalcTotals();
}

// ── Service select change ──────────────────────────────────────
$(document).on('change', '.svc-select', function() {
    var tr         = $(this).closest('tr');
    var service_id = parseInt($(this).val()) || 0;
    var $tdVar     = tr.find('.var-cell');

    if (!service_id) {
        $tdVar.html('<span class="no-var-badge">— pick service first</span>');
        tr.find('.svc-unit-price').text('₹0.00');
        tr.find('.svc-subtotal').text('₹0.00');
        recalcTotals();
        return;
    }

    var varHtml = buildVariationCell(service_id, 0, 0);
    $tdVar.html(varHtml);
    updateRowPrice(tr, service_id, 0, 0);
});

// ── Variation change ───────────────────────────────────────────
$(document).on('change', '.var-select', function() {
    var tr         = $(this).closest('tr');
    var service_id = parseInt($(this).data('service-id')) || 0;
    var var_id     = parseInt($(this).val()) || 0;
    updateRowPrice(tr, service_id, var_id, 0);
    // Update hidden price
    var price = parseFloat($(this).find(':selected').data('price')) || 0;
    if (!var_id || !price) {
        var svc = pkgServices.find(function(s){ return s.service_id == service_id; });
        price = svc ? svc.service_price : 0;
    }
    tr.find('.var-unit-price-hidden').val(price);
});

// ── Qty change ─────────────────────────────────────────────────
$(document).on('input change', '.svc-qty', function() {
    var tr    = $(this).closest('tr');
    var price = parseFloat(tr.find('.svc-unit-price').text().replace('₹','')) || 0;
    var qty   = parseInt($(this).val()) || 1;
    tr.find('.svc-subtotal').text('₹' + (price * qty).toFixed(2));
    recalcTotals();
});

// ── Remove row ─────────────────────────────────────────────────
$(document).on('click', '.svc-remove', function() {
    if ($('#svc_rows_body tr').length <= 1) { alert('At least one service is required.'); return; }
    // Destroy select2 on removed row to prevent memory leak
    var $sel = $(this).closest('tr').find('.svc-select');
    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
    $(this).closest('tr').remove();
    recalcTotals();
});

// ── Add row button ─────────────────────────────────────────────
$('#btn_add_svc_row').on('click', function() {
    if (!servicesReady) { alert('Services are still loading, please wait.'); return; }
    addSvcRow();
    recalcTotals();
});

// ── Recalculate totals ─────────────────────────────────────────
$('#pkg_selling_price').on('input', recalcTotals);

function recalcTotals() {
    var mrp = 0;
    $('.svc-subtotal').each(function() { mrp += parseFloat($(this).text().replace('₹','')) || 0; });
    $('#pkg_mrp_display').text('₹' + mrp.toFixed(2));
    var selling  = parseFloat($('#pkg_selling_price').val()) || 0;
    var savings  = Math.max(0, mrp - selling);
    $('#pkg_savings_display').text('₹' + savings.toFixed(2));
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

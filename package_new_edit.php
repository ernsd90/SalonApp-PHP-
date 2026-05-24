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

// All services for this salon
$services = select_array("SELECT s.service_id, s.service_name, s.service_price, sc.service_catName
    FROM hr_services s
    LEFT JOIN hr_servicesCategory sc ON sc.service_catid = s.service_catid
    WHERE s.salon_id='$salon_id' AND s.service_status=1
    ORDER BY sc.service_catName, s.service_name");
?>
<style>
.pkg-qty-input { width:70px;text-align:center;padding:6px; }
.service-row-table { width:100%;border-collapse:collapse; }
.service-row-table th { padding:10px 12px;font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border-color);text-align:left;background:#f8fafc; }
.service-row-table td { padding:8px 12px;border-bottom:1px solid var(--border-color);vertical-align:middle; }
.service-row-table tbody tr:hover td { background:#fafbfc; }
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
                        <th>Service</th>
                        <th style="width:80px;text-align:center;">Qty</th>
                        <th style="width:110px;text-align:right;">Unit Price</th>
                        <th style="width:110px;text-align:right;">Subtotal</th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="svc_rows_body">
                    <?php if($pkg_items): foreach($pkg_items as $item): ?>
                    <tr class="svc-row">
                        <td>
                            <select name="service_id[]" class="form-control svc-select" style="min-width:200px;">
                                <option value="">-- Select Service --</option>
                                <?php
                                $last_cat = '';
                                foreach($services as $sv):
                                    if($sv['service_catName'] !== $last_cat) {
                                        if($last_cat !== '') echo '</optgroup>';
                                        echo '<optgroup label="' . htmlspecialchars($sv['service_catName'] ?? 'General') . '">';
                                        $last_cat = $sv['service_catName'];
                                    }
                                ?>
                                    <option value="<?= $sv['service_id'] ?>" data-price="<?= $sv['service_price'] ?>"
                                        <?= $item['service_id']==$sv['service_id']?'selected':'' ?>>
                                        <?= htmlspecialchars($sv['service_name']) ?> (₹<?= number_format($sv['service_price'],0) ?>)
                                    </option>
                                <?php endforeach; if($last_cat!=='') echo '</optgroup>'; ?>
                            </select>
                        </td>
                        <td><input type="number" name="qty[]" class="form-control svc-qty pkg-qty-input" min="1" value="<?= $item['quantity'] ?>"></td>
                        <td style="text-align:right;"><span class="svc-unit-price" style="font-weight:600;">₹<?= number_format($item['service_price'],2) ?></span></td>
                        <td style="text-align:right;"><span class="svc-subtotal" style="font-weight:700;color:var(--primary);">₹<?= number_format($item['service_price']*$item['quantity'],2) ?></span></td>
                        <td><button type="button" class="svc-remove" style="background:#fee2e2;color:#dc2626;border:none;width:28px;height:28px;border-radius:6px;cursor:pointer;"><i class="ph ph-trash"></i></button></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
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
        <button type="submit" class="btn-primary" style="width:auto;padding:12px 26px;margin:0;">
            <i class="ph ph-floppy-disk"></i> <?= $pkg_id ? 'Update Package' : 'Create Package' ?>
        </button>
    </div>
</form>

<!-- Carry service data as JSON for JS -->
<script>
var pkgServices = <?php
    $svc_json = [];
    foreach($services as $sv) {
        $svc_json[] = [
            'id'    => (int)$sv['service_id'],
            'name'  => $sv['service_name'],
            'price' => floatval($sv['service_price']),
            'cat'   => $sv['service_catName'] ?? 'General',
        ];
    }
    echo json_encode($svc_json);
?>;

$('#gst_pkg_chk').change(function(){ $('#gst_pkg_wrap').toggle(this.checked); });

// Build service <select> html
function buildSvcSelect(selected_id) {
    var html = '<option value="">-- Select Service --</option>';
    var lastCat = '';
    pkgServices.forEach(function(s) {
        if(s.cat !== lastCat) {
            if(lastCat !== '') html += '</optgroup>';
            html += '<optgroup label="' + s.cat.replace(/"/g,'&quot;') + '">';
            lastCat = s.cat;
        }
        html += '<option value="' + s.id + '" data-price="' + s.price + '"' +
            (selected_id == s.id ? ' selected' : '') +
            '>' + s.name + ' (₹' + parseInt(s.price).toLocaleString('en-IN') + ')' +
            '</option>';
    });
    if(lastCat !== '') html += '</optgroup>';
    return html;
}

function newSvcRow(selected_id, qty) {
    var tr = $('<tr class="svc-row"></tr>');
    tr.append($('<td></td>').append($('<select name="service_id[]" class="form-control svc-select" style="min-width:200px;"></select>').html(buildSvcSelect(selected_id))));
    tr.append($('<td></td>').append('<input type="number" name="qty[]" class="form-control svc-qty pkg-qty-input" min="1" value="' + (qty||1) + '">'));
    tr.append($('<td style="text-align:right;"><span class="svc-unit-price" style="font-weight:600;">₹0.00</span></td>'));
    tr.append($('<td style="text-align:right;"><span class="svc-subtotal" style="font-weight:700;color:var(--primary);">₹0.00</span></td>'));
    tr.append($('<td></td>').append('<button type="button" class="svc-remove" style="background:#fee2e2;color:#dc2626;border:none;width:28px;height:28px;border-radius:6px;cursor:pointer;"><i class="ph ph-trash"></i></button>'));
    return tr;
}

$('#btn_add_svc_row').on('click', function() {
    var row = newSvcRow();
    $('#svc_rows_body').append(row);
    row.find('.svc-select').trigger('focus');
    recalcTotals();
});

$(document).on('change', '.svc-select', function() {
    var row = $(this).closest('tr');
    var opt = $(this).find(':selected');
    var price = parseFloat(opt.attr('data-price')) || 0;
    row.find('.svc-unit-price').text('₹' + price.toFixed(2));
    var qty = parseInt(row.find('.svc-qty').val()) || 1;
    row.find('.svc-subtotal').text('₹' + (price * qty).toFixed(2));
    recalcTotals();
});

$(document).on('input change', '.svc-qty', function() {
    var row = $(this).closest('tr');
    var price = parseFloat(row.find('.svc-unit-price').text().replace('₹','')) || 0;
    var qty = parseInt($(this).val()) || 1;
    row.find('.svc-subtotal').text('₹' + (price * qty).toFixed(2));
    recalcTotals();
});

$(document).on('click', '.svc-remove', function() {
    var rows = $('#svc_rows_body tr').length;
    if(rows <= 1) { alert('At least one service is required.'); return; }
    $(this).closest('tr').remove();
    recalcTotals();
});

$('#pkg_selling_price').on('input', recalcTotals);

function recalcTotals() {
    var mrp = 0;
    $('.svc-subtotal').each(function() { mrp += parseFloat($(this).text().replace('₹','')) || 0; });
    $('#pkg_mrp_display').text('₹' + mrp.toFixed(2));
    var selling = parseFloat($('#pkg_selling_price').val()) || 0;
    var savings = Math.max(0, mrp - selling);
    $('#pkg_savings_display').text('₹' + savings.toFixed(2));
}

// Trigger update on existing rows (edit mode)
$('.svc-select').each(function() {
    var row = $(this).closest('tr');
    var price = parseFloat($(this).find(':selected').attr('data-price')) || 0;
    row.find('.svc-unit-price').text('₹' + price.toFixed(2));
    var qty = parseInt(row.find('.svc-qty').val()) || 1;
    row.find('.svc-subtotal').text('₹' + (price * qty).toFixed(2));
});
recalcTotals();

// Add first row if new package
<?php if(!$pkg_id || !$pkg_items): ?>
$('#btn_add_svc_row').trigger('click');
<?php endif; ?>
</script>

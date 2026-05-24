<?php
include 'header.php';
$salon_id = get_session_data('salon_id');

// Outlet info
$salon_info = select_row("SELECT * FROM hr_salon WHERE salon_id='$salon_id'");

// Active packages
$packages = select_array("SELECT p.*, GROUP_CONCAT(pi.service_name ORDER BY pi.item_id SEPARATOR ', ') as service_list
    FROM hr_packages_new p
    LEFT JOIN hr_package_items pi ON pi.pkg_id = p.pkg_id
    WHERE p.salon_id='$salon_id' AND p.status=1
    GROUP BY p.pkg_id
    ORDER BY p.pkg_id ASC");

// Payment methods from DB (excluding wallet and package – those are system-defined, added below)
$db_methods = select_array("SELECT * FROM hr_payment_methods WHERE (salon_id='$salon_id' OR is_global=1) AND status=1 AND method_key NOT IN ('wallet','pkg','package') ORDER BY sort_order ASC");

// Staff list
$staff_list = select_array("SELECT * FROM hr_staff WHERE salon_id='$salon_id' AND staff_status=1");

// Pre-selected customer
$pre_cust_id = intval($_GET['cust_id'] ?? 0);
$pre_cust = $pre_cust_id ? select_row("SELECT * FROM hr_customer WHERE cust_id='$pre_cust_id'") : null;
?>

<div class="dashboard-header" style="margin-bottom:22px;">
    <h1 style="font-size:22px;font-weight:700;margin-bottom:4px;"><i class="ph-fill ph-package" style="color:var(--primary);margin-right:8px;"></i>Sell Service Package</h1>
    <p style="color:var(--text-muted);font-size:14px;">Select a customer and choose a package to sell.</p>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:22px;align-items:start;">

    <!-- Left: Customer + Package -->
    <div>
        <!-- Customer Search -->
        <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:22px;margin-bottom:18px;">
            <div style="font-weight:700;font-size:15px;margin-bottom:14px;"><i class="ph ph-user-circle" style="color:var(--primary);margin-right:6px;"></i>Customer</div>
            <div style="position:relative;">
                <div style="display:flex;gap:8px;">
                    <input type="text" id="pkg_cust_search" class="form-control" placeholder="Search by mobile number or name..."
                        value="<?= $pre_cust ? htmlspecialchars($pre_cust['cust_name'].' – '.$pre_cust['cust_mobile']) : '' ?>"
                        autocomplete="off">
                    <button type="button" id="btn_pkg_add_new_cust" style="background:var(--primary);color:white;border:none;padding:10px 16px;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:6px;">
                        <i class="ph ph-user-plus"></i> New
                    </button>
                </div>
                <div id="pkg_cust_dropdown" style="position:absolute;z-index:999;background:white;border:1px solid var(--border-color);border-radius:10px;box-shadow:var(--shadow-md);width:100%;display:none;max-height:260px;overflow-y:auto;"></div>
            </div>
            <input type="hidden" id="sell_pkg_cust_id" value="<?= $pre_cust_id ?>">
            <div id="pkg_sel_cust_info" style="margin-top:12px;display:<?= $pre_cust ? 'flex' : 'none' ?>;align-items:center;gap:14px;padding:12px 14px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;">
                <i class="ph-fill ph-user-circle" style="font-size:28px;color:#059669;"></i>
                <div style="flex:1;">
                    <div id="pkg_sel_cust_name" style="font-weight:700;font-size:14px;"><?= $pre_cust ? htmlspecialchars($pre_cust['cust_name']) : '' ?></div>
                    <div id="pkg_sel_cust_mobile" style="font-size:13px;color:var(--text-muted);"><?= $pre_cust ? htmlspecialchars($pre_cust['cust_mobile']) : '' ?></div>
                </div>
                <button type="button" onclick="clearPkgCustomer()" style="background:#fee2e2;color:#dc2626;border:none;width:28px;height:28px;border-radius:6px;cursor:pointer;"><i class="ph ph-x"></i></button>
            </div>
        </div>

        <!-- Package Selection -->
        <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:22px;">
            <div style="font-weight:700;font-size:15px;margin-bottom:16px;"><i class="ph ph-package" style="color:var(--primary);margin-right:6px;"></i>Select Package</div>
            <?php if(!$packages): ?>
                <p style="color:var(--text-muted);text-align:center;padding:20px;">No active packages. <a href="packages_new.php">Create one first.</a></p>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;">
                <?php foreach($packages as $p):
                    $savings = max(0, $p['mrp_total'] - $p['selling_price']);
                    $validity_months = round($p['validity_days'] / 30);
                ?>
                <label style="cursor:pointer;" class="pkg-card-label">
                    <input type="radio" name="sel_pkg" value="<?= $p['pkg_id'] ?>"
                        data-name="<?= htmlspecialchars($p['package_name']) ?>"
                        data-price="<?= $p['selling_price'] ?>"
                        data-mrp="<?= $p['mrp_total'] ?>"
                        data-savings="<?= $savings ?>"
                        data-gst="<?= $p['gst_applicable'] ? $p['gst_percent'] : 0 ?>"
                        data-validity="<?= $validity_months ?>"
                        style="display:none;">
                    <div class="pkg-card" style="border:2px solid var(--border-color);border-radius:14px;padding:18px;transition:.2s;height:100%;">
                        <div style="font-weight:700;font-size:15px;margin-bottom:8px;"><?= htmlspecialchars($p['package_name']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px;"><?= htmlspecialchars($p['service_list'] ?: '—') ?></div>
                        <div style="font-size:20px;font-weight:800;color:var(--primary);">₹<?= number_format($p['selling_price'],0) ?></div>
                        <div style="font-size:12px;color:var(--text-muted);text-decoration:line-through;">MRP ₹<?= number_format($p['mrp_total'],0) ?></div>
                        <?php if($savings > 0): ?><div style="font-size:12px;background:#dcfce7;color:#059669;padding:3px 10px;border-radius:20px;display:inline-block;margin-top:4px;">Saves ₹<?= number_format($savings,0) ?></div><?php endif; ?>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:8px;"><i class="ph ph-clock"></i> Valid for <?= $validity_months ?> month<?= $validity_months>1?'s':'' ?></div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Order Summary + Payment -->
    <div style="position:sticky;top:20px;">
        <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:22px;">
            <div style="font-weight:700;font-size:15px;margin-bottom:16px;border-bottom:1px solid var(--border-color);padding-bottom:12px;"><i class="ph ph-receipt" style="color:var(--primary);margin-right:6px;"></i>Order Summary</div>

            <div id="pkg_summary_empty" style="text-align:center;padding:20px;color:var(--text-muted);">Select a package to see summary.</div>
            <div id="pkg_summary_detail" style="display:none;">
                <div style="margin-bottom:14px;">
                    <div style="font-weight:700;font-size:16px;" id="psm_name">—</div>
                    <div style="font-size:13px;color:var(--text-muted);" id="psm_validity">—</div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;"><span>MRP Total</span><strong id="psm_mrp">—</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;"><span>Package Price</span><strong style="color:var(--primary);" id="psm_price">—</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;color:#059669;" id="psm_savings_row"><span>You Save</span><strong id="psm_savings">—</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;" id="psm_gst_row"><span>GST</span><strong id="psm_gst">₹0.00</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;padding:10px 0;color:var(--primary);"><span>Total</span><strong id="psm_total">—</strong></div>

                <!-- Billing Date -->
                <div class="form-group" style="margin-top:10px;">
                    <label>Billing / Start Date</label>
                    <input type="date" id="pkg_billing_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label>Staff (Sold By)</label>
                    <select id="pkg_staff_id" class="form-control">
                        <option value="">-- Select Staff --</option>
                        <?php if($staff_list): foreach($staff_list as $st): ?>
                            <option value="<?= $st['staff_id'] ?>"><?= htmlspecialchars($st['staff_name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label>Payment Mode</label>
                    <select id="pkg_pay_mode" class="form-control">
                        <?php foreach($db_methods as $m): ?>
                            <option value="<?= htmlspecialchars($m['method_key']) ?>"><?= htmlspecialchars($m['method_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Partial Payment Option -->
                <div class="form-group" style="margin-top:4px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" id="pkg_partial_pay_chk" style="width:16px;height:16px;">
                        <span>Partial Payment (collect part now)</span>
                    </label>
                </div>
                <div id="pkg_partial_pay_wrap" style="display:none;">
                    <div class="form-group">
                        <label>Amount to collect now (₹) <span style="color:var(--danger);">*</span></label>
                        <input type="number" id="pkg_paid_now" class="form-control" min="1" placeholder="Enter amount" step="any">
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <input type="text" id="pkg_notes" class="form-control" placeholder="Optional notes">
                </div>

                <button id="btn_sell_package" class="btn-primary" style="width:100%;margin-top:12px;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="ph ph-check"></i> Confirm & Sell Package
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Add New Customer Modal (Package page) ─── -->
<div id="pkg_new_cust_overlay" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;width:360px;max-width:96vw;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:var(--primary);color:white;">
            <strong><i class="ph ph-user-plus"></i> Quick Add Customer</strong>
            <button onclick="$('#pkg_new_cust_overlay').css('display','none')" style="background:rgba(255,255,255,.2);color:white;border:none;width:30px;height:30px;border-radius:8px;cursor:pointer;"><i class="ph ph-x"></i></button>
        </div>
        <div style="padding:20px;">
            <div class="form-group">
                <label>Customer Name <span style="color:var(--danger);">*</span></label>
                <input type="text" id="pkg_new_cust_name" class="form-control" placeholder="Full name">
            </div>
            <div class="form-group">
                <label>Mobile Number <span style="color:var(--danger);">*</span></label>
                <input type="tel" id="pkg_new_cust_mobile" class="form-control" placeholder="10-digit mobile" maxlength="10">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select id="pkg_new_cust_gender" class="form-control">
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button id="btn_pkg_save_new_cust" class="btn-primary" style="width:100%;margin-top:4px;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="ph ph-floppy-disk"></i> Save Customer
            </button>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="pkg_receipt_overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;width:420px;max-width:96vw;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:var(--primary);color:white;">
            <strong>Package Receipt</strong>
            <div style="display:flex;gap:8px;">
                <button onclick="printPackageReceipt()" style="background:rgba(255,255,255,.2);color:white;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;"><i class="ph ph-printer"></i> Print</button>
                <button onclick="document.getElementById('pkg_receipt_overlay').style.display='none'" style="background:rgba(255,255,255,.2);color:white;border:none;padding:6px 10px;border-radius:8px;cursor:pointer;"><i class="ph ph-x"></i></button>
            </div>
        </div>
        <div id="pkg_receipt_content" style="padding:20px;"></div>
    </div>
</div>

<style>
.pkg-card-label input:checked + .pkg-card { border-color:var(--primary) !important; background:linear-gradient(135deg,#f0f4ff,#e8edfe); box-shadow:0 0 0 3px rgba(99,102,241,.15); }
.pkg-card:hover { border-color:var(--primary); box-shadow:0 4px 14px rgba(99,102,241,.15); }
</style>

<script>
var selPkgData = {};

// ─── Customer Search ───
var pkgSearchTimer;
$('#pkg_cust_search').on('input', function() {
    clearTimeout(pkgSearchTimer);
    var q = $(this).val().trim();
    if(q.length < 2) { $('#pkg_cust_dropdown').hide(); return; }
    pkgSearchTimer = setTimeout(function() {
        $.post('ajax/customer_ajax.php', {method:'get_customer_from_mobile', cust_mob:q, detail:2}, function(res) {
            try {
                var r = JSON.parse(res);
                var html = '';
                if(r && r.length > 0) {
                    r.forEach(function(c) {
                        html += '<div class="pkg-cust-opt" data-id="'+c.cust_id+'" data-name="'+$('<div>').text(c.cust_name).html()+'" data-mobile="'+c.cust_mobile+'" style="padding:11px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;font-size:13px;">' +
                            '<div style="font-weight:600;">'+$('<div>').text(c.cust_name).html()+'</div>' +
                            '<div style="font-size:12px;color:var(--text-muted);">'+c.cust_mobile+'</div>' +
                            '</div>';
                    });
                } else {
                    html = '<div style="padding:10px 14px;color:var(--text-muted);font-size:13px;">No customers found.</div>';
                }
                html += '<div id="pkg_add_new_opt" data-query="'+q+'" style="padding:11px 14px;cursor:pointer;background:#f0f4ff;font-size:13px;font-weight:600;color:var(--primary);display:flex;align-items:center;gap:8px;">' +
                    '<i class="ph ph-user-plus"></i> Add "' + q + '" as new customer</div>';
                $('#pkg_cust_dropdown').html(html).show();
            } catch(e) {}
        });
    }, 300);
});

$(document).on('click', '.pkg-cust-opt', function() {
    setPkgCustomer($(this).data('id'), $(this).data('name'), $(this).data('mobile'));
});

$(document).on('click', '#pkg_add_new_opt', function() {
    var q = $(this).data('query');
    var isPhone = /^[0-9]{6,}$/.test(q);
    if(isPhone) { $('#pkg_new_cust_mobile').val(q); $('#pkg_new_cust_name').val(''); }
    else { $('#pkg_new_cust_name').val(q); $('#pkg_new_cust_mobile').val(''); }
    $('#pkg_cust_dropdown').hide();
    $('#pkg_new_cust_overlay').css('display','flex');
});

$('#btn_pkg_add_new_cust').click(function() {
    $('#pkg_new_cust_name').val(''); $('#pkg_new_cust_mobile').val('');
    $('#pkg_new_cust_overlay').css('display','flex');
    $('#pkg_new_cust_name').focus();
});

function setPkgCustomer(id, name, mobile) {
    $('#sell_pkg_cust_id').val(id);
    $('#pkg_sel_cust_name').text(name);
    $('#pkg_sel_cust_mobile').text(mobile);
    $('#pkg_cust_search').val(name + ' – ' + mobile);
    $('#pkg_cust_dropdown').hide();
    $('#pkg_sel_cust_info').show();
}

function clearPkgCustomer() {
    $('#sell_pkg_cust_id').val('');
    $('#pkg_sel_cust_info').hide();
    $('#pkg_cust_search').val('').focus();
}

$('#btn_pkg_save_new_cust').click(function() {
    var name = $.trim($('#pkg_new_cust_name').val());
    var mobile = $.trim($('#pkg_new_cust_mobile').val());
    if(!name || !mobile) { alert('Name and mobile number are required.'); return; }
    if(!/^[0-9]{10}$/.test(mobile)) { alert('Please enter a valid 10-digit mobile number.'); return; }
    var btn = $(this).html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled',true);
    $.post('ajax/customer_ajax.php', {method:'customer_create', cust_name:name, cust_mobile:mobile, salon_id:'<?= $salon_id ?>'}, function(res) {
        try {
            var r = JSON.parse(res);
            btn.html('Save Customer').prop('disabled',false);
            if(r.error == 0) {
                setPkgCustomer(r.cust_id, name, mobile);
                $('#pkg_new_cust_overlay').css('display','none');
                alert('✅ Customer added successfully!');
            } else { alert('Error: ' + (r.msg || 'Could not save customer')); }
        } catch(e) { btn.html('Save Customer').prop('disabled',false); alert('Error saving.'); }
    });
});

$(document).on('click', function(e) {
    if(!$(e.target).closest('#pkg_cust_search, #pkg_cust_dropdown, #btn_pkg_add_new_cust').length) $('#pkg_cust_dropdown').hide();
});

// Package selection
$(document).on('change', 'input[name="sel_pkg"]', function() {
    selPkgData = {
        pkg_id:   $(this).val(),
        pkg_name: $(this).data('name'),
        price:    parseFloat($(this).data('price')),
        mrp:      parseFloat($(this).data('mrp')),
        savings:  parseFloat($(this).data('savings')),
        gst_pct:  parseFloat($(this).data('gst')),
        validity: parseInt($(this).data('validity')),
    };
    var gst = selPkgData.gst_pct > 0 ? Math.round(selPkgData.price * selPkgData.gst_pct / 100 * 100) / 100 : 0;
    selPkgData.total = selPkgData.price + gst;

    $('#psm_name').text(selPkgData.pkg_name);
    $('#psm_validity').text('Valid for ' + selPkgData.validity + ' month' + (selPkgData.validity > 1 ? 's' : ''));
    $('#psm_mrp').text('₹' + selPkgData.mrp.toFixed(2));
    $('#psm_price').text('₹' + selPkgData.price.toFixed(2));
    $('#psm_savings').text('₹' + selPkgData.savings.toFixed(2));
    $('#psm_savings_row').toggle(selPkgData.savings > 0);
    $('#psm_gst').text('₹' + gst.toFixed(2));
    $('#psm_gst_row').toggle(gst > 0);
    $('#psm_total').text('₹' + selPkgData.total.toFixed(2));
    $('#pkg_paid_now').attr('max', selPkgData.total).attr('placeholder', selPkgData.total.toFixed(2));
    $('#pkg_summary_empty').hide();
    $('#pkg_summary_detail').show();
});

$('#pkg_partial_pay_chk').change(function() { $('#pkg_partial_pay_wrap').toggle(this.checked); });

// Sell Package
$('#btn_sell_package').click(function() {
    var cust_id = $('#sell_pkg_cust_id').val();
    if(!cust_id) { alert('Please select a customer first.'); return; }
    if(!selPkgData.pkg_id) { alert('Please select a package.'); return; }

    var is_partial = $('#pkg_partial_pay_chk').is(':checked');
    var paid_now = is_partial ? parseFloat($('#pkg_paid_now').val()) : selPkgData.total;
    if(is_partial && (!paid_now || paid_now <= 0)) { alert('Enter amount for partial payment.'); return; }
    if(is_partial && paid_now > selPkgData.total) { alert('Amount cannot exceed total.'); return; }

    var btn = $(this).html('<i class="ph ph-spinner ph-spin"></i> Processing...').prop('disabled',true);
    $.post('ajax/membership_ajax.php', {
        method: 'sell_package_new',
        cust_id: cust_id,
        pkg_id: selPkgData.pkg_id,
        staff_id: $('#pkg_staff_id').val(),
        billing_date: $('#pkg_billing_date').val(),
        paid_now: paid_now,
        payment_mode: $('#pkg_pay_mode').val(),
        notes: $('#pkg_notes').val()
    }, function(res) {
        var r = JSON.parse(res);
        btn.html('<i class="ph ph-check"></i> Confirm & Sell Package').prop('disabled',false);
        if(r.error == 0) {
            showPackageReceipt($('#pkg_sel_cust_name').text(), selPkgData, $('#pkg_pay_mode').val(), paid_now, selPkgData.total - paid_now);
        } else {
            alert('Error: ' + r.msg);
        }
    });
});

function showPackageReceipt(cust_name, pkg, pay_mode, paid_now, remaining) {
    var now = new Date();
    var dateStr = now.toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'}) + ' ' + now.toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit'});
    var expDate = new Date();
    expDate.setMonth(expDate.getMonth() + pkg.validity);
    var expStr = expDate.toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'});
    var html = '<div id="pkg_receipt_printable" style="font-family:Arial,sans-serif;font-size:13px;">' +
        '<div style="text-align:center;padding-bottom:12px;border-bottom:2px dashed #ccc;margin-bottom:12px;">' +
        '<div style="font-size:18px;font-weight:800;"><?= htmlspecialchars($salon_info['firm_name'] ?? '') ?></div>' +
        '<div style="font-size:11px;color:#666;"><?= htmlspecialchars($salon_info['salon_address'] ?? '') ?></div>' +
        '<div style="font-size:11px;color:#666;">GST: <?= htmlspecialchars($salon_info['salon_gst'] ?? '') ?></div></div>' +
        '<div style="margin-bottom:14px;"><strong>SERVICE PACKAGE RECEIPT</strong><br>Customer: ' + cust_name + '<br>Date: ' + dateStr + '</div>' +
        '<table style="width:100%;border-collapse:collapse;font-size:12px;">' +
        '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;"><b>Package</b></td><td style="text-align:right;border-bottom:1px solid #f1f5f9;">' + pkg.pkg_name + '</td></tr>' +
        '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;">MRP</td><td style="text-align:right;border-bottom:1px solid #f1f5f9;text-decoration:line-through;color:#999;">₹' + pkg.mrp.toFixed(2) + '</td></tr>' +
        '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;font-weight:700;">Price Charged</td><td style="text-align:right;border-bottom:1px solid #f1f5f9;font-weight:700;color:var(--primary);">₹' + pkg.price.toFixed(2) + '</td></tr>' +
        (pkg.savings > 0 ? '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;color:#059669;">You Saved</td><td style="text-align:right;border-bottom:1px solid #f1f5f9;color:#059669;font-weight:700;">₹' + pkg.savings.toFixed(2) + '</td></tr>' : '') +
        '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;">Paid Now (' + pay_mode.toUpperCase() + ')</td><td style="text-align:right;font-weight:700;border-bottom:1px solid #f1f5f9;">₹' + paid_now.toFixed(2) + '</td></tr>' +
        (remaining > 0 ? '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;color:#dc2626;">Balance Due</td><td style="text-align:right;color:#dc2626;font-weight:700;border-bottom:1px solid #f1f5f9;">₹' + remaining.toFixed(2) + '</td></tr>' : '') +
        '<tr><td style="padding:5px 0;">Valid Till</td><td style="text-align:right;">' + expStr + '</td></tr>' +
        '</table>' +
        '<div style="text-align:center;margin-top:16px;padding-top:12px;border-top:2px dashed #ccc;font-size:11px;color:#888;">Thank you for choosing us!</div>' +
        '</div>';
    $('#pkg_receipt_content').html(html);
    $('#pkg_receipt_overlay').css('display','flex');
}

function printPackageReceipt() {
    var content = document.getElementById('pkg_receipt_printable').innerHTML;
    var win = window.open('','_blank','width=400,height=600');
    win.document.write('<html><head><title>Package Receipt</title>' +
        '<style>body{font-family:Arial,sans-serif;font-size:13px;padding:10px;}@page{margin:5mm;}</style>' +
        '</head><body>' + content + '</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); win.close(); }, 400);
}
</script>

<?php include 'footer.php'; ?>

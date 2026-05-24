<?php
include 'header.php';
$salon_id = get_session_data('salon_id');

// Outlet info
$salon_info = select_row("SELECT * FROM hr_salon WHERE salon_id='$salon_id'");
$outlet_gst  = $salon_info ? floatval($salon_info['gst_percentage']) : 0;

// Active plans
$plans = select_array("SELECT * FROM hr_membership_plans WHERE salon_id='$salon_id' AND status=1 ORDER BY plan_price ASC");

// Payment methods from DB + always add Wallet (system defined)
$db_methods = select_array("SELECT * FROM hr_payment_methods WHERE (salon_id='$salon_id' OR is_global=1) AND status=1 AND method_key NOT IN ('wallet','pkg','package') ORDER BY sort_order ASC");

// Staff list
$staff_list = select_array("SELECT * FROM hr_staff WHERE salon_id='$salon_id' AND staff_status=1");

// Pre-selected customer
$pre_cust_id = intval($_GET['cust_id'] ?? 0);
$pre_cust = $pre_cust_id ? select_row("SELECT * FROM hr_customer WHERE cust_id='$pre_cust_id'") : null;
?>

<div class="dashboard-header" style="margin-bottom:22px;">
    <h1 style="font-size:22px;font-weight:700;margin-bottom:4px;"><i class="ph-fill ph-identification-badge" style="color:var(--primary);margin-right:8px;"></i>Sell Membership</h1>
    <p style="color:var(--text-muted);font-size:14px;">Select a customer and choose a membership plan to sell.</p>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:22px;align-items:start;">

    <!-- Left: Customer + Plan -->
    <div>
        <!-- Customer Search -->
        <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:22px;margin-bottom:18px;">
            <div style="font-weight:700;font-size:15px;margin-bottom:14px;"><i class="ph ph-user-circle" style="color:var(--primary);margin-right:6px;"></i>Customer</div>
            <div style="position:relative;">
                <div style="display:flex;gap:8px;">
                    <input type="text" id="mem_cust_search" class="form-control" placeholder="Search by mobile number or name..."
                        value="<?= $pre_cust ? htmlspecialchars($pre_cust['cust_name'].' – '.$pre_cust['cust_mobile']) : '' ?>"
                        autocomplete="off">
                    <button type="button" id="btn_add_new_cust" style="background:var(--primary);color:white;border:none;padding:10px 16px;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:6px;">
                        <i class="ph ph-user-plus"></i> New
                    </button>
                </div>
                <div id="mem_cust_dropdown" style="position:absolute;z-index:999;background:white;border:1px solid var(--border-color);border-radius:10px;box-shadow:var(--shadow-md);width:100%;display:none;max-height:260px;overflow-y:auto;"></div>
            </div>
            <input type="hidden" id="sel_cust_id" value="<?= $pre_cust_id ?>">
            <div id="sel_cust_info" style="margin-top:12px;display:<?= $pre_cust ? 'flex' : 'none' ?>;align-items:center;gap:14px;padding:12px 14px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;">
                <i class="ph-fill ph-user-circle" style="font-size:28px;color:#059669;"></i>
                <div style="flex:1;">
                    <div id="sel_cust_name" style="font-weight:700;font-size:14px;"><?= $pre_cust ? htmlspecialchars($pre_cust['cust_name']) : '' ?></div>
                    <div style="font-size:13px;color:var(--text-muted);">
                        <span id="sel_cust_mobile"><?= $pre_cust ? htmlspecialchars($pre_cust['cust_mobile']) : '' ?></span>
                        <span style="margin:0 6px;">·</span> Wallet: <strong id="sel_cust_wallet" style="color:#059669;"><?= $pre_cust ? '₹'.number_format($pre_cust['cust_wallet'],2) : '₹0.00' ?></strong>
                    </div>
                </div>
                <button type="button" onclick="clearMemCustomer()" style="background:#fee2e2;color:#dc2626;border:none;width:28px;height:28px;border-radius:6px;cursor:pointer;"><i class="ph ph-x"></i></button>
            </div>
        </div>

        <!-- Plan Selection -->
        <div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:22px;">
            <div style="font-weight:700;font-size:15px;margin-bottom:16px;"><i class="ph ph-star" style="color:var(--primary);margin-right:6px;"></i>Select Plan</div>
            <?php if(!$plans): ?>
                <p style="color:var(--text-muted);text-align:center;padding:20px;">No active membership plans. <a href="membership_plans.php">Create one first.</a></p>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;">
                <?php foreach($plans as $p):
                    $validity_months = round($p['validity_days'] / 30);
                    $savings = $p['wallet_credit'] - $p['plan_price'];
                ?>
                <label style="cursor:pointer;" class="plan-card-label">
                    <input type="radio" name="sel_plan" value="<?= $p['plan_id'] ?>"
                        data-price="<?= $p['plan_price'] ?>"
                        data-wallet="<?= $p['wallet_credit'] ?>"
                        data-validity="<?= $validity_months ?>"
                        data-name="<?= htmlspecialchars($p['plan_name']) ?>"
                        data-gst="<?= $p['gst_applicable'] ? $p['gst_percent'] : 0 ?>"
                        style="display:none;">
                    <div class="plan-card" style="border:2px solid var(--border-color);border-radius:14px;padding:18px;transition:.2s;height:100%;">
                        <div style="font-weight:700;font-size:15px;margin-bottom:8px;"><?= htmlspecialchars($p['plan_name']) ?></div>
                        <div style="font-size:22px;font-weight:800;color:var(--primary);">₹<?= number_format($p['plan_price'],0) ?></div>
                        <div style="font-size:13px;color:var(--text-muted);margin:4px 0;">Customer gets <strong style="color:#059669;">₹<?= number_format($p['wallet_credit'],0) ?></strong> in wallet</div>
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

            <div id="mem_summary_empty" style="text-align:center;padding:20px;color:var(--text-muted);">Select a plan to see summary.</div>
            <div id="mem_summary_detail" style="display:none;">
                <div style="margin-bottom:14px;">
                    <div style="font-weight:700;font-size:16px;" id="sm_plan_name">—</div>
                    <div style="font-size:13px;color:var(--text-muted);" id="sm_validity">—</div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;"><span>Plan Price</span><strong id="sm_price">—</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;"><span>Wallet Credit</span><strong style="color:#059669;" id="sm_wallet">—</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;" id="sm_gst_row"><span>GST</span><strong id="sm_gst">₹0.00</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;padding:10px 0;color:var(--primary);"><span>Total Due</span><strong id="sm_total">—</strong></div>

                <!-- Billing Date -->
                <div class="form-group" style="margin-top:10px;">
                    <label>Billing / Start Date</label>
                    <input type="date" id="mem_billing_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Staff -->
                <div class="form-group" style="margin-top:10px;">
                    <label>Staff (Sold By)</label>
                    <select id="mem_staff_id" class="form-control">
                        <option value="">-- Select Staff --</option>
                        <?php if($staff_list): foreach($staff_list as $st): ?>
                            <option value="<?= $st['staff_id'] ?>"><?= htmlspecialchars($st['staff_name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <!-- Payment Mode -->
                <div class="form-group" style="margin-top:10px;">
                    <label>Payment Mode</label>
                    <select id="mem_pay_mode" class="form-control">
                        <?php foreach($db_methods as $m): ?>
                            <option value="<?= htmlspecialchars($m['method_key']) ?>"><?= htmlspecialchars($m['method_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Partial Payment Option -->
                <div class="form-group" style="margin-top:4px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" id="partial_pay_chk" style="width:16px;height:16px;">
                        <span>Partial Payment (collect part now)</span>
                    </label>
                </div>
                <div id="partial_pay_wrap" style="display:none;">
                    <div class="form-group">
                        <label>Amount to collect now (₹) <span style="color:var(--danger);">*</span></label>
                        <input type="number" id="mem_paid_now" class="form-control" min="1" placeholder="Enter amount" step="any">
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <input type="text" id="mem_notes" class="form-control" placeholder="Optional notes">
                </div>

                <button id="btn_sell_membership" class="btn-primary" style="width:100%;margin-top:12px;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="ph ph-check"></i> Confirm & Sell Membership
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Membership History -->
<div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:22px;margin-top:22px;">
    <div style="font-weight:700;font-size:15px;margin-bottom:16px;"><i class="ph ph-clock-counter-clockwise" style="color:var(--primary);margin-right:6px;"></i>Customer Membership History</div>
    <div id="mem_history_body" style="color:var(--text-muted);text-align:center;padding:20px;">Select a customer to view history.</div>
</div>

<!-- ─── Add New Customer Modal ─── -->
<div id="new_cust_overlay" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;width:360px;max-width:96vw;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:var(--primary);color:white;">
            <strong><i class="ph ph-user-plus"></i> Quick Add Customer</strong>
            <button onclick="$('#new_cust_overlay').css('display','none')" style="background:rgba(255,255,255,.2);color:white;border:none;width:30px;height:30px;border-radius:8px;cursor:pointer;"><i class="ph ph-x"></i></button>
        </div>
        <div style="padding:20px;">
            <div class="form-group">
                <label>Customer Name <span style="color:var(--danger);">*</span></label>
                <input type="text" id="new_cust_name" class="form-control" placeholder="Full name">
            </div>
            <div class="form-group">
                <label>Mobile Number <span style="color:var(--danger);">*</span></label>
                <input type="tel" id="new_cust_mobile" class="form-control" placeholder="10-digit mobile" maxlength="10">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select id="new_cust_gender" class="form-control">
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button id="btn_save_new_cust" class="btn-primary" style="width:100%;margin-top:4px;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="ph ph-floppy-disk"></i> Save Customer
            </button>
        </div>
    </div>
</div>

<!-- ─── Receipt Modal ─── -->
<div id="mem_receipt_overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;width:420px;max-width:96vw;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:var(--primary);color:white;">
            <strong>Membership Receipt</strong>
            <div style="display:flex;gap:8px;">
                <button onclick="printMembershipReceipt()" style="background:rgba(255,255,255,.2);color:white;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;"><i class="ph ph-printer"></i> Print</button>
                <button onclick="document.getElementById('mem_receipt_overlay').style.display='none'" style="background:rgba(255,255,255,.2);color:white;border:none;padding:6px 10px;border-radius:8px;cursor:pointer;"><i class="ph ph-x"></i></button>
            </div>
        </div>
        <div id="mem_receipt_content" style="padding:20px;"></div>
    </div>
</div>

<style>
.plan-card-label input:checked + .plan-card { border-color:var(--primary) !important; background:linear-gradient(135deg,#f0f4ff,#e8edfe); box-shadow:0 0 0 3px rgba(99,102,241,.15); }
.plan-card:hover { border-color:var(--primary); box-shadow:0 4px 14px rgba(99,102,241,.15); }
</style>

<script>
// ─── Customer Search ───
var memSearchTimer;
$('#mem_cust_search').on('input', function() {
    clearTimeout(memSearchTimer);
    var q = $(this).val().trim();
    if(q.length < 2) { $('#mem_cust_dropdown').hide(); return; }
    memSearchTimer = setTimeout(function() {
        $.post('ajax/customer_ajax.php', {method:'get_customer_from_mobile', cust_mob:q, detail:2}, function(res) {
            try {
                var r = JSON.parse(res);
                var html = '';
                if(r && r.length > 0) {
                    r.forEach(function(c) {
                        var wallet = parseFloat(c.cust_wallet||0);
                        html += '<div class="mem-cust-opt" data-id="'+c.cust_id+'" data-name="'+$('<div>').text(c.cust_name).html()+'" data-mobile="'+c.cust_mobile+'" data-wallet="'+wallet+'" style="padding:11px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;font-size:13px;">' +
                            '<div style="font-weight:600;">'+$('<div>').text(c.cust_name).html()+'</div>' +
                            '<div style="font-size:12px;color:var(--text-muted);display:flex;justify-content:space-between;"><span>'+c.cust_mobile+'</span><span style="color:#059669;">₹'+wallet.toFixed(2)+' wallet</span></div>' +
                            '</div>';
                    });
                } else {
                    html = '<div style="padding:10px 14px;color:var(--text-muted);font-size:13px;">No customers found.</div>';
                }
                // Always show Add New option
                html += '<div id="mem_add_new_opt" data-query="'+q+'" style="padding:11px 14px;cursor:pointer;background:#f0f4ff;font-size:13px;font-weight:600;color:var(--primary);display:flex;align-items:center;gap:8px;">' +
                    '<i class="ph ph-user-plus"></i> Add "' + q + '" as new customer</div>';
                $('#mem_cust_dropdown').html(html).show();
            } catch(e) {}
        });
    }, 300);
});

// Select existing customer from dropdown
$(document).on('click', '.mem-cust-opt', function() {
    setMemCustomer($(this).data('id'), $(this).data('name'), $(this).data('mobile'), parseFloat($(this).data('wallet')||0));
});

// Add new customer inline
$(document).on('click', '#mem_add_new_opt', function() {
    var q = $(this).data('query');
    // Prefill modal with search value (name or mobile)
    var isPhone = /^[0-9]{6,}$/.test(q);
    if(isPhone) { $('#new_cust_mobile').val(q); $('#new_cust_name').val(''); }
    else { $('#new_cust_name').val(q); $('#new_cust_mobile').val(''); }
    $('#mem_cust_dropdown').hide();
    $('#new_cust_overlay').css('display','flex');
});

$('#btn_add_new_cust').click(function() {
    $('#new_cust_name').val(''); $('#new_cust_mobile').val('');
    $('#new_cust_overlay').css('display','flex');
    $('#new_cust_name').focus();
});

function setMemCustomer(id, name, mobile, wallet) {
    $('#sel_cust_id').val(id);
    $('#sel_cust_name').text(name);
    $('#sel_cust_mobile').text(mobile);
    $('#sel_cust_wallet').text('₹'+wallet.toFixed(2));
    $('#mem_cust_search').val(name + ' – ' + mobile);
    $('#mem_cust_dropdown').hide();
    $('#sel_cust_info').show();
    loadMemHistory(id);
}

function clearMemCustomer() {
    $('#sel_cust_id').val('');
    $('#sel_cust_info').hide();
    $('#mem_cust_search').val('').focus();
    $('#mem_history_body').html('<p style="text-align:center;color:var(--text-muted);padding:20px;">Select a customer to view history.</p>');
}

// Save new customer
$('#btn_save_new_cust').click(function() {
    var name = $.trim($('#new_cust_name').val());
    var mobile = $.trim($('#new_cust_mobile').val());
    var gender = $('#new_cust_gender').val();
    if(!name || !mobile) { alert('Name and mobile number are required.'); return; }
    if(!/^[0-9]{10}$/.test(mobile)) { alert('Please enter a valid 10-digit mobile number.'); return; }
    var btn = $(this).html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled',true);
    $.post('ajax/customer_ajax.php', {method:'customer_create', cust_name:name, cust_mobile:mobile, cust_gender:gender, salon_id:'<?= $salon_id ?>'}, function(res) {
        try {
            var r = JSON.parse(res);
            btn.html('Save Customer').prop('disabled',false);
            if(r.cust_id || r.error == 0) {
                var cid = r.cust_id || r.id;
                setMemCustomer(cid, name, mobile, 0);
                $('#new_cust_overlay').css('display','none');
                alert('✅ Customer added successfully!');
            } else { alert('Error: ' + (r.msg || 'Could not save customer')); }
        } catch(e) { btn.html('Save Customer').prop('disabled',false); alert('Error saving customer.'); }
    });
});

$(document).on('click', function(e) {
    if(!$(e.target).closest('#mem_cust_search, #mem_cust_dropdown, #btn_add_new_cust').length) $('#mem_cust_dropdown').hide();
});

// Plan selection
var selPlanData = {};
$(document).on('change', 'input[name="sel_plan"]', function() {
    selPlanData = {
        plan_id:  $(this).val(),
        plan_name: $(this).data('name'),
        price: parseFloat($(this).data('price')),
        wallet: parseFloat($(this).data('wallet')),
        validity: $(this).data('validity'),
        gst: parseFloat($(this).data('gst')),
    };
    updateMemSummary();
    $('#mem_summary_empty').hide();
    $('#mem_summary_detail').show();
});

function updateMemSummary() {
    if(!selPlanData.plan_id) return;
    var gst = selPlanData.gst > 0 ? Math.round(selPlanData.price * selPlanData.gst / 100 * 100) / 100 : 0;
    var total = selPlanData.price + gst;
    $('#sm_plan_name').text(selPlanData.plan_name);
    $('#sm_validity').text('Valid for ' + selPlanData.validity + ' month' + (selPlanData.validity > 1 ? 's' : ''));
    $('#sm_price').text('₹' + selPlanData.price.toFixed(2));
    $('#sm_wallet').text('₹' + selPlanData.wallet.toFixed(2));
    $('#sm_gst').text('₹' + gst.toFixed(2));
    $('#sm_gst_row').toggle(gst > 0);
    $('#sm_total').text('₹' + total.toFixed(2));
    $('#mem_paid_now').attr('max', total).attr('placeholder', total.toFixed(2));
    selPlanData.total = total;
}

$('#partial_pay_chk').change(function() { $('#partial_pay_wrap').toggle(this.checked); });

function loadMemHistory(cust_id) {
    $.post('ajax/membership_ajax.php', {method:'get_customer_memberships', cust_id:cust_id}, function(res) {
        var r = JSON.parse(res);
        if(!r || r.length === 0) { $('#mem_history_body').html('<p style="text-align:center;color:var(--text-muted);padding:20px;">No membership history.</p>'); return; }
        var html = '<div style="overflow:auto;"><table style="width:100%;border-collapse:collapse;">' +
            '<thead><tr style="background:#f8fafc;">' +
            '<th style="padding:10px 12px;font-size:12px;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);text-align:left;">Plan</th>' +
            '<th style="padding:10px 12px;font-size:12px;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Price</th>' +
            '<th style="padding:10px 12px;font-size:12px;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Wallet</th>' +
            '<th style="padding:10px 12px;font-size:12px;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Status</th>' +
            '<th style="padding:10px 12px;font-size:12px;color:var(--text-muted);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Expiry</th>' +
            '</tr></thead><tbody>';
        r.forEach(function(m) {
            var sc = {active:'#059669',pending:'#d97706',expired:'#dc2626',refunded:'#6b7280',paused:'#7c3aed'}[m.status]||'#6b7280';
            html += '<tr><td style="padding:10px 12px;font-weight:600;border-bottom:1px solid #f1f5f9;">'+m.plan_name+'</td>' +
                '<td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;text-align:right;">'+m.paid_amount+'</td>' +
                '<td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;text-align:right;color:#059669;">'+m.wallet_credit+'</td>' +
                '<td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;text-align:center;"><span style="background:'+sc+'20;color:'+sc+';padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">'+m.status+'</span></td>' +
                '<td style="padding:10px 12px;border-bottom:1px solid #f1f5f9;text-align:right;color:var(--text-muted);">'+m.expiry_formatted+'</td></tr>';
        });
        html += '</tbody></table></div>';
        $('#mem_history_body').html(html);
    });
}

<?php if($pre_cust_id): ?>loadMemHistory(<?= $pre_cust_id ?>);<?php endif; ?>

// Sell Membership
$('#btn_sell_membership').click(function() {
    var cust_id = $('#sel_cust_id').val();
    if(!cust_id) { alert('Please select a customer first.'); return; }
    if(!selPlanData.plan_id) { alert('Please select a membership plan.'); return; }
    var pay_mode = $('#mem_pay_mode').val();
    var is_partial = $('#partial_pay_chk').is(':checked');
    var paid_now = is_partial ? parseFloat($('#mem_paid_now').val()) : selPlanData.total;
    if(is_partial && (!paid_now || paid_now <= 0)) { alert('Enter amount for partial payment.'); return; }
    if(is_partial && paid_now > selPlanData.total) { alert('Amount cannot exceed total.'); return; }

    var btn = $(this).html('<i class="ph ph-spinner ph-spin"></i> Processing...').prop('disabled',true);
    $.post('ajax/membership_ajax.php', {
        method: 'sell_membership',
        cust_id: cust_id,
        plan_id: selPlanData.plan_id,
        staff_id: $('#mem_staff_id').val(),
        billing_date: $('#mem_billing_date').val(),
        paid_now: paid_now,
        payment_mode: pay_mode,
        notes: $('#mem_notes').val()
    }, function(res) {
        var r = JSON.parse(res);
        btn.html('<i class="ph ph-check"></i> Confirm & Sell Membership').prop('disabled',false);
        if(r.error == 0) {
            showMembershipReceipt(cust_id, selPlanData, paid_now, selPlanData.total - paid_now, pay_mode);
            loadMemHistory(cust_id);
        } else {
            alert('Error: ' + r.msg);
        }
    });
});

function showMembershipReceipt(cust_id, plan, paid, remaining, pay_mode) {
    var cust_name = $('#sel_cust_name').text();
    var now = new Date();
    var dateStr = now.toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'}) + ' ' + now.toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit'});
    var expired_date = new Date();
    expired_date.setMonth(expired_date.getMonth() + parseInt(plan.validity));
    var expiryStr = expired_date.toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'});
    var html = '<div id="receipt_printable" style="font-family:Arial,sans-serif;font-size:13px;">' +
        '<div style="text-align:center;padding-bottom:12px;border-bottom:2px dashed #ccc;margin-bottom:12px;">' +
        '<div style="font-size:18px;font-weight:800;"><?= htmlspecialchars($salon_info['firm_name'] ?? '') ?></div>' +
        '<div style="font-size:11px;color:#666;"><?= htmlspecialchars($salon_info['salon_address'] ?? '') ?></div>' +
        '<div style="font-size:11px;color:#666;">GST: <?= htmlspecialchars($salon_info['salon_gst'] ?? '') ?></div></div>' +
        '<div style="margin-bottom:14px;"><strong>MEMBERSHIP RECEIPT</strong><br>' +
        'Customer: ' + cust_name + '<br>Date: ' + dateStr + '</div>' +
        '<table style="width:100%;border-collapse:collapse;font-size:12px;">' +
        '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;"><b>Plan</b></td><td style="text-align:right;border-bottom:1px solid #f1f5f9;">' + plan.plan_name + '</td></tr>' +
        '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;">Price</td><td style="text-align:right;border-bottom:1px solid #f1f5f9;">₹' + plan.price.toFixed(2) + '</td></tr>' +
        '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;">Wallet Credit</td><td style="text-align:right;color:#059669;font-weight:700;border-bottom:1px solid #f1f5f9;">₹' + plan.wallet.toFixed(2) + '</td></tr>' +
        '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;">Paid Now (' + pay_mode.toUpperCase() + ')</td><td style="text-align:right;font-weight:700;border-bottom:1px solid #f1f5f9;">₹' + paid.toFixed(2) + '</td></tr>' +
        (remaining > 0 ? '<tr><td style="padding:5px 0;border-bottom:1px solid #f1f5f9;color:#dc2626;">Balance Due</td><td style="text-align:right;color:#dc2626;font-weight:700;border-bottom:1px solid #f1f5f9;">₹' + remaining.toFixed(2) + '</td></tr>' : '') +
        '<tr><td style="padding:5px 0;">Valid Till</td><td style="text-align:right;">' + (paid >= plan.total ? expiryStr : 'After full payment') + '</td></tr>' +
        '</table>' +
        '<div style="text-align:center;margin-top:16px;padding-top:12px;border-top:2px dashed #ccc;font-size:11px;color:#888;">Thank you for choosing us!</div>' +
        '</div>';
    $('#mem_receipt_content').html(html);
    $('#mem_receipt_overlay').css('display','flex');
}

function printMembershipReceipt() {
    var content = document.getElementById('receipt_printable').innerHTML;
    var win = window.open('','_blank','width=400,height=600');
    win.document.write('<html><head><title>Membership Receipt</title>' +
        '<style>body{font-family:Arial,sans-serif;font-size:13px;padding:10px;}@page{margin:5mm;}</style>' +
        '</head><body>' + content + '</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); win.close(); }, 400);
}
</script>

<?php include 'footer.php'; ?>

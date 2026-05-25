<?php 
include 'header.php';

$job_card_id = isset($_GET['job_card_id']) ? $_GET['job_card_id'] : '';
$job_card = array();
$customer_name = "";
$customer_mobile = "";
$customer_gender = "";

if($job_card_id != ''){
    $query = "SELECT * FROM `hr_jobcard` WHERE `job_card_id` = '".mysqli_real_escape_string($conn, $job_card_id)."'";
    $job_card = select_row($query);

    if($job_card) {
        $query = "SELECT * FROM `hr_customer` WHERE `cust_id` = '".$job_card['cust_id']."'";
        $customer = select_row($query);
        if($customer) {
            $customer_name = $customer['cust_name'];
            $customer_mobile = $customer['cust_mobile'];
            $customer_gender = $customer['cust_gender'];
        }
    }
}

// Fetch categories, staff
$query = "SELECT * FROM `hr_servicesCategory` where salon_id='".$salon_id."' ORDER BY service_catName ASC";
$service_catNames = select_array($query);

// Fetch salon details for GST settings
$salon = select_row("SELECT salon_name,salon_address,salon_contact,salon_gst,gst_enable,gst_percentage,firm_name,round_off FROM `hr_salon` WHERE `salon_id` = $salon_id");
if($salon) extract($salon);

$query = "SELECT * FROM `hr_staff` where salon_id='".$salon_id."' and staff_status=1 ORDER BY staff_name ASC";
$staff = select_array($query);
?>

<!-- DataTables & Select2 CSS/JS via CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">POS Terminal</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Process sales, verify customer wallets, and track associate commissions.</p>
</div>

<form action="invoice.php" method="post" id="billingform" autocomplete="off" style="margin-bottom: 50px;">
    <input type="hidden" name="job_card_id" value="<?= htmlspecialchars($job_card_id) ?>" />
    
    <!-- 1. Customer Section -->
    <div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: visible; margin-bottom: 24px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px; background: #f8fafc;">
            <i class="ph-fill ph-user-circle" style="color: var(--primary); font-size: 24px;"></i>
            <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-main);">Customer Details</h3>
        </div>
        
        <div style="padding: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
            <input type="hidden" value="" name="cust_id" class="cust_id" />
            
            <div class="form-group">
                <label>Mobile Number</label>
                <div style="position: relative;">
                    <i class="ph ph-phone" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px;"></i>
                    <input value="<?= htmlspecialchars($customer_mobile) ?>" type="text" name="cust_mob" required class="form-control cust_mob auto-search" id="cust_mob" placeholder="Search by mobile or name" style="padding-left: 44px; background: white;" autocomplete="off">
                    <div class="autocomplete-dropdown" id="mob_autocomplete" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid var(--border-color); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:9999; max-height:250px; overflow-y:auto; margin-top:4px;"></div>
                </div>
            </div>

            <div class="form-group">
                <label>Customer Name</label>
                <div style="position: relative;">
                    <input type="text" value="<?= htmlspecialchars($customer_name) ?>" name="cust_name" required class="form-control required auto-search" id="cust_name" placeholder="Full Name" style="background: white;" autocomplete="off">
                    <div class="autocomplete-dropdown" id="name_autocomplete" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid var(--border-color); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:9999; max-height:250px; overflow-y:auto; margin-top:4px;"></div>
                </div>
            </div>

            <div class="form-group">
                <label>Invoice Date</label>
                <input required class="form-control date" type="date" id="invoice_date" name="invoice_date" value="<?= date('Y-m-d') ?>" style="background: white;">
            </div>

            <?php if(!$job_card_id): ?>
                <div class="form-group gender_check">
                    <label>Gender</label>
                    <select name="cust_gender" class="form-control" style="background: white;">
                        <option value="Female" <?= $customer_gender == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Male" <?= $customer_gender == 'Male' ? 'selected' : '' ?>>Male</option>
                    </select>
                </div>
                <!-- Source dropdown hidden for brevity on new UI unless explicitly requested, can be added later -->
            <?php endif; ?>

            <div id="customer_data_block" style="display: none; grid-column: 1 / -1; padding: 16px; border-radius: 12px; background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); border: 1px solid var(--primary-light);">
                <div style="display: flex; gap: 40px; align-items: center; flex-wrap: wrap;">
                    <div><span style="color: var(--text-muted); font-size: 13px;">Wallet Balance:</span> <span class="cust_wallet" style="font-weight: 700; color: var(--text-main); font-size: 16px;">₹0.00</span></div>
                    <div><span style="color: var(--text-muted); font-size: 13px;">Outstanding:</span> <span class="cust_outstanding" style="font-weight: 700; color: var(--danger); font-size: 16px;">₹0.00</span></div>
                    <div id="loyalty_pts_block" style="display:none;">
                        <span style="color: var(--text-muted); font-size: 13px;">Loyalty Points:</span>
                        <span id="loyalty_pts_val" style="font-weight: 800; color: #7c3aed; font-size: 16px;">0</span>
                        <span style="font-size:11px;color:#94a3b8;"> pts</span>
                        <button type="button" id="btn_redeem_pts" style="margin-left:8px;background:#7c3aed;color:white;border:none;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;">Redeem</button>
                    </div>
                    <button type="button" id="btn_view_history" class="btn-secondary" style="margin-left:auto; width:auto; padding: 6px 16px; font-size:13px;"><i class="ph ph-clock-counter-clockwise"></i> Last 10 Services</button>
                    <input type="hidden" name="check_wallet" id="check_wallet" />
                    <input type="hidden" name="redeem_points" id="redeem_points" value="0">
                </div>
                <!-- Redemption Banner (shown when redeeming) -->
                <div id="redeem_banner" style="display:none;margin-top:10px;padding:10px 14px;background:#f5f3ff;border:1px solid #c4b5fd;border-radius:10px;font-size:13px;display:flex;align-items:center;gap:10px;">
                    <i class="ph-fill ph-gift" style="color:#7c3aed;font-size:20px;"></i>
                    <span>Redeeming <strong id="redeem_pts_label" style="color:#7c3aed;">0 pts</strong> = <strong id="redeem_val_label" style="color:#059669;">₹0</strong> off this bill</span>
                    <button type="button" id="btn_cancel_redeem" style="margin-left:auto;background:#fee2e2;color:#dc2626;border:none;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;">Cancel</button>
                </div>
                <!-- Membership / Package Alerts Banner -->
                <div id="membership_alerts" style="margin-top:12px;"></div>
            </div>
        </div>
    </div>

    <!-- 2. Services Section -->
    <div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="ph-fill ph-scissors" style="color: var(--primary); font-size: 24px;"></i>
                <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-main);">Billing Items</h3>
            </div>
            <button type="button" id="btn_add_row" class="btn-primary" style="width: auto; padding: 8px 16px; margin: 0; font-size: 13px; box-shadow: none;">
                <i class="ph-bold ph-plus"></i> Add Item
            </button>
        </div>
        
        <div style="padding: 24px; overflow-x: auto;">
            <table class="table-modern" id="item_table" style="width: 100%; min-width: 800px;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Service / Product</th>
                        <th style="width: 25%;">Assigned Staff</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 15%;">Price (₹)</th>
                        <?php if(isset($gst_enable) && $gst_enable != "no"): ?>
                        <th style="width: 10%;">Tax</th>
                        <?php endif; ?>
                        <th style="width: 10%; text-align: right;">Total</th>
                        <th style="width: 40px;"></th>
                    </tr>
                </thead>
                <tbody id="billing_tbody">
                    <!-- Lines injected via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Checkout Summary -->
    <div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
        <div style="padding: 30px 24px; display: grid; grid-template-columns: 1fr 400px; gap: 40px;">
            
            <!-- Left Side: Notes & Discounts -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div class="form-group">
                    <label>Discount</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <select class="form-control text-right" id="discount_mode" name="discount_mode" style="width: 100px; background: white;">
                            <option value="1">% Percent</option>
                            <option value="0">₹ Amount</option>
                        </select>
                        <input class="form-control text-right calcEvent" id="discount" step="any" min="0" name="discount" type="number" placeholder="0.00" style="background: white; flex: 1;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Tip / Extra Fee</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input class="form-control text-right calcEvent" id="extra_tax" step="any" min="0" name="extra_tax" type="number" placeholder="0.00" style="background: white; flex: 1;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select class="form-control payment_mode bg-white" name="payment_mode" style="background: white;">
                        <?php
                        $pay_methods = select_array("SELECT * FROM `hr_payment_methods` WHERE (`salon_id`='$salon_id' OR `is_global`=1) AND `status`=1 ORDER BY `sort_order` ASC");
                        if(!$pay_methods) {
                            $pay_methods = [
                                ['method_key'=>'cash','method_name'=>'Cash'],
                                ['method_key'=>'card','method_name'=>'Card / POS'],
                                ['method_key'=>'upi','method_name'=>'UPI / Online'],
                            ];
                        }
                        foreach($pay_methods as $pm):
                            // wallet & pkg are always added below as system-defined
                            if(in_array($pm['method_key'], ['wallet','pkg','package'])) continue;
                        ?>
                            <option value="<?= htmlspecialchars($pm['method_key']) ?>"><?= htmlspecialchars($pm['method_name']) ?></option>
                        <?php endforeach; ?>
                        <option value="wallet">💳 Wallet Balance</option>
                        <option value="pkg">📦 Package Sessions</option>
                        <option value="split">🔄 Split Payment</option>
                    </select>
                </div>

                <!-- Split Payment Panel -->
                <div id="split_pay_panel" style="display:none;padding:14px;background:#f8fafc;border-radius:12px;border:1px solid #cbd5e1;margin-bottom:14px;">
                    <div style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:10px;"><i class="ph ph-arrows-split"></i> Split Payment Details</div>
                    <div id="split_wallet_banner" style="display:none;margin-bottom:10px;padding:9px 13px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;font-size:12px;color:#065f46;">
                        <i class="ph-fill ph-wallet" style="color:#059669;"></i>
                        Wallet balance: <strong id="split_wallet_avail">₹0.00</strong> will be applied. Remaining amount via Mode 2.
                    </div>
                    <div style="display: flex; gap: 12px; margin-bottom: 8px;">
                        <div style="flex:1">
                            <label style="font-size:11px;color:#475569">Amount 1</label>
                            <input type="number" class="form-control calcEvent" id="split_amount_1" name="part_cash" step="any" min="0" placeholder="0.00" style="background:white; border-color:#cbd5e1;">
                        </div>
                        <div style="flex:1">
                            <label style="font-size:11px;color:#475569">Mode 1</label>
                            <select name="part_cash_mode" id="split_mode_1" class="form-control" style="background:white; border-color:#cbd5e1;">
                                <?php foreach($pay_methods as $pm): if(in_array($pm['method_key'], ['pkg','package'])) continue; ?>
                                <option value="<?= htmlspecialchars($pm['method_key']) ?>"><?= htmlspecialchars($pm['method_name']) ?></option>
                                <?php endforeach; ?>
                                <option value="wallet">💳 Wallet Balance</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <div style="flex:1">
                            <label style="font-size:11px;color:#475569">Amount 2</label>
                            <input type="number" class="form-control" id="split_amount_2" name="part_cc" readonly placeholder="0.00" style="background:#f1f5f9; border-color:#e2e8f0; color:#475569">
                        </div>
                        <div style="flex:1">
                            <label style="font-size:11px;color:#475569">Mode 2</label>
                            <select name="part_cc_mode" id="split_mode_2" class="form-control" style="background:white; border-color:#cbd5e1;">
                                <?php foreach($pay_methods as $pm): if(in_array($pm['method_key'], ['wallet','pkg','package','cash'])) continue; ?>
                                <option value="<?= htmlspecialchars($pm['method_key']) ?>"><?= htmlspecialchars($pm['method_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="split_low_warn" style="display:none;margin-top:8px;padding:8px 12px;background:#fef3c7;border-radius:8px;font-size:12px;color:#92400e;border:1px solid #fcd34d;">
                        <i class="ph ph-warning"></i> Amount 1 exceeds Grand Total!
                    </div>
                    <div id="split_wallet_low_warn" style="display:none;margin-top:8px;padding:8px 12px;background:#fef3c7;border-radius:8px;font-size:12px;color:#92400e;border:1px solid #fcd34d;">
                        <i class="ph ph-warning"></i> Wallet balance is insufficient to cover Amount 1. Please adjust.
                    </div>
                </div>

                <!-- Wallet Balance Panel -->
                <div id="wallet_pay_panel" style="display:none;padding:14px;background:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0;margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-size:12px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Wallet Available</div>
                            <div style="font-size:24px;font-weight:800;color:#059669;" id="wallet_avail_balance">₹0.00</div>
                        </div>
                        <button type="button" id="btn_use_full_wallet" style="background:#059669;color:white;border:none;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                            <i class="ph ph-arrow-right"></i> Apply to Bill
                        </button>
                    </div>
                    <div id="wallet_low_warn" style="display:none;margin-top:8px;padding:8px 12px;background:#fef3c7;border-radius:8px;font-size:12px;color:#92400e;border:1px solid #fcd34d;">
                        <i class="ph ph-warning"></i> Wallet balance is less than invoice total. Collect the difference separately.
                    </div>
                </div>

                <!-- Package Sessions Panel -->
                <div id="pkg_pay_panel" style="display:none;padding:14px;background:#fff7ed;border-radius:12px;border:1px solid #fed7aa;margin-bottom:14px;">
                    <div style="font-size:12px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;"><i class="ph ph-package"></i> Active Packages</div>
                    <div id="pkg_no_cust_msg" style="font-size:13px;color:var(--text-muted);">Select a customer first to see their active packages.</div>
                    <div id="pkg_select_wrap" style="display:none;">
                        <select id="pos_active_pkg_select" class="form-control" style="margin-bottom:10px;">
                            <option value="">-- Select Package --</option>
                        </select>
                        <div id="pos_pkg_services" style="font-size:13px;color:var(--text-muted);"></div>
                        <input type="hidden" name="active_cp_id" id="pos_cp_id_hidden" value="">
                    </div>
                </div>

                <div class="form-group">
                    <label>Billing Remark</label>
                    <textarea class="form-control" id="billing_remark" name="billing_remark" rows="3" placeholder="Additional notes about this sale..."></textarea>
                </div>
            </div>

            <!-- Right Side: Totals -->
            <div style="background: #f8fafc; border-radius: 16px; padding: 24px; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 16px;">
                
                <div style="display: flex; justify-content: space-between; font-size: 15px; color: var(--text-main);">
                    <span>Subtotal</span>
                    <span id="subTotal" style="font-weight: 600;">₹0.00</span>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 15px; color: var(--text-main);">
                    <span>Discount Value</span>
                    <span id="discount_value_display" style="font-weight: 600; color: var(--success);">-₹0.00</span>
                </div>

                <?php if(isset($gst_enable) && $gst_enable != "no"): ?>
                <div style="display: flex; justify-content: space-between; font-size: 15px; color: var(--text-main);">
                    <span>Tax Total</span>
                    <span id="taxTotal" style="font-weight: 600;">₹0.00</span>
                </div>
                <?php endif; ?>

                <div style="height: 1px; background: var(--border-color); margin: 8px 0;"></div>

                <div style="display: flex; justify-content: space-between; font-size: 18px; color: var(--text-main); font-weight: 700;">
                    <span>Grand Total</span>
                    <span id="grandTotal" style="color: var(--primary);">₹0.00</span>
                    <input class="grandTotal" name="grandTotal" value="0" type="hidden">
                    <input type="hidden" id="wallet_applied_amount" value="0">
                </div>
                
                <div id="loyalty_summary_row" style="display: none; justify-content: space-between; font-size: 15px; color: var(--text-main);">
                    <span>Loyalty Redeemed</span>
                    <span id="loyalty_summary_val" style="font-weight: 600; color: var(--success);">-₹0.00</span>
                </div>

                <div id="wallet_summary_row" style="display: none; justify-content: space-between; font-size: 15px; color: var(--text-main);">
                    <span>Wallet Applied</span>
                    <span id="wallet_summary_val" style="font-weight: 600; color: var(--success);">-₹0.00</span>
                </div>

                <div id="payable_summary_row" style="display: none; justify-content: space-between; font-size: 18px; color: var(--danger); font-weight: 700; margin-top: 8px; border-top: 1px dashed var(--border-color); padding-top: 8px;">
                    <span>Amount to Pay</span>
                    <span id="payable_summary_val">₹0.00</span>
                </div>

                <button type="submit" name="save_bill_print" class="btn-primary" style="margin-top: auto; width: 100%; padding: 16px; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
                    <i class="ph-bold ph-printer"></i> Process & Checkout
                </button>
            </div>

        </div>
    </div>
</form>

<!-- Customer History Modal -->
<div class="modal-overlay" id="customerHistoryModal">
    <div class="modal-v3">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid var(--border-color); background: #f8fafc;">
            <h3 style="margin:0; font-size:16px; font-weight:600;"><i class="ph ph-clock-counter-clockwise" style="color:var(--primary); margin-right:8px;"></i> Last 10 Services</h3>
            <button type="button" class="close-modal" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-muted);"><i class="ph ph-x"></i></button>
        </div>
        <div class="modal-body-scroll" style="padding: 24px;">
            <table class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Service</th>
                        <th>Staff</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody id="history_tbody">
                    <tr><td colspan="4" style="text-align:center; padding: 20px;">Loading history...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Modern Table Scoping */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern th { color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 2px solid var(--border-color); text-align: left; }
.table-modern td { padding: 12px 16px; font-size: 14px; position: relative; border-bottom: 1px solid var(--border-color); }

/* Select2 Modern Overrides */
.select2-container--default .select2-selection--single {
    height: 48px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background-color: white;
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--multiple {
    min-height: 48px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background-color: white;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    font-size: 14.5px;
    color: var(--text-main);
    padding-left: 16px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 46px;
    right: 12px;
}
</style>

<script>
// Mock data for initial rendering
var availableServices = <?php 
    $optgroups = [];
    foreach($service_catNames as $name) {
        $query = "SELECT * FROM `hr_services` WHERE `service_catid` = '".$name['service_catid']."' AND `service_status` = 1 ORDER BY `service_id` ASC";
        $services = select_array($query);
        if($services) {
            $optgroups[] = [
                'text' => $name['service_catName'],
                'children' => array_map(function($s) use ($name) {
                    return ['id' => 's_'.$s['service_id'], 'text' => $s['service_name'], 'cat_id' => $name['service_catid']];
                }, $services)
            ];
        }
    }

    $query = "SELECT p.*, b.brand_name FROM `hr_product` p LEFT JOIN `hr_product_brand` b ON p.brand_id = b.brand_id WHERE p.`salon_id` = '".$salon_id."' AND p.`product_status` = 1 ORDER BY p.`product_name` ASC";
    $products = select_array($query);
    if($products) {
        $optgroups[] = [
            'text' => 'Products',
            'children' => array_map(function($p) {
                $brand = $p['brand_name'] ? " ({$p['brand_name']})" : "";
                return ['id' => 'p_'.$p['product_id'], 'text' => $p['product_name'].$brand, 'cat_id' => 'product'];
            }, $products)
        ];
    }

    echo json_encode($optgroups);
?>;

var availableStaff = <?php 
    echo json_encode(array_map(function($s) {
        return ['id' => $s['staff_id'], 'text' => $s['staff_name']];
    }, $staff));
?>;

// Initialize variables directly from PHP Outlet Data
var gstEnabled = <?= isset($gst_enable) && $gst_enable == '1' ? 'true' : 'false' ?>;
var gstInclusive = <?= isset($include_gst) && $include_gst == '1' ? 'true' : 'false' ?>;
var taxValue = <?= isset($gst_percentage) ? floatval($gst_percentage) : 18.00 ?>;
var roundOff = <?= isset($round_off) && $round_off == '1' ? 'true' : 'false' ?>;
var rowCount = 0;

function addBillingRow(prefillData = null) {
    var tr = $('<tr class="item-row" id="row_'+rowCount+'"></tr>');
    
    // Service Select
    var tdService = $('<td></td>');
    var selectService = $('<select name="sub_service[]" class="form-control select2-service required" style="width: 100%;"><option value="0">Search Service...</option></select>');
    
    availableServices.forEach(function(group) {
        var optgroup = $('<optgroup label="'+group.text+'"></optgroup>');
        group.children.forEach(function(item) {
            var selected = (prefillData && prefillData.service_id == item.id) ? 'selected' : '';
            optgroup.append($('<option value="'+item.id+'" data-catid="'+item.cat_id+'" '+selected+'>'+item.text+'</option>'));
        });
        selectService.append(optgroup);
    });
    tdService.append(selectService);
    tdService.append('<input type="hidden" name="service_catid[]" class="row-catid" />');
    // Variation picker — hidden until a service with variations is selected
    tdService.append(
        '<div class="row-variation-wrap" style="display:none; margin-top:6px;">'+
            '<select name="service_var[]" class="form-control row-var-select" style="font-size:12px; height:36px; padding:4px 10px; background:#f0fdf4; border-color:#86efac; color:#15803d;">'+
                '<option value="">— Select Variation —</option>'+
            '</select>'+
        '</div>'
    );
    tdService.append('<input type="hidden" name="service_var_id[]" class="row-var-id" value="">');

    // Staff Select (Multiple)
    var tdStaff = $('<td></td>');
    var selectStaff = $('<select name="service_staff['+rowCount+'][]" multiple="multiple" class="form-control select2-staff required" style="width: 100%;"></select>');
    availableStaff.forEach(function(staff) {
        var selected = (prefillData && prefillData.staff_services && prefillData.staff_services.includes(staff.id)) ? 'selected' : '';
        selectStaff.append($('<option value="'+staff.id+'" '+selected+'>'+staff.text+'</option>'));
    });
    tdStaff.append(selectStaff);

    // Qty
    var tdQty = $('<td><input name="service_qty[]" type="number" class="form-control calcEvent row-qty" min="1" value="1" style="background:white; padding: 6px 12px;"></td>');

    // Price
    var tdPrice = $('<td><input name="service_price[]" type="number" class="form-control calcEvent row-price" step="any" min="0" style="background:white; padding: 6px 12px;"><input type="hidden" name="service_price_org[]" class="row-price-org"></td>');

    // Tax (Optional)
    var tdTax = '';
    if(gstEnabled) {
        tdTax = $('<td><input type="hidden" name="service_gst[]" class="row-gst" value="'+taxValue+'"><span style="font-size: 13px; color: var(--text-muted); font-weight: 500;">'+taxValue+'%</span></td>');
    }

    // Total
    var tdTotal = $('<td style="text-align: right; font-weight: 600; color: var(--text-main); font-size: 15px;"><span class="row-total-display">0.00</span><input type="hidden" name="service_total[]" class="row-total"></td>');

    // Remove Action
    var tdAction = $('<td style="text-align: right;"><button type="button" class="btn-remove-row" style="background: #fee2e2; color: #dc2626; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; transition: 0.2s;"><i class="ph ph-trash"></i></button></td>');

    // Assemble
    tr.append(tdService).append(tdStaff).append(tdQty).append(tdPrice);
    if(gstEnabled) tr.append(tdTax);
    tr.append(tdTotal).append(tdAction);

    $('#billing_tbody').append(tr);

    // Init Select2
    tr.find('.select2-service').select2();
    tr.find('.select2-staff').select2({ placeholder: "Assign Staff" });

    if(prefillData && prefillData.service_id) {
        tr.find('.select2-service').trigger('change');
    }

    rowCount++;
}

// Calculate
var activeCustGstOnService = true;

function calculateGrandTotal() {
    var subtotal = 0;
    var taxtotal = 0;
    
    var currentGstEnabled = gstEnabled;
    if ($('.payment_mode').val() === 'wallet' && !activeCustGstOnService) {
        currentGstEnabled = false;
    }

    $('.item-row').each(function() {
        var qty = parseFloat($(this).find('.row-qty').val()) || 0;
        var displayPrice = parseFloat($(this).find('.row-price').val()) || 0;
        var rowTotal = qty * displayPrice;
        
        $(this).find('.row-total-display').text(rowTotal.toFixed(2));
        $(this).find('.row-total').val(rowTotal.toFixed(2));
        
        if(currentGstEnabled) {
            var gstPct = parseFloat($(this).find('.row-gst').val()) || 0;
            if(gstInclusive) {
                // The display price ALREADY includes the tax
                // taxable_base = total / (1 + (gst/100))
                var baseAmount = rowTotal / (1 + (gstPct/100));
                var taxAmount = rowTotal - baseAmount;
                subtotal += baseAmount;
                taxtotal += taxAmount;
            } else {
                // The display price is the base price, tax is added on top
                subtotal += rowTotal;
                taxtotal += (rowTotal * gstPct / 100);
            }
        } else {
            subtotal += rowTotal;
        }
    });

    var discountMode = parseInt($('#discount_mode').val());
    var discountInput = parseFloat($('#discount').val()) || 0;
    var extraTaxInput = parseFloat($('#extra_tax').val()) || 0;
    var discountValue = 0;

    if(discountMode === 1) { // percentage
        discountValue = subtotal * (discountInput / 100);
    } else { // fixed
        discountValue = discountInput;
    }

    var grandTotal = gstInclusive ? (subtotal + taxtotal - discountValue) : (subtotal - discountValue + taxtotal);
    grandTotal += extraTaxInput;
    
    if ($('.payment_mode').val() !== 'pkg' && roundOff) {
        grandTotal = Math.round(grandTotal);
    }
    
    $('#subTotal').text('₹' + subtotal.toFixed(2));
    $('#discount_value_display').text('-₹' + discountValue.toFixed(2));
    if(gstEnabled) {
        // Even if gst is globally enabled, if currentGstEnabled is false, taxtotal is 0
        $('#taxTotal').text('₹' + taxtotal.toFixed(2));
    }
    
    $('#grandTotal').text('₹' + grandTotal.toFixed(2));
    $('.grandTotal').val(grandTotal.toFixed(2));

    var payable = grandTotal;

    // Apply loyalty visually
    var loyaltyPts = parseFloat($('#redeem_points').val()) || 0;
    if (loyaltyPts > 0) {
        $('#loyalty_summary_row').css('display', 'flex');
        $('#loyalty_summary_val').text('-₹' + loyaltyPts.toFixed(2));
        payable -= loyaltyPts;
    } else {
        $('#loyalty_summary_row').hide();
    }

    // Apply wallet visually
    var walletApplied = parseFloat($('#wallet_applied_amount').val()) || 0;
    if (walletApplied > 0) {
        $('#wallet_summary_row').css('display', 'flex');
        $('#wallet_summary_val').text('-₹' + walletApplied.toFixed(2));
        payable -= walletApplied;
    } else {
        $('#wallet_summary_row').hide();
    }

    if (loyaltyPts > 0 || walletApplied > 0) {
        $('#payable_summary_row').css('display', 'flex');
        $('#payable_summary_val').text('₹' + Math.max(0, payable).toFixed(2));
    } else {
        $('#payable_summary_row').hide();
    }

    // Auto-calculate split amounts if open
    if ($('.payment_mode').val() === 'split') {
        var mode1 = $('#split_mode_1').val();
        var part1 = parseFloat($('#split_amount_1').val()) || 0;

        // If wallet is Mode 1, cap part1 to wallet balance
        if (mode1 === 'wallet') {
            var walletBal = parseFloat($('#wallet_avail_balance').text().replace('₹','').replace(/,/g,'')) || 0;
            if (part1 > walletBal) {
                part1 = walletBal;
                $('#split_amount_1').val(walletBal.toFixed(2));
            }
            $('#split_wallet_low_warn').toggle(parseFloat($('#split_amount_1').val()) > walletBal);
        } else {
            $('#split_wallet_low_warn').hide();
        }

        var part2 = grandTotal - part1;
        $('#split_amount_2').val(part2 >= 0 ? part2.toFixed(2) : '0.00');
        $('#split_low_warn').toggle(part1 > grandTotal || part1 < 0);
    }
}

// Events
$(document).ready(function() {
    
    var existingServices = <?php 
        if(isset($job_card_id) && $job_card_id != '') { // Pre-fetch job card services array to pass to JS
            $q = "SELECT * FROM `hr_jobcardservice` WHERE `job_card_id` = '".$job_card_id."' AND `delete_status` = 'active' ORDER BY `job_card_service_id` ASC";
            $arr = select_array($q) ?: [];
            foreach($arr as $k => $v) {
                $q2 = "SELECT js.staff_id FROM `hr_jobcardstaff` js WHERE js.`job_card_service_id` = '".$v['job_card_service_id']."' AND js.`delete_status` = 'active'";
                $arr[$k]['staff_services'] = array_column(select_array($q2) ?: [], 'staff_id');
                $arr[$k]['service_id'] = 's_'.$v['service_id'];
            }
            echo json_encode($arr);
        } else {
            echo "[]";
        }
    ?>;

    // Initialized at bottom after events are bound

    $('#btn_add_row').click(function() {
        addBillingRow();
    });

    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        calculateGrandTotal();
    });

    $(document).on('change keyup', '.calcEvent', function() {
        calculateGrandTotal();
    });

    $(document).on('change', '.select2-service', function() {
        var tr = $(this).closest('tr');
        var catid = $(this).find(':selected').attr('data-catid');
        tr.find('.row-catid').val(catid);

        // Clear any previous variation state
        tr.find('.row-variation-wrap').hide();
        tr.find('.row-var-select').html('<option value="">— Select Variation —</option>');
        tr.find('.row-var-id').val('');
        tr.find('.row-price').prop('readonly', false).css('opacity', 1);
        
        var serviceId = $(this).val();
        if(serviceId != "0") {
            if (gstEnabled) {
                var isProduct = String(serviceId).startsWith('p_');
                tr.find('.row-gst').val(isProduct ? 0 : taxValue);
                tr.find('.row-gst').siblings('span').text(isProduct ? '0%' : (taxValue + '%'));
            }

            // Only fetch variations for real services (not products)
            var isProduct2 = String(serviceId).startsWith('p_');
            var numericId  = String(serviceId).replace(/^[sp]_/, '');

            // Step 1: Get base price
            $.ajax({
                type: "POST", url: "ajax/salon_ajax.php",
                data: { method: 'service_price', service_id: serviceId },
                success: function(res) {
                    var priceStr = String(res).replace(/[^0-9.]/g, '');
                    var price = parseFloat(priceStr) || 0;
                    tr.find('.row-price').val(price);
                    tr.find('.row-price-org').val(price);
                    calculateGrandTotal();
                }
            });

            // Step 2: Check for variations (services only)
            if(!isProduct2) {
                $.ajax({
                    type: "POST", url: "ajax/salon_ajax.php",
                    data: { method: 'get_service_variations', service_id: numericId },
                    success: function(res) {
                        try {
                            var vars = JSON.parse(res);
                            if(vars && vars.length > 0) {
                                // Populate the variation picker
                                var opts = '<option value="">— Select Variation —</option>';
                                vars.forEach(function(v) {
                                    opts += '<option value="'+v.var_id+'" data-price="'+v.var_price+'">'+v.var_name+' (\u20b9'+parseFloat(v.var_price).toFixed(0)+')</option>';
                                });
                                tr.find('.row-var-select').html(opts);
                                tr.find('.row-variation-wrap').show();
                                // Lock price until variation is chosen
                                tr.find('.row-price').val('').prop('readonly', true).css('opacity', 0.5);
                                tr.find('.row-price-org').val('');
                                calculateGrandTotal();
                            }
                        } catch(e) {}
                    }
                });
            }
        }
    });

    // When a variation is picked, update price
    $(document).on('change', '.row-var-select', function() {
        var tr = $(this).closest('tr');
        var selected = $(this).find(':selected');
        var varId = $(this).val();
        if(varId) {
            var price = parseFloat(selected.data('price')) || 0;
            tr.find('.row-var-id').val(varId);
            tr.find('.row-price').val(price).prop('readonly', false).css('opacity', 1);
            tr.find('.row-price-org').val(price);
            calculateGrandTotal();
        } else {
            tr.find('.row-var-id').val('');
            tr.find('.row-price').val('').prop('readonly', true).css('opacity', 0.5);
            tr.find('.row-price-org').val('');
            calculateGrandTotal();
        }
    });


    // Customer Autocomplete Search
    var searchTimer;
    $('.auto-search').on('keyup focus', function() {
        var input = $(this);
        var val = input.val().trim();
        var dropdown = input.siblings('.autocomplete-dropdown');
        
        clearTimeout(searchTimer);
        
        if(val.length < 3) {
            $('.autocomplete-dropdown').hide();
            return;
        }

        searchTimer = setTimeout(function() {
            $.ajax({
                type: "POST",
                url: "ajax/customer_ajax.php",
                data: { method: 'get_customer_from_mobile', cust_mob: val, detail: 2 },
                success: function(res) {
                    try {
                        var data = JSON.parse(res);
                        dropdown.empty();
                        if(data && data.length > 0) {
                            data.forEach(function(cust) {
                                var item = $('<div class="dropdown-item" style="padding:10px 16px; cursor:pointer; border-bottom:1px solid #f1f5f9; hover:background:#f8fafc;">' +
                                    '<div style="font-weight:600; color:var(--text-main); font-size:14px;">'+cust.cust_name+'</div>' +
                                    '<div style="font-size:12px; color:var(--text-muted);">'+cust.cust_mobile+'</div>' +
                                '</div>');
                                
                                item.on('mousedown', function(e) { e.preventDefault(); }); // prevent blur hiding
                                item.on('click', function() {
                                    $('.autocomplete-dropdown').hide();
                                    selectCustomer(cust);
                                });
                                dropdown.append(item);
                            });
                            $('.autocomplete-dropdown').hide(); // hide others
                            dropdown.show();
                        } else {
                            dropdown.hide();
                            clearCustomerData();
                        }
                    } catch(e) {
                        dropdown.hide();
                    }
                }
            });
        }, 300);
    });

    // Hide dropdowns on click outside
    $(document).on('click', function(e) {
        if(!$(e.target).closest('.autocomplete-dropdown, .auto-search').length) {
            $('.autocomplete-dropdown').hide();
        }
    });

    // Handle Form Submit
    $('#billingform').submit(function(e) {
        var isValid = true;
        var rowCount = $('#billing_tbody tr').length;
        
        if(rowCount === 0) {
            alert('Please add at least one service or product billing item.');
            e.preventDefault();
            return false;
        }

        // Check if rows have assigned staff and valid service
        $('#billing_tbody tr').each(function(index) {
            var rowNum = index + 1;

            var service = $(this).find('.select2-service').val();
            if(!service || service == "0" || service == "") {
                 alert('Row ' + rowNum + ': Please select a valid service/product.');
                 isValid = false;
                 return false;
            }

            var staff = $(this).find('.select2-staff').val();
            if(!staff || staff.length === 0) {
                 alert('Row ' + rowNum + ': Please assign at least one staff member.');
                 isValid = false;
                 return false;
            }

            var varWrap = $(this).find('.row-variation-wrap');
            if(varWrap.is(':visible')) {
                var variation = $(this).find('.row-var-select').val();
                if(!variation || variation === "") {
                    alert('Row ' + rowNum + ': Please select a variation for the service.');
                    isValid = false;
                    return false;
                }
            }

            var qty = parseFloat($(this).find('.row-qty').val());
            if(isNaN(qty) || qty <= 0) {
                alert('Row ' + rowNum + ': Please enter a valid quantity greater than 0.');
                isValid = false;
                return false;
            }

            var price = $(this).find('.row-price').val();
            if(price === "" || isNaN(parseFloat(price)) || parseFloat(price) < 0) {
                alert('Row ' + rowNum + ': Please enter a valid price.');
                isValid = false;
                return false;
            }
        });

        if(!isValid) {
            e.preventDefault();
            return false;
        }

        // ── Package Payment Validation ──────────────────────────────────
        var payMode = $('.payment_mode').val();
        if(payMode === 'pkg') {
            var cp_id = String($('#pos_cp_id_hidden').val() || '').trim();
            if(!cp_id) {
                alert('You selected "Package Sessions" as payment mode but did not select an active package.\n\nPlease select a package from the Package Sessions panel.');
                e.preventDefault();
                return false;
            }

            // Build set of service IDs covered by the selected package (strict string match)
            var pkgServices = window.activePackages ? window.activePackages.filter(function(p){ 
                return String(p.cp_id).trim() === cp_id; 
            }) : [];
            var coveredServiceIds = pkgServices.map(function(s){ return String(s.service_id).trim(); });

            // Check every billing row
            var invalidServices = [];
            // Reset any previous highlights
            $('#billing_tbody tr').css('outline','');

            $('#billing_tbody tr').each(function() {
                var $select = $(this).find('.select2-service');
                if($select.length === 0) return; // skip if not a service row

                var svcId   = String($select.val() || '').trim();
                var svcName = $select.find('option:selected').text().trim();

                // Skip products / empty rows ('0', '', 'undefined')
                if(!svcId || svcId === '0' || svcId === 'undefined') return;
                
                // Exclude products from package validation entirely
                if(svcId.startsWith('p_')) return;
                
                // Strip 's_' prefix for comparison
                var actualSvcId = svcId.replace('s_', '');

                // Check if this service is in the allowed list
                if(coveredServiceIds.length > 0 && coveredServiceIds.indexOf(actualSvcId) === -1) {
                    $(this).css('outline','2px solid #dc2626');
                    invalidServices.push(svcName || 'Unknown Service');
                }
            });

            if(invalidServices.length > 0) {
                var covered = pkgServices.map(function(s){ return s.service_name; }).join(', ') || 'None';
                alert(
                    '⚠ Package Mismatch!\n\n' +
                    'The following service(s) are NOT covered by the selected package:\n• ' +
                    invalidServices.join('\n• ') +
                    '\n\nThe selected package only covers:\n  ' + covered +
                    '\n\nPlease remove the non-covered services from the bill, or change the payment mode.'
                );
                e.preventDefault();
                return false;
            }
        }
        // ────────────────────────────────────────────────────────────────

        // Prepare for submission by locking the button
        var btn = $(this).find('button[type="submit"]');
        btn.html('<i class="ph-bold ph-spinner ph-spin"></i> Processing...').css('pointer-events', 'none');
        return true; 
    });

    function selectCustomer(customer) {
        $('.cust_id').val(customer.cust_id);
        $('#cust_name').val(customer.cust_name);
        $('#cust_mob').val(customer.cust_mobile);
        if(customer.cust_gender) { $('select[name="cust_gender"]').val(customer.cust_gender); }
        var wallet = parseFloat(customer.cust_wallet || 0);
        $('.cust_wallet').text('\u20b9' + wallet.toFixed(2));
        $('.cust_outstanding').text('\u20b9' + parseFloat(customer.cust_outstanding || 0).toFixed(2));
        $('#wallet_avail_balance').text('\u20b9' + wallet.toFixed(2));
        
        activeCustGstOnService = (String(customer.gst_on_service) === "0") ? false : true;
        
        $('#customer_data_block').slideDown(200);
        buildMembershipAlerts(customer);
        loadActivePackages(customer.cust_id);
        loadLoyaltyPoints(customer.cust_id);
        // Reset any previous redemption
        cancelRedemption();
        calculateGrandTotal();
    }

    // ── Loyalty Points ────────────────────────────────────────────────────────
    var _loyaltyBalance = 0;

    function loadLoyaltyPoints(cust_id) {
        $.post('ajax/loyalty_ajax.php', { method: 'get_points_for_pos', cust_id: cust_id }, function(res) {
            try {
                var d = JSON.parse(res);
                _loyaltyBalance = parseFloat(d.balance || 0);
                if (_loyaltyBalance > 0) {
                    $('#loyalty_pts_val').text(Math.floor(_loyaltyBalance));
                    $('#loyalty_pts_block').show();
                } else {
                    $('#loyalty_pts_block').hide();
                }
            } catch(e) { $('#loyalty_pts_block').hide(); }
        });
    }

    $('#btn_redeem_pts').click(function() {
        if (_loyaltyBalance <= 0) return;
        var total = parseFloat($('.grandTotal').val() || 0);
        // Max redemption: min(points balance, 50% of bill)
        var maxRedeem = Math.min(Math.floor(_loyaltyBalance), Math.floor(total * 0.5));
        if (maxRedeem <= 0) { alert('Not enough points to redeem (minimum ₹1 off needed, max 50% of bill).'); return; }
        var toRedeem = prompt('You have ' + Math.floor(_loyaltyBalance) + ' points (= ₹' + Math.floor(_loyaltyBalance) + ' value).\nMax redeemable on this bill: ' + maxRedeem + ' pts (50% of total).\n\nEnter points to redeem (1–' + maxRedeem + '):', maxRedeem);
        if (toRedeem === null) return;
        toRedeem = Math.min(parseInt(toRedeem) || 0, maxRedeem);
        if (toRedeem <= 0) return;
        $('#redeem_points').val(toRedeem);
        $('#redeem_pts_label').text(toRedeem + ' pts');
        $('#redeem_val_label').text('₹' + toRedeem);
        $('#redeem_banner').show();
        // Recalculate summary to show loyalty
        calculateGrandTotal();
    });

    $('#btn_cancel_redeem').click(function() { cancelRedemption(); });

    function cancelRedemption() {
        $('#redeem_points').val(0);
        $('#redeem_banner').hide();
        calculateGrandTotal();
    }
    // ─────────────────────────────────────────────────────────────────────────

    window.activePackages = [];

    function loadActivePackages(cust_id) {
        $.post('ajax/membership_ajax.php', {method:'get_active_packages_for_billing', cust_id:cust_id}, function(res){
            try {
                var data = JSON.parse(res);
                window.activePackages = Array.isArray(data) ? data : [];
                updatePackageSuggestions();
                // If package mode is currently selected, auto-refresh dropdown
                if($('.payment_mode').val() === 'pkg') { renderPosPkgDropdown(); }
            } catch(e) { window.activePackages = []; }
        });
    }

    function buildMembershipAlerts(customer) {
        var alerts = '';
        var wallet = parseFloat(customer.cust_wallet || 0);
        if(wallet > 0) {
            alerts += '<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;font-size:13px;margin-bottom:8px;">' +
                '<i class="ph-fill ph-wallet" style="color:#059669;font-size:18px;"></i>' +
                '<span>Customer has <strong style="color:#059669;">₹' + wallet.toFixed(2) + '</strong> wallet balance available. Select <strong>Wallet</strong> as payment mode to use it.</span>' +
                '</div>';
        }
        // Package alerts will be added when service rows are selected
        $('#membership_alerts').html(alerts);
    }

    function updatePackageSuggestions() {
        if(!window.activePackages || window.activePackages.length === 0) return;
        // Check each billing row for package-eligible services
        var pkgAlert = '';
        var alreadyAlerted = {};
        window.activePackages.forEach(function(pkg){
            if(!alreadyAlerted[pkg.cp_id]) {
                alreadyAlerted[pkg.cp_id] = true;
                pkgAlert += '<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff7ed;border-radius:10px;border:1px solid #fed7aa;font-size:13px;margin-bottom:8px;">' +
                    '<i class="ph-fill ph-package" style="color:#d97706;font-size:18px;"></i>' +
                    '<span>Customer has active package: <strong>' + pkg.package_name + '</strong> — ' + pkg.service_name + ' (₹' + pkg.remaining + ' sessions left, expires ' + pkg.expiry_date + '). Select <strong>Package</strong> as payment mode.</span>' +
                    '</div>';
            }
        });
        // Build full alerts
        var wallet = parseFloat($('.cust_wallet').text().replace('₹','')) || 0;
        var baseAlert = wallet > 0 ? '<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;font-size:13px;margin-bottom:8px;"><i class="ph-fill ph-wallet" style="color:#059669;font-size:18px;"></i><span>Customer has <strong style="color:#059669;">₹' + wallet.toFixed(2) + '</strong> wallet balance. Select <strong>Wallet</strong> as payment mode to use it.</span></div>' : '';
        $('#membership_alerts').html(baseAlert + pkgAlert);
    }

    // Disable discount when wallet payment is selected
    $(document).on('change', '.payment_mode', function(){
        var mode = $(this).val();
        // Wallet panel
        $('#wallet_pay_panel').toggle(mode === 'wallet');
        // Package panel
        $('#pkg_pay_panel').toggle(mode === 'pkg');
        // Split panel
        $('#split_pay_panel').toggle(mode === 'split');
        if (mode === 'split') {
            refreshSplitMode2Options();
            calculateGrandTotal();
        }
        // If package selected and no customer loaded, show hint
        if(mode === 'pkg') {
            var custId = $('.cust_id').val();
            if(!custId) {
                $('#pkg_no_cust_msg').show(); $('#pkg_select_wrap').hide();
            } else {
                renderPosPkgDropdown();
            }
        }
        if(mode === 'wallet') {
            <?php
            $plan_settings = select_array("SELECT plan_id FROM hr_membership_plans WHERE salon_id='$salon_id' AND allow_discount=0 LIMIT 1");
            $no_discount_with_wallet = count($plan_settings) > 0 ? 'true' : 'false';
            ?>
            if(<?= $no_discount_with_wallet ?>) {
                $('#discount').val('').prop('disabled',true);
                $('#discount_mode').prop('disabled',true);
                calculateGrandTotal();
                if(!$('#wallet_discount_note').length) {
                    $('<small id="wallet_discount_note" style="color:#d97706;font-size:12px;margin-top:4px;display:block;"><i class="ph ph-info"></i> Discounts are disabled when paying via Wallet.</small>').insertAfter('#discount');
                }
            }
            // Check if balance covers the total
            var wallet = parseFloat($('#wallet_avail_balance').text().replace('₹','').replace(/,/g,'')) || 0;
            var total = parseFloat($('#grandTotal').text().replace('₹','').replace(/,/g,'')) || 0;
            $('#wallet_low_warn').toggle(wallet > 0 && wallet < total);
        } else {
            $('#discount').prop('disabled',false);
            $('#discount_mode').prop('disabled',false);
            $('#wallet_discount_note').remove();
            
            // Reset applied wallet if mode is not wallet
            $('#wallet_applied_amount').val(0);
        }
        
        // Unconditionally recalculate total on payment mode change
        calculateGrandTotal();
    });

    // When Mode 1 changes in split panel — handle wallet auto-fill and re-sync Mode 2
    $(document).on('change', '#split_mode_1', function() {
        var mode1 = $(this).val();
        var grandTotal = parseFloat($('.grandTotal').val()) || 0;

        if (mode1 === 'wallet') {
            var walletBal = parseFloat($('#wallet_avail_balance').text().replace('₹','').replace(/,/g,'')) || 0;
            if (walletBal <= 0) {
                alert('No wallet balance available for this customer.');
                $(this).val('cash');  // reset to first available
                return;
            }
            // Auto-fill wallet amount (capped to grand total)
            var applyWallet = Math.min(walletBal, grandTotal);
            $('#split_amount_1').val(applyWallet.toFixed(2));
            $('#split_wallet_banner').show();
            $('#split_wallet_avail').text('₹' + walletBal.toFixed(2));
            // Make Amount 1 readonly since wallet auto-fills
            $('#split_amount_1').prop('readonly', true).css({'background':'#f1f5f9','color':'#475569'});
        } else {
            $('#split_wallet_banner').hide();
            $('#split_wallet_low_warn').hide();
            // Make Amount 1 editable again
            $('#split_amount_1').prop('readonly', false).css({'background':'white','color':''});
        }

        // Refresh Mode 2 to exclude selected Mode 1
        refreshSplitMode2Options();
        calculateGrandTotal();
    });

    // Rebuild Mode 2 options excluding whatever Mode 1 currently has
    function refreshSplitMode2Options() {
        var mode1 = $('#split_mode_1').val();
        var currentMode2 = $('#split_mode_2').val();
        var allMethods = <?php
            $split_methods = [];
            foreach($pay_methods as $pm) {
                if(in_array($pm['method_key'], ['pkg','package'])) continue;
                $split_methods[] = $pm;
            }
            $split_methods[] = ['method_key'=>'wallet','method_name'=>'💳 Wallet Balance'];
            echo json_encode($split_methods);
        ?>;

        var html = '';
        allMethods.forEach(function(m) {
            // Exclude whatever Mode 1 has, and exclude pkg/package
            if (m.method_key === mode1) return;
            html += '<option value="' + m.method_key + '"' + (m.method_key === currentMode2 ? ' selected' : '') + '>' + m.method_name + '</option>';
        });
        $('#split_mode_2').html(html);
    }

    // Apply full wallet balance to bill
    $('#btn_use_full_wallet').click(function() {
        var wallet = parseFloat($('#wallet_avail_balance').text().replace('₹','').replace(/,/g,'')) || 0;
        var total = parseFloat($('.grandTotal').val()) || 0;
        var loyalty = parseFloat($('#redeem_points').val()) || 0;
        var requiredPay = total - loyalty;
        
        if(wallet <= 0) { alert('No wallet balance available.'); return; }
        
        var toApply = Math.min(wallet, requiredPay);
        $('#wallet_applied_amount').val(toApply);
        calculateGrandTotal();

        if(wallet < requiredPay) {
            $('#wallet_low_warn').show();
            alert('Wallet balance (₹' + wallet.toFixed(2) + ') is less than amount to pay (₹' + requiredPay.toFixed(2) + '). The remaining amount should be collected separately.');
        } else {
            $('#wallet_low_warn').hide();
        }
        // Remark to note wallet usage
        if($('#billing_remark').val() === '' || $('#billing_remark').val().indexOf('Wallet') === -1) {
            $('#billing_remark').val('Paid via Wallet Balance');
        }
    });



    // Package dropdown change
    $(document).on('change', '#pos_active_pkg_select', function() {
        var cp_id = $(this).val();
        $('#pos_cp_id_hidden').val(cp_id);
        if(!cp_id) { $('#pos_pkg_services').html(''); return; }
        // Show services in selected package
        var html = '<div style="padding:8px 0;">';
        if(window.activePackages) {
            var services = window.activePackages.filter(function(p) { return p.cp_id == cp_id; });
            if(services.length) {
                html += '<div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:6px;">Sessions available in this package:</div>';
                services.forEach(function(s) {
                    html += '<div style="display:flex;justify-content:space-between;padding:5px 8px;background:rgba(255,255,255,.6);border-radius:6px;margin-bottom:4px;font-size:13px;">' +
                        '<span>' + s.service_name + '</span>' +
                        '<span style="font-weight:700;color:#059669;">' + s.remaining + ' session' + (s.remaining !== 1 ? 's' : '') + ' left</span>' +
                        '</div>';
                });
            } else {
                html += '<p style="color:var(--text-muted);font-size:13px;">No sessions found.</p>';
            }
        }
        html += '</div>';
        $('#pos_pkg_services').html(html);
    });

    function renderPosPkgDropdown() {
        var pkgs = window.activePackages || [];
        if(pkgs.length === 0) {
            $('#pkg_no_cust_msg').text('No active packages for this customer.').show();
            $('#pkg_select_wrap').hide();
            return;
        }
        $('#pkg_no_cust_msg').hide();
        $('#pkg_select_wrap').show();
        // Build unique packages
        var seen = {};
        var html = '<option value="">-- Select Package --</option>';
        pkgs.forEach(function(p) {
            if(!seen[p.cp_id]) {
                seen[p.cp_id] = true;
                html += '<option value="' + p.cp_id + '">' + p.package_name + ' (exp: ' + p.expiry_date + ')</option>';
            }
        });
        $('#pos_active_pkg_select').html(html);
        $('#pos_pkg_services').html('');
    }

    function clearCustomerData() {
        $('.cust_id').val('');
        $('#customer_data_block').slideUp(200);
    }

    // View History
    $('#btn_view_history').click(function() {
        var cust_id = $('.cust_id').val();
        if(!cust_id) return;
        
        $('#customerHistoryModal').addClass('active');
        $('#history_tbody').html('<tr><td colspan="4" style="text-align:center; padding: 20px;"><i class="ph ph-spinner ph-spin"></i> Loading...</td></tr>');
        
        $.ajax({
            type: "POST",
            url: "ajax/customer_ajax.php",
            data: { method: 'get_customer_history', cust_id: cust_id },
            success: function(res) {
                try {
                    var data = JSON.parse(res);
                    $('#history_tbody').empty();
                    if(data.length > 0) {
                        data.forEach(function(row) {
                            $('#history_tbody').append('<tr>' +
                                '<td><span style="color:var(--text-muted); font-size:13px;">'+row.invoice_date+'</span></td>' +
                                '<td style="font-weight:600; color:var(--text-main); font-size:14px;">'+row.service+'</td>' +
                                '<td style="font-size:13px;">'+row.staff_name+'</td>' +
                                '<td style="font-weight:600; color:var(--primary);">₹'+parseFloat(row.service_total_wth_gst).toFixed(2)+'</td>' +
                            '</tr>');
                        });
                    } else {
                        $('#history_tbody').html('<tr><td colspan="4" style="text-align:center; padding: 20px; color:var(--text-muted);">No past services found.</td></tr>');
                    }
                } catch(e) {
                    $('#history_tbody').html('<tr><td colspan="4" style="text-align:center; padding: 20px; color:var(--danger);">Error loading history.</td></tr>');
                }
            }
        });
    });

    $(document).on('click', '.close-modal', function() {
        $('.modal-overlay').removeClass('active');
    });

    // Finally, run preloads to trigger all bound events natively
    if(existingServices && existingServices.length > 0) {
        existingServices.forEach(function(srv) {
            addBillingRow(srv);
        });
    } else {
        addBillingRow();
    }

});
</script>

<?php include 'footer.php'; ?>

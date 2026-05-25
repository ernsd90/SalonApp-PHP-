<?php 
include "config.php";
include "function.php";
$invoice_id = $_REQUEST['invoice_id'];

$salon_id = get_session_data('salon_id');

$salon = select_row("SELECT salon_name,salon_address,salon_contact,salon_gst,firm_name FROM `hr_salon` WHERE `salon_id` = $salon_id");
if($salon) extract($salon);

$invoice = select_row("SELECT * FROM `hr_invoice` WHERE `invoice_id` = '".$invoice_id."'");
if($invoice) extract($invoice);

$invoice_service = select_array("SELECT * FROM `hr_invoice_service` WHERE `invoice_id` = '".$invoice_id."'");

// For split payments: fetch each payment leg
$split_payments = [];
if(isset($payment_mode) && strtolower($payment_mode) === 'split') {
    $split_rows = select_array("SELECT payment_mode, grand_total FROM `hr_invoice_payment` WHERE `invoice_id` = '".$invoice_id."' ORDER BY id ASC");
    if($split_rows) {
        // Build friendly method names from hr_payment_methods
        $method_map = [];
        $methods = select_array("SELECT method_key, method_name FROM hr_payment_methods WHERE (salon_id='$salon_id' OR is_global=1) AND status=1");
        if($methods) foreach($methods as $m) $method_map[$m['method_key']] = $m['method_name'];
        $method_map['wallet'] = 'Wallet';
        $method_map['cash']   = 'Cash';
        foreach($split_rows as $sp) {
            $mk = strtolower($sp['payment_mode']);
            $split_payments[] = [
                'label'  => isset($method_map[$mk]) ? $method_map[$mk] : ucfirst($mk),
                'amount' => floatval($sp['grand_total']),
                'mode'   => $mk,
            ];
        }
    }
}
?>

<div style="padding: 30px;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid var(--border-color); padding-bottom: 30px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0 0 8px 0;"><?= htmlspecialchars($salon_name) ?> <span style="font-size: 14px; color: var(--text-muted); font-weight: 500;">(<?= htmlspecialchars($firm_name) ?>)</span></h1>
            <p style="margin: 0 0 4px 0; color: var(--text-muted); font-size: 14px; display: flex; align-items: center; gap: 8px;"><i class="ph ph-map-pin"></i> <?= htmlspecialchars($salon_address) ?></p>
            <p style="margin: 0; color: var(--text-muted); font-size: 14px; display: flex; align-items: center; gap: 8px;"><i class="ph ph-phone"></i> <?= htmlspecialchars($salon_contact) ?></p>
        </div>
        
        <div style="text-align: right;">
            <div style="background: var(--primary-light); color: var(--primary); padding: 8px 16px; border-radius: 8px; display: inline-block; font-weight: 700; font-size: 18px; letter-spacing: 1px; margin-bottom: 12px;">
                INVOICE #<?= $invoice_number ?>
            </div>
            <p style="margin: 0 0 4px 0; color: var(--text-main); font-size: 15px; font-weight: 600;">Date: <?= date('d M Y, h:i A', strtotime($invoice_date)) ?></p>
        </div>
    </div>

    <!-- Bill To -->
    <div style="background: #f8fafc; border-radius: 12px; padding: 24px; margin-bottom: 40px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <p style="color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 8px 0;">Billed To</p>
            <h3 style="margin: 0 0 4px 0; font-size: 18px; color: var(--text-main);"><?= htmlspecialchars(strtoupper($cust_name)) ?></h3>
            <p style="margin: 0; color: var(--text-muted); font-size: 14px; display: flex; align-items: center; gap: 8px;"><i class="ph ph-phone"></i> <?= htmlspecialchars($cust_mob) ?></p>
        </div>
        <?php if(!empty($payment_mode)): ?>
        <div style="text-align: right;">
            <p style="color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 8px 0;">Payment Method</p>
            <?php if(strtolower($payment_mode) === 'split' && !empty($split_payments)): ?>
                <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                    <?php foreach($split_payments as $sp): ?>
                    <div style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                        <?php
                        // Color-code by mode
                        $sp_color = '#059669'; // green default
                        if($sp['mode'] === 'wallet') $sp_color = '#7c3aed';
                        elseif(in_array($sp['mode'], ['cash'])) $sp_color = '#059669';
                        else $sp_color = 'var(--primary)';
                        ?>
                        <i class="ph-fill ph-check-circle" style="color: <?= $sp_color ?>; font-size: 18px;"></i>
                        <span style="font-weight: 600; color: var(--text-main); font-size: 14px;">
                            <?= htmlspecialchars($sp['label']) ?>:
                            <span style="color: <?= $sp_color ?>;">₹<?= number_format($sp['amount'], 2) ?></span>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="display: flex; align-items: center; gap: 8px; justify-content: flex-end; color: var(--text-main); font-weight: 500;">
                    <i class="ph-fill ph-check-circle" style="color: var(--success); font-size: 20px;"></i>
                    <?= strtoupper(htmlspecialchars($payment_mode)) ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Items Table -->
    <div style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; margin-bottom: 40px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 16px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Description</th>
                    <th style="padding: 16px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Staff Assigned</th>
                    <th style="padding: 16px; text-align: center; font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; width: 100px;">Qty</th>
                    <th style="padding: 16px; text-align: right; font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; width: 150px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($invoice_service as $services): extract($services); ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 16px; font-size: 14px; color: var(--text-main); font-weight: 500;"><?= htmlspecialchars($service) ?></td>
                    <td style="padding: 16px; font-size: 14px; color: var(--text-muted);"><?= htmlspecialchars($staff_name) ?></td>
                    <td style="padding: 16px; font-size: 14px; color: var(--text-main); text-align: center;"><?= $service_qty ?></td>
                    <td style="padding: 16px; font-size: 14px; color: var(--text-main); font-weight: 500; text-align: right;">₹<?= number_format((float)$service_price, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div style="display: flex; justify-content: flex-end;">
        <div style="width: 350px;">
            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: var(--text-muted); font-size: 15px;">
                <span>Subtotal</span>
                <span style="color: var(--text-main); font-weight: 500;">₹<?= number_format((float)$service_total, 2) ?></span>
            </div>
            
            <?php if(isset($service_total_tax) && $service_total_tax > 0): ?>
            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: var(--text-muted); font-size: 15px;">
                <span>Tax Total</span>
                <span style="color: var(--text-main); font-weight: 500;">₹<?= number_format((float)$service_total_tax, 2) ?></span>
            </div>
            <?php endif; ?>

            <?php if(isset($extra_fee) && $extra_fee > 0): ?>
            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: var(--text-muted); font-size: 15px;">
                <span>Additional Fees</span>
                <span style="color: var(--text-main); font-weight: 500;">₹<?= number_format((float)$extra_fee, 2) ?></span>
            </div>
            <?php endif; ?>

            <?php if(isset($discount) && $discount > 0): ?>
            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: var(--success); font-size: 15px;">
                <span>Discount Applied</span>
                <span style="font-weight: 500;">-₹<?= number_format((float)$discount, 2) ?></span>
            </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; padding: 20px 0 0 0; color: var(--text-main); font-size: 20px; font-weight: 700;">
                <span>Grand Total</span>
                <span style="color: var(--primary);">₹<?= number_format((float)$grand_total, 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="margin-top: 50px; display: flex; justify-content: flex-end; gap: 16px; border-top: 1px solid var(--border-color); padding-top: 30px;">
        <button type="button" class="close-modal btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; box-shadow: none;">Close</button>
        <a href="print_invoice.php?invoice_id=<?= $invoice_id ?>&view=1" target="_blank" class="btn-primary" style="background: var(--primary); color: white; width: auto; text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-printer"></i> Print Receipt
        </a>
    </div>

</div>

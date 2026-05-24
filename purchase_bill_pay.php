<?php
if(session_status()===PHP_SESSION_NONE) session_start();
include "config.php"; include "function.php";
$salon_id = get_session_data('salon_id');
$bill_id  = intval($_GET['bill_id'] ?? 0);
$bill = select_row("SELECT b.*, v.vendor_name FROM hr_bill b LEFT JOIN hr_vendor v ON v.id=b.vendor WHERE b.bill_id='$bill_id' AND b.salon_id='$salon_id'");
if(!$bill) die('Bill not found.');
$pay_methods = select_array("SELECT * FROM `hr_payment_methods` WHERE (`salon_id`='$salon_id' OR `is_global`=1) AND `status`=1 ORDER BY `sort_order` ASC");
if(!$pay_methods) $pay_methods = [['method_key'=>'cash','method_name'=>'Cash'],['method_key'=>'card','method_name'=>'Card'],['method_key'=>'upi','method_name'=>'UPI']];
$balance = $bill['total'] - $bill['amount_paid'];
?>
<div class="modal-header" style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid var(--border-color);">
    <div>
        <h3 style="font-size:18px;font-weight:600;margin:0;">Record Payment</h3>
        <p style="margin:4px 0 0 0;font-size:13px;color:var(--text-muted);">Bill #<?= $bill_id ?> — <?= htmlspecialchars($bill['vendor_name']) ?></p>
    </div>
    <button type="button" class="close-modal" style="background:none;border:none;font-size:20px;color:var(--text-muted);cursor:pointer;"><i class="ph ph-x"></i></button>
</div>

<div style="padding:24px;background:#f8fafc;border-bottom:1px solid var(--border-color);">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;">
        <div><div style="font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Grand Total</div><div style="font-size:20px;font-weight:700;">₹<?= number_format($bill['total'],2) ?></div></div>
        <div><div style="font-size:12px;color:#16a34a;font-weight:600;text-transform:uppercase;">Paid So Far</div><div style="font-size:20px;font-weight:700;color:#16a34a;">₹<?= number_format($bill['amount_paid'],2) ?></div></div>
        <div><div style="font-size:12px;color:var(--danger);font-weight:600;text-transform:uppercase;">Balance Due</div><div style="font-size:20px;font-weight:700;color:var(--danger);">₹<?= number_format(max(0,$balance),2) ?></div></div>
    </div>
</div>

<form class="ajax-form" data-action-url="ajax/inventory_ajax.php" method="post" style="padding:24px;">
    <input name="method" type="hidden" value="pay_bill">
    <input name="bill_id" type="hidden" value="<?= $bill_id ?>">
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="form-group">
            <label>Amount to Pay (₹) <span style="color:var(--danger);">*</span></label>
            <input required name="pay_amount" type="number" step="0.01" min="0.01" max="<?= max(0,$balance) ?>" class="form-control" placeholder="0.00" value="<?= number_format(max(0,$balance),2) ?>">
            <small style="color:var(--text-muted);">Max outstanding: ₹<?= number_format(max(0,$balance),2) ?></small>
        </div>
        <div class="form-group">
            <label>Payment Mode</label>
            <select name="payment_mode" class="form-control">
                <?php foreach($pay_methods as $pm): ?>
                <option value="<?= htmlspecialchars($pm['method_key']) ?>"><?= htmlspecialchars($pm['method_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Remark (optional)</label>
            <input name="remark" type="text" class="form-control" placeholder="e.g. Paid via cheque no. 12345">
        </div>
    </div>
    <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:12px;border-top:1px solid var(--border-color);padding-top:20px;">
        <button type="button" class="close-modal form-control" style="width:auto;background:white;">Cancel</button>
        <button type="submit" class="btn-primary" style="width:auto;margin-top:0;padding:10px 24px;"><i class="ph ph-money"></i> Record Payment</button>
    </div>
</form>

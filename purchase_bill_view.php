<?php
if(session_status()===PHP_SESSION_NONE) session_start();
include "config.php"; include "function.php";
$salon_id = get_session_data('salon_id');
$bill_id  = intval($_GET['bill_id'] ?? 0);
$bill = select_row("SELECT b.*, v.vendor_name, v.vendor_phone, v.vendor_gst, v.vendor_address FROM hr_bill b LEFT JOIN hr_vendor v ON v.id=b.vendor WHERE b.bill_id='$bill_id' AND b.salon_id='$salon_id'");
if(!$bill) die('<p style="padding:40px;font-family:sans-serif;">Bill not found.</p>');
$products = select_array("SELECT * FROM hr_bill_product WHERE bill_id='$bill_id'");
$payments = select_array("SELECT * FROM hr_vendor_payment WHERE bill_id='$bill_id' AND amt_out>0 ORDER BY created_date ASC");
$salon    = select_row("SELECT salon_name, salon_address, salon_contact, salon_gst, logo, firm_name FROM hr_salon WHERE salon_id='$salon_id'");
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Purchase Bill #<?= $bill_id ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;font-size:14px;color:#1e293b;background:#f8fafc;padding:30px}
.page{background:white;max-width:860px;margin:0 auto;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.08);overflow:hidden}
.header{background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;padding:32px}
.header h1{font-size:26px;font-weight:700;margin-bottom:4px}
.header p{opacity:0.8;font-size:14px}
.meta{display:flex;justify-content:space-between;padding:24px 32px;border-bottom:1px solid #e2e8f0;background:#fafafa}
.meta-block h4{font-size:11px;font-weight:600;text-transform:uppercase;color:#64748b;letter-spacing:0.5px;margin-bottom:6px}
.meta-block p{font-size:14px;color:#1e293b;font-weight:500}
table.items{width:100%;border-collapse:collapse;margin:0}
table.items th{background:#f8fafc;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;padding:12px 20px;text-align:left;border-bottom:1px solid #e2e8f0}
table.items td{padding:14px 20px;border-bottom:1px solid #f1f5f9;font-size:14px}
.totals-box{padding:24px 32px;display:flex;justify-content:flex-end}
.totals-inner{width:280px}
.totals-row{display:flex;justify-content:space-between;padding:8px 0;font-size:14px;color:#64748b}
.totals-row.grand{font-size:18px;font-weight:700;color:#4f46e5;border-top:2px solid #e0e7ff;margin-top:8px;padding-top:12px}
.status-badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase}
.badge-paid{background:#dcfce7;color:#16a34a}
.badge-partial{background:#fef9c3;color:#ca8a04}
.badge-unpaid{background:#fee2e2;color:#dc2626}
.print-btn{background:#4f46e5;color:white;border:none;padding:10px 22px;border-radius:10px;cursor:pointer;font-weight:600;font-size:14px;display:flex;align-items:center;gap:8px}
@media print {
    .no-print { display: none !important; }
    html, body {
        margin: 0;
        padding: 0;
        background: white;
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        color: #000;
    }
    @page {
        size: 80mm auto;
        margin: 4mm 4mm;
    }
    .page {
        box-shadow: none;
        border-radius: 0;
        max-width: 100%;
        width: 100%;
    }
    /* Collapse the colourful header into a simple centered block */
    .header {
        background: white !important;
        color: black !important;
        padding: 8px 0 !important;
        text-align: center;
        border-bottom: 2px dashed #000 !important;
    }
    .header h1 { font-size: 14px !important; color: #000 !important; }
    .header p   { font-size: 10px !important; color: #000 !important; opacity: 1 !important; }
    /* Make meta section stack tightly */
    .meta {
        display: block !important;
        padding: 6px 0 !important;
        border-bottom: 1px dashed #000 !important;
        background: white !important;
    }
    .meta-block { margin-bottom: 4px; }
    .meta-block h4 { font-size: 9px !important; }
    .meta-block p  { font-size: 11px !important; }
    /* Table */
    table.items th, table.items td {
        padding: 4px 4px !important;
        font-size: 10px !important;
        border-bottom: 1px dotted #888 !important;
    }
    table.items th { border-bottom: 1px solid #000 !important; }
    /* Totals */
    .totals-box { padding: 6px 0 !important; display: block !important; }
    .totals-inner { width: 100% !important; }
    .totals-row { font-size: 11px !important; }
    .totals-row.grand { font-size: 13px !important; border-top: 2px dashed #000 !important; padding-top: 5px !important; }
    /* Status badge simplified */
    .status-badge { border: 1px solid #000 !important; background: white !important; color: #000 !important; }
    /* Payment history */
    h4 { font-size: 10px !important; }
}
</style>
</head>
<body>
<div class="no-print" style="max-width:860px;margin:0 auto 16px auto;display:flex;gap:12px;">
    <a href="purchase_bills.php" style="background:white;border:1px solid #e2e8f0;color:#1e293b;padding:10px 18px;border-radius:10px;text-decoration:none;font-size:14px;display:flex;align-items:center;gap:8px;">← Back</a>
    <button class="print-btn" onclick="window.print()">🖨 Print Bill</button>
</div>

<div class="page">
    <div class="header">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <h1><?= htmlspecialchars($salon['salon_name'] ?? 'Salon') ?></h1>
                <p><?= htmlspecialchars($salon['salon_address'] ?? '') ?></p>
                <p>GST: <?= htmlspecialchars($salon['salon_gst'] ?? '') ?></p>
            </div>
            <div style="text-align:right;">
                <div style="background:rgba(255,255,255,0.2);padding:8px 16px;border-radius:10px;font-size:18px;font-weight:700;letter-spacing:1px;">PURCHASE BILL</div>
                <div style="margin-top:10px;font-size:16px;font-weight:600;">#<?= $bill_id ?></div>
                <span class="status-badge badge-<?= $bill['payment_status'] ?>" style="margin-top:8px;"><?= strtoupper($bill['payment_status']) ?></span>
            </div>
        </div>
    </div>

    <div class="meta">
        <div class="meta-block">
            <h4>Vendor</h4>
            <p style="font-size:16px;font-weight:700;"><?= htmlspecialchars($bill['vendor_name'] ?? '—') ?></p>
            <?php if($bill['vendor_phone']): ?><p style="color:#64748b;margin-top:4px;"><?= htmlspecialchars($bill['vendor_phone']) ?></p><?php endif; ?>
            <?php if($bill['vendor_gst']): ?><p style="color:#64748b;">GST: <?= htmlspecialchars($bill['vendor_gst']) ?></p><?php endif; ?>
        </div>
        <div class="meta-block" style="text-align:right;">
            <h4>Bill Date</h4>
            <p><?= date('d M Y', strtotime($bill['invoice_date'])) ?></p>
            <?php if($bill['invoice_no']): ?>
            <h4 style="margin-top:10px;">Supplier Invoice</h4>
            <p><?= htmlspecialchars($bill['invoice_no']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Products -->
    <div style="padding:0 0 0 0;">
        <table class="items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Unit Price</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
            <?php $sr=1; foreach((array)$products as $p): ?>
            <tr>
                <td style="color:#94a3b8;"><?= $sr++ ?></td>
                <td><strong><?= htmlspecialchars($p['product_name']) ?></strong></td>
                <td style="color:#64748b;"><?= htmlspecialchars($p['product_type']) ?: '—' ?></td>
                <td style="text-align:center;"><?= $p['qty'] ?></td>
                <td style="text-align:right;">₹<?= number_format($p['mrp'],2) ?></td>
                <td style="text-align:right;font-weight:600;">₹<?= number_format($p['grand_total'],2) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="totals-box">
        <div class="totals-inner">
            <div class="totals-row"><span>Subtotal</span><span>₹<?= number_format($bill['subtotal'],2) ?></span></div>
            <?php if($bill['discount']>0): ?><div class="totals-row"><span>Discount</span><span style="color:#16a34a;">-₹<?= number_format($bill['discount'],2) ?></span></div><?php endif; ?>
            <?php if($bill['gst']>0): ?><div class="totals-row"><span>GST</span><span>₹<?= number_format($bill['gst'],2) ?></span></div><?php endif; ?>
            <div class="totals-row grand"><span>Grand Total</span><span>₹<?= number_format($bill['total'],2) ?></span></div>
            <div class="totals-row" style="color:#16a34a;"><span>Amount Paid</span><span>₹<?= number_format($bill['amount_paid'],2) ?></span></div>
            <div class="totals-row" style="color:#dc2626;font-weight:600;"><span>Balance Due</span><span>₹<?= number_format(max(0,$bill['total']-$bill['amount_paid']),2) ?></span></div>
        </div>
    </div>

    <!-- Payment History -->
    <?php if($payments): ?>
    <div style="padding:0 32px 32px 32px;">
        <h4 style="font-size:13px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">Payment History</h4>
        <table class="items" style="border:1px solid #f1f5f9;border-radius:8px;overflow:hidden;">
            <thead><tr><th>Date</th><th>Amount</th><th>Mode</th><th>Remark</th></tr></thead>
            <tbody>
            <?php foreach($payments as $pay): ?>
            <tr>
                <td><?= date('d M Y', strtotime($pay['created_date'])) ?></td>
                <td><strong style="color:#16a34a;">₹<?= number_format($pay['amt_out'],2) ?></strong></td>
                <td><?= strtoupper($pay['payment_mode']) ?></td>
                <td style="color:#64748b;"><?= htmlspecialchars($pay['vendor_remark']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if($bill['bill_note']): ?>
    <div style="padding:0 32px 32px 32px;"><div style="background:#f8fafc;border-radius:10px;padding:16px;"><strong style="font-size:12px;color:#64748b;text-transform:uppercase;">Notes</strong><p style="margin-top:6px;"><?= htmlspecialchars($bill['bill_note']) ?></p></div></div>
    <?php endif; ?>
</div>
</body>
</html>

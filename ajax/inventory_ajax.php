<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$user_id  = get_session_data('user_id');
$salon_id = get_session_data('salon_id');
$method   = $_REQUEST['method'] ?? '';

if ($method && function_exists('inv_' . $method)) {
    $result = call_user_func('inv_' . $method);
    $json = json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    echo $json !== false ? $json : json_encode(['error' => 1, 'msg' => 'JSON encoding error']);
} else {
    echo json_encode(['error' => 1, 'msg' => 'Method not found: ' . $method]);
}

/* ======================================================
   PRODUCT SEARCH (for autocomplete)
====================================================== */
function inv_search_products() {
    global $conn, $salon_id;
    $q = '%'.mysqli_real_escape_string($conn, $_GET['q'] ?? '').'%';

    // Search distinct product names from purchase history (hr_bill_product)
    $results = select_array("SELECT product_name as name, AVG(mrp) as price
        FROM hr_bill_product
        WHERE salon_id='$salon_id' AND product_name LIKE '$q'
        GROUP BY product_name
        ORDER BY product_name ASC
        LIMIT 15");

    $out = [];
    foreach ((array)$results as $r) {
        $out[] = ['name' => $r['name'], 'price' => round($r['price'], 2)];
    }
    return ['error' => 0, 'results' => $out];
}

/* ======================================================
   VENDOR CRUD
====================================================== */

function inv_create_vendor() {
    global $conn, $salon_id, $user_id;
    $name    = mysqli_real_escape_string($conn, $_POST['vendor_name'] ?? '');
    $phone   = mysqli_real_escape_string($conn, $_POST['vendor_phone'] ?? '');
    $email   = mysqli_real_escape_string($conn, $_POST['vendor_email'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['vendor_address'] ?? '');
    $gst     = mysqli_real_escape_string($conn, $_POST['vendor_gst'] ?? '');
    $status  = intval($_POST['status'] ?? 1);

    if (!$name) return ['error' => 1, 'msg' => 'Vendor name is required.'];

    $conn->query("INSERT INTO `hr_vendor` SET
        `salon_id`='$salon_id', `vendor_name`='$name', `vendor_phone`='$phone',
        `vendor_email`='$email', `vendor_address`='$address',
        `vendor_gst`='$gst', `status`='$status'");
    return ['error' => 0, 'msg' => 'Vendor added successfully.'];
}

function inv_update_vendor() {
    global $conn, $salon_id;
    $id      = intval($_POST['vendor_id']);
    $name    = mysqli_real_escape_string($conn, $_POST['vendor_name'] ?? '');
    $phone   = mysqli_real_escape_string($conn, $_POST['vendor_phone'] ?? '');
    $email   = mysqli_real_escape_string($conn, $_POST['vendor_email'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['vendor_address'] ?? '');
    $gst     = mysqli_real_escape_string($conn, $_POST['vendor_gst'] ?? '');
    $status  = intval($_POST['status'] ?? 1);

    if (!$id || !$name) return ['error' => 1, 'msg' => 'Invalid request.'];

    $conn->query("UPDATE `hr_vendor` SET
        `vendor_name`='$name', `vendor_phone`='$phone', `vendor_email`='$email',
        `vendor_address`='$address', `vendor_gst`='$gst', `status`='$status'
        WHERE `id`='$id'");
    return ['error' => 0, 'msg' => 'Vendor updated successfully.'];
}

function inv_get_vendors_list() {
    global $conn, $salon_id;
    // For select2/dropdown in bill form
    $vendors = select_array("SELECT id, vendor_name FROM `hr_vendor`
        WHERE (`salon_id`='$salon_id' OR `salon_id`=0) AND `status`=1
        ORDER BY vendor_name ASC");
    return ['error' => 0, 'data' => $vendors ?: []];
}

/* ======================================================
   PURCHASE BILL CRUD
====================================================== */

function inv_create_bill() {
    global $conn, $salon_id, $user_id;

    $vendor_id    = intval($_POST['vendor_id'] ?? 0);
    $invoice_no   = mysqli_real_escape_string($conn, $_POST['invoice_no'] ?? '');
    $invoice_date = mysqli_real_escape_string($conn, $_POST['invoice_date'] ?? date('Y-m-d'));
    $discount     = floatval($_POST['discount'] ?? 0);
    $gst          = floatval($_POST['gst'] ?? 0);
    $subtotal     = floatval($_POST['subtotal'] ?? 0);
    $grand_total  = floatval($_POST['grand_total'] ?? 0);
    $bill_note    = mysqli_real_escape_string($conn, $_POST['bill_note'] ?? '');
    $pay_now      = floatval($_POST['pay_now'] ?? 0);
    $payment_mode = mysqli_real_escape_string($conn, $_POST['payment_mode'] ?? 'cash');

    // Determine payment status
    $amount_paid = $pay_now;
    if ($pay_now <= 0)           $payment_status = 'unpaid';
    elseif ($pay_now < $grand_total) $payment_status = 'partial';
    else                         $payment_status = 'paid';

    if (!$vendor_id) return ['error' => 1, 'msg' => 'Please select a vendor.'];

    // Insert bill
    $conn->query("INSERT INTO `hr_bill` SET
        `salon_id`='$salon_id', `user_id`='$user_id',
        `invoice_no`='$invoice_no', `vendor`='$vendor_id',
        `invoice_date`='$invoice_date', `discount`='$discount',
        `gst`='$gst', `subtotal`='$subtotal', `total`='$grand_total',
        `amount_paid`='$amount_paid', `payment_status`='$payment_status',
        `bill_note`='$bill_note', `payment_mode`='$payment_mode',
        `created_date`=NOW()");
    $bill_id = $conn->insert_id;

    if (!$bill_id) return ['error' => 1, 'msg' => 'Failed to create bill.'];

    // Insert line items
    $products     = $_POST['products'] ?? [];
    foreach ($products as $p) {
        $pname  = mysqli_real_escape_string($conn, $p['product_name'] ?? '');
        $ptype  = mysqli_real_escape_string($conn, $p['product_type'] ?? '');
        $qty    = floatval($p['qty'] ?? 1);
        $mrp    = floatval($p['mrp'] ?? 0);
        $total  = $qty * $mrp;
        if (!$pname) continue;
        $conn->query("INSERT INTO `hr_bill_product` SET
            `bill_id`='$bill_id', `salon_id`='$salon_id',
            `product_name`='$pname', `product_type`='$ptype',
            `qty`='$qty', `mrp`='$mrp', `grand_total`='$total',
            `created_date`=NOW()");
    }

    // Record initial payment in ledger (debit = bill amount, credit = payment)
    $conn->query("INSERT INTO `hr_vendor_payment` SET
        `salon_id`='$salon_id', `vendor_id`='$vendor_id',
        `bill_id`='$bill_id', `amt_in`='$grand_total',
        `amt_out`='0', `payment_mode`='', `vendor_remark`='Bill Created',
        `created_date`=NOW()");

    // Record payment if paid now
    if ($pay_now > 0) {
        $conn->query("INSERT INTO `hr_vendor_payment` SET
            `salon_id`='$salon_id', `vendor_id`='$vendor_id',
            `bill_id`='$bill_id', `amt_in`='0',
            `amt_out`='$pay_now', `payment_mode`='$payment_mode',
            `vendor_remark`='Payment on bill creation',
            `created_date`=NOW()");
    }

    return ['error' => 0, 'msg' => 'Purchase bill created successfully.', 'bill_id' => $bill_id];
}

function inv_get_bills() {
    global $conn, $salon_id;
    extract($_REQUEST);

    $where = "";
    $search_val = isset($search['value']) ? trim($search['value']) : '';
    if ($search_val) {
        $sv = mysqli_real_escape_string($conn, $search_val);
        $where .= " AND (b.invoice_no LIKE '%$sv%' OR v.vendor_name LIKE '%$sv%')";
    }
    if (!empty($fromdate) && !empty($todate)) {
        $fd = date('Y-m-d', strtotime($fromdate));
        $td = date('Y-m-d', strtotime($todate));
        $where .= " AND (b.invoice_date BETWEEN '$fd' AND '$td')";
    } elseif (!empty($fromdate)) {
        $fd = date('Y-m-d', strtotime($fromdate));
        $where .= " AND b.invoice_date = '$fd'";
    }
    if (!empty($status_filter) && $status_filter !== 'all') {
        $sf = mysqli_real_escape_string($conn, $status_filter);
        $where .= " AND b.payment_status = '$sf'";
    }

    $start_from = intval($start ?? 0);
    $page_len   = intval($length ?? 10);

    $sql = "FROM `hr_bill` b
            LEFT JOIN `hr_vendor` v ON v.id = b.vendor
            WHERE b.salon_id = '$salon_id' $where
            ORDER BY b.bill_id DESC";

    $total = num_rows("SELECT b.bill_id $sql");
    $rows  = select_array("SELECT b.*, v.vendor_name $sql LIMIT $start_from, $page_len");

    $data = [];
    foreach ((array)$rows as $r) {
        $balance = $r['total'] - $r['amount_paid'];
        if ($r['payment_status'] === 'paid') {
            $status_badge = '<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">PAID</span>';
        } elseif ($r['payment_status'] === 'partial') {
            $status_badge = '<span style="background:#fef9c3;color:#ca8a04;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">PARTIAL</span>';
        } else {
            $status_badge = '<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">UNPAID</span>';
        }
        $actions  = '<div style="display:flex;gap:6px;">';
        $actions .= '<a href="purchase_bill_view.php?bill_id='.$r['bill_id'].'" target="_blank" style="background:#f1f5f9;color:var(--primary);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;" title="View"><i class="ph ph-eye"></i></a>';
        if ($r['payment_status'] !== 'paid') {
            $actions .= '<button type="button" class="modalButtonCommon" data-href="purchase_bill_pay.php?bill_id='.$r['bill_id'].'" style="background:#dcfce7;color:#16a34a;border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:15px;" title="Record Payment"><i class="ph ph-money"></i></button>';
            $actions .= '<button type="button" class="btn-mark-paid" data-id="'.$r['bill_id'].'" style="background:#e0e7ff;color:#4f46e5;border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:15px;" title="Mark as Paid"><i class="ph ph-check"></i></button>';
        }
        $actions .= '</div>';

        $data[] = [
            'bill_id'        => $r['bill_id'],
            'invoice_no'     => $r['invoice_no'] ?: '—',
            'invoice_date'   => date('d M Y', strtotime($r['invoice_date'])),
            'vendor_name'    => $r['vendor_name'] ?: '—',
            'payment_date'   => (function() use ($r, $conn) {
                $pay = select_row("SELECT MAX(created_date) as pd FROM hr_vendor_payment WHERE bill_id='".$r['bill_id']."' AND amt_out > 0");
                return $pay && $pay['pd'] ? date('d M Y', strtotime($pay['pd'])) : null;
            })(),
            'grand_total'    => number_format($r['total'], 2),
            'amount_paid'    => number_format($r['amount_paid'], 2),
            'balance'        => number_format(max(0, $r['total'] - $r['amount_paid']), 2),
            'payment_status' => $status_badge,
            'action'         => $actions,
        ];
    }

    return [
        'draw'            => intval($draw ?? 1),
        'recordsTotal'    => $total,
        'recordsFiltered' => $total,
        'data'            => $data,
    ];
}

function inv_pay_bill() {
    global $conn, $salon_id;
    $bill_id      = intval($_POST['bill_id'] ?? 0);
    $pay_amount   = floatval($_POST['pay_amount'] ?? 0);
    $payment_mode = mysqli_real_escape_string($conn, $_POST['payment_mode'] ?? 'cash');
    $remark       = mysqli_real_escape_string($conn, $_POST['remark'] ?? '');

    if (!$bill_id || $pay_amount <= 0) return ['error' => 1, 'msg' => 'Invalid payment amount.'];

    $bill = select_row("SELECT * FROM hr_bill WHERE bill_id='$bill_id' AND salon_id='$salon_id'");
    if (!$bill) return ['error' => 1, 'msg' => 'Bill not found.'];

    $new_paid   = $bill['amount_paid'] + $pay_amount;
    $total      = $bill['total'];
    $new_status = $new_paid >= $total ? 'paid' : 'partial';
    $new_paid   = min($new_paid, $total); // cap at total

    $conn->query("UPDATE hr_bill SET amount_paid='$new_paid', payment_status='$new_status' WHERE bill_id='$bill_id'");
    $conn->query("INSERT INTO hr_vendor_payment SET
        salon_id='$salon_id', vendor_id='".$bill['vendor']."',
        bill_id='$bill_id', amt_in='0', amt_out='$pay_amount',
        payment_mode='$payment_mode',
        vendor_remark='".($remark ?: 'Payment recorded')."',
        created_date=NOW()");

    return ['error' => 0, 'msg' => 'Payment of ₹'.number_format($pay_amount, 2).' recorded successfully.'];
}

function inv_get_vendor_ledger() {
    global $conn, $salon_id;
    extract($_REQUEST);

    $vendor_id   = intval($vendor_id ?? 0);
    $all_vendors = ($vendor_id === 0);

    $date_where   = "";
    $vendor_where = $all_vendors ? "" : "AND vp.vendor_id='$vendor_id'";
    if (!empty($fromdate) && !empty($todate)) {
        $fd = date('Y-m-d', strtotime($fromdate));
        $td = date('Y-m-d', strtotime($todate));
        $date_where = "AND DATE(vp.created_date) BETWEEN '$fd' AND '$td'";
    }

    // To calculate the running balance accurately despite old dirty data,
    // we take the true All-Time Outstanding balance and walk *backwards* from the newest entry to the oldest.
    $vendor_bill_sql = $all_vendors ? "" : "AND vendor='$vendor_id'";
    $true_outstanding = floatval(select_row("SELECT COALESCE(SUM(total),0) - COALESCE(SUM(amount_paid),0) as s FROM hr_bill WHERE salon_id='$salon_id' AND payment_status!='paid' $vendor_bill_sql")['s']);

    // Get ALL transactions ordered newest first to walk backwards
    $rows = select_array("SELECT vp.*, v.vendor_name, b.invoice_no, b.invoice_date, b.total as bill_total
        FROM hr_vendor_payment vp
        LEFT JOIN hr_bill b ON b.bill_id = vp.bill_id AND vp.bill_id > 0
        LEFT JOIN hr_vendor v ON v.id = vp.vendor_id
        WHERE vp.salon_id='$salon_id' $vendor_where $date_where
        ORDER BY vp.created_date DESC, vp.id DESC");

    $running = $true_outstanding;
    $ledger  = [];
    
    // Rows are newest to oldest. We record the balance, then un-do the transaction for the previous row's balance.
    foreach ((array)$rows as $r) {
        $vendor_prefix = $all_vendors ? '['.htmlspecialchars($r['vendor_name'] ?: 'Unknown').'] ' : '';
        if ($r['amt_in'] > 0) {
            $ref  = $r['invoice_no'] ? 'Inv#'.$r['invoice_no'] : 'Bill#'.$r['bill_id'];
            $desc = $vendor_prefix.'Purchase Bill raised — '.$ref;
        } else {
            $remark = $r['vendor_remark'] ?: 'Payment';
            $desc   = $vendor_prefix.($r['bill_id'] > 0
                ? $remark.' (Bill#'.$r['bill_id'].($r['invoice_no'] ? '/Inv#'.$r['invoice_no'] : '').')'
                : $remark.' (General)');
        }

        $ledger[] = [
            'date'        => date('d M Y', strtotime($r['created_date'])),
            'description' => $desc,
            'vendor'      => $r['vendor_name'] ?: '—',
            'debit'       => $r['amt_in']  > 0 ? '₹'.number_format($r['amt_in'], 2)  : '—',
            'credit'      => $r['amt_out'] > 0 ? '₹'.number_format($r['amt_out'], 2) : '—',
            'balance'     => '₹'.number_format(max(0,$running), 2),
            'balance_raw' => $running,
        ];
        
        // Reverse the transaction to find what the balance was *before* this happened
        $running -= $r['amt_in'];   // Undo debit
        $running += $r['amt_out'];  // Undo credit
    }

    // Outstanding must exactly match the unpaid bills total from hr_bill
    $vendor_bill_sql = $all_vendors ? "" : "AND vendor='$vendor_id'";
    $outstanding = floatval(select_row("SELECT COALESCE(SUM(total),0) - COALESCE(SUM(amount_paid),0) as s FROM hr_bill WHERE salon_id='$salon_id' AND payment_status!='paid' $vendor_bill_sql")['s']);

    $vendor_filter_sql = $all_vendors ? "" : "AND vendor_id='$vendor_id'";
    // Tiles: use date filter if provided, else all-time for Billed/Paid
    $tile_date = !empty($date_where) ? str_replace('vp.', '', $date_where) : "";
    $total_billed = floatval(select_row("SELECT COALESCE(SUM(amt_in),0) as s FROM hr_vendor_payment WHERE salon_id='$salon_id' $vendor_filter_sql $tile_date")['s']);
    
    // To ensure the math (Billed - Paid = Outstanding) is always perfect for All Time views:
    if (empty($tile_date)) {
        $total_paid = max(0, $total_billed - $outstanding);
    } else {
        $total_paid = floatval(select_row("SELECT COALESCE(SUM(amt_out),0) as s FROM hr_vendor_payment WHERE salon_id='$salon_id' $vendor_filter_sql $tile_date")['s']);
    }

    return [
        'error'         => 0,
        'rows'          => $ledger,
        'all_vendors'   => $all_vendors,
        'total_billed'  => number_format($total_billed, 2),
        'total_paid'    => number_format($total_paid, 2),
        'outstanding'   => number_format(max(0, $outstanding), 2),
    ];
}

function inv_get_bill_summary() {
    global $conn, $salon_id;
    extract($_REQUEST);

    $date_where = "AND DATE(b.invoice_date) = '".date('Y-m-d')."'";
    if (!empty($fromdate) && !empty($todate)) {
        $fd = date('Y-m-d', strtotime($fromdate));
        $td = date('Y-m-d', strtotime($todate));
        $date_where = "AND b.invoice_date BETWEEN '$fd' AND '$td'";
    } else {
        $date_where = ""; // Show all-time summary
    }

    $r = select_row("SELECT
        COUNT(*) as total_bills,
        COALESCE(SUM(b.total),0) as total_amount,
        COALESCE(SUM(b.amount_paid),0) as total_paid,
        COALESCE(SUM(b.total - b.amount_paid),0) as total_outstanding
        FROM hr_bill b WHERE b.salon_id='$salon_id' $date_where");

    return [
        'error'             => 0,
        'total_bills'       => intval($r['total_bills']),
        'total_amount'      => number_format($r['total_amount'], 2),
        'total_paid'        => number_format($r['total_paid'], 2),
        'total_outstanding' => number_format($r['total_outstanding'], 2),
    ];
}

function inv_mark_paid() {
    global $conn, $salon_id;
    $bill_id = intval($_POST['bill_id'] ?? 0);
    if (!$bill_id) return ['error' => 1, 'msg' => 'Invalid bill.'];

    $bill = select_row("SELECT * FROM hr_bill WHERE bill_id='$bill_id' AND salon_id='$salon_id'");
    if (!$bill) return ['error' => 1, 'msg' => 'Bill not found.'];

    $remaining = $bill['total'] - $bill['amount_paid'];
    if ($remaining <= 0) return ['error' => 0, 'msg' => 'Bill is already fully paid.'];

    // Update hr_bill
    $conn->query("UPDATE hr_bill SET amount_paid='".$bill['total']."', payment_status='paid' WHERE bill_id='$bill_id'");

    // Also insert credit entry into hr_vendor_payment so vendor ledger stays in sync
    $conn->query("INSERT INTO hr_vendor_payment SET
        salon_id='$salon_id', vendor_id='".$bill['vendor']."',
        bill_id='$bill_id', amt_in='0', amt_out='$remaining',
        payment_mode='cash', vendor_remark='Marked as Paid',
        created_date=NOW()");

    return ['error' => 0, 'msg' => 'Bill #'.$bill_id.' marked as fully paid.'];
}

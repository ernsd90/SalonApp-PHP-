<?php
/**
 * Membership & Package AJAX Handler
 * All membership, package, sell, report and edge-case functions live here.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
include "../config.php";
include "../function.php";

$user_id  = get_session_data('user_id');
$salon_id = get_session_data('salon_id');

// Auto-apply schema on first load (idempotent)
apply_membership_schema();

$method = $_REQUEST['method'] ?? '';
if ($method && function_exists($method)) {
    echo json_encode($method());
} else {
    echo json_encode(['error' => 1, 'msg' => 'Method not found: ' . $method]);
}

// ──────────────────────────────────────────────────────────
// SCHEMA INSTALLER (runs once, safe to run repeatedly)
// ──────────────────────────────────────────────────────────
function apply_membership_schema() {
    global $conn;
    $sqls = [
        "CREATE TABLE IF NOT EXISTS `hr_membership_plans` (
          `plan_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `salon_id` INT UNSIGNED NOT NULL DEFAULT 0,
          `plan_name` VARCHAR(120) NOT NULL,
          `plan_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `wallet_credit` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `validity_days` INT NOT NULL DEFAULT 90,
          `description` TEXT,
          `allow_discount` TINYINT(1) NOT NULL DEFAULT 0,
          `gst_applicable` TINYINT(1) NOT NULL DEFAULT 0,
          `gst_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
          `status` TINYINT(1) NOT NULL DEFAULT 1,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`plan_id`), KEY `idx_salon` (`salon_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `hr_customer_membership` (
          `cm_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `salon_id` INT UNSIGNED NOT NULL DEFAULT 0,
          `cust_id` INT UNSIGNED NOT NULL,
          `plan_id` INT UNSIGNED NOT NULL,
          `plan_name` VARCHAR(120) NOT NULL,
          `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `remaining_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `wallet_credit` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `wallet_credited` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `gst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `status` ENUM('pending','active','expired','refunded','paused') NOT NULL DEFAULT 'pending',
          `start_date` DATE DEFAULT NULL,
          `expiry_date` DATE DEFAULT NULL,
          `pause_date` DATE DEFAULT NULL,
          `invoice_id` INT UNSIGNED DEFAULT NULL,
          `sold_by` INT UNSIGNED DEFAULT NULL,
          `notes` TEXT,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`cm_id`), KEY `idx_salon_cust` (`salon_id`,`cust_id`), KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `hr_membership_payments` (
          `mp_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `cm_id` INT UNSIGNED NOT NULL,
          `salon_id` INT UNSIGNED NOT NULL DEFAULT 0,
          `cust_id` INT UNSIGNED NOT NULL,
          `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `payment_mode` VARCHAR(30) NOT NULL DEFAULT 'cash',
          `paid_by` INT UNSIGNED DEFAULT NULL,
          `notes` VARCHAR(255) DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`mp_id`), KEY `idx_cm` (`cm_id`), KEY `idx_salon` (`salon_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `hr_package_payments` (
          `pp_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `cp_id` INT UNSIGNED NOT NULL,
          `salon_id` INT UNSIGNED NOT NULL DEFAULT 0,
          `cust_id` INT UNSIGNED NOT NULL,
          `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `payment_mode` VARCHAR(30) NOT NULL DEFAULT 'cash',
          `paid_by` INT UNSIGNED DEFAULT NULL,
          `notes` VARCHAR(255) DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`pp_id`), KEY `idx_cp` (`cp_id`), KEY `idx_salon` (`salon_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `hr_packages_new` (
          `pkg_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `salon_id` INT UNSIGNED NOT NULL DEFAULT 0,
          `package_name` VARCHAR(120) NOT NULL,
          `validity_days` INT NOT NULL DEFAULT 90,
          `mrp_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `savings` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `allow_discount` TINYINT(1) NOT NULL DEFAULT 0,
          `gst_applicable` TINYINT(1) NOT NULL DEFAULT 0,
          `gst_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
          `status` TINYINT(1) NOT NULL DEFAULT 1,
          `created_by` INT UNSIGNED DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`pkg_id`), KEY `idx_salon` (`salon_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `hr_package_items` (
          `item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `pkg_id` INT UNSIGNED NOT NULL,
          `service_id` INT UNSIGNED NOT NULL,
          `service_name` VARCHAR(120) NOT NULL,
          `service_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `quantity` INT NOT NULL DEFAULT 1,
          PRIMARY KEY (`item_id`), KEY `idx_pkg` (`pkg_id`), KEY `idx_service` (`service_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `hr_customer_packages` (
          `cp_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `salon_id` INT UNSIGNED NOT NULL DEFAULT 0,
          `cust_id` INT UNSIGNED NOT NULL,
          `pkg_id` INT UNSIGNED NOT NULL,
          `package_name` VARCHAR(120) NOT NULL,
          `purchase_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `remaining_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `gst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `payment_mode` VARCHAR(30) NOT NULL DEFAULT 'cash',
          `purchase_date` DATE NOT NULL,
          `expiry_date` DATE DEFAULT NULL,
          `status` ENUM('active','expired','refunded','fully_used') NOT NULL DEFAULT 'active',
          `invoice_id` INT UNSIGNED DEFAULT NULL,
          `sold_by` INT UNSIGNED DEFAULT NULL,
          `notes` TEXT,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`cp_id`), KEY `idx_salon_cust` (`salon_id`,`cust_id`), KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `hr_customer_package_usage` (
          `usage_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `cp_id` INT UNSIGNED NOT NULL,
          `pkg_id` INT UNSIGNED NOT NULL,
          `cust_id` INT UNSIGNED NOT NULL,
          `service_id` INT UNSIGNED NOT NULL,
          `qty_used` INT NOT NULL DEFAULT 1,
          `invoice_id` INT UNSIGNED DEFAULT NULL,
          `used_by` INT UNSIGNED DEFAULT NULL,
          `used_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`usage_id`), KEY `idx_cp` (`cp_id`), KEY `idx_cust_service` (`cust_id`,`service_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `hr_wallet_audit_log` (
          `log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `cust_id` INT UNSIGNED NOT NULL,
          `salon_id` INT UNSIGNED NOT NULL DEFAULT 0,
          `user_id` INT UNSIGNED NOT NULL,
          `old_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `new_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `change_type` ENUM('credit','debit','refund','adjustment') NOT NULL DEFAULT 'adjustment',
          `reason` VARCHAR(255) DEFAULT NULL,
          `reference` VARCHAR(100) DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`log_id`), KEY `idx_cust` (`cust_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
    foreach ($sqls as $sql) {
        mysqli_query($conn, $sql);
    }
    // Safe column addition - check INFORMATION_SCHEMA first to avoid duplicate column error
    $col_check = mysqli_query($conn, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hr_customer' AND COLUMN_NAME = 'active_membership_id'");
    if ($col_check && mysqli_num_rows($col_check) === 0) {
        mysqli_query($conn, "ALTER TABLE `hr_customer` ADD COLUMN `active_membership_id` INT UNSIGNED DEFAULT NULL");
    }

    // New additions for partial payments
    mysqli_query($conn, "ALTER TABLE `hr_customer_membership` MODIFY `wallet_credited` DECIMAL(10,2) NOT NULL DEFAULT 0.00");
    
    $pkg_col_check = mysqli_query($conn, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hr_customer_packages' AND COLUMN_NAME = 'paid_amount'");
    if ($pkg_col_check && mysqli_num_rows($pkg_col_check) === 0) {
        mysqli_query($conn, "ALTER TABLE `hr_customer_packages` ADD COLUMN `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `purchase_price`, ADD COLUMN `remaining_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `paid_amount`");
        mysqli_query($conn, "UPDATE `hr_customer_packages` SET `paid_amount` = `purchase_price` + `gst_amount`, `remaining_amount` = 0");
    }
}

// ──────────────────────────────────────────────────────────
// MEMBERSHIP PLANS CRUD
// ──────────────────────────────────────────────────────────
function create_membership_plan() {
    global $salon_id, $user_id;
    extract($_POST);
    $plan_name     = mysqli_real_escape_string($GLOBALS['conn'], trim($plan_name));
    $plan_price    = floatval($plan_price);
    $wallet_credit = floatval($wallet_credit);
    $validity_days = intval($validity_days) * 30; // months to days
    $description   = mysqli_real_escape_string($GLOBALS['conn'], $description ?? '');
    $allow_discount = intval($allow_discount ?? 0);
    $gst_applicable = intval($gst_applicable ?? 0);
    $gst_percent   = floatval($gst_percent ?? 0);
    $gst_on_service = isset($gst_on_service) ? intval($gst_on_service) : 1;
    $status        = intval($status ?? 1);

    if (empty($plan_name) || $plan_price <= 0 || $wallet_credit <= 0 || $validity_days <= 0) {
        return ['error' => 1, 'msg' => 'Please fill all required fields.'];
    }
    $exists = select_row("SELECT plan_id FROM hr_membership_plans WHERE plan_name='$plan_name' AND salon_id='$salon_id'");
    if ($exists) return ['error' => 1, 'msg' => 'A plan with this name already exists.'];

    $sql = "INSERT INTO `hr_membership_plans` SET
        salon_id='$salon_id', plan_name='$plan_name', plan_price='$plan_price',
        wallet_credit='$wallet_credit', validity_days='$validity_days',
        description='$description', allow_discount='$allow_discount',
        gst_applicable='$gst_applicable', gst_percent='$gst_percent',
        gst_on_service='$gst_on_service', status='$status'";
    insert_query($sql);
    return ['error' => 0, 'msg' => 'Membership Plan created successfully.'];
}

function update_membership_plan() {
    global $salon_id;
    extract($_POST);
    $plan_id       = intval($plan_id);
    $plan_name     = mysqli_real_escape_string($GLOBALS['conn'], trim($plan_name));
    $plan_price    = floatval($plan_price);
    $wallet_credit = floatval($wallet_credit);
    $validity_days = intval($validity_days) * 30;
    $description   = mysqli_real_escape_string($GLOBALS['conn'], $description ?? '');
    $allow_discount = intval($allow_discount ?? 0);
    $gst_applicable = intval($gst_applicable ?? 0);
    $gst_percent   = floatval($gst_percent ?? 0);
    $gst_on_service = isset($gst_on_service) ? intval($gst_on_service) : 1;
    $status        = intval($status ?? 1);

    $sql = "UPDATE `hr_membership_plans` SET
        plan_name='$plan_name', plan_price='$plan_price', wallet_credit='$wallet_credit',
        validity_days='$validity_days', description='$description',
        allow_discount='$allow_discount', gst_applicable='$gst_applicable',
        gst_percent='$gst_percent', gst_on_service='$gst_on_service', status='$status'
        WHERE plan_id='$plan_id' AND salon_id='$salon_id'";
    update_query($sql);
    return ['error' => 0, 'msg' => 'Membership Plan updated successfully.'];
}

function delete_membership_plan() {
    global $salon_id;
    extract($_POST);
    $plan_id = intval($id);
    update_query("DELETE FROM hr_membership_plans WHERE plan_id='$plan_id' AND salon_id='$salon_id'");
    return ['error' => 0, 'msg' => 'Plan deleted.'];
}

function toggle_membership_plan_status() {
    global $salon_id;
    extract($_POST);
    $plan_id = intval($plan_id);
    $status  = intval($status);
    update_query("UPDATE hr_membership_plans SET status='$status' WHERE plan_id='$plan_id' AND salon_id='$salon_id'");
    return ['error' => 0, 'msg' => $status ? 'Plan activated.' : 'Plan deactivated.'];
}

function get_membership_plans() {
    global $salon_id;
    extract($_REQUEST);
    $where = "WHERE salon_id='$salon_id'";
    if (!empty($search['value'])) {
        $sv = mysqli_real_escape_string($GLOBALS['conn'], $search['value']);
        $where .= " AND plan_name LIKE '%$sv%'";
    }
    $total = num_rows("SELECT plan_id FROM hr_membership_plans $where");
    $sql   = "SELECT * FROM hr_membership_plans $where ORDER BY plan_id DESC LIMIT $start, $length";
    $rows  = select_array($sql);
    $data  = [];
    foreach ($rows as $r) {
        $validity_months = round($r['validity_days'] / 30);
        $status_badge = $r['status'] == 1
            ? '<span class="badge-success-sm">Active</span>'
            : '<span class="badge-danger-sm">Inactive</span>';
        $toggle_btn = $r['status'] == 1
            ? '<button class="btn-deactivate" onclick="toggleMembershipPlan('.$r['plan_id'].', 0)"><i class="ph ph-x-circle"></i> Deactivate</button>'
            : '<button class="btn-activate" onclick="toggleMembershipPlan('.$r['plan_id'].', 1)"><i class="ph ph-check-circle"></i> Activate</button>';
        $edit_btn = '<button class="btn-edit modalButtonCommon" data-href="membership_plan_edit.php?plan_id='.$r['plan_id'].'"><i class="ph ph-pencil-simple"></i> Edit</button>';

        $data[] = [
            'plan_id'        => $r['plan_id'],
            'plan_name'      => $r['plan_name'],
            'plan_price'     => '₹' . number_format($r['plan_price'], 2),
            'wallet_credit'  => '₹' . number_format($r['wallet_credit'], 2),
            'validity'       => $validity_months . ' Month' . ($validity_months != 1 ? 's' : ''),
            'status'         => $status_badge,
            'action'         => '<div style="display:flex;gap:6px;">'.$edit_btn.$toggle_btn.'</div>',
        ];
    }
    return ['draw' => intval($draw ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data];
}

// ──────────────────────────────────────────────────────────
// PACKAGES (SERVICE BUNDLE) CRUD
// ──────────────────────────────────────────────────────────
function create_package_new() {
    global $salon_id, $user_id;
    extract($_POST);
    $package_name   = mysqli_real_escape_string($GLOBALS['conn'], trim($package_name));
    $validity_days  = intval($validity_months ?? 3) * 30;
    $selling_price  = floatval($selling_price);
    $allow_discount = intval($allow_discount ?? 0);
    $gst_applicable = intval($gst_applicable ?? 0);
    $gst_percent    = floatval($gst_percent ?? 0);
    $status         = intval($status ?? 1);
    $service_ids    = isset($service_id) ? (array)$service_id : [];
    $quantities     = isset($qty) ? (array)$qty : [];

    if (empty($package_name) || $selling_price <= 0 || empty($service_ids)) {
        return ['error' => 1, 'msg' => 'Package name, price and at least one service are required.'];
    }

    // Calculate MRP from services
    $mrp_total = 0;
    $service_data = [];
    foreach ($service_ids as $i => $sid) {
        $sid = intval($sid);
        $qty = max(1, intval($quantities[$i] ?? 1));
        $svc = select_row("SELECT service_id, service_name, service_price FROM hr_services WHERE service_id='$sid'");
        if ($svc) {
            $mrp_total += $svc['service_price'] * $qty;
            $service_data[] = ['sid' => $sid, 'name' => $svc['service_name'], 'price' => $svc['service_price'], 'qty' => $qty];
        }
    }
    $savings = max(0, $mrp_total - $selling_price);

    $pkg_id = insert_query("INSERT INTO hr_packages_new SET
        salon_id='$salon_id', package_name='$package_name', validity_days='$validity_days',
        mrp_total='$mrp_total', selling_price='$selling_price', savings='$savings',
        allow_discount='$allow_discount', gst_applicable='$gst_applicable', gst_percent='$gst_percent',
        status='$status', created_by='$user_id'");

    if ($pkg_id) {
        foreach ($service_data as $sd) {
            $sname = mysqli_real_escape_string($GLOBALS['conn'], $sd['name']);
            insert_query("INSERT INTO hr_package_items SET
                pkg_id='$pkg_id', service_id='{$sd['sid']}', service_name='$sname',
                service_price='{$sd['price']}', quantity='{$sd['qty']}'");
        }
        return ['error' => 0, 'msg' => 'Package created successfully.'];
    }
    return ['error' => 1, 'msg' => 'Failed to create package.'];
}

function update_package_new() {
    global $salon_id;
    extract($_POST);
    $pkg_id         = intval($pkg_id);
    $package_name   = mysqli_real_escape_string($GLOBALS['conn'], trim($package_name));
    $validity_days  = intval($validity_months ?? 3) * 30;
    $selling_price  = floatval($selling_price);
    $allow_discount = intval($allow_discount ?? 0);
    $gst_applicable = intval($gst_applicable ?? 0);
    $gst_percent    = floatval($gst_percent ?? 0);
    $status         = intval($status ?? 1);
    $service_ids    = isset($service_id) ? (array)$service_id : [];
    $quantities     = isset($qty) ? (array)$qty : [];

    $mrp_total = 0;
    $service_data = [];
    foreach ($service_ids as $i => $sid) {
        $sid = intval($sid);
        $qty = max(1, intval($quantities[$i] ?? 1));
        $svc = select_row("SELECT service_id, service_name, service_price FROM hr_services WHERE service_id='$sid'");
        if ($svc) {
            $mrp_total += $svc['service_price'] * $qty;
            $service_data[] = ['sid' => $sid, 'name' => $svc['service_name'], 'price' => $svc['service_price'], 'qty' => $qty];
        }
    }
    $savings = max(0, $mrp_total - $selling_price);

    update_query("UPDATE hr_packages_new SET
        package_name='$package_name', validity_days='$validity_days',
        mrp_total='$mrp_total', selling_price='$selling_price', savings='$savings',
        allow_discount='$allow_discount', gst_applicable='$gst_applicable', gst_percent='$gst_percent',
        status='$status' WHERE pkg_id='$pkg_id' AND salon_id='$salon_id'");

    update_query("DELETE FROM hr_package_items WHERE pkg_id='$pkg_id'");
    foreach ($service_data as $sd) {
        $sname = mysqli_real_escape_string($GLOBALS['conn'], $sd['name']);
        insert_query("INSERT INTO hr_package_items SET
            pkg_id='$pkg_id', service_id='{$sd['sid']}', service_name='$sname',
            service_price='{$sd['price']}', quantity='{$sd['qty']}'");
    }
    return ['error' => 0, 'msg' => 'Package updated successfully.'];
}

function delete_package_new() {
    global $salon_id;
    extract($_POST);
    $pkg_id = intval($id);
    update_query("DELETE FROM hr_package_items WHERE pkg_id='$pkg_id'");
    update_query("DELETE FROM hr_packages_new WHERE pkg_id='$pkg_id' AND salon_id='$salon_id'");
    return ['error' => 0, 'msg' => 'Package deleted.'];
}

function toggle_package_new_status() {
    global $salon_id;
    extract($_POST);
    $pkg_id = intval($pkg_id);
    $status = intval($status);
    update_query("UPDATE hr_packages_new SET status='$status' WHERE pkg_id='$pkg_id' AND salon_id='$salon_id'");
    return ['error' => 0, 'msg' => $status ? 'Package activated.' : 'Package deactivated.'];
}

function get_packages_new() {
    global $salon_id;
    extract($_REQUEST);
    $where = "WHERE p.salon_id='$salon_id'";
    if (!empty($search['value'])) {
        $sv = mysqli_real_escape_string($GLOBALS['conn'], $search['value']);
        $where .= " AND p.package_name LIKE '%$sv%'";
    }
    $total = num_rows("SELECT pkg_id FROM hr_packages_new p $where");
    $sql   = "SELECT p.* FROM hr_packages_new p $where ORDER BY p.pkg_id DESC LIMIT $start, $length";
    $rows  = select_array($sql);
    $data  = [];
    foreach ($rows as $r) {
        $validity_months = round($r['validity_days'] / 30);
        $items = select_array("SELECT service_name, quantity FROM hr_package_items WHERE pkg_id='{$r['pkg_id']}'");
        $svc_list = implode(', ', array_map(fn($i) => $i['quantity'].'x '.$i['service_name'], $items));
        $status_badge = $r['status'] == 1
            ? '<span class="badge-success-sm">Active</span>'
            : '<span class="badge-danger-sm">Inactive</span>';
        $toggle_btn = $r['status'] == 1
            ? '<button class="btn-deactivate" onclick="togglePackageNew('.$r['pkg_id'].', 0)"><i class="ph ph-x-circle"></i> Deactivate</button>'
            : '<button class="btn-activate" onclick="togglePackageNew('.$r['pkg_id'].', 1)"><i class="ph ph-check-circle"></i> Activate</button>';
        $edit_btn = '<button class="btn-edit modalButtonCommon" data-href="package_new_edit.php?pkg_id='.$r['pkg_id'].'"><i class="ph ph-pencil-simple"></i> Edit</button>';

        $data[] = [
            'pkg_id'        => $r['pkg_id'],
            'package_name'  => $r['package_name'],
            'services'      => $svc_list ?: '—',
            'mrp_total'     => '₹' . number_format($r['mrp_total'], 2),
            'selling_price' => '₹' . number_format($r['selling_price'], 2),
            'savings'       => '₹' . number_format($r['savings'], 2),
            'validity'      => $validity_months . ' Month' . ($validity_months != 1 ? 's' : ''),
            'status'        => $status_badge,
            'action'        => '<div style="display:flex;gap:6px;">'.$edit_btn.$toggle_btn.'</div>',
        ];
    }
    return ['draw' => intval($draw ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data];
}

function get_package_services() {
    global $salon_id;
    extract($_REQUEST);
    $pkg_id = intval($pkg_id);
    $items  = select_array("SELECT pi.*, s.service_price as current_price FROM hr_package_items pi
        JOIN hr_services s ON s.service_id = pi.service_id
        WHERE pi.pkg_id='$pkg_id'");
    return $items ?: [];
}

function get_services_for_package() {
    global $salon_id;
    $services = select_array("SELECT s.service_id, s.service_name, s.service_price, sc.service_catName
        FROM hr_services s JOIN hr_servicesCategory sc ON sc.service_catid = s.service_catid
        WHERE s.salon_id='$salon_id' AND s.service_status=1 ORDER BY sc.service_catName, s.service_name");
    return $services ?: [];
}

// ──────────────────────────────────────────────────────────
// SELL MEMBERSHIP
// ──────────────────────────────────────────────────────────
function sell_membership() {
    global $salon_id, $user_id, $conn;
    extract($_POST);

    $cust_id       = intval($cust_id);
    $plan_id       = intval($plan_id);
    $paid_now      = floatval($paid_now);
    $payment_mode  = mysqli_real_escape_string($conn, $payment_mode ?? 'cash');
    $notes         = mysqli_real_escape_string($conn, $notes ?? '');
    $sold_by       = intval($staff_id ?? $user_id);
    $billing_date  = !empty($billing_date) ? date('Y-m-d', strtotime($billing_date)) : date('Y-m-d');

    if (!$cust_id || !$plan_id || $paid_now <= 0) {
        return ['error' => 1, 'msg' => 'Missing required fields.'];
    }

    $plan = select_row("SELECT * FROM hr_membership_plans WHERE plan_id='$plan_id' AND salon_id='$salon_id' AND status=1");
    if (!$plan) return ['error' => 1, 'msg' => 'Membership plan not found or inactive.'];

    $total_price = $plan['plan_price'];
    $gst_amount  = 0;
    if ($plan['gst_applicable'] && $plan['gst_percent'] > 0) {
        $gst_amount = round($total_price * $plan['gst_percent'] / 100, 2);
        $total_price += $gst_amount;
    }

    $paid_now       = min($paid_now, $total_price);
    $remaining      = round($total_price - $paid_now, 2);
    $wallet_credit  = $plan['wallet_credit'];
    $is_fully_paid  = ($remaining <= 0);
    $status         = $is_fully_paid ? 'active' : 'pending';
    $start_date     = $billing_date;
    $expiry_date    = date('Y-m-d', strtotime($billing_date . ' + ' . $plan['validity_days'] . ' days'));
    
    // Partial payment gets credit equal to amount paid (up to max wallet credit)
    $wallet_credited = $is_fully_paid ? $wallet_credit : min($wallet_credit, $paid_now);

    $start_q = "'$start_date'";
    $expiry_q = "'$expiry_date'";
    $created_at_val = $billing_date . ' ' . date('H:i:s');

    $cm_id = insert_query("INSERT INTO hr_customer_membership SET
        salon_id='$salon_id', cust_id='$cust_id', plan_id='$plan_id',
        plan_name='" . mysqli_real_escape_string($conn, $plan['plan_name']) . "',
        total_price='$total_price', paid_amount='$paid_now', remaining_amount='$remaining',
        wallet_credit='$wallet_credit', wallet_credited='$wallet_credited',
        gst_amount='$gst_amount', status='$status',
        start_date=$start_q, expiry_date=$expiry_q,
        sold_by='$sold_by', notes='$notes', created_at='$created_at_val'");

    if (!$cm_id) return ['error' => 1, 'msg' => 'Failed to save membership.'];

    // Record payment entry
    insert_query("INSERT INTO hr_membership_payments SET
        cm_id='$cm_id', salon_id='$salon_id', cust_id='$cust_id',
        amount='$paid_now', payment_mode='$payment_mode', paid_by='$user_id',
        notes='Initial payment', created_at='$created_at_val'");

    // Credit wallet with initial dispensed amount
    if ($wallet_credited > 0) {
        credit_customer_wallet($cust_id, $wallet_credited, $cm_id, 'Membership' . ($is_fully_paid ? '' : ' (Partial)') . ': ' . $plan['plan_name']);
    }
    if ($is_fully_paid) {
        update_query("UPDATE hr_customer SET active_membership_id='$cm_id' WHERE cust_id='$cust_id'");
    }

    return ['error' => 0, 'msg' => 'Membership sold successfully.' . ($remaining > 0 ? ' Remaining: ₹' . $remaining : ' Wallet credited: ₹' . $wallet_credit)];
}

function record_membership_payment() {
    global $salon_id, $user_id, $conn;
    extract($_POST);
    $cm_id        = intval($cm_id);
    $amount       = floatval($amount);
    $payment_mode = mysqli_real_escape_string($conn, $payment_mode ?? 'cash');
    $notes_str    = mysqli_real_escape_string($conn, $notes ?? '');
    $p_date       = !empty($payment_date) ? date('Y-m-d', strtotime($payment_date)) : date('Y-m-d');
    $created_at_val = $p_date . ' ' . date('H:i:s');

    if (!$cm_id || $amount <= 0) return ['error' => 1, 'msg' => 'Invalid payment data.'];

    $membership = select_row("SELECT * FROM hr_customer_membership WHERE cm_id='$cm_id' AND salon_id='$salon_id'");
    if (!$membership) return ['error' => 1, 'msg' => 'Membership record not found.'];
    if ($membership['status'] == 'refunded') return ['error' => 1, 'msg' => 'This membership has been refunded.'];

    $amount = min($amount, $membership['remaining_amount']);
    $new_paid = $membership['paid_amount'] + $amount;
    $new_remaining = round($membership['total_price'] - $new_paid, 2);
    $is_fully_paid = ($new_remaining <= 0);

    // Wallet credit to give for this payment
    $remaining_credit_capacity = $membership['wallet_credit'] - $membership['wallet_credited'];
    $wallet_credit_to_give = $is_fully_paid ? $remaining_credit_capacity : min($amount, $remaining_credit_capacity);
    $new_wallet_credited = $membership['wallet_credited'] + $wallet_credit_to_give;

    insert_query("INSERT INTO hr_membership_payments SET
        cm_id='$cm_id', salon_id='$salon_id', cust_id='{$membership['cust_id']}',
        amount='$amount', payment_mode='$payment_mode', paid_by='$user_id', notes='$notes_str', created_at='$created_at_val'");

    if ($is_fully_paid) {
        $status_update = "";
        if ($membership['status'] == 'pending') {
            $status_update = ", status='active'";
        }
        update_query("UPDATE hr_customer_membership SET
            paid_amount='$new_paid', remaining_amount='$new_remaining', wallet_credited='$new_wallet_credited'
            $status_update
            WHERE cm_id='$cm_id'");
        
        if ($wallet_credit_to_give > 0) {
            credit_customer_wallet($membership['cust_id'], $wallet_credit_to_give, $cm_id, 'Membership fully paid: ' . $membership['plan_name']);
        }
        update_query("UPDATE hr_customer SET active_membership_id='$cm_id' WHERE cust_id='{$membership['cust_id']}'");
        return ['error' => 0, 'msg' => 'Payment recorded. Membership fully paid! Wallet credited ₹' . $wallet_credit_to_give];
    } else {
        update_query("UPDATE hr_customer_membership SET paid_amount='$new_paid', remaining_amount='$new_remaining', wallet_credited='$new_wallet_credited' WHERE cm_id='$cm_id'");
        if ($wallet_credit_to_give > 0) {
            credit_customer_wallet($membership['cust_id'], $wallet_credit_to_give, $cm_id, 'Membership Partial Payment: ' . $membership['plan_name']);
        }
        return ['error' => 0, 'msg' => 'Payment recorded. Remaining: ₹' . $new_remaining . ' Wallet credited: ₹' . $wallet_credit_to_give];
    }
}

// ──────────────────────────────────────────────────────────
// SELL PACKAGE
// ──────────────────────────────────────────────────────────
function sell_package_new() {
    global $salon_id, $user_id, $conn;
    extract($_POST);
    $cust_id      = intval($cust_id);
    $pkg_id       = intval($pkg_id);
    $paid_now     = floatval($paid_now ?? 0);
    $payment_mode = mysqli_real_escape_string($conn, $payment_mode ?? 'cash');
    $notes_str    = mysqli_real_escape_string($conn, $notes ?? '');
    $sold_by      = intval($staff_id ?? $user_id);
    $billing_date = !empty($billing_date) ? date('Y-m-d', strtotime($billing_date)) : date('Y-m-d');

    if (!$cust_id || !$pkg_id) return ['error' => 1, 'msg' => 'Select customer and package.'];

    $pkg = select_row("SELECT * FROM hr_packages_new WHERE pkg_id='$pkg_id' AND salon_id='$salon_id' AND status=1");
    if (!$pkg) return ['error' => 1, 'msg' => 'Package not found or inactive.'];

    $purchase_price = $pkg['selling_price'];
    $gst_amount = 0;
    if ($pkg['gst_applicable'] && $pkg['gst_percent'] > 0) {
        $gst_amount = round($purchase_price * $pkg['gst_percent'] / 100, 2);
        $purchase_price += $gst_amount;
    }
    
    // If paid_now not specified, assume full payment
    if ($paid_now <= 0) $paid_now = $purchase_price;
    $paid_now = min($paid_now, $purchase_price);
    $remaining = round($purchase_price - $paid_now, 2);

    $purchase_date = $billing_date;
    $expiry_date   = date('Y-m-d', strtotime($billing_date . ' + ' . $pkg['validity_days'] . ' days'));
    $created_at_val = $billing_date . ' ' . date('H:i:s');

    $cp_id = insert_query("INSERT INTO hr_customer_packages SET
        salon_id='$salon_id', cust_id='$cust_id', pkg_id='$pkg_id',
        package_name='" . mysqli_real_escape_string($conn, $pkg['package_name']) . "',
        purchase_price='$purchase_price', paid_amount='$paid_now', remaining_amount='$remaining',
        gst_amount='$gst_amount', payment_mode='$payment_mode', purchase_date='$purchase_date',
        expiry_date='$expiry_date', status='active', sold_by='$sold_by', notes='$notes_str', created_at='$created_at_val'");

    if (!$cp_id) return ['error' => 1, 'msg' => 'Failed to create package record.'];

    insert_query("INSERT INTO hr_package_payments SET
        cp_id='$cp_id', salon_id='$salon_id', cust_id='$cust_id',
        amount='$paid_now', payment_mode='$payment_mode', paid_by='$user_id', notes='Initial payment', created_at='$created_at_val'");

    return ['error' => 0, 'msg' => 'Package sold successfully. Expires: ' . date('d M Y', strtotime($expiry_date)) . ($remaining > 0 ? ' Remaining: ₹' . $remaining : '')];
}

function record_package_payment() {
    global $salon_id, $user_id, $conn;
    extract($_POST);
    $cp_id        = intval($cp_id);
    $amount       = floatval($amount);
    $payment_mode = mysqli_real_escape_string($conn, $payment_mode ?? 'cash');
    $notes_str    = mysqli_real_escape_string($conn, $notes ?? '');
    $p_date       = !empty($payment_date) ? date('Y-m-d', strtotime($payment_date)) : date('Y-m-d');
    $created_at_val = $p_date . ' ' . date('H:i:s');

    if (!$cp_id || $amount <= 0) return ['error' => 1, 'msg' => 'Invalid payment data.'];

    $pkg = select_row("SELECT * FROM hr_customer_packages WHERE cp_id='$cp_id' AND salon_id='$salon_id'");
    if (!$pkg) return ['error' => 1, 'msg' => 'Package record not found.'];
    if ($pkg['status'] == 'refunded') return ['error' => 1, 'msg' => 'This package has been refunded.'];

    $amount = min($amount, $pkg['remaining_amount']);
    $new_paid = $pkg['paid_amount'] + $amount;
    $new_remaining = round(($pkg['purchase_price'] + $pkg['gst_amount']) - $new_paid, 2);

    insert_query("INSERT INTO hr_package_payments SET
        cp_id='$cp_id', salon_id='$salon_id', cust_id='{$pkg['cust_id']}',
        amount='$amount', payment_mode='$payment_mode', paid_by='$user_id', notes='$notes_str', created_at='$created_at_val'");

    update_query("UPDATE hr_customer_packages SET paid_amount='$new_paid', remaining_amount='$new_remaining' WHERE cp_id='$cp_id'");
    
    if ($new_remaining <= 0) {
        return ['error' => 0, 'msg' => 'Payment recorded. Package fully paid!'];
    } else {
        return ['error' => 0, 'msg' => 'Payment recorded. Remaining: ₹' . $new_remaining];
    }
}

// ──────────────────────────────────────────────────────────
// CUSTOMER PROFILE DATA
// ──────────────────────────────────────────────────────────
function get_customer_memberships() {
    global $salon_id;
    extract($_REQUEST);
    $cust_id = intval($cust_id);
    $rows = select_array("SELECT * FROM hr_customer_membership WHERE cust_id='$cust_id' AND salon_id='$salon_id' ORDER BY cm_id DESC");
    // Expire check
    foreach ($rows as &$r) {
        if ($r['status'] == 'active' && $r['expiry_date'] && $r['expiry_date'] < date('Y-m-d')) {
            update_query("UPDATE hr_customer_membership SET status='expired' WHERE cm_id='{$r['cm_id']}'");
            $r['status'] = 'expired';
        }
        $r['paid_amount']      = '₹' . number_format($r['paid_amount'], 2);
        $r['remaining_amount'] = '₹' . number_format($r['remaining_amount'], 2);
        $r['wallet_credit']    = '₹' . number_format($r['wallet_credit'], 2);
        $r['expiry_formatted'] = $r['expiry_date'] ? date('d M Y', strtotime($r['expiry_date'])) : 'Not activated';
    }
    return $rows ?: [];
}

function get_customer_packages() {
    global $salon_id;
    extract($_REQUEST);
    $cust_id = intval($cust_id);
    $rows = select_array("SELECT cp.*, p.mrp_total FROM hr_customer_packages cp
        JOIN hr_packages_new p ON p.pkg_id = cp.pkg_id
        WHERE cp.cust_id='$cust_id' AND cp.salon_id='$salon_id' ORDER BY cp.cp_id DESC");
    foreach ($rows as &$r) {
        // Expire check
        if ($r['status'] == 'active' && $r['expiry_date'] && $r['expiry_date'] < date('Y-m-d')) {
            update_query("UPDATE hr_customer_packages SET status='expired' WHERE cp_id='{$r['cp_id']}'");
            $r['status'] = 'expired';
        }
        // Load service counts
        $items = select_array("SELECT pi.service_id, pi.service_name, pi.quantity,
            COALESCE(SUM(u.qty_used),0) AS used
            FROM hr_package_items pi
            LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id='{$r['cp_id']}'
            WHERE pi.pkg_id='{$r['pkg_id']}'
            GROUP BY pi.item_id");
        $r['services'] = $items;
        $r['expiry_formatted'] = $r['expiry_date'] ? date('d M Y', strtotime($r['expiry_date'])) : '—';
        $r['purchase_price_fmt'] = '₹' . number_format($r['purchase_price'], 2);
    }
    return $rows ?: [];
}

function get_active_packages_for_billing() {
    global $salon_id;
    extract($_REQUEST);
    $cust_id = intval($cust_id);
    $today   = date('Y-m-d');
    $rows = select_array("SELECT cp.cp_id, cp.pkg_id, cp.package_name, cp.expiry_date,
        pi.service_id, pi.service_name, pi.quantity,
        COALESCE(SUM(u.qty_used),0) AS used
        FROM hr_customer_packages cp
        JOIN hr_package_items pi ON pi.pkg_id = cp.pkg_id
        LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id=cp.cp_id
        WHERE cp.cust_id='$cust_id' AND cp.salon_id='$salon_id'
          AND cp.status='active' AND cp.expiry_date >= '$today'
        GROUP BY pi.item_id
        HAVING (pi.quantity - used) > 0");
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'cp_id'        => $r['cp_id'],
            'pkg_id'       => $r['pkg_id'],
            'package_name' => $r['package_name'],
            'service_id'   => $r['service_id'],
            'service_name' => $r['service_name'],
            'remaining'    => (int)$r['quantity'] - (int)$r['used'],
            'expiry_date'  => $r['expiry_date'],
        ];
    }
    return $out;
}

function get_wallet_ledger() {
    global $salon_id;
    extract($_REQUEST);
    $cust_id = intval($cust_id);
    $start   = intval($start ?? 0);
    $length  = intval($length ?? 25);
    $total   = num_rows("SELECT wallet_id FROM hr_customer_wallet WHERE cust_id='$cust_id'");
    $rows    = select_array("SELECT * FROM hr_customer_wallet WHERE cust_id='$cust_id' ORDER BY wallet_id DESC LIMIT $start, $length");
    $data    = [];
    foreach ($rows as $r) {
        $data[] = [
            'date'    => date('d M Y H:i', strtotime($r['created_date'])),
            'credit'  => $r['credit'] > 0 ? '₹' . number_format($r['credit'], 2) : '—',
            'debit'   => $r['debit']  > 0 ? '₹' . number_format($r['debit'], 2)  : '—',
            'balance' => '₹' . number_format($r['balance'], 2),
            'remark'  => $r['remark'] ?: '—',
        ];
    }
    return ['draw'=>intval($draw??1),'recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$data];
}

function deduct_package_service() {
    global $salon_id, $user_id;
    extract($_POST);
    $cp_id      = intval($cp_id);
    $service_id = intval($service_id);
    $qty        = max(1, intval($qty ?? 1));
    $invoice_id = intval($invoice_id ?? 0);

    $cp = select_row("SELECT cp.*, p.mrp_total, p.selling_price FROM hr_customer_packages cp JOIN hr_packages_new p ON p.pkg_id = cp.pkg_id WHERE cp.cp_id='$cp_id' AND cp.salon_id='$salon_id' AND cp.status='active'");
    if (!$cp) return ['error'=>1,'msg'=>'Package not found or not active.'];
    if ($cp['expiry_date'] < date('Y-m-d')) return ['error'=>1,'msg'=>'Package has expired.'];

    $item  = select_row("SELECT quantity, service_price FROM hr_package_items WHERE pkg_id='{$cp['pkg_id']}' AND service_id='$service_id'");
    if (!$item) return ['error'=>1,'msg'=>'Service not in this package.'];

    $used  = (int)select_row("SELECT COALESCE(SUM(qty_used),0) as u FROM hr_customer_package_usage WHERE cp_id='$cp_id' AND service_id='$service_id'")['u'];
    $remaining = $item['quantity'] - $used;
    if ($qty > $remaining) return ['error'=>1,'msg'=>"Only $remaining session(s) remaining."];

    if ($cp['remaining_amount'] > 0) {
        $allowed_mrp_value = $cp['purchase_price'] > 0 ? ($cp['paid_amount'] / $cp['purchase_price']) * $cp['mrp_total'] : $cp['mrp_total'];
        $past_usage_mrp = (float)select_row("SELECT COALESCE(SUM(u.qty_used * pi.service_price), 0) as used_mrp 
            FROM hr_customer_package_usage u 
            JOIN hr_package_items pi ON pi.pkg_id = u.pkg_id AND pi.service_id = u.service_id
            WHERE u.cp_id='$cp_id'")['used_mrp'];
        $new_usage_mrp = $qty * $item['service_price'];
        if (($past_usage_mrp + $new_usage_mrp) > $allowed_mrp_value) {
            return ['error'=>1,'msg'=>'Limit reached based on paid amount. Please pay remaining package balance.'];
        }
    }

    insert_query("INSERT INTO hr_customer_package_usage SET
        cp_id='$cp_id', pkg_id='{$cp['pkg_id']}', cust_id='{$cp['cust_id']}',
        service_id='$service_id', qty_used='$qty', invoice_id='$invoice_id', used_by='$user_id'");

    // Check if fully used
    $all_items = select_array("SELECT pi.quantity, COALESCE(SUM(u.qty_used),0) AS used
        FROM hr_package_items pi
        LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id='$cp_id'
        WHERE pi.pkg_id='{$cp['pkg_id']}' GROUP BY pi.item_id");
    $fully_used = true;
    foreach ($all_items as $ai) {
        if ((int)$ai['used'] < (int)$ai['quantity']) { $fully_used = false; break; }
    }
    if ($fully_used) update_query("UPDATE hr_customer_packages SET status='fully_used' WHERE cp_id='$cp_id'");

    return ['error'=>0,'msg'=>'Package service deducted. Remaining: '.($remaining - $qty)];
}

// ──────────────────────────────────────────────────────────
// REFUND & PAUSE
// ──────────────────────────────────────────────────────────
function refund_membership() {
    global $salon_id, $user_id;
    extract($_POST);
    $cm_id = intval($cm_id);
    $m = select_row("SELECT * FROM hr_customer_membership WHERE cm_id='$cm_id' AND salon_id='$salon_id'");
    if (!$m || $m['status'] == 'refunded') return ['error'=>1,'msg'=>'Membership not found or already refunded.'];

    // Debit wallet if credit was already given
    if ($m['wallet_credited']) {
        $cust = select_row("SELECT cust_wallet FROM hr_customer WHERE cust_id='{$m['cust_id']}'");
        $old_balance = floatval($cust['cust_wallet']);
        $deduct = min((float)$m['wallet_credit'], $old_balance);
        $new_balance = $old_balance - $deduct;
        $reason = mysqli_real_escape_string($GLOBALS['conn'], "Membership refund: " . $m['plan_name']);
        update_query("INSERT INTO hr_customer_wallet SET cust_id='{$m['cust_id']}', debit='$deduct', credit=0, balance='$new_balance', remark='$reason'");
        update_query("UPDATE hr_customer SET cust_wallet='$new_balance' WHERE cust_id='{$m['cust_id']}'");
        insert_query("INSERT INTO hr_wallet_audit_log SET cust_id='{$m['cust_id']}', salon_id='$salon_id', user_id='$user_id',
            old_balance='$old_balance', new_balance='$new_balance', change_type='refund', reason='$reason', reference='cm_$cm_id'");
    }
    update_query("UPDATE hr_customer_membership SET status='refunded' WHERE cm_id='$cm_id'");
    return ['error'=>0,'msg'=>'Membership refunded successfully.'];
}

function pause_membership() {
    global $salon_id;
    extract($_POST);
    $cm_id = intval($cm_id);
    $m = select_row("SELECT * FROM hr_customer_membership WHERE cm_id='$cm_id' AND salon_id='$salon_id' AND status='active'");
    if (!$m) return ['error'=>1,'msg'=>'Active membership not found.'];
    update_query("UPDATE hr_customer_membership SET status='paused', pause_date='".date('Y-m-d')."' WHERE cm_id='$cm_id'");
    return ['error'=>0,'msg'=>'Membership paused.'];
}

function resume_membership() {
    global $salon_id;
    extract($_POST);
    $cm_id = intval($cm_id);
    $m = select_row("SELECT * FROM hr_customer_membership WHERE cm_id='$cm_id' AND salon_id='$salon_id' AND status='paused'");
    if (!$m) return ['error'=>1,'msg'=>'Paused membership not found.'];
    // Calculate days paused and extend expiry
    $paused_days = (int)(strtotime('now') - strtotime($m['pause_date'])) / 86400;
    $new_expiry  = date('Y-m-d', strtotime($m['expiry_date']) + ($paused_days * 86400));
    update_query("UPDATE hr_customer_membership SET status='active', expiry_date='$new_expiry', pause_date=NULL WHERE cm_id='$cm_id'");
    return ['error'=>0,'msg'=>'Membership resumed. New expiry: '.date('d M Y', strtotime($new_expiry))];
}

function refund_package() {
    global $salon_id, $user_id;
    extract($_POST);
    $cp_id = intval($cp_id);
    $cp = select_row("SELECT * FROM hr_customer_packages WHERE cp_id='$cp_id' AND salon_id='$salon_id'");
    if (!$cp || $cp['status'] == 'refunded') return ['error'=>1,'msg'=>'Package not found or already refunded.'];
    update_query("UPDATE hr_customer_packages SET status='refunded' WHERE cp_id='$cp_id'");
    return ['error'=>0,'msg'=>'Package refunded.'];
}

// ──────────────────────────────────────────────────────────
// REPORTING DATA
// ──────────────────────────────────────────────────────────
function membership_report_data() {
    global $salon_id;
    extract($_REQUEST);
    $from = !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : date('Y-m-01');
    $to   = !empty($to_date)   ? date('Y-m-d', strtotime($to_date))   : date('Y-m-d');

    $total_sold   = num_rows("SELECT cm_id FROM hr_customer_membership WHERE salon_id='$salon_id' AND DATE(created_at) BETWEEN '$from' AND '$to'");
    $active_count = num_rows("SELECT cm_id FROM hr_customer_membership WHERE salon_id='$salon_id' AND status='active'");
    $expired_count= num_rows("SELECT cm_id FROM hr_customer_membership WHERE salon_id='$salon_id' AND status='expired'");

    $revenue    = select_row("SELECT COALESCE(SUM(paid_amount),0) as total FROM hr_customer_membership WHERE salon_id='$salon_id' AND DATE(created_at) BETWEEN '$from' AND '$to'");
    $liability  = select_row("SELECT COALESCE(SUM(c.cust_wallet),0) as total FROM hr_customer c WHERE c.salon_id='$salon_id'");
    $redeemed   = select_row("SELECT COALESCE(SUM(debit),0) as total FROM hr_customer_wallet w JOIN hr_customer c ON c.cust_id=w.cust_id WHERE c.salon_id='$salon_id'");

    $members_list = select_array("SELECT cm.cm_id, cm.plan_name, cm.paid_amount, cm.remaining_amount,
        cm.wallet_credit, cm.status, cm.expiry_date, cm.invoice_id, 
        (SELECT payment_mode FROM hr_membership_payments WHERE cm_id=cm.cm_id ORDER BY mp_id ASC LIMIT 1) as payment_mode,
        c.cust_id, c.cust_name, c.cust_mobile
        FROM hr_customer_membership cm
        JOIN hr_customer c ON c.cust_id=cm.cust_id
        WHERE cm.salon_id='$salon_id' AND DATE(cm.created_at) BETWEEN '$from' AND '$to'
        ORDER BY cm.cm_id DESC");

    return [
        'error'         => 0,
        'total_sold'    => $total_sold,
        'active_count'  => $active_count,
        'expired_count' => $expired_count,
        'total_revenue' => floatval($revenue['total']),
        'wallet_liability'=> floatval($liability['total']),
        'wallet_redeemed' => floatval($redeemed['total']),
        'members_list'  => $members_list,
    ];
}

function package_report_data() {
    global $salon_id;
    extract($_REQUEST);
    $from = !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : date('Y-m-01');
    $to   = !empty($to_date)   ? date('Y-m-d', strtotime($to_date))   : date('Y-m-d');
    $today = date('Y-m-d');

    $total_sold    = num_rows("SELECT cp_id FROM hr_customer_packages WHERE salon_id='$salon_id' AND DATE(created_at) BETWEEN '$from' AND '$to'");
    $active_count  = num_rows("SELECT cp_id FROM hr_customer_packages WHERE salon_id='$salon_id' AND status='active'");
    $expiring_soon = num_rows("SELECT cp_id FROM hr_customer_packages WHERE salon_id='$salon_id' AND status='active' AND expiry_date BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 30 DAY)");

    $revenue = select_row("SELECT COALESCE(SUM(purchase_price),0) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND DATE(created_at) BETWEEN '$from' AND '$to'");

    // Service liability (remaining sessions × service price)
    $liability_rows = select_array("SELECT pi.service_name, pi.service_price, pi.quantity,
        COALESCE(SUM(u.qty_used),0) AS total_used,
        COUNT(DISTINCT cp.cp_id) as pkg_count
        FROM hr_customer_packages cp
        JOIN hr_package_items pi ON pi.pkg_id = cp.pkg_id
        LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id=cp.cp_id
        WHERE cp.salon_id='$salon_id' AND cp.status='active'
        GROUP BY pi.service_id, cp.pkg_id");

    $pkg_list = select_array("SELECT cp.cp_id, cp.package_name, cp.purchase_price, cp.paid_amount,
        cp.remaining_amount, cp.status, cp.purchase_date, cp.expiry_date, cp.invoice_id, cp.payment_mode,
        c.cust_id, c.cust_name, c.cust_mobile
        FROM hr_customer_packages cp
        JOIN hr_customer c ON c.cust_id=cp.cust_id
        WHERE cp.salon_id='$salon_id' AND DATE(cp.created_at) BETWEEN '$from' AND '$to'
        ORDER BY cp.cp_id DESC");

    return [
        'error'         => 0,
        'total_sold'    => $total_sold,
        'active_count'  => $active_count,
        'expiring_soon' => $expiring_soon,
        'total_revenue' => floatval($revenue['total']),
        'liability_rows'=> $liability_rows,
        'pkg_list'      => $pkg_list,
    ];
}

// ──────────────────────────────────────────────────────────
// UTILITY HELPER
// ──────────────────────────────────────────────────────────
function credit_customer_wallet($cust_id, $amount, $reference_cm_id, $remark) {
    $cust = select_row("SELECT cust_wallet FROM hr_customer WHERE cust_id='$cust_id'");
    $old_balance = floatval($cust['cust_wallet'] ?? 0);
    $new_balance = $old_balance + $amount;
    $remark_esc  = mysqli_real_escape_string($GLOBALS['conn'], $remark);
    update_query("INSERT INTO hr_customer_wallet SET
        cust_id='$cust_id', credit='$amount', debit=0, balance='$new_balance',
        remark='$remark_esc'");
    update_query("UPDATE hr_customer SET cust_wallet='$new_balance' WHERE cust_id='$cust_id'");
    insert_query("INSERT INTO hr_wallet_audit_log SET
        cust_id='$cust_id', salon_id='" . get_session_data('salon_id') . "',
        user_id='" . (get_session_data('user_id') ?: 0) . "',
        old_balance='$old_balance', new_balance='$new_balance',
        change_type='credit', reason='$remark_esc', reference='cm_$reference_cm_id'");
    return $new_balance;
}
?>

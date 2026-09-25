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
    mysqli_query($conn, "ALTER TABLE `hr_customer_membership` MODIFY `sold_by` VARCHAR(255) DEFAULT NULL");
    
    $pkg_col_check = mysqli_query($conn, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hr_customer_packages' AND COLUMN_NAME = 'paid_amount'");
    if ($pkg_col_check && mysqli_num_rows($pkg_col_check) === 0) {
        mysqli_query($conn, "ALTER TABLE `hr_customer_packages` ADD COLUMN `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `purchase_price`, ADD COLUMN `remaining_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `paid_amount`");
        mysqli_query($conn, "UPDATE `hr_customer_packages` SET `paid_amount` = `purchase_price` + `gst_amount`, `remaining_amount` = 0");
    }
    mysqli_query($conn, "ALTER TABLE `hr_customer_packages` MODIFY `sold_by` VARCHAR(255) DEFAULT NULL");
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
    $vm = intval($validity_months ?? 3);
    if ($vm === 0) {
        $validity_days = 0;
    } elseif ($vm === 24) {
        $validity_days = 730;
    } elseif ($vm === 12) {
        $validity_days = 365;
    } else {
        $validity_days = $vm * 30;
    }
    $selling_price  = floatval($selling_price);
    $allow_discount = intval($allow_discount ?? 0);
    $gst_applicable = intval($gst_applicable ?? 0);
    $gst_percent    = floatval($gst_percent ?? 0);
    $status         = intval($status ?? 1);
    $service_ids    = isset($service_id)    ? (array)$service_id    : [];
    $quantities     = isset($qty)           ? (array)$qty           : [];
    $var_ids        = isset($var_id)        ? (array)$var_id        : [];
    $var_prices     = isset($var_unit_price) ? (array)$var_unit_price : [];

    if (empty($package_name) || $selling_price <= 0 || empty($service_ids)) {
        return ['error' => 1, 'msg' => 'Package name, price and at least one service are required.'];
    }

    // Calculate MRP from services (variation price takes priority if supplied)
    $mrp_total = 0;
    $service_data = [];
    foreach ($service_ids as $i => $sid) {
        $sid = intval($sid);
        $qty = max(1, intval($quantities[$i] ?? 1));
        $var_id_val = intval($var_ids[$i] ?? 0);
        // If a variation price is sent use it, otherwise fall back to service base price
        $unit_price = isset($var_prices[$i]) && floatval($var_prices[$i]) > 0
            ? floatval($var_prices[$i]) : 0;
        $svc = select_row("SELECT service_id, service_name, service_price FROM hr_services WHERE service_id='$sid'");
        if ($svc) {
            if ($unit_price <= 0) $unit_price = floatval($svc['service_price']);
            $mrp_total += $unit_price * $qty;
            $service_data[] = [
                'sid'    => $sid,
                'name'   => $svc['service_name'],
                'price'  => $unit_price,
                'qty'    => $qty,
                'var_id' => $var_id_val,
            ];
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
    $vm = intval($validity_months ?? 3);
    if ($vm === 0) {
        $validity_days = 0;
    } elseif ($vm === 24) {
        $validity_days = 730;
    } elseif ($vm === 12) {
        $validity_days = 365;
    } else {
        $validity_days = $vm * 30;
    }
    $selling_price  = floatval($selling_price);
    $allow_discount = intval($allow_discount ?? 0);
    $gst_applicable = intval($gst_applicable ?? 0);
    $gst_percent    = floatval($gst_percent ?? 0);
    $status         = intval($status ?? 1);
    $service_ids    = isset($service_id)     ? (array)$service_id     : [];
    $quantities     = isset($qty)            ? (array)$qty            : [];
    $var_ids        = isset($var_id)         ? (array)$var_id         : [];
    $var_prices     = isset($var_unit_price) ? (array)$var_unit_price : [];

    $mrp_total = 0;
    $service_data = [];
    foreach ($service_ids as $i => $sid) {
        $sid = intval($sid);
        $qty = max(1, intval($quantities[$i] ?? 1));
        $var_id_val = intval($var_ids[$i] ?? 0);
        $unit_price = isset($var_prices[$i]) && floatval($var_prices[$i]) > 0
            ? floatval($var_prices[$i]) : 0;
        $svc = select_row("SELECT service_id, service_name, service_price FROM hr_services WHERE service_id='$sid'");
        if ($svc) {
            if ($unit_price <= 0) $unit_price = floatval($svc['service_price']);
            $mrp_total += $unit_price * $qty;
            $service_data[] = [
                'sid'    => $sid,
                'name'   => $svc['service_name'],
                'price'  => $unit_price,
                'qty'    => $qty,
                'var_id' => $var_id_val,
            ];
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

    $fields = [
        0 => 'p.pkg_id',
        1 => 'p.package_name',
        3 => 'p.mrp_total',
        4 => 'p.selling_price',
        5 => 'p.savings',
        6 => 'p.validity_days',
        7 => 'p.status'
    ];

    if (isset($order[0]['column']) && isset($fields[$order[0]['column']])) {
        $col = $fields[$order[0]['column']];
        $dir = (isset($order[0]['dir']) && strtolower($order[0]['dir']) === 'asc') ? 'ASC' : 'DESC';
        if ($col === 'p.status') {
            $order_by = "ORDER BY p.status $dir, p.pkg_id DESC";
        } else {
            $order_by = "ORDER BY $col $dir";
        }
    } else {
        $order_by = "ORDER BY p.status DESC, p.pkg_id DESC";
    }

    $total = num_rows("SELECT pkg_id FROM hr_packages_new p $where");
    $sql   = "SELECT p.* FROM hr_packages_new p $where $order_by LIMIT $start, $length";
    $rows  = select_array($sql);
    $data  = [];
    foreach ($rows as $r) {
        $v_days = (int)$r['validity_days'];
        if ($v_days == 0 || $v_days >= 36500) {
            $validity_str = '<span style="background:#e0e7ff;color:#4f46e5;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">No Expiry</span>';
        } else {
            $vm = round($v_days / 30);
            if ($vm >= 24 && $vm % 12 == 0) {
                $yrs = round($vm / 12);
                $validity_str = $yrs . ' Years';
            } elseif ($vm == 12) {
                $validity_str = '1 Year';
            } else {
                $validity_str = $vm . ' Month' . ($vm != 1 ? 's' : '');
            }
        }
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
            'validity'      => $validity_str,
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

function get_services_with_variations() {
    global $salon_id;
    // Fetch all active services with category
    $services = select_array("SELECT s.service_id, s.service_name, s.service_price, sc.service_catName
        FROM hr_services s
        LEFT JOIN hr_servicesCategory sc ON sc.service_catid = s.service_catid
        WHERE s.salon_id='$salon_id' AND s.service_status=1
        ORDER BY sc.service_catName, s.service_name");
    if (!$services) return [];
    // Fetch all variations for this salon in one query
    $all_vars = select_array("SELECT var_id, service_id, var_name, var_price
        FROM hr_service_variations WHERE salon_id='$salon_id' ORDER BY sort_order ASC, var_id ASC");
    // Index variations by service_id
    $vars_map = [];
    foreach ($all_vars as $v) {
        $vars_map[$v['service_id']][] = [
            'var_id'    => (int)$v['var_id'],
            'var_name'  => $v['var_name'],
            'var_price' => (float)$v['var_price'],
        ];
    }
    // Attach variations to each service
    foreach ($services as &$svc) {
        $svc['service_id']    = (int)$svc['service_id'];
        $svc['service_price'] = (float)$svc['service_price'];
        $svc['variations']    = $vars_map[$svc['service_id']] ?? [];
    }
    return $services;
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
    $sold_by_arr   = isset($_POST['staff_id']) ? (is_array($_POST['staff_id']) ? $_POST['staff_id'] : [$_POST['staff_id']]) : [];
    $sold_by_clean = array_filter(array_map('intval', $sold_by_arr));
    if (empty($sold_by_clean)) {
        return ['error' => 1, 'msg' => 'Please select at least one staff member (Sold By).'];
    }
    $sold_by       = mysqli_real_escape_string($conn, implode(',', $sold_by_clean));
    $split_payments = isset($_POST['split_payments']) ? json_decode($_POST['split_payments'], true) : [];
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
    if (!empty($split_payments) && is_array($split_payments)) {
        foreach ($split_payments as $sp) {
            $sp_mode = mysqli_real_escape_string($conn, $sp['mode']);
            $sp_amt = floatval($sp['amount']);
            if ($sp_amt > 0) {
                insert_query("INSERT INTO hr_membership_payments SET
                    cm_id='$cm_id', salon_id='$salon_id', cust_id='$cust_id',
                    amount='$sp_amt', payment_mode='$sp_mode', paid_by='$user_id',
                    notes='Initial split payment', created_at='$created_at_val'");
            }
        }
    } else {
        insert_query("INSERT INTO hr_membership_payments SET
            cm_id='$cm_id', salon_id='$salon_id', cust_id='$cust_id',
            amount='$paid_now', payment_mode='$payment_mode', paid_by='$user_id',
            notes='Initial payment', created_at='$created_at_val'");
    }

    // Create POS Invoice for transparency in invoice history
    $cust_row = select_row("SELECT cust_name, cust_mobile FROM hr_customer WHERE cust_id='$cust_id'");
    $inv_res = create_pos_invoice_with_staff([
        'salon_id' => $salon_id,
        'user_id' => $user_id,
        'cust_id' => $cust_id,
        'cust_name' => $cust_row['cust_name'] ?? '',
        'cust_mob' => $cust_row['cust_mobile'] ?? '',
        'service_cat' => 'Membership',
        'service_name' => $plan['plan_name'],
        'service_price' => $total_price,
        'paid_amount' => $paid_now,
        'outstanding' => $remaining,
        'payment_mode' => $payment_mode,
        'billing_remark' => 'Membership Purchase: ' . $plan['plan_name'],
        'invoice_date' => $created_at_val,
        'staff_ids' => $sold_by_clean,
        'split_payments' => $split_payments
    ]);

    // Credit wallet with initial dispensed amount
    if ($wallet_credited > 0) {
        credit_customer_wallet($cust_id, $wallet_credited, $cm_id, 'Membership' . ($is_fully_paid ? '' : ' (Partial)') . ': ' . $plan['plan_name']);
    }
    if ($is_fully_paid) {
        update_query("UPDATE hr_customer SET active_membership_id='$cm_id' WHERE cust_id='$cust_id'");
    }

    $print_url = $inv_res['print_url'] ?? '';
    return ['error' => 0, 'msg' => 'Membership sold successfully.' . ($remaining > 0 ? ' Remaining: ₹' . $remaining : ' Wallet credited: ₹' . $wallet_credit), 'print_url' => $print_url];
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

    // Create POS Invoice for transparency in invoice history
    $cust_row = select_row("SELECT cust_name, cust_mobile FROM hr_customer WHERE cust_id='{$membership['cust_id']}'");
    $sold_by_clean = array_filter(array_map('intval', explode(',', $membership['sold_by'] ?? '')));
    $inv_res = create_pos_invoice_with_staff([
        'salon_id' => $salon_id,
        'user_id' => $user_id,
        'cust_id' => $membership['cust_id'],
        'cust_name' => $cust_row['cust_name'] ?? '',
        'cust_mob' => $cust_row['cust_mobile'] ?? '',
        'service_cat' => 'Membership Payment',
        'service_name' => 'Membership Payment: ' . $membership['plan_name'],
        'service_price' => $amount,
        'paid_amount' => $amount,
        'outstanding' => 0,
        'payment_mode' => $payment_mode,
        'billing_remark' => 'Membership Payment: ' . $membership['plan_name'],
        'invoice_date' => $created_at_val,
        'staff_ids' => $sold_by_clean
    ]);

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
        if (function_exists('sync_customer_outstanding')) {
            sync_customer_outstanding($membership['cust_id']);
        }
        return ['error' => 0, 'msg' => 'Payment recorded. Membership fully paid! Wallet credited ₹' . $wallet_credit_to_give, 'print_url' => $inv_res['print_url'] ?? ''];
    } else {
        update_query("UPDATE hr_customer_membership SET paid_amount='$new_paid', remaining_amount='$new_remaining', wallet_credited='$new_wallet_credited' WHERE cm_id='$cm_id'");
        if ($wallet_credit_to_give > 0) {
            credit_customer_wallet($membership['cust_id'], $wallet_credit_to_give, $cm_id, 'Membership Partial Payment: ' . $membership['plan_name']);
        }
        if (function_exists('sync_customer_outstanding')) {
            sync_customer_outstanding($membership['cust_id']);
        }
        return ['error' => 0, 'msg' => 'Payment recorded. Remaining: ₹' . $new_remaining . ' Wallet credited: ₹' . $wallet_credit_to_give, 'print_url' => $inv_res['print_url'] ?? ''];
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
    $sold_by_arr  = isset($_POST['staff_id']) ? (is_array($_POST['staff_id']) ? $_POST['staff_id'] : [$_POST['staff_id']]) : [];
    $sold_by_clean = array_filter(array_map('intval', $sold_by_arr));
    if (empty($sold_by_clean)) {
        return ['error' => 1, 'msg' => 'Please select at least one staff member (Sold By).'];
    }
    $sold_by      = mysqli_real_escape_string($conn, implode(',', $sold_by_clean));
    $split_payments = isset($_POST['split_payments']) ? json_decode($_POST['split_payments'], true) : [];
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
    if ((int)$pkg['validity_days'] == 0 || (int)$pkg['validity_days'] >= 36500) {
        $expiry_date = '2099-12-31';
    } else {
        $expiry_date = date('Y-m-d', strtotime($billing_date . ' + ' . $pkg['validity_days'] . ' days'));
    }
    $created_at_val = $billing_date . ' ' . date('H:i:s');

    $cp_id = insert_query("INSERT INTO hr_customer_packages SET
        salon_id='$salon_id', cust_id='$cust_id', pkg_id='$pkg_id',
        package_name='" . mysqli_real_escape_string($conn, $pkg['package_name']) . "',
        purchase_price='$purchase_price', paid_amount='$paid_now', remaining_amount='$remaining',
        gst_amount='$gst_amount', payment_mode='$payment_mode', purchase_date='$purchase_date',
        expiry_date='$expiry_date', status='active', sold_by='$sold_by', notes='$notes_str', created_at='$created_at_val'");

    if (!$cp_id) return ['error' => 1, 'msg' => 'Failed to create package record.'];

    // Record payment entry
    if (!empty($split_payments) && is_array($split_payments)) {
        foreach ($split_payments as $sp) {
            $sp_mode = mysqli_real_escape_string($conn, $sp['mode']);
            $sp_amt = floatval($sp['amount']);
            if ($sp_amt > 0) {
                insert_query("INSERT INTO hr_package_payments SET
                    cp_id='$cp_id', salon_id='$salon_id', cust_id='$cust_id',
                    amount='$sp_amt', payment_mode='$sp_mode', paid_by='$user_id', notes='Initial split payment', created_at='$created_at_val'");
            }
        }
    } else {
        insert_query("INSERT INTO hr_package_payments SET
            cp_id='$cp_id', salon_id='$salon_id', cust_id='$cust_id',
            amount='$paid_now', payment_mode='$payment_mode', paid_by='$user_id', notes='Initial payment', created_at='$created_at_val'");
    }

    // Create POS Invoice for transparency in invoice history
    $cust_row = select_row("SELECT cust_name, cust_mobile FROM hr_customer WHERE cust_id='$cust_id'");
    $inv_res = create_pos_invoice_with_staff([
        'salon_id' => $salon_id,
        'user_id' => $user_id,
        'cust_id' => $cust_id,
        'cust_name' => $cust_row['cust_name'] ?? '',
        'cust_mob' => $cust_row['cust_mobile'] ?? '',
        'service_cat' => 'Package',
        'service_name' => $pkg['package_name'],
        'service_price' => $purchase_price,
        'paid_amount' => $paid_now,
        'outstanding' => $remaining,
        'payment_mode' => $payment_mode,
        'billing_remark' => 'Package Purchase: ' . $pkg['package_name'],
        'invoice_date' => $created_at_val,
        'staff_ids' => $sold_by_clean,
        'split_payments' => $split_payments
    ]);

    $print_url = $inv_res['print_url'] ?? '';
    return ['error' => 0, 'msg' => 'Package sold successfully. Expires: ' . date('d M Y', strtotime($expiry_date)) . ($remaining > 0 ? ' Remaining: ₹' . $remaining : ''), 'print_url' => $print_url];
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

    // Create POS Invoice for transparency in invoice history
    $cust_row = select_row("SELECT cust_name, cust_mobile FROM hr_customer WHERE cust_id='{$pkg['cust_id']}'");
    $sold_by_clean = array_filter(array_map('intval', explode(',', $pkg['sold_by'] ?? '')));
    $inv_res = create_pos_invoice_with_staff([
        'salon_id' => $salon_id,
        'user_id' => $user_id,
        'cust_id' => $pkg['cust_id'],
        'cust_name' => $cust_row['cust_name'] ?? '',
        'cust_mob' => $cust_row['cust_mobile'] ?? '',
        'service_cat' => 'Package Payment',
        'service_name' => 'Package Payment: ' . $pkg['package_name'],
        'service_price' => $amount,
        'paid_amount' => $amount,
        'outstanding' => 0,
        'payment_mode' => $payment_mode,
        'billing_remark' => 'Package Payment: ' . $pkg['package_name'],
        'invoice_date' => $created_at_val,
        'staff_ids' => $sold_by_clean
    ]);

    update_query("UPDATE hr_customer_packages SET paid_amount='$new_paid', remaining_amount='$new_remaining' WHERE cp_id='$cp_id'");
    
    if (function_exists('sync_customer_outstanding')) {
        sync_customer_outstanding($pkg['cust_id']);
    }
    
    if ($new_remaining <= 0) {
        return ['error' => 0, 'msg' => 'Payment recorded. Package fully paid!', 'print_url' => $inv_res['print_url'] ?? ''];
    } else {
        return ['error' => 0, 'msg' => 'Payment recorded. Remaining: ₹' . $new_remaining, 'print_url' => $inv_res['print_url'] ?? ''];
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
        pi.service_id, pi.service_name, pi.quantity, pi.service_price,
        COALESCE(SUM(u.qty_used),0) AS used
        FROM hr_customer_packages cp
        JOIN hr_package_items pi ON pi.pkg_id = cp.pkg_id
        LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id=cp.cp_id
        WHERE cp.cust_id='$cust_id' AND cp.salon_id='$salon_id'
          AND cp.status='active' AND cp.expiry_date >= '$today'
        GROUP BY pi.item_id
        HAVING (pi.quantity - used) > 0");

    // Fetch all variations for this salon once (avoid N+1 per row)
    $all_vars_raw = select_array("SELECT var_id, service_id, var_name, var_price
        FROM hr_service_variations WHERE salon_id='$salon_id' ORDER BY sort_order ASC, var_id ASC");
    $vars_map = [];
    foreach ($all_vars_raw as $v) {
        $vars_map[$v['service_id']][] = $v;
    }

    $out = [];
    foreach ($rows as $r) {
        $variations  = $vars_map[$r['service_id']] ?? [];
        // Find the variation whose price matches the package item's saved price (±₹0.01 tolerance)
        $matched_var = null;
        foreach ($variations as $v) {
            if (abs(floatval($v['var_price']) - floatval($r['service_price'])) < 0.01) {
                $matched_var = $v;
                break;
            }
        }
        $out[] = [
            'cp_id'            => $r['cp_id'],
            'pkg_id'           => $r['pkg_id'],
            'package_name'     => $r['package_name'],
            'service_id'       => $r['service_id'],
            'service_name'     => $r['service_name'],
            'service_price'    => (float)$r['service_price'],
            'remaining'        => (int)$r['quantity'] - (int)$r['used'],
            'expiry_date'      => $r['expiry_date'],
            'variations'       => array_values(array_map(fn($v) => [
                'var_id'    => (int)$v['var_id'],
                'var_name'  => $v['var_name'],
                'var_price' => (float)$v['var_price'],
            ], $variations)),
            'matched_var_id'   => $matched_var ? (int)$matched_var['var_id']   : 0,
            'matched_var_name' => $matched_var ? $matched_var['var_name']       : '',
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

    $total_sold   = num_rows("SELECT cm_id FROM hr_customer_membership WHERE salon_id='$salon_id' AND (DATE(created_at) BETWEEN '$from' AND '$to' OR (start_date IS NOT NULL AND start_date BETWEEN '$from' AND '$to'))");
    $active_count = num_rows("SELECT cm_id FROM hr_customer_membership WHERE salon_id='$salon_id' AND status='active'");
    $expired_count= num_rows("SELECT cm_id FROM hr_customer_membership WHERE salon_id='$salon_id' AND status='expired'");

    $revenue    = select_row("SELECT COALESCE(SUM(paid_amount),0) as total FROM hr_customer_membership WHERE salon_id='$salon_id' AND (DATE(created_at) BETWEEN '$from' AND '$to' OR (start_date IS NOT NULL AND start_date BETWEEN '$from' AND '$to'))");
    $liability  = select_row("SELECT COALESCE(SUM(c.cust_wallet),0) as total FROM hr_customer c WHERE c.salon_id='$salon_id'");
    $redeemed   = select_row("SELECT COALESCE(SUM(debit),0) as total FROM hr_customer_wallet w JOIN hr_customer c ON c.cust_id=w.cust_id WHERE c.salon_id='$salon_id'");

    $members_list = select_array("SELECT cm.cm_id, cm.plan_name, cm.paid_amount, cm.remaining_amount,
        cm.wallet_credit, cm.status, cm.expiry_date, cm.invoice_id, cm.start_date,
        COALESCE(cm.invoice_id, (SELECT invoice_id FROM hr_invoice WHERE cust_id=c.cust_id AND delete_bill=0 ORDER BY invoice_id DESC LIMIT 1)) as effective_invoice_id,
        (SELECT payment_mode FROM hr_membership_payments WHERE cm_id=cm.cm_id ORDER BY mp_id ASC LIMIT 1) as payment_mode,
        c.cust_id, c.cust_name, c.cust_mobile
        FROM hr_customer_membership cm
        JOIN hr_customer c ON c.cust_id=cm.cust_id
        WHERE cm.salon_id='$salon_id' AND (DATE(cm.created_at) BETWEEN '$from' AND '$to' OR (cm.start_date IS NOT NULL AND cm.start_date BETWEEN '$from' AND '$to'))
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

    $total_sold    = num_rows("SELECT cp_id FROM hr_customer_packages WHERE salon_id='$salon_id' AND (DATE(created_at) BETWEEN '$from' AND '$to' OR (purchase_date IS NOT NULL AND purchase_date BETWEEN '$from' AND '$to'))");
    $active_count  = num_rows("SELECT cp_id FROM hr_customer_packages WHERE salon_id='$salon_id' AND status='active'");
    $expiring_soon = num_rows("SELECT cp_id FROM hr_customer_packages WHERE salon_id='$salon_id' AND status='active' AND expiry_date BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 30 DAY)");

    $revenue = select_row("SELECT COALESCE(SUM(purchase_price),0) as total FROM hr_customer_packages WHERE salon_id='$salon_id' AND (DATE(created_at) BETWEEN '$from' AND '$to' OR (purchase_date IS NOT NULL AND purchase_date BETWEEN '$from' AND '$to'))");

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
        COALESCE(cp.invoice_id, (SELECT invoice_id FROM hr_invoice WHERE cust_id=c.cust_id AND delete_bill=0 ORDER BY invoice_id DESC LIMIT 1)) as effective_invoice_id,
        c.cust_id, c.cust_name, c.cust_mobile
        FROM hr_customer_packages cp
        JOIN hr_customer c ON c.cust_id=cp.cust_id
        WHERE cp.salon_id='$salon_id' AND (DATE(cp.created_at) BETWEEN '$from' AND '$to' OR (cp.purchase_date IS NOT NULL AND cp.purchase_date BETWEEN '$from' AND '$to'))
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
// MANUAL REGISTER IMPORT & BALANCE ADJUSTMENTS
// ──────────────────────────────────────────────────────────

function get_package_items_manual() {
    global $salon_id;
    $pkg_id = intval($_REQUEST['pkg_id'] ?? 0);
    if (!$pkg_id) return ['error' => 1, 'msg' => 'Invalid package ID.'];

    $pkg = select_row("SELECT * FROM hr_packages_new WHERE pkg_id='$pkg_id' AND salon_id='$salon_id'");
    if (!$pkg) return ['error' => 1, 'msg' => 'Package not found.'];

    $items = select_array("SELECT item_id, service_id, service_name, quantity, service_price FROM hr_package_items WHERE pkg_id='$pkg_id'");

    return [
        'error' => 0,
        'package' => $pkg,
        'items' => $items
    ];
}

function add_manual_package_entry() {
    global $salon_id, $user_id, $conn;
    extract($_POST);

    $cust_id        = intval($cust_id);
    $pkg_id         = intval($pkg_id);
    $purchase_price = floatval($purchase_price ?? 0);
    $paid_amount    = floatval($paid_amount ?? 0);
    $payment_mode   = mysqli_real_escape_string($conn, $payment_mode ?? 'cash');
    $notes_str      = mysqli_real_escape_string($conn, $notes ?? '');
    $p_date         = !empty($purchase_date) ? date('Y-m-d', strtotime($purchase_date)) : date('Y-m-d');
    $created_at_val = $p_date . ' 10:00:00';

    if (!$cust_id || !$pkg_id) {
        return ['error' => 1, 'msg' => 'Please select a customer and a package.'];
    }

    $pkg = select_row("SELECT * FROM hr_packages_new WHERE pkg_id='$pkg_id' AND salon_id='$salon_id'");
    if (!$pkg) return ['error' => 1, 'msg' => 'Package not found.'];

    $pkg_items = select_array("SELECT * FROM hr_package_items WHERE pkg_id='$pkg_id'");
    if (!$pkg_items) return ['error' => 1, 'msg' => 'No service items in this package.'];

    $remaining = max(0, round($purchase_price - $paid_amount, 2));
    if ((int)$pkg['validity_days'] == 0 || (int)$pkg['validity_days'] >= 36500) {
        $expiry_date = '2099-12-31';
    } else {
        $v_days = (int)($pkg['validity_days'] ?: 365);
        $expiry_date = date('Y-m-d', strtotime($p_date . ' + ' . $v_days . ' days'));
    }

    $total_qty = 0;
    $total_taken = 0;
    $service_taken_map = isset($_POST['qty_taken']) && is_array($_POST['qty_taken']) ? $_POST['qty_taken'] : [];

    foreach ($pkg_items as $pi) {
        $sid = $pi['service_id'];
        $q = (int)$pi['quantity'];
        $taken = isset($service_taken_map[$sid]) ? min($q, max(0, (int)$service_taken_map[$sid])) : 0;
        $total_qty += $q;
        $total_taken += $taken;
    }

    $status = ($total_taken >= $total_qty && $total_qty > 0) ? 'fully_used' : 'active';
    if ($expiry_date < date('Y-m-d') && $status === 'active') {
        $status = 'expired';
    }

    $cp_id = insert_query("INSERT INTO hr_customer_packages SET
        salon_id='$salon_id', cust_id='$cust_id', pkg_id='$pkg_id',
        package_name='" . mysqli_real_escape_string($conn, $pkg['package_name']) . "',
        purchase_price='$purchase_price', paid_amount='$paid_amount', remaining_amount='$remaining',
        gst_amount='0', payment_mode='$payment_mode', purchase_date='$p_date',
        expiry_date='$expiry_date', status='$status', sold_by='$user_id',
        notes='" . mysqli_real_escape_string($conn, 'Manual register entry: ' . $notes_str) . "',
        created_at='$created_at_val'");

    if (!$cp_id) return ['error' => 1, 'msg' => 'Failed to record manual package entry.'];

    if ($paid_amount > 0) {
        insert_query("INSERT INTO hr_package_payments SET
            cp_id='$cp_id', salon_id='$salon_id', cust_id='$cust_id',
            amount='$paid_amount', payment_mode='$payment_mode', paid_by='$user_id',
            notes='Initial payment from manual register entry', created_at='$created_at_val'");
    }

    foreach ($pkg_items as $pi) {
        $sid = $pi['service_id'];
        $q = (int)$pi['quantity'];
        $taken = isset($service_taken_map[$sid]) ? min($q, max(0, (int)$service_taken_map[$sid])) : 0;
        if ($taken > 0) {
            insert_query("INSERT INTO hr_customer_package_usage SET
                cp_id='$cp_id', pkg_id='$pkg_id', cust_id='$cust_id', service_id='$sid',
                qty_used='$taken', invoice_id=NULL, used_by='$user_id', used_at='$created_at_val'");
        }
    }

    return ['error' => 0, 'msg' => 'Manual package & past sessions recorded successfully!'];
}

function adjust_customer_wallet_manual() {
    global $salon_id, $user_id, $conn;
    extract($_POST);

    $cust_id  = intval($cust_id);
    $adj_type = mysqli_real_escape_string($conn, $adj_type ?? 'credit');
    $amount   = floatval($amount ?? 0);
    $remark   = trim($remark ?? '');
    $adj_date = !empty($adj_date) ? date('Y-m-d H:i:s', strtotime($adj_date . ' ' . date('H:i:s'))) : date('Y-m-d H:i:s');

    if (!$cust_id) return ['error' => 1, 'msg' => 'Invalid customer ID.'];
    if ($amount < 0) return ['error' => 1, 'msg' => 'Amount cannot be negative.'];
    if (empty($remark)) return ['error' => 1, 'msg' => 'Please enter a valid reason / remark for audit purposes.'];

    $cust = select_row("SELECT cust_wallet FROM hr_customer WHERE cust_id='$cust_id' AND salon_id='$salon_id'");
    if (!$cust) return ['error' => 1, 'msg' => 'Customer not found.'];

    $current_bal = (float)$cust['cust_wallet'];
    $debit = 0;
    $credit = 0;
    $new_bal = $current_bal;

    if ($adj_type === 'credit') {
        $credit = $amount;
        $new_bal = $current_bal + $amount;
    } elseif ($adj_type === 'debit') {
        $debit = $amount;
        $new_bal = max(0, $current_bal - $amount);
    } elseif ($adj_type === 'set') {
        $new_bal = $amount;
        if ($amount > $current_bal) {
            $credit = $amount - $current_bal;
        } else {
            $debit = $current_bal - $amount;
        }
    }

    $remark_esc = mysqli_real_escape_string($conn, $remark);

    // Insert wallet entry
    insert_query("INSERT INTO hr_customer_wallet SET
        cust_id='$cust_id', debit='$debit', credit='$credit', balance='$new_bal',
        remark='$remark_esc', created_date='$adj_date'");

    // Update customer balance
    update_query("UPDATE hr_customer SET cust_wallet='$new_bal' WHERE cust_id='$cust_id' AND salon_id='$salon_id'");

    // Log in wallet audit log if exists
    try {
        insert_query("INSERT INTO hr_wallet_audit_log SET
            cust_id='$cust_id', salon_id='$salon_id', user_id='$user_id',
            old_balance='$current_bal', new_balance='$new_bal',
            change_type='".($credit > 0 ? 'credit' : 'debit')."', reason='$remark_esc', reference='manual_register_sync'");
    } catch (\Throwable $e) {}

    return ['error' => 0, 'msg' => 'Wallet balance updated to ₹' . number_format($new_bal, 2) . '!'];
}

function get_customer_package_usage_details() {
    global $salon_id, $conn;
    $cp_id = intval($_POST['cp_id'] ?? 0);
    $cp = select_row("SELECT cp.*, p.package_name FROM hr_customer_packages cp JOIN hr_packages_new p ON p.pkg_id = cp.pkg_id WHERE cp.cp_id='$cp_id' AND cp.salon_id='$salon_id'");
    if (!$cp) return ['error' => 1, 'msg' => 'Package not found.'];

    $items = select_array("SELECT pi.service_id, pi.service_name, pi.quantity,
        COALESCE(SUM(u.qty_used), 0) AS used
        FROM hr_package_items pi
        LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id='$cp_id'
        WHERE pi.pkg_id='{$cp['pkg_id']}' GROUP BY pi.item_id");

    $result_items = [];
    foreach ($items as $item) {
        $total = (int)$item['quantity'];
        $used = (int)$item['used'];
        $result_items[] = [
            'service_id'   => (int)$item['service_id'],
            'service_name' => $item['service_name'],
            'total_qty'    => $total,
            'used_qty'     => $used,
            'remaining_qty'=> max(0, $total - $used),
        ];
    }

    return [
        'error'        => 0,
        'cp_id'        => $cp_id,
        'package_name' => $cp['package_name'],
        'cust_id'      => (int)$cp['cust_id'],
        'items'        => $result_items,
    ];
}

function update_customer_package_sessions() {
    global $salon_id, $user_id, $conn;
    $cp_id = intval($_POST['cp_id'] ?? 0);
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));

    $cp = select_row("SELECT * FROM hr_customer_packages WHERE cp_id='$cp_id' AND salon_id='$salon_id'");
    if (!$cp) return ['error' => 1, 'msg' => 'Customer package not found.'];

    $qty_taken_map = isset($_POST['qty_taken']) && is_array($_POST['qty_taken']) ? $_POST['qty_taken'] : [];
    $pkg_items = select_array("SELECT * FROM hr_package_items WHERE pkg_id='{$cp['pkg_id']}'");

    foreach ($pkg_items as $pi) {
        $sid = (int)$pi['service_id'];
        $total_pkg_qty = (int)$pi['quantity'];
        $target_taken = isset($qty_taken_map[$sid]) ? max(0, min($total_pkg_qty, intval($qty_taken_map[$sid]))) : 0;

        // Calculate software billed usage (invoice_id > 0)
        $software_billed = (int)select_row("SELECT COALESCE(SUM(qty_used), 0) AS sb FROM hr_customer_package_usage WHERE cp_id='$cp_id' AND service_id='$sid' AND invoice_id IS NOT NULL AND invoice_id > 0")['sb'];

        // Remove old manual register entries (invoice_id IS NULL or invoice_id = 0)
        update_query("DELETE FROM hr_customer_package_usage WHERE cp_id='$cp_id' AND service_id='$sid' AND (invoice_id IS NULL OR invoice_id = 0)");

        // Calculate manual usage needed
        $manual_needed = max(0, $target_taken - $software_billed);

        if ($manual_needed > 0) {
            insert_query("INSERT INTO hr_customer_package_usage SET
                cp_id='$cp_id', pkg_id='{$cp['pkg_id']}', cust_id='{$cp['cust_id']}',
                service_id='$sid', qty_used='$manual_needed', invoice_id=0, used_by='$user_id', used_at=NOW()");
        }
    }

    // Check if package is now fully used or active
    $all_items = select_array("SELECT pi.quantity, COALESCE(SUM(u.qty_used), 0) AS used
        FROM hr_package_items pi
        LEFT JOIN hr_customer_package_usage u ON u.service_id=pi.service_id AND u.cp_id='$cp_id'
        WHERE pi.pkg_id='{$cp['pkg_id']}' GROUP BY pi.item_id");

    $fully_used = true;
    foreach ($all_items as $ai) {
        if ((int)$ai['used'] < (int)$ai['quantity']) {
            $fully_used = false;
            break;
        }
    }

    $new_status = $fully_used ? 'fully_used' : 'active';
    update_query("UPDATE hr_customer_packages SET status='$new_status' WHERE cp_id='$cp_id'");

    return ['error' => 0, 'msg' => 'Package session counts updated successfully!'];
}

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

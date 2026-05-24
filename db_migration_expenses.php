<?php
include 'config.php';

echo "Starting Database Migration...\n";

// 1. Modify hr_expenses
$alter_expenses_sql = "
ALTER TABLE `hr_expenses`
ADD COLUMN IF NOT EXISTS `expense_type` ENUM('fixed', 'variable') NULL,
ADD COLUMN IF NOT EXISTS `gst_amount` DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS `attachment` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `recurring_type` VARCHAR(50) DEFAULT 'none',
ADD COLUMN IF NOT EXISTS `approved_by` INT(11) NULL,
ADD COLUMN IF NOT EXISTS `approval_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
ADD COLUMN IF NOT EXISTS `created_by` INT(11) NULL,
ADD COLUMN IF NOT EXISTS `tags` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `reference_number` VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS `vendor_id` INT(11) NULL;
";

if (mysqli_query($conn, $alter_expenses_sql)) {
    echo "Successfully updated hr_expenses table.\n";
} else {
    echo "Error updating hr_expenses: " . mysqli_error($conn) . "\n";
}

// 2. Create hr_vendors
$create_vendors_sql = "
CREATE TABLE IF NOT EXISTS `hr_vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salon_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `gst_no` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_vendors_sql)) {
    echo "Successfully created hr_vendors table.\n";
} else {
    echo "Error creating hr_vendors: " . mysqli_error($conn) . "\n";
}

// 3. Create hr_expense_attachments
$create_attachments_sql = "
CREATE TABLE IF NOT EXISTS `hr_expense_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exp_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_attachments_sql)) {
    echo "Successfully created hr_expense_attachments table.\n";
} else {
    echo "Error creating hr_expense_attachments: " . mysqli_error($conn) . "\n";
}

// 4. Create hr_expense_budgets
$create_budgets_sql = "
CREATE TABLE IF NOT EXISTS `hr_expense_budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salon_id` int(11) NOT NULL,
  `exp_catId` int(11) NOT NULL,
  `budget_month` int(2) NOT NULL,
  `budget_year` int(4) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_budgets_sql)) {
    echo "Successfully created hr_expense_budgets table.\n";
} else {
    echo "Error creating hr_expense_budgets: " . mysqli_error($conn) . "\n";
}

// 5. Create hr_recurring_expenses
$create_recurring_sql = "
CREATE TABLE IF NOT EXISTS `hr_recurring_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exp_id` int(11) NOT NULL,
  `next_run_date` date NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_recurring_sql)) {
    echo "Successfully created hr_recurring_expenses table.\n";
} else {
    echo "Error creating hr_recurring_expenses: " . mysqli_error($conn) . "\n";
}

// 6. Create hr_expense_approvals
$create_approvals_sql = "
CREATE TABLE IF NOT EXISTS `hr_expense_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exp_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_approvals_sql)) {
    echo "Successfully created hr_expense_approvals table.\n";
} else {
    echo "Error creating hr_expense_approvals: " . mysqli_error($conn) . "\n";
}

// 7. Create hr_audit_logs
$create_audit_sql = "
CREATE TABLE IF NOT EXISTS `hr_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_audit_sql)) {
    echo "Successfully created hr_audit_logs table.\n";
} else {
    echo "Error creating hr_audit_logs: " . mysqli_error($conn) . "\n";
}


// Migrate existing vendors to hr_vendors table
$vendor_query = mysqli_query($conn, "SELECT DISTINCT salon_id, exp_vendor FROM hr_expenses WHERE exp_vendor IS NOT NULL AND exp_vendor != ''");
if ($vendor_query) {
    while ($row = mysqli_fetch_assoc($vendor_query)) {
        $salon_id = $row['salon_id'];
        $vendor_name = mysqli_real_escape_string($conn, $row['exp_vendor']);
        
        // Check if vendor already exists
        $check = mysqli_query($conn, "SELECT id FROM hr_vendors WHERE salon_id='$salon_id' AND name='$vendor_name'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO hr_vendors (salon_id, name) VALUES ('$salon_id', '$vendor_name')");
            $vendor_id = mysqli_insert_id($conn);
        } else {
            $existing = mysqli_fetch_assoc($check);
            $vendor_id = $existing['id'];
        }

        // Update existing hr_expenses to link vendor_id
        mysqli_query($conn, "UPDATE hr_expenses SET vendor_id='$vendor_id' WHERE salon_id='$salon_id' AND exp_vendor='" . mysqli_real_escape_string($conn, $row['exp_vendor']) . "'");
    }
    echo "Vendor migration completed.\n";
} else {
    echo "Error migrating vendors: " . mysqli_error($conn) . "\n";
}

echo "Migration finished.\n";
?>

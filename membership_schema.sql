-- ============================================================
-- Salon OS – Membership & Package System Schema
-- Run once against the active database
-- ============================================================

-- 1. Membership Plans (catalog)
CREATE TABLE IF NOT EXISTS `hr_membership_plans` (
  `plan_id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `salon_id`         INT UNSIGNED NOT NULL DEFAULT 0,
  `plan_name`        VARCHAR(120) NOT NULL,
  `plan_price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount customer pays',
  `wallet_credit`    DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Wallet value credited on activation',
  `validity_days`    INT NOT NULL DEFAULT 90 COMMENT 'Validity in days',
  `description`      TEXT,
  `allow_discount`   TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=no discount stacking with wallet',
  `gst_applicable`   TINYINT(1) NOT NULL DEFAULT 0,
  `gst_percent`      DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `status`           TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active 0=Inactive',
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`plan_id`),
  KEY `idx_salon` (`salon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Customer Membership (purchase record)
CREATE TABLE IF NOT EXISTS `hr_customer_membership` (
  `cm_id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `salon_id`         INT UNSIGNED NOT NULL DEFAULT 0,
  `cust_id`          INT UNSIGNED NOT NULL,
  `plan_id`          INT UNSIGNED NOT NULL,
  `plan_name`        VARCHAR(120) NOT NULL COMMENT 'Snapshot at time of purchase',
  `total_price`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `wallet_credit`    DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Wallet value to be credited',
  `wallet_credited`  TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 once wallet has been topped up',
  `gst_amount`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`           ENUM('pending','active','expired','refunded','paused') NOT NULL DEFAULT 'pending',
  `start_date`       DATE DEFAULT NULL,
  `expiry_date`      DATE DEFAULT NULL,
  `pause_date`       DATE DEFAULT NULL COMMENT 'Date when membership was paused',
  `invoice_id`       INT UNSIGNED DEFAULT NULL COMMENT 'Linked POS invoice (if sold via POS)',
  `sold_by`          INT UNSIGNED DEFAULT NULL COMMENT 'User who sold',
  `notes`            TEXT,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cm_id`),
  KEY `idx_salon_cust` (`salon_id`, `cust_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Membership Payment History (partial payments)
CREATE TABLE IF NOT EXISTS `hr_membership_payments` (
  `mp_id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cm_id`       INT UNSIGNED NOT NULL,
  `salon_id`    INT UNSIGNED NOT NULL DEFAULT 0,
  `cust_id`     INT UNSIGNED NOT NULL,
  `amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_mode` VARCHAR(30) NOT NULL DEFAULT 'cash',
  `paid_by`     INT UNSIGNED DEFAULT NULL COMMENT 'user_id who recorded payment',
  `notes`       VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mp_id`),
  KEY `idx_cm` (`cm_id`),
  KEY `idx_salon` (`salon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Service-Bundle Package Plans (catalog)
CREATE TABLE IF NOT EXISTS `hr_packages_new` (
  `pkg_id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `salon_id`       INT UNSIGNED NOT NULL DEFAULT 0,
  `package_name`   VARCHAR(120) NOT NULL,
  `validity_days`  INT NOT NULL DEFAULT 90,
  `mrp_total`      DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Auto-calculated from services',
  `selling_price`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `savings`        DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'mrp_total - selling_price',
  `allow_discount` TINYINT(1) NOT NULL DEFAULT 0,
  `gst_applicable` TINYINT(1) NOT NULL DEFAULT 0,
  `gst_percent`    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `status`         TINYINT(1) NOT NULL DEFAULT 1,
  `created_by`     INT UNSIGNED DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pkg_id`),
  KEY `idx_salon` (`salon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Package Items (services inside each bundle)
CREATE TABLE IF NOT EXISTS `hr_package_items` (
  `item_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pkg_id`       INT UNSIGNED NOT NULL,
  `service_id`   INT UNSIGNED NOT NULL,
  `service_name` VARCHAR(120) NOT NULL COMMENT 'Snapshot',
  `service_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Snapshot at creation',
  `quantity`     INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`item_id`),
  KEY `idx_pkg` (`pkg_id`),
  KEY `idx_service` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Customer Purchased Packages
CREATE TABLE IF NOT EXISTS `hr_customer_packages` (
  `cp_id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `salon_id`      INT UNSIGNED NOT NULL DEFAULT 0,
  `cust_id`       INT UNSIGNED NOT NULL,
  `pkg_id`        INT UNSIGNED NOT NULL,
  `package_name`  VARCHAR(120) NOT NULL COMMENT 'Snapshot',
  `purchase_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `gst_amount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_mode`  VARCHAR(30) NOT NULL DEFAULT 'cash',
  `purchase_date` DATE NOT NULL,
  `expiry_date`   DATE DEFAULT NULL,
  `status`        ENUM('active','expired','refunded','fully_used') NOT NULL DEFAULT 'active',
  `invoice_id`    INT UNSIGNED DEFAULT NULL,
  `sold_by`       INT UNSIGNED DEFAULT NULL,
  `notes`         TEXT,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cp_id`),
  KEY `idx_salon_cust` (`salon_id`, `cust_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Package Service Usage Tracking
CREATE TABLE IF NOT EXISTS `hr_customer_package_usage` (
  `usage_id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cp_id`       INT UNSIGNED NOT NULL,
  `pkg_id`      INT UNSIGNED NOT NULL,
  `cust_id`     INT UNSIGNED NOT NULL,
  `service_id`  INT UNSIGNED NOT NULL,
  `qty_used`    INT NOT NULL DEFAULT 1,
  `invoice_id`  INT UNSIGNED DEFAULT NULL,
  `used_by`     INT UNSIGNED DEFAULT NULL COMMENT 'user_id',
  `used_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usage_id`),
  KEY `idx_cp` (`cp_id`),
  KEY `idx_cust_service` (`cust_id`, `service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Wallet Edit Audit Log
CREATE TABLE IF NOT EXISTS `hr_wallet_audit_log` (
  `log_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cust_id`     INT UNSIGNED NOT NULL,
  `salon_id`    INT UNSIGNED NOT NULL DEFAULT 0,
  `user_id`     INT UNSIGNED NOT NULL,
  `old_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `new_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `change_type` ENUM('credit','debit','refund','adjustment') NOT NULL DEFAULT 'adjustment',
  `reason`      VARCHAR(255) DEFAULT NULL,
  `reference`   VARCHAR(100) DEFAULT NULL COMMENT 'invoice_id or cm_id for traceability',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_cust` (`cust_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Extend hr_customer with membership context (safe ALTER, ignores if column exists)
ALTER TABLE `hr_customer` 
  ADD COLUMN IF NOT EXISTS `active_membership_id` INT UNSIGNED DEFAULT NULL;

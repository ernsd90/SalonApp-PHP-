-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 27, 2026 at 06:04 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u883623029_salon`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer_invoices`
--

CREATE TABLE `customer_invoices` (
  `customer_invoices_id` bigint(20) NOT NULL,
  `cust_id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `debit` int(11) NOT NULL DEFAULT 0,
  `credit` int(11) NOT NULL DEFAULT 0,
  `narration` varchar(255) NOT NULL,
  `invoice_date` date NOT NULL,
  `grand_total` float NOT NULL DEFAULT 0,
  `discount_per` bigint(20) NOT NULL,
  `discount_value` bigint(20) NOT NULL,
  `payment_mode` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_attendance`
--

CREATE TABLE `hr_attendance` (
  `id` bigint(20) NOT NULL,
  `name` varchar(250) NOT NULL,
  `working_hr` float NOT NULL,
  `duty_in` time NOT NULL,
  `duty_out` time NOT NULL,
  `user_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_bill`
--

CREATE TABLE `hr_bill` (
  `bill_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `invoice_no` varchar(250) NOT NULL,
  `vendor` bigint(250) NOT NULL,
  `invoice_date` date NOT NULL,
  `discount` float NOT NULL,
  `gst` float NOT NULL,
  `total` int(11) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_bill_product`
--

CREATE TABLE `hr_bill_product` (
  `id` bigint(20) NOT NULL,
  `bill_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `product_type` varchar(200) NOT NULL,
  `product_name` varchar(250) NOT NULL,
  `qty` bigint(20) NOT NULL,
  `qty_out` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `mrp` float NOT NULL,
  `grand_total` float NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_customer`
--

CREATE TABLE `hr_customer` (
  `cust_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `cust_name` varchar(250) NOT NULL,
  `cust_mobile` varchar(50) NOT NULL,
  `cust_gender` varchar(100) NOT NULL,
  `cust_reffer` varchar(212) NOT NULL DEFAULT 'walkin',
  `cust_wallet` float NOT NULL DEFAULT 0,
  `cust_outstanding` float NOT NULL DEFAULT 0,
  `cust_added` datetime NOT NULL DEFAULT current_timestamp(),
  `package` varchar(250) NOT NULL,
  `package_expired` varchar(250) NOT NULL,
  `old_html` longtext NOT NULL,
  `pkg_html` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_customer_pkg`
--

CREATE TABLE `hr_customer_pkg` (
  `id` bigint(20) NOT NULL,
  `cust_id` bigint(20) NOT NULL,
  `pkg_id` bigint(20) NOT NULL,
  `pkg_started` datetime NOT NULL DEFAULT current_timestamp(),
  `pkg_expired` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_customer_wallet`
--

CREATE TABLE `hr_customer_wallet` (
  `wallet_id` bigint(20) NOT NULL,
  `cust_id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `credit` bigint(20) NOT NULL DEFAULT 0,
  `debit` bigint(20) NOT NULL DEFAULT 0,
  `balance` bigint(20) NOT NULL DEFAULT 0,
  `remark` varchar(255) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_expenses`
--

CREATE TABLE `hr_expenses` (
  `exp_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `exp_catId` bigint(20) NOT NULL,
  `exp_name` varchar(255) NOT NULL,
  `exp_vendor` varchar(255) NOT NULL,
  `exp_date` datetime NOT NULL DEFAULT current_timestamp(),
  `exp_total` float NOT NULL,
  `payment_mode` varchar(100) NOT NULL,
  `exp_note` longtext NOT NULL,
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_expenses_category`
--

CREATE TABLE `hr_expenses_category` (
  `exp_catId` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_feedback`
--

CREATE TABLE `hr_feedback` (
  `feedback_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `cust_name` varchar(255) NOT NULL,
  `cust_mob` varchar(255) NOT NULL,
  `experience` varchar(250) NOT NULL,
  `message` longtext NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_invoice`
--

CREATE TABLE `hr_invoice` (
  `invoice_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `invoice_number` bigint(20) NOT NULL,
  `cust_ref_by` varchar(250) NOT NULL,
  `invoice_type` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '0=service,1=membership,2=product',
  `delete_bill` bigint(20) NOT NULL DEFAULT 0,
  `cust_id` bigint(20) NOT NULL,
  `cust_name` varchar(255) NOT NULL,
  `cust_mob` bigint(20) NOT NULL,
  `discount` float NOT NULL,
  `discount_mode` bigint(20) NOT NULL DEFAULT 1 COMMENT '0=value,1=percentage',
  `service_total` float NOT NULL,
  `extra_fee` float NOT NULL,
  `service_total_tax` float NOT NULL,
  `round_off` float NOT NULL,
  `grand_total` float NOT NULL DEFAULT 0 COMMENT 'SERVICE+TAX-DICOUNT +-roundoff',
  `outstanding` float NOT NULL,
  `payment_mode` varchar(120) NOT NULL,
  `billing_remark` longtext NOT NULL,
  `delete_reason` longtext NOT NULL,
  `invoice_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_invoices_old`
--

CREATE TABLE `hr_invoices_old` (
  `invoices_old_id` bigint(20) NOT NULL,
  `cust_name` varchar(255) NOT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `invoice_id` bigint(11) NOT NULL,
  `service` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `staff_name` varchar(255) NOT NULL,
  `product` varchar(255) NOT NULL,
  `package` varchar(255) NOT NULL,
  `membership` varchar(255) NOT NULL,
  `service_cost` int(11) NOT NULL,
  `total_cost` int(11) NOT NULL COMMENT 'quantity * service cost*tax',
  `invoice_date` varchar(255) NOT NULL,
  `payment_type` varchar(255) NOT NULL,
  `discount_value` int(11) NOT NULL,
  `discount_per` int(11) NOT NULL,
  `tax` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_invoice_payment`
--

CREATE TABLE `hr_invoice_payment` (
  `id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `grand_total` float NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_invoice_service`
--

CREATE TABLE `hr_invoice_service` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `pkg_id` bigint(20) DEFAULT NULL,
  `service_cat` varchar(255) NOT NULL,
  `service` varchar(255) NOT NULL,
  `staff_id` varchar(20) NOT NULL,
  `staff_name` varchar(255) NOT NULL,
  `service_price` float NOT NULL,
  `service_qty` bigint(20) NOT NULL,
  `service_gst` bigint(20) NOT NULL,
  `service_total_wth_gst` float NOT NULL DEFAULT 0,
  `service_discount` float NOT NULL DEFAULT 0 COMMENT 'In perrcentage'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_invoice_staff`
--

CREATE TABLE `hr_invoice_staff` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `invoice_service` bigint(20) NOT NULL,
  `staff_id` bigint(20) NOT NULL,
  `total_amt` float NOT NULL COMMENT 'per_Staff',
  `persrvice_discount` float DEFAULT NULL,
  `staff_work_price` float DEFAULT NULL,
  `grand_total` float DEFAULT NULL,
  `invoice_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_jobcard`
--

CREATE TABLE `hr_jobcard` (
  `job_card_id` bigint(11) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `cust_id` bigint(11) DEFAULT NULL,
  `created_by` bigint(11) DEFAULT NULL,
  `jobcard_status` bigint(20) NOT NULL COMMENT '0:Started; \r\n1:Updated\r\n2:Billed',
  `invoice_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_jobcardservice`
--

CREATE TABLE `hr_jobcardservice` (
  `job_card_service_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `job_card_id` bigint(20) DEFAULT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `service_remark` text NOT NULL,
  `added_by` bigint(20) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT NULL,
  `delete_status` enum('active','deleted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_jobcardstaff`
--

CREATE TABLE `hr_jobcardstaff` (
  `job_card_staff_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `job_card_id` bigint(20) DEFAULT NULL,
  `job_card_service_id` bigint(20) NOT NULL,
  `staff_id` bigint(20) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `delete_status` enum('active','deleted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_jobcard_service_update_log`
--

CREATE TABLE `hr_jobcard_service_update_log` (
  `update_log_id` bigint(20) NOT NULL,
  `job_card_service_id` bigint(20) DEFAULT NULL,
  `old_service_id` bigint(20) DEFAULT NULL,
  `new_service_id` bigint(20) DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_packages`
--

CREATE TABLE `hr_packages` (
  `pkg_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `pakage_validity` int(99) NOT NULL COMMENT 'in Days',
  `customer_pay` int(11) NOT NULL,
  `customer_get` int(11) NOT NULL,
  `package_status` bigint(20) NOT NULL DEFAULT 0 COMMENT '1=active,0=deactive',
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_product`
--

CREATE TABLE `hr_product` (
  `product_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `brand_id` bigint(20) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_qty` bigint(20) NOT NULL DEFAULT 10,
  `product_price` float NOT NULL,
  `product_status` bigint(20) NOT NULL COMMENT '1=active',
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_product_brand`
--

CREATE TABLE `hr_product_brand` (
  `brand_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `brand_status` bigint(20) NOT NULL DEFAULT 1 COMMENT '1=active',
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_product_inventory`
--

CREATE TABLE `hr_product_inventory` (
  `id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `product_name` varchar(250) NOT NULL,
  `stock_in` bigint(20) NOT NULL,
  `stock_out` bigint(20) NOT NULL,
  `product_unit` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_product_new`
--

CREATE TABLE `hr_product_new` (
  `product_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `brand_id` bigint(20) NOT NULL,
  `product_type` bigint(20) NOT NULL COMMENT '1=store,0=retail',
  `product_name` varchar(250) NOT NULL,
  `product_unit` varchar(200) NOT NULL COMMENT 'ml,piece etc etc',
  `product_mrp` float NOT NULL,
  `product_purchase` float NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_salon`
--

CREATE TABLE `hr_salon` (
  `salon_id` bigint(20) NOT NULL,
  `salon_name` varchar(255) NOT NULL,
  `salon_address` varchar(255) NOT NULL,
  `salon_contact` bigint(20) NOT NULL,
  `gst_enable` varchar(20) NOT NULL,
  `salon_gst` varchar(200) NOT NULL,
  `include_gst` bigint(20) NOT NULL DEFAULT 0,
  `logo` varchar(250) NOT NULL,
  `firm_name` varchar(250) NOT NULL,
  `whatsapp_enable` bigint(20) NOT NULL DEFAULT 0,
  `whatsapp_api` longtext NOT NULL,
  `msg_id` varchar(100) NOT NULL,
  `google_review_link` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_salon_cashdiscount`
--

CREATE TABLE `hr_salon_cashdiscount` (
  `id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `month_discount` date NOT NULL,
  `cash_discount` bigint(20) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_services`
--

CREATE TABLE `hr_services` (
  `service_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `service_catid` bigint(20) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_price` int(11) NOT NULL DEFAULT 0,
  `service_reminder` bigint(20) NOT NULL,
  `service_status` bigint(20) NOT NULL COMMENT '1=active,0=Inactive',
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_servicesCategory`
--

CREATE TABLE `hr_servicesCategory` (
  `service_catid` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `service_catName` varchar(255) NOT NULL,
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_staff`
--

CREATE TABLE `hr_staff` (
  `staff_id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `staff_name` varchar(200) NOT NULL,
  `staff_mob` varchar(200) NOT NULL,
  `staff_salary` int(11) NOT NULL DEFAULT 0,
  `staff_status` bigint(20) NOT NULL DEFAULT 0 COMMENT '1=active,0=deactive',
  `joining_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_user`
--

CREATE TABLE `hr_user` (
  `user_id` bigint(20) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(250) NOT NULL,
  `user_mobile` varchar(250) NOT NULL,
  `user_type` bigint(20) NOT NULL COMMENT '1 superadmin, 2 Salon Admin, ',
  `salon_id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_user_owner`
--

CREATE TABLE `hr_user_owner` (
  `id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `user_name` varchar(260) NOT NULL,
  `mobile_no` varchar(250) NOT NULL,
  `is_active` bigint(20) NOT NULL DEFAULT 0,
  `ref_enable` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_user_role`
--

CREATE TABLE `hr_user_role` (
  `role_id` bigint(10) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_permission` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_vendor`
--

CREATE TABLE `hr_vendor` (
  `id` bigint(20) NOT NULL,
  `vendor_name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_vendor_payment`
--

CREATE TABLE `hr_vendor_payment` (
  `id` bigint(20) NOT NULL,
  `salon_id` bigint(20) NOT NULL,
  `vendor_id` bigint(20) NOT NULL,
  `bill_id` bigint(20) NOT NULL,
  `amt_in` float NOT NULL,
  `amt_out` float NOT NULL,
  `payment_mode` varchar(100) NOT NULL,
  `bill_deleted` bigint(20) NOT NULL,
  `vendor_remark` longtext DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer_invoices`
--
ALTER TABLE `customer_invoices`
  ADD PRIMARY KEY (`customer_invoices_id`);

--
-- Indexes for table `hr_attendance`
--
ALTER TABLE `hr_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_bill`
--
ALTER TABLE `hr_bill`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `vendor` (`vendor`);

--
-- Indexes for table `hr_bill_product`
--
ALTER TABLE `hr_bill_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `hr_customer`
--
ALTER TABLE `hr_customer`
  ADD PRIMARY KEY (`cust_id`),
  ADD KEY `salon_customer` (`salon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hr_customer_pkg`
--
ALTER TABLE `hr_customer_pkg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_customer_wallet`
--
ALTER TABLE `hr_customer_wallet`
  ADD PRIMARY KEY (`wallet_id`);

--
-- Indexes for table `hr_expenses`
--
ALTER TABLE `hr_expenses`
  ADD PRIMARY KEY (`exp_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `exp_catId` (`exp_catId`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `hr_expenses_category`
--
ALTER TABLE `hr_expenses_category`
  ADD PRIMARY KEY (`exp_catId`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hr_feedback`
--
ALTER TABLE `hr_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `hr_invoice`
--
ALTER TABLE `hr_invoice`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `salon_id_2` (`salon_id`,`invoice_number`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hr_invoices_old`
--
ALTER TABLE `hr_invoices_old`
  ADD PRIMARY KEY (`invoices_old_id`);

--
-- Indexes for table `hr_invoice_payment`
--
ALTER TABLE `hr_invoice_payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `hr_invoice_service`
--
ALTER TABLE `hr_invoice_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `service_cat` (`service_cat`);

--
-- Indexes for table `hr_invoice_staff`
--
ALTER TABLE `hr_invoice_staff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `invoice_service` (`invoice_service`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `hr_jobcard`
--
ALTER TABLE `hr_jobcard`
  ADD PRIMARY KEY (`job_card_id`),
  ADD KEY `Customer_jobcard` (`cust_id`),
  ADD KEY `User_Jobcard` (`created_by`);

--
-- Indexes for table `hr_jobcardservice`
--
ALTER TABLE `hr_jobcardservice`
  ADD PRIMARY KEY (`job_card_service_id`),
  ADD KEY `JobCard_Services` (`service_id`),
  ADD KEY `hr_jobcardservice_ibfk_1` (`job_card_id`),
  ADD KEY `hr_jobcardservice_ibfk_2` (`added_by`);

--
-- Indexes for table `hr_jobcardstaff`
--
ALTER TABLE `hr_jobcardstaff`
  ADD PRIMARY KEY (`job_card_staff_id`),
  ADD KEY `job_card_service_id` (`job_card_service_id`),
  ADD KEY `hr_jobcardstaff_ibfk_1` (`job_card_id`),
  ADD KEY `hr_jobcardstaff_ibfk_2` (`staff_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `hr_jobcard_service_update_log`
--
ALTER TABLE `hr_jobcard_service_update_log`
  ADD PRIMARY KEY (`update_log_id`),
  ADD KEY `idx_job_card_service_id` (`job_card_service_id`),
  ADD KEY `idx_old_service_id` (`old_service_id`),
  ADD KEY `idx_new_service_id` (`new_service_id`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Indexes for table `hr_packages`
--
ALTER TABLE `hr_packages`
  ADD PRIMARY KEY (`pkg_id`);

--
-- Indexes for table `hr_product`
--
ALTER TABLE `hr_product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hr_product_brand`
--
ALTER TABLE `hr_product_brand`
  ADD PRIMARY KEY (`brand_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hr_product_inventory`
--
ALTER TABLE `hr_product_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_product_new`
--
ALTER TABLE `hr_product_new`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `hr_salon`
--
ALTER TABLE `hr_salon`
  ADD PRIMARY KEY (`salon_id`);

--
-- Indexes for table `hr_salon_cashdiscount`
--
ALTER TABLE `hr_salon_cashdiscount`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `hr_services`
--
ALTER TABLE `hr_services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_catid` (`service_catid`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `hr_servicesCategory`
--
ALTER TABLE `hr_servicesCategory`
  ADD PRIMARY KEY (`service_catid`),
  ADD KEY `salon_servicecat` (`salon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hr_staff`
--
ALTER TABLE `hr_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `hr_user`
--
ALTER TABLE `hr_user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `hr_user_owner`
--
ALTER TABLE `hr_user_owner`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `hr_user_role`
--
ALTER TABLE `hr_user_role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `hr_vendor`
--
ALTER TABLE `hr_vendor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_vendor_payment`
--
ALTER TABLE `hr_vendor_payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer_invoices`
--
ALTER TABLE `customer_invoices`
  MODIFY `customer_invoices_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_attendance`
--
ALTER TABLE `hr_attendance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_bill`
--
ALTER TABLE `hr_bill`
  MODIFY `bill_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_bill_product`
--
ALTER TABLE `hr_bill_product`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_customer`
--
ALTER TABLE `hr_customer`
  MODIFY `cust_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_customer_pkg`
--
ALTER TABLE `hr_customer_pkg`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_customer_wallet`
--
ALTER TABLE `hr_customer_wallet`
  MODIFY `wallet_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_expenses`
--
ALTER TABLE `hr_expenses`
  MODIFY `exp_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_expenses_category`
--
ALTER TABLE `hr_expenses_category`
  MODIFY `exp_catId` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_feedback`
--
ALTER TABLE `hr_feedback`
  MODIFY `feedback_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_invoice`
--
ALTER TABLE `hr_invoice`
  MODIFY `invoice_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_invoices_old`
--
ALTER TABLE `hr_invoices_old`
  MODIFY `invoices_old_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_invoice_payment`
--
ALTER TABLE `hr_invoice_payment`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_invoice_service`
--
ALTER TABLE `hr_invoice_service`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_invoice_staff`
--
ALTER TABLE `hr_invoice_staff`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_jobcard`
--
ALTER TABLE `hr_jobcard`
  MODIFY `job_card_id` bigint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_jobcardservice`
--
ALTER TABLE `hr_jobcardservice`
  MODIFY `job_card_service_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_jobcardstaff`
--
ALTER TABLE `hr_jobcardstaff`
  MODIFY `job_card_staff_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_jobcard_service_update_log`
--
ALTER TABLE `hr_jobcard_service_update_log`
  MODIFY `update_log_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_packages`
--
ALTER TABLE `hr_packages`
  MODIFY `pkg_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_product`
--
ALTER TABLE `hr_product`
  MODIFY `product_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_product_brand`
--
ALTER TABLE `hr_product_brand`
  MODIFY `brand_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_product_inventory`
--
ALTER TABLE `hr_product_inventory`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_product_new`
--
ALTER TABLE `hr_product_new`
  MODIFY `product_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_salon`
--
ALTER TABLE `hr_salon`
  MODIFY `salon_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_salon_cashdiscount`
--
ALTER TABLE `hr_salon_cashdiscount`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_services`
--
ALTER TABLE `hr_services`
  MODIFY `service_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_servicesCategory`
--
ALTER TABLE `hr_servicesCategory`
  MODIFY `service_catid` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_staff`
--
ALTER TABLE `hr_staff`
  MODIFY `staff_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_user`
--
ALTER TABLE `hr_user`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_user_owner`
--
ALTER TABLE `hr_user_owner`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_user_role`
--
ALTER TABLE `hr_user_role`
  MODIFY `role_id` bigint(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_vendor`
--
ALTER TABLE `hr_vendor`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_vendor_payment`
--
ALTER TABLE `hr_vendor_payment`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hr_bill`
--
ALTER TABLE `hr_bill`
  ADD CONSTRAINT `salons` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `vendor_rel` FOREIGN KEY (`vendor`) REFERENCES `hr_vendor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_customer`
--
ALTER TABLE `hr_customer`
  ADD CONSTRAINT `salon_customer` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_customer` FOREIGN KEY (`user_id`) REFERENCES `hr_user` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_expenses`
--
ALTER TABLE `hr_expenses`
  ADD CONSTRAINT `exp_cat` FOREIGN KEY (`exp_catId`) REFERENCES `hr_expenses_category` (`exp_catId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `exp_user` FOREIGN KEY (`user_id`) REFERENCES `hr_user` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_expenses_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_expenses_category`
--
ALTER TABLE `hr_expenses_category`
  ADD CONSTRAINT `hr_expenses_category_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_feedback`
--
ALTER TABLE `hr_feedback`
  ADD CONSTRAINT `hr_feedback_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_feedback_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `hr_invoice` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_invoice`
--
ALTER TABLE `hr_invoice`
  ADD CONSTRAINT `salon_in` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_invoice` FOREIGN KEY (`user_id`) REFERENCES `hr_user` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_invoice_payment`
--
ALTER TABLE `hr_invoice_payment`
  ADD CONSTRAINT `hr_invoice_payment_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `hr_invoice` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_invoice_payment_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_invoice_service`
--
ALTER TABLE `hr_invoice_service`
  ADD CONSTRAINT `invoice` FOREIGN KEY (`invoice_id`) REFERENCES `hr_invoice` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_invoice_staff`
--
ALTER TABLE `hr_invoice_staff`
  ADD CONSTRAINT `hr_invoice_staff_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `hr_invoice` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_invoice_staff_ibfk_2` FOREIGN KEY (`invoice_service`) REFERENCES `hr_invoice_service` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_invoice_staff_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `hr_staff` (`staff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_jobcard`
--
ALTER TABLE `hr_jobcard`
  ADD CONSTRAINT `Customer_jobcard` FOREIGN KEY (`cust_id`) REFERENCES `hr_customer` (`cust_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `User_Jobcard` FOREIGN KEY (`created_by`) REFERENCES `hr_user` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_jobcardservice`
--
ALTER TABLE `hr_jobcardservice`
  ADD CONSTRAINT `JobCard_Services` FOREIGN KEY (`service_id`) REFERENCES `hr_services` (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_jobcardservice_ibfk_1` FOREIGN KEY (`job_card_id`) REFERENCES `hr_jobcard` (`job_card_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_jobcardservice_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `hr_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_jobcardstaff`
--
ALTER TABLE `hr_jobcardstaff`
  ADD CONSTRAINT `hr_jobcardstaff_ibfk_1` FOREIGN KEY (`job_card_id`) REFERENCES `hr_jobcard` (`job_card_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_jobcardstaff_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `hr_staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_jobcardstaff_ibfk_3` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_jobcardstaff_ibfk_4` FOREIGN KEY (`job_card_service_id`) REFERENCES `hr_jobcardservice` (`job_card_service_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_product`
--
ALTER TABLE `hr_product`
  ADD CONSTRAINT `hr_product_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_product_brand`
--
ALTER TABLE `hr_product_brand`
  ADD CONSTRAINT `hr_product_brand_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_product_new`
--
ALTER TABLE `hr_product_new`
  ADD CONSTRAINT `hr_product_new_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `hr_product_brand` (`brand_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_product_new_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_salon_cashdiscount`
--
ALTER TABLE `hr_salon_cashdiscount`
  ADD CONSTRAINT `hr_salon_cashdiscount_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_services`
--
ALTER TABLE `hr_services`
  ADD CONSTRAINT `salonssss` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `service_cat` FOREIGN KEY (`service_catid`) REFERENCES `hr_servicesCategory` (`service_catid`) ON UPDATE CASCADE,
  ADD CONSTRAINT `service_user` FOREIGN KEY (`user_id`) REFERENCES `hr_user` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_servicesCategory`
--
ALTER TABLE `hr_servicesCategory`
  ADD CONSTRAINT `salon_servicecat` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_service_cat` FOREIGN KEY (`user_id`) REFERENCES `hr_user` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_staff`
--
ALTER TABLE `hr_staff`
  ADD CONSTRAINT `salonid` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_user`
--
ALTER TABLE `hr_user`
  ADD CONSTRAINT `Role` FOREIGN KEY (`role_id`) REFERENCES `hr_user_role` (`role_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `salon` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON UPDATE CASCADE;

--
-- Constraints for table `hr_user_owner`
--
ALTER TABLE `hr_user_owner`
  ADD CONSTRAINT `hr_user_owner_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_vendor_payment`
--
ALTER TABLE `hr_vendor_payment`
  ADD CONSTRAINT `hr_vendor_payment_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `hr_salon` (`salon_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hr_vendor_payment_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `hr_vendor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

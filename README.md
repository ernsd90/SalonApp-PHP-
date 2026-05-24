# 💇‍♂️ SalonApp - Premium Salon Management & Analytics Platform

[![PHP Version](https://img.shields.io/badge/php-%5E7.4%20%7C%20%5E8.0-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/mysql-%5E5.7%20%7C%20%5E8.0-orange.svg)](https://www.mysql.com/)
[![JS](https://img.shields.io/badge/javascript-ES6%2B-yellow.svg)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Bootstrap](https://img.shields.io/badge/bootstrap-4.x%20%7C%205.x-purple.svg)](https://getbootstrap.com/)

**SalonApp** is a robust, production-ready enterprise-grade Salon Management and Business Intelligence application. Designed to streamline day-to-day salon operations, manage customer relationships, automate financial accounting, track inventory, and empower salon owners with AI-like financial insights.

---

## 🚀 Key Modules & Features

### 1. 💳 Billing, Invoicing & POS
* **Quick Checkout:** Fully interactive Point of Sale (POS) interface for quick checkout.
* **Flexible Payments:** Comprehensive payment methods supporting **Cash**, **Card/UPI**, **Packages**, and **Split (Part) Payments**.
* **Flexible Billing:** Bill for individual **Services**, custom **Packages**, or retail **Products**.
* **Taxation Engine:** Auto-calculates configurable taxes (e.g., standard 5% tax) on invoices.
* **Invoice Archive:** View, search, print, and download premium PDF invoices.

### 2. 👥 CRM & Customer Wallet System
* **Customer Registry:** Detailed profile management, registration dates, and custom details.
* **Integrated Wallet:** Tracks customer credit, debit, and real-time wallet balances with comprehensive audit trails.
* **Loyalty Program:** Automated loyalty points calculation based on billing value, allowing easy point accrual and redemption.
* **Membership Plans:** Configure, sell, and manage membership subscriptions to build recurring revenue.

### 3. 📈 Financial & Expense Management (Smart Insights)
* **Expense Tracker:** Log daily business expenses categorized by department/utility.
* **Budget Management:** Set monthly expense budgets and trigger alerts when spending nears thresholds.
* **Approval Workflows:** Multi-level approval flow (`pending`, `approved`, `rejected`) for store expenses.
* **Smart Insights Engine (`ExpenseInsightsEngine`):**
  * **MoM Comparisons:** Tracks expense trend variations between current and previous months.
  * **Spike Detection:** Auto-detects and warns owners about abnormal spending spikes in specific categories.
  * **Cash Ratio Analysis:** Flags high-cash dependency (e.g., >50% cash transactions) and suggests digital alternatives.
  * **Pending Notifications:** Warns admins of pending expense requests requiring attention.

### 4. 🧑‍🤝‍🧑 Staff & Performance Analytics
* **Performance Analytics:** Comprehensive staff reporting showcasing sales contributions, services performed, and total revenue generated.
* **Attendance System:** Log and track staff daily check-ins and hours worked.
* **Role-Based Access Control (RBAC):** Define specific roles (Owner, Manager, Frontdesk, Stylist) and customize individual view/edit/delete permissions.

### 5. 📦 Inventory, Vendor & Purchasing
* **Brand & Product Management:** Catalog products by brand, category, SKU, and unit prices.
* **Vendor Ledgers:** Keep complete accounts for external suppliers, logs purchase bills, payments made, and current outstanding balances.
* **Purchase Bills:** Log product inventory restocks and link them to outstanding vendor balances.

### 6. 📱 Automated Integrations
* **WhatsApp Reports:** Automated report generation with built-in integrations to share invoices, appointment details, and membership updates directly to customers' WhatsApp numbers.

---

## 🛠 Tech Stack

* **Backend:** PHP (PHP 7.4 - 8.1 compatibility)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, JavaScript (ES6+), jQuery, Bootstrap, DataTables (with Server-Side AJAX Processing), Phosphor Icons / FontAwesome.
* **APIs:** Automated WhatsApp Messaging API.

---

## 📂 Project Structure Overview

```bash
salonapp/
├── config.php                   # Database & global software configuration
├── function.php                 # Core helper functions & database utility layers
├── billing.php                  # Checkout and POS interface
├── crm_reports.php              # Customer Relationship reports & analytics
├── loyalty.php                  # Loyalty points engine & configurations
├── invoices.php                 # Search, view, and print invoices
├── expenses_dashboard.php       # Expense visualization & quick analytics
├── staff_analytics.php          # Detailed metrics and stylist sales reports
├── ajax/                        # Dynamic AJAX endpoint handlers (server-side tables)
├── includes/                    # Business Logic Engines (e.g., ExpenseInsightsEngine.php)
├── model/                       # Reusable modal overlays & dynamic views
├── backupoldApp/                # Historical database dumps (.sql) and asset backups
└── assets/                      # Stylesheets, icons, web fonts, and custom scripts
```

---

## 💻 Installation & Setup

Follow these steps to run **SalonApp** locally on your system using **XAMPP / MAMP / WAMP**:

### 1. Prerequisites
Ensure you have the following installed:
* **PHP** >= 7.4
* **MySQL / MariaDB**
* A local server environment like **XAMPP**

### 2. Setup Codebase
1. Clone or download this repository into your local server's document root (e.g., `/Applications/XAMPP/xamppfiles/htdocs/` or `C:/xampp/htdocs/`):
   ```bash
   git clone https://github.com/ernsd90/SalonApp-PHP-.git salonapp
   ```

### 3. Database Migration
1. Start your local Apache and MySQL services.
2. Go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin) and create a new database named `salonapp`.
3. Import the database schema from the backup file:
   * Location: `backupoldApp/u883623029_salon-4.sql` (or `membership_schema.sql` for membership structures).
   ```bash
   mysql -u root -p salonapp < backupoldApp/u883623029_salon-4.sql
   ```

### 4. Configuration
1. Open the `config.php` file in a text editor.
2. Update the `mysqli_connect` parameters to match your database environment:
   ```php
   $conn = mysqli_connect("localhost", "YOUR_MYSQL_USER", "YOUR_MYSQL_PASSWORD", "salonapp");
   ```
3. Update the `DOMAIN_SOFTWARE` constant if your local directory differs:
   ```php
   define("DOMAIN_SOFTWARE", "http://localhost/salonapp/");
   ```

### 5. Access the Platform
* Open your browser and navigate to: [http://localhost/salonapp/](http://localhost/salonapp/)
* Login using your configured administrator credentials.

---

## 📈 Optimization & Scaling
* **DataTables Server-Side Processing:** High-performance listing pages utilize `ajax/` endpoints connected with `DataTables` to process thousands of database records dynamically in milliseconds.
* **Database Indexes:** Optimization indexes are pre-configured on foreign keys (`cust_id`, `salon_id`, `invoice_id`) to ensure fast queries on large report sheets. See `add_indexes.php` for index automation.

---

## 📄 License
This project is proprietary and confidential. Unauthorized copying, distribution, or modifications are strictly prohibited.

<?php
include "config.php";
include "function.php";

$staff_id = intval($_GET['staff_id'] ?? 0);
$mob      = trim($_GET['mob'] ?? '');

if ($staff_id < 1 || empty($mob)) {
    die("Invalid access.");
}

// Verify staff
$staff = select_row("SELECT s.*, sl.salon_name, sl.logo, sl.staff_global_target 
    FROM hr_staff s 
    JOIN hr_salon sl ON sl.salon_id = s.salon_id 
    WHERE s.staff_id='$staff_id' AND s.staff_mob='".mysqli_real_escape_string($conn, $mob)."'");

if (!$staff) {
    die("<div style='font-family:sans-serif;padding:60px;text-align:center;'><h2>Report not found</h2><p>The link may be invalid or expired.</p></div>");
}

$staff_name     = htmlspecialchars($staff['staff_name']);
$staff_role     = htmlspecialchars($staff['staff_role'] ?? '');
$department     = htmlspecialchars($staff['department'] ?? '');
$seniority      = htmlspecialchars($staff['seniority'] ?? '');
$salon_name     = htmlspecialchars($staff['salon_name']);
$global_target  = floatval($staff['staff_global_target']);
$avatar_letter  = strtoupper(substr($staff_name, 0, 1));
$joining        = (!empty($staff['joining_date']) && $staff['joining_date'] != '0000-00-00') ? date('d M Y', strtotime($staff['joining_date'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $staff_name ?> — Performance Report | <?= $salon_name ?></title>
<meta name="description" content="Individual staff performance report for <?= $staff_name ?> at <?= $salon_name ?>. View revenue, clients, services and daily billing.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
  :root {
    --primary: #6366f1;
    --primary-soft: #ede9fe;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --grad: linear-gradient(135deg, #6366f1 0%, #a855f7 60%, #ec4899 100%);
    --grad-success: linear-gradient(135deg, #10b981, #34d399);
    --grad-warning: linear-gradient(135deg, #f59e0b, #fbbf24);
    --grad-info: linear-gradient(135deg, #3b82f6, #60a5fa);
    --bg: #f0f2f8;
    --card: #fff;
    --border: #e2e8f0;
    --text: #0f172a;
    --muted: #64748b;
    --radius: 16px;
    --shadow: 0 4px 6px -1px rgba(0,0,0,.06), 0 2px 4px -1px rgba(0,0,0,.04);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.04);
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

  /* ─── Header ─── */
  .page-header {
    background: var(--grad);
    padding: 0 0 40px;
    position: relative;
    overflow: hidden;
  }
  .page-header::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23fff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  }
  .header-top {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 32px; position: relative;
  }
  .salon-badge {
    color: rgba(255,255,255,0.9);
    font-size: 14px; font-weight: 600;
    display: flex; align-items: center; gap: 8px;
  }
  .report-badge {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: white; font-size: 12px; font-weight: 600;
    padding: 4px 12px; border-radius: 20px;
    backdrop-filter: blur(8px);
  }
  .staff-hero {
    padding: 0 32px; position: relative;
    display: flex; align-items: flex-end; gap: 24px;
  }
  .staff-avatar {
    width: 88px; height: 88px; border-radius: 50%;
    background: rgba(255,255,255,0.2);
    border: 3px solid rgba(255,255,255,0.5);
    display: flex; align-items: center; justify-content: center;
    font-size: 36px; font-weight: 800; color: white;
    flex-shrink: 0;
    backdrop-filter: blur(8px);
  }
  .staff-info h1 {
    font-size: 28px; font-weight: 800; color: white; margin-bottom: 6px;
  }
  .staff-tags { display: flex; flex-wrap: wrap; gap: 8px; }
  .staff-tag {
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.25);
    color: rgba(255,255,255,0.9);
    padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;
  }

  /* ─── Main Container ─── */
  .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

  /* ─── Filter Pill Bar ─── */
  .filter-bar {
    background: white; border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    padding: 16px 20px;
    margin: -20px auto 28px;
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    position: relative; z-index: 10;
  }
  .filter-label {
    font-size: 12px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-right: 4px;
  }
  .filter-btn {
    border: 1.5px solid var(--border); background: white;
    color: var(--muted); padding: 6px 14px; border-radius: 20px;
    font-size: 13px; font-weight: 500; cursor: pointer;
    transition: all 0.2s;
  }
  .filter-btn:hover { border-color: var(--primary); color: var(--primary); }
  .filter-btn.active {
    background: var(--primary); border-color: var(--primary);
    color: white; font-weight: 600;
  }
  .custom-month-wrap { display: none; align-items: center; gap: 8px; }
  .custom-month-wrap.show { display: flex; }
  .custom-month-wrap input {
    border: 1.5px solid var(--border); border-radius: 8px;
    padding: 6px 10px; font-size: 13px; font-family: 'Inter', sans-serif;
    outline: none;
  }
  .custom-month-wrap input:focus { border-color: var(--primary); }
  .apply-btn {
    background: var(--primary); color: white; border: none;
    padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer;
  }

  /* ─── KPI Grid ─── */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px; margin-bottom: 24px;
  }
  .kpi-card {
    background: var(--card); border-radius: var(--radius);
    padding: 22px; box-shadow: var(--shadow);
    position: relative; overflow: hidden;
    border: 1px solid var(--border);
  }
  .kpi-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: var(--grad);
  }
  .kpi-card.success::before { background: var(--grad-success); }
  .kpi-card.warning::before { background: var(--grad-warning); }
  .kpi-card.info::before { background: var(--grad-info); }
  .kpi-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; margin-bottom: 12px;
  }
  .kpi-card.default .kpi-icon { background: #ede9fe; color: #7c3aed; }
  .kpi-card.success .kpi-icon { background: #d1fae5; color: #059669; }
  .kpi-card.warning .kpi-icon { background: #fef3c7; color: #d97706; }
  .kpi-card.info    .kpi-icon { background: #dbeafe; color: #2563eb; }
  .kpi-label { font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
  .kpi-value { font-size: 26px; font-weight: 800; color: var(--text); }
  .kpi-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }

  /* ─── Section Card ─── */
  .section-card {
    background: var(--card); border-radius: var(--radius);
    box-shadow: var(--shadow); border: 1px solid var(--border);
    margin-bottom: 24px; overflow: hidden;
  }
  .section-header {
    padding: 18px 24px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
  }
  .section-header h2 {
    font-size: 15px; font-weight: 700; color: var(--text);
  }
  .section-header .section-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
  }
  .section-body { padding: 24px; }

  /* ─── Target Progress ─── */
  .target-row {
    display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
  }
  @media (max-width: 640px) { .target-row { grid-template-columns: 1fr; } }
  .progress-wrap { margin-top: 12px; }
  .progress-bar-bg {
    height: 12px; background: #f1f5f9; border-radius: 6px; overflow: hidden;
  }
  .progress-bar-fill {
    height: 100%; border-radius: 6px;
    background: var(--grad);
    transition: width 1s ease;
  }
  .progress-bar-fill.exceeded { background: var(--grad-success); }
  .progress-labels {
    display: flex; justify-content: space-between; margin-top: 6px;
    font-size: 12px; color: var(--muted);
  }
  .target-pct {
    font-size: 42px; font-weight: 900;
    background: var(--grad); -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .compare-box {
    background: #f8fafc; border-radius: 12px; padding: 18px;
    border: 1px solid var(--border);
  }
  .compare-box h3 { font-size: 13px; font-weight: 700; color: var(--muted); text-transform: uppercase; margin-bottom: 12px; }
  .compare-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 10px;
  }
  .compare-row:last-child { margin-bottom: 0; }
  .compare-label { font-size: 13px; color: var(--muted); }
  .compare-val { font-size: 15px; font-weight: 700; color: var(--text); }
  .badge-up   { background: #d1fae5; color: #059669; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 700; }
  .badge-down { background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 700; }
  .badge-neutral { background: #f1f5f9; color: var(--muted); padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 700; }

  /* ─── Tables ─── */
  .data-table { width: 100%; border-collapse: collapse; }
  .data-table th {
    background: #f8fafc; color: var(--muted);
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    padding: 10px 14px; text-align: left; border-bottom: 1px solid var(--border);
  }
  .data-table td {
    padding: 12px 14px; font-size: 14px; border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
  }
  .data-table tbody tr:hover td { background: #fafbff; }
  .data-table tbody tr:last-child td { border-bottom: none; }
  .total-row td { background: #0f172a !important; color: white !important; font-weight: 700 !important; font-size: 13px !important; }
  .amount { font-weight: 600; color: #059669; }
  .count-badge {
    background: #f1f5f9; color: var(--text);
    padding: 2px 8px; border-radius: 8px; font-size: 12px; font-weight: 700;
  }
  .count-badge.redemp { background: #ede9fe; color: #7c3aed; }
  .count-badge.other  { background: #dbeafe; color: #2563eb; }

  /* Service breakdown rows */
  .svc-header td {
    background: #f8fafc; cursor: pointer; padding: 14px 14px 10px;
  }
  .svc-header:hover td { background: #f1f5f9; }
  .svc-row td { padding-left: 28px; }
  .svc-total td { background: #ecfdf5; font-weight: 800; }

  /* ─── Repeat Analysis ─── */
  .repeat-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px; margin-bottom: 20px;
  }
  .repeat-tile {
    text-align: center; padding: 20px 16px;
    background: #f8fafc; border-radius: 12px; border: 1px solid var(--border);
  }
  .repeat-tile .val { font-size: 32px; font-weight: 800; color: var(--primary); }
  .repeat-tile .lbl { font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; margin-top: 4px; }

  /* ─── Daily billing ─── */
  .date-group-header {
    background: linear-gradient(90deg, #6366f1, #a855f7);
    color: white; font-size: 12px; font-weight: 700;
    padding: 8px 14px; text-transform: uppercase; letter-spacing: 0.5px;
  }
  .bill-row:hover td { background: #f8fafc; }

  /* ─── Footer ─── */
  .page-footer {
    text-align: center; padding: 30px 20px; color: var(--muted);
    font-size: 12px; border-top: 1px solid var(--border); margin-top: 8px;
  }

  /* ─── Loading spinner ─── */
  .loading-row td { text-align: center; color: var(--muted); padding: 30px; }
  .spin { animation: spin 1s linear infinite; display: inline-block; }
  @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

  /* ─── No target message ─── */
  .no-target-msg {
    background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px;
    padding: 12px 16px; color: #92400e; font-size: 13px; margin-top: 12px;
    display: none;
  }

  @media (max-width: 768px) {
    .staff-hero { flex-direction: column; align-items: flex-start; }
    .staff-info h1 { font-size: 22px; }
    .header-top { padding: 16px 20px; }
    .staff-hero { padding: 0 20px; }
    .section-body { padding: 16px; }
    .kpi-value { font-size: 22px; }
  }
</style>
</head>
<body>

<!-- ═══════════════ PAGE HEADER ═══════════════ -->
<div class="page-header">
  <div class="header-top">
    <div class="salon-badge">
      <i class="ph-bold ph-scissors"></i>
      <?= $salon_name ?>
    </div>
    <span class="report-badge"><i class="ph ph-chart-bar"></i> Staff Report</span>
  </div>
  <div class="staff-hero">
    <div class="staff-avatar"><?= $avatar_letter ?></div>
    <div class="staff-info">
      <h1><?= $staff_name ?></h1>
      <div class="staff-tags">
        <?php if ($staff_role): ?><span class="staff-tag"><i class="ph ph-identification-badge"></i> <?= $staff_role ?></span><?php endif; ?>
        <?php if ($department): ?><span class="staff-tag"><i class="ph ph-buildings"></i> <?= $department ?></span><?php endif; ?>
        <?php if ($seniority): ?><span class="staff-tag"><i class="ph ph-star"></i> <?= $seniority ?></span><?php endif; ?>
        <?php if ($joining): ?><span class="staff-tag"><i class="ph ph-calendar"></i> Since <?= $joining ?></span><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="container">

<!-- ═══════════════ FILTER BAR ═══════════════ -->
<div class="filter-bar">
  <span class="filter-label"><i class="ph ph-funnel"></i> Period</span>
  <button class="filter-btn" data-filter="today" onclick="setFilter('today',this)">Today</button>
  <button class="filter-btn" data-filter="yesterday" onclick="setFilter('yesterday',this)">Yesterday</button>
  <button class="filter-btn" data-filter="last_7" onclick="setFilter('last_7',this)">Last 7 Days</button>
  <button class="filter-btn active" data-filter="this_month" onclick="setFilter('this_month',this)">This Month</button>
  <button class="filter-btn" data-filter="last_month" onclick="setFilter('last_month',this)">Last Month</button>
  <button class="filter-btn" data-filter="custom_month" onclick="setFilter('custom_month',this)">Custom Month</button>
  <div class="custom-month-wrap" id="custom_month_wrap">
    <input type="month" id="custom_month_input" value="<?= date('Y-m') ?>">
    <button class="apply-btn" onclick="loadAll()">Apply</button>
  </div>
</div>

<!-- ═══════════════ KPI TILES ═══════════════ -->
<div class="kpi-grid" id="kpi_grid">
  <div class="kpi-card default">
    <div class="kpi-icon"><i class="ph-bold ph-currency-inr"></i></div>
    <div class="kpi-label">Total Revenue</div>
    <div class="kpi-value" id="kpi_revenue">—</div>
    <div class="kpi-sub" id="kpi_revenue_sub"></div>
  </div>
  <div class="kpi-card warning">
    <div class="kpi-icon"><i class="ph-bold ph-target"></i></div>
    <div class="kpi-label">Target Achievement</div>
    <div class="kpi-value" id="kpi_target">—</div>
    <div class="kpi-sub" id="kpi_target_sub"></div>
  </div>
  <div class="kpi-card success">
    <div class="kpi-icon"><i class="ph-bold ph-users"></i></div>
    <div class="kpi-label">Clients Served</div>
    <div class="kpi-value" id="kpi_clients">—</div>
    <div class="kpi-sub" id="kpi_clients_sub"></div>
  </div>
  <div class="kpi-card info">
    <div class="kpi-icon"><i class="ph-bold ph-arrow-u-up-right"></i></div>
    <div class="kpi-label">Repeat Clients</div>
    <div class="kpi-value" id="kpi_repeat">—</div>
    <div class="kpi-sub" id="kpi_repeat_sub"></div>
  </div>
  <div class="kpi-card success">
    <div class="kpi-icon"><i class="ph-bold ph-scissors"></i></div>
    <div class="kpi-label">Services Done</div>
    <div class="kpi-value" id="kpi_services">—</div>
    <div class="kpi-sub">Total service count</div>
  </div>
  <div class="kpi-card info">
    <div class="kpi-icon"><i class="ph-bold ph-receipt"></i></div>
    <div class="kpi-label">Bills Raised</div>
    <div class="kpi-value" id="kpi_invoices">—</div>
    <div class="kpi-sub">Total invoices</div>
  </div>
</div>

<!-- ═══════════════ TARGET + COMPARISON ═══════════════ -->
<div class="section-card">
  <div class="section-header">
    <div class="section-icon" style="background:#fef3c7; color:#d97706;"><i class="ph-bold ph-target"></i></div>
    <h2>Target Achievement & Period Comparison</h2>
  </div>
  <div class="section-body">
    <div class="target-row">
      <!-- Target Progress -->
      <div>
        <div style="display:flex; align-items:baseline; gap:10px; margin-bottom:6px;">
          <span class="target-pct" id="target_pct_big">0%</span>
          <span style="font-size:14px; color:var(--muted); font-weight:500;">of monthly target</span>
        </div>
        <div class="progress-wrap">
          <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="target_bar" style="width:0%"></div>
          </div>
          <div class="progress-labels">
            <span>₹0</span>
            <span id="target_max_lbl">Target: calculating...</span>
          </div>
        </div>
        <div class="no-target-msg" id="no_target_msg">
          ⚠️ No global target set. Please set a target in the Staff management page.
        </div>
        <div style="margin-top:16px; display:flex; gap:12px; flex-wrap:wrap;">
          <div style="background:#f8fafc; border-radius:10px; padding:12px 16px; flex:1; min-width:120px;">
            <div style="font-size:11px; color:var(--muted); font-weight:700; text-transform:uppercase; margin-bottom:4px;">Generated</div>
            <div style="font-size:18px; font-weight:800; color:#059669;" id="target_generated">₹0</div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:12px 16px; flex:1; min-width:120px;">
            <div style="font-size:11px; color:var(--muted); font-weight:700; text-transform:uppercase; margin-bottom:4px;">Remaining</div>
            <div style="font-size:18px; font-weight:800; color:#ef4444;" id="target_remaining">₹0</div>
          </div>
        </div>
      </div>
      <!-- Comparison -->
      <div class="compare-box" id="compare_box">
        <h3>Period Comparison</h3>
        <div class="loading-row" style="text-align:center;padding:20px;color:var(--muted);">
          <span class="spin">⟳</span> Loading...
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════ COMPREHENSIVE BREAKDOWN ═══════════════ -->
<div class="section-card">
  <div class="section-header">
    <div class="section-icon" style="background:#ede9fe; color:#7c3aed;"><i class="ph-bold ph-chart-bar"></i></div>
    <h2>Revenue Breakdown</h2>
  </div>
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Clients</th>
          <th style="text-align:right;">Services</th>
          <th style="text-align:right;">Redemptions</th>
          <th style="text-align:right;">Packages</th>
          <th style="text-align:right;">Memberships</th>
          <th style="text-align:right;">Products</th>
          <th style="text-align:right; color:#059669;">Total Generated</th>
        </tr>
      </thead>
      <tbody id="comprehensive_tbody">
        <tr class="loading-row"><td colspan="7"><span class="spin">⟳</span> Loading...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ═══════════════ SERVICE BREAKDOWN ═══════════════ -->
<div class="section-card">
  <div class="section-header">
    <div class="section-icon" style="background:#dbeafe; color:#2563eb;"><i class="ph-bold ph-scissors"></i></div>
    <h2>Service-wise Sales Breakdown</h2>
  </div>
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Service Name</th>
          <th style="text-align:center;">Wallet/Pkg</th>
          <th style="text-align:right;">Rev (W/P)</th>
          <th style="text-align:center;">Regular</th>
          <th style="text-align:right;">Rev (Regular)</th>
          <th style="text-align:center;">Total</th>
          <th style="text-align:right;">Total Revenue</th>
        </tr>
      </thead>
      <tbody id="breakdown_tbody">
        <tr class="loading-row"><td colspan="7"><span class="spin">⟳</span> Loading...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ═══════════════ REPEAT CLIENT ANALYSIS ═══════════════ -->
<div class="section-card">
  <div class="section-header">
    <div class="section-icon" style="background:#d1fae5; color:#059669;"><i class="ph-bold ph-arrow-u-up-right"></i></div>
    <h2>Repeat Client Analysis</h2>
  </div>
  <div class="section-body">
    <div class="repeat-grid" id="repeat_grid">
      <div class="repeat-tile"><div class="val" id="rc_total">—</div><div class="lbl">Unique Clients</div></div>
      <div class="repeat-tile"><div class="val" id="rc_repeat" style="color:#059669;">—</div><div class="lbl">Repeat Clients</div></div>
      <div class="repeat-tile"><div class="val" id="rc_new" style="color:#3b82f6;">—</div><div class="lbl">New Clients</div></div>
      <div class="repeat-tile"><div class="val" id="rc_rate" style="color:#a855f7;">—</div><div class="lbl">Repeat Rate</div></div>
    </div>
    <div id="top_repeats_wrap" style="display:none;">
      <div style="font-size:13px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Top Loyal Customers</div>
      <table class="data-table">
        <thead><tr><th>#</th><th>Customer Name</th><th style="text-align:center;">Visits</th></tr></thead>
        <tbody id="top_repeats_tbody"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ═══════════════ DAILY BILLING ═══════════════ -->
<div class="section-card">
  <div class="section-header">
    <div class="section-icon" style="background:#fce7f3; color:#db2777;"><i class="ph-bold ph-receipt"></i></div>
    <h2>Daily Billing Log</h2>
    <span style="margin-left:auto; font-size:12px; color:var(--muted); background:#f1f5f9; padding:3px 10px; border-radius:20px;" id="billing_count_badge"></span>
  </div>
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Invoice #</th>
          <th>Customer</th>
          <th>Mobile</th>
          <th>Services</th>
          <th>Payment</th>
          <th style="text-align:right;">Amount</th>
        </tr>
      </thead>
      <tbody id="billing_tbody">
        <tr class="loading-row"><td colspan="7"><span class="spin">⟳</span> Loading...</td></tr>
      </tbody>
    </table>
  </div>
</div>

</div><!-- /container -->

<div class="page-footer">
  <i class="ph ph-lock"></i> Confidential report for <?= $staff_name ?> · <?= $salon_name ?> · Generated <?= date('d M Y, h:i A') ?>
</div>

<script>
const STAFF_ID = <?= $staff_id ?>;
const MOB      = '<?= addslashes($mob) ?>';
const AJAX_URL = 'ajax/staff_report_ajax.php';

let currentFilter = 'this_month';
let currentCustomMonth = '<?= date('Y-m') ?>';

function baseParams() {
    return {
        staff_id: STAFF_ID,
        mob: MOB,
        filter: currentFilter,
        custom_month: currentCustomMonth
    };
}

function setFilter(filter, btn) {
    currentFilter = filter;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    var wrap = document.getElementById('custom_month_wrap');
    if (filter === 'custom_month') {
        wrap.classList.add('show');
    } else {
        wrap.classList.remove('show');
        loadAll();
    }
}

function loadAll() {
    if (currentFilter === 'custom_month') {
        currentCustomMonth = document.getElementById('custom_month_input').value || '<?= date('Y-m') ?>';
    }
    loadKpis();
    loadComparison();
    loadComprehensive();
    loadServiceBreakdown();
    loadRepeatAnalysis();
    loadDailyBilling();
}

// ─── KPIs ───────────────────────────────────────
function loadKpis() {
    var p = Object.assign({ method: 'get_staff_kpis' }, baseParams());
    $.post(AJAX_URL, p, function(res) {
        try {
            var d = JSON.parse(res);
            if (d.error) return;

            $('#kpi_revenue').text('₹' + numFmt(d.total_revenue));
            $('#kpi_revenue_sub').text(d.total_invoices + ' invoices · ' + d.from + ' to ' + d.to);
            $('#kpi_target').text(d.target_pct + '%');
            // Show: "5× salary = ₹50,000  |  Counting: Services + Memberships"
            var compLabels = { services: 'Services', redemptions: 'Redemptions', packages: 'Packages', memberships: 'Memberships', products: 'Products' };
            var compNames = (d.components || []).map(function(c) { return compLabels[c] || c; }).join(' + ');
            var targetLabel = d.multiplier + '× salary = ₹' + numFmt(d.monthly_target);
            if (compNames) targetLabel += ' · Counting: ' + compNames;
            $('#kpi_target_sub').text(targetLabel);
            $('#kpi_clients').text(d.unique_clients);
            $('#kpi_clients_sub').text(d.new_clients + ' new · ' + d.repeat_clients + ' repeat');
            $('#kpi_repeat').text(d.repeat_clients);
            $('#kpi_repeat_sub').text(d.unique_clients > 0 ? Math.round((d.repeat_clients/d.unique_clients)*100) + '% repeat rate' : '');
            $('#kpi_services').text(d.services_count);
            $('#kpi_invoices').text(d.total_invoices);

            // Big progress bar — uses target_revenue (selected components only)
            var pct = Math.min(d.target_pct, 100);
            $('#target_pct_big').text(d.target_pct + '%');
            $('#target_bar').css('width', pct + '%');
            if (d.target_pct >= 100) $('#target_bar').addClass('exceeded');
            else $('#target_bar').removeClass('exceeded');
            $('#target_generated').text('₹' + numFmt(d.target_revenue));
            var remaining = Math.max(0, d.monthly_target - d.target_revenue);
            $('#target_remaining').text('₹' + numFmt(remaining));
            var barLabel = d.multiplier + '× salary · Target: ₹' + numFmt(d.monthly_target);
            if (compNames) barLabel += ' · ' + compNames + ' only';
            $('#target_max_lbl').text(barLabel);

            if (!d.monthly_target || d.monthly_target <= 0) {
                $('#no_target_msg').text('⚠️ No salary set for this staff. Please update staff details to enable target tracking.').show();
            } else {
                $('#no_target_msg').hide();
            }

        } catch(e) {}
    });
}

// ─── Period Comparison ───────────────────────────
function loadComparison() {
    var p = Object.assign({ method: 'get_staff_comparison' }, baseParams());
    $.post(AJAX_URL, p, function(res) {
        try {
            var d = JSON.parse(res);
            if (d.error) return;

            var badgeClass = d.direction === 'up' ? 'badge-up' : (d.direction === 'down' ? 'badge-down' : 'badge-neutral');
            var arrow = d.direction === 'up' ? '↑' : (d.direction === 'down' ? '↓' : '↔');

            var html = '<h3>Period Comparison</h3>';
            html += '<div class="compare-row"><span class="compare-label">Current Period</span><span class="compare-val">₹' + numFmt(d.current) + '</span></div>';
            html += '<div class="compare-row"><span class="compare-label">Previous Period <small style="color:var(--muted);font-size:11px;">(' + d.prev_from + ' – ' + d.prev_to + ')</small></span><span class="compare-val">₹' + numFmt(d.previous) + '</span></div>';
            html += '<div class="compare-row"><span class="compare-label">Change</span><span class="' + badgeClass + '">' + arrow + ' ' + d.change_pct + '%</span></div>';
            html += '<div style="height:1px;background:var(--border);margin:12px 0;"></div>';
            html += '<div class="compare-row"><span class="compare-label">Last Full Month</span><span class="compare-val">₹' + numFmt(d.last_month) + '</span></div>';
            html += '<div style="font-size:11px;color:var(--muted);margin-top:4px;">' + d.lm_from + ' – ' + d.lm_to + '</div>';

            $('#compare_box').html(html);
        } catch(e) {}
    });
}

// ─── Comprehensive Table ──────────────────────────
function loadComprehensive() {
    $('#comprehensive_tbody').html('<tr class="loading-row"><td colspan="7"><span class="spin">⟳</span> Loading...</td></tr>');
    var p = Object.assign({ method: 'get_staff_comprehensive' }, baseParams());
    $.post(AJAX_URL, p, function(res) {
        try {
            var d = JSON.parse(res);
            if (d.error) { $('#comprehensive_tbody').html('<tr><td colspan="7" style="text-align:center;color:var(--muted);">No data</td></tr>'); return; }

            var html = '<tr>';
            html += '<td><span class="count-badge">' + d.clients + '</span></td>';
            html += '<td style="text-align:right;" class="amount">₹' + numFmt(d.services_rev) + '</td>';
            html += '<td style="text-align:right;" class="amount">₹' + numFmt(d.redemptions) + '</td>';
            html += '<td style="text-align:right;" class="amount">₹' + numFmt(d.packages_sold) + '</td>';
            html += '<td style="text-align:right;" class="amount">₹' + numFmt(d.memberships_sold) + '</td>';
            html += '<td style="text-align:right;" class="amount">₹' + numFmt(d.products_sold) + '</td>';
            html += '<td style="text-align:right; font-size:16px; font-weight:900; color:#059669;">₹' + numFmt(d.total_generated) + '</td>';
            html += '</tr>';
            $('#comprehensive_tbody').html(html);
        } catch(e) {}
    });
}

// ─── Service Breakdown ────────────────────────────
function loadServiceBreakdown() {
    $('#breakdown_tbody').html('<tr class="loading-row"><td colspan="7"><span class="spin">⟳</span> Loading...</td></tr>');
    var p = Object.assign({ method: 'get_staff_service_breakdown' }, baseParams());
    $.post(AJAX_URL, p, function(res) {
        try {
            var d = JSON.parse(res);
            if (!d.breakdown || d.breakdown.length === 0) {
                $('#breakdown_tbody').html('<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px;">No service data for this period.</td></tr>');
                return;
            }
            var html = '';
            var totals = { tc: 0, tr: 0, rc: 0, rr: 0, oc: 0, or_: 0 };
            d.breakdown.forEach(function(b) {
                totals.tc += parseInt(b.total_count) || 0;
                totals.tr += parseFloat(b.total_revenue) || 0;
                totals.rc += parseInt(b.redemp_count) || 0;
                totals.rr += parseFloat(b.redemp_revenue) || 0;
                totals.oc += parseInt(b.other_count) || 0;
                totals.or_ += parseFloat(b.other_revenue) || 0;

                html += '<tr class="bill-row">';
                html += '<td style="font-weight:600;"><span style="background:#e0e7ff;color:#4f46e5;padding:2px 8px;border-radius:8px;font-size:11px;margin-right:6px;"><i class="ph ph-scissors"></i></span>' + esc(b.service_name) + '</td>';
                html += '<td style="text-align:center;"><span class="count-badge redemp">' + b.redemp_count + 'x</span></td>';
                html += '<td style="text-align:right;" class="amount">₹' + numFmt(b.redemp_revenue) + '</td>';
                html += '<td style="text-align:center;"><span class="count-badge other">' + b.other_count + 'x</span></td>';
                html += '<td style="text-align:right;" class="amount">₹' + numFmt(b.other_revenue) + '</td>';
                html += '<td style="text-align:center;"><span class="count-badge">' + b.total_count + 'x</span></td>';
                html += '<td style="text-align:right;font-weight:700;color:#059669;">₹' + numFmt(b.total_revenue) + '</td>';
                html += '</tr>';
            });
            // Grand total row
            html += '<tr class="total-row">';
            html += '<td>GRAND TOTAL</td>';
            html += '<td style="text-align:center;">' + totals.rc + 'x</td>';
            html += '<td style="text-align:right;">₹' + numFmt(totals.rr) + '</td>';
            html += '<td style="text-align:center;">' + totals.oc + 'x</td>';
            html += '<td style="text-align:right;">₹' + numFmt(totals.or_) + '</td>';
            html += '<td style="text-align:center;">' + totals.tc + 'x</td>';
            html += '<td style="text-align:right;">₹' + numFmt(totals.tr) + '</td>';
            html += '</tr>';
            $('#breakdown_tbody').html(html);
        } catch(e) { console.error(e); }
    });
}

// ─── Repeat Analysis ──────────────────────────────
function loadRepeatAnalysis() {
    var p = Object.assign({ method: 'get_staff_repeat_analysis' }, baseParams());
    $.post(AJAX_URL, p, function(res) {
        try {
            var d = JSON.parse(res);
            if (d.error) return;
            $('#rc_total').text(d.total_unique);
            $('#rc_repeat').text(d.repeat_count);
            $('#rc_new').text(d.new_count);
            $('#rc_rate').text(d.repeat_rate + '%');

            if (d.top_repeats && d.top_repeats.length > 0) {
                var html = '';
                d.top_repeats.forEach(function(c, i) {
                    html += '<tr><td><span style="background:#f1f5f9;padding:2px 8px;border-radius:6px;font-weight:700;">' + (i+1) + '</span></td>';
                    html += '<td style="font-weight:600;">' + esc(c.cust_name || 'Walk-in') + '</td>';
                    html += '<td style="text-align:center;"><span style="background:#d1fae5;color:#059669;padding:3px 10px;border-radius:12px;font-weight:700;">' + c.visits + ' visits</span></td></tr>';
                });
                $('#top_repeats_tbody').html(html);
                $('#top_repeats_wrap').show();
            }
        } catch(e) {}
    });
}

// ─── Daily Billing ────────────────────────────────
function loadDailyBilling() {
    $('#billing_tbody').html('<tr class="loading-row"><td colspan="7"><span class="spin">⟳</span> Loading...</td></tr>');
    var p = Object.assign({ method: 'get_staff_daily_billing' }, baseParams());
    $.post(AJAX_URL, p, function(res) {
        try {
            var d = JSON.parse(res);
            if (!d.bills || d.bills.length === 0) {
                $('#billing_tbody').html('<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px;">No billing records for this period.</td></tr>');
                $('#billing_count_badge').text('');
                return;
            }
            $('#billing_count_badge').text(d.bills.length + ' records');

            var html = '';
            var prevDate = '';
            var dayTotal = 0;
            var dayBills = [];
            
            // Group by date
            var groups = {};
            d.bills.forEach(function(b) {
                if (!groups[b.bill_date]) groups[b.bill_date] = [];
                groups[b.bill_date].push(b);
            });

            Object.keys(groups).forEach(function(date) {
                var bills = groups[date];
                var daySum = bills.reduce(function(s, b) { return s + parseFloat(b.grand_total); }, 0);
                
                html += '<tr><td colspan="7" class="date-group-header">';
                html += '<i class="ph ph-calendar-blank"></i> ' + date;
                html += ' &nbsp;·&nbsp; <strong>' + bills.length + ' bill' + (bills.length > 1 ? 's' : '') + '</strong>';
                html += ' &nbsp;·&nbsp; Day Total: <strong>₹' + numFmt(daySum) + '</strong>';
                html += '</td></tr>';

                bills.forEach(function(b) {
                    html += '<tr class="bill-row">';
                    html += '<td style="color:var(--muted);font-size:12px;">' + date + '</td>';
                    html += '<td><span style="background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:6px;font-weight:700;font-size:12px;">#' + b.invoice_id + '</span></td>';
                    html += '<td style="font-weight:600;">' + esc(b.cust_name) + '</td>';
                    html += '<td style="font-family:monospace;letter-spacing:1px;color:var(--muted);">' + esc(b.cust_mobile) + '</td>';
                    html += '<td style="font-size:12px;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + esc(b.services) + '">' + esc(b.services) + '</td>';
                    html += '<td><span style="background:#f1f5f9;color:var(--text);padding:2px 8px;border-radius:6px;font-size:12px;">' + esc(b.payment_mode) + '</span></td>';
                    html += '<td style="text-align:right;font-weight:700;color:#059669;">₹' + numFmt(b.grand_total) + '</td>';
                    html += '</tr>';
                });
            });
            $('#billing_tbody').html(html);
        } catch(e) { console.error(e); }
    });
}

// ─── Utilities ────────────────────────────────────
function numFmt(n) {
    var num = parseFloat(n) || 0;
    return num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Load on page ready
$(document).ready(function() {
    loadAll();
});
</script>
</body>
</html>

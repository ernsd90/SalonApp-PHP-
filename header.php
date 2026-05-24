<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once "config.php";
include_once "function.php";

check_login();

$user_name = get_session_data('full_name');
$user_role_name = get_session_data('role_name') ?: "Admin";
$active_salon_id = get_active_salon_id();
$salon_id = $active_salon_id;
$user_id = get_session_data('user_id');

// Fetch salons if Superadmin
$salons = [];
if(is_superadmin()) {
    $salons = select_array("SELECT salon_id, salon_name FROM hr_salon WHERE salon_status=1");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon OS - Dashboard</title>
    
    <!-- jQuery must load first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

<div class="app-container">
    
    <!-- Sidebar Include -->
    <?php include "sidebar.php"; ?>

    <!-- Main Content Area -->
    <div class="main-content">
        
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left" style="display: flex; align-items: center; gap: 16px;">
                <button id="mobile-menu-btn" style="background:none; border:none; font-size: 24px; cursor:pointer; color: var(--text-main); display: none;">
                    <i class="ph ph-list"></i>
                </button>
                <div class="topbar-title" style="font-weight: 600; font-size: 18px;">
                    <!-- Page title injected contextually if needed -->
                    Dashboard
                </div>
            </div>

            <div class="topbar-right" style="display: flex; align-items: center; gap: 24px;">
                
                <?php if(is_superadmin()): ?>
                    <div class="salon-switcher" style="display: flex; align-items: center; gap: 8px;">
                        <i class="ph ph-storefront" style="color: var(--text-muted); font-size: 20px;"></i>
                        <select id="globalSalonSelect" class="form-control" style="padding: 8px 12px; height: auto; width: 180px; font-size: 14px;">
                            <?php foreach($salons as $sal): ?>
                                <option value="<?= $sal['salon_id'] ?>" <?= ($sal['salon_id'] == $active_salon_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sal['salon_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="user-profile" style="display: flex; align-items: center; gap: 12px;">
                    <div style="text-align: right;">
                        <div style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($user_name) ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($user_role_name) ?></div>
                    </div>
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                        <?= substr($user_name, 0, 1) ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Wrapper starts here (closed in footer) -->
        <main class="page-wrapper">

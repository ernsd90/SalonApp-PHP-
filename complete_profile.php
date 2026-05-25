<?php
session_start();
include 'config.php';
include 'function.php';
require_once 'loyalty_functions.php';

$inv_id = isset($_GET['inv']) ? (int)$_GET['inv'] : 0;
$submitted = false;
$error = '';
$invoice = null;
$customer = null;
$loyalty_enabled = false;
$profile_points = 0;

if ($inv_id > 0) {
    $invoice = select_row("SELECT i.invoice_id, i.cust_id, i.salon_id, s.salon_name
        FROM hr_invoice i
        JOIN hr_salon s ON i.salon_id = s.salon_id
        WHERE i.invoice_id = '$inv_id' AND i.delete_bill = 0");

    if ($invoice) {
        $cust_id = (int)$invoice['cust_id'];
        $salon_id = (int)$invoice['salon_id'];
        
        $customer = select_row("SELECT cust_name, cust_mobile, cust_dob, cust_anniversary, cust_gender, loyalty_profile_bonus_given
            FROM hr_customer
            WHERE cust_id = '$cust_id'");

        // Get loyalty settings
        $ls = select_row("SELECT loyalty_enabled, profile_complete_points FROM hr_loyalty_settings WHERE salon_id='$salon_id'");
        if ($ls && (int)$ls['loyalty_enabled'] === 1) {
            $loyalty_enabled = true;
            $profile_points = (float)($ls['profile_complete_points'] ?? 0);
        }
    }
}

$already_awarded = $customer && (int)($customer['loyalty_profile_bonus_given'] ?? 0) === 1;

// Process Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $invoice && $customer) {
    $gender = mysqli_real_escape_string($conn, $_POST['cust_gender'] ?? '');
    $dob = trim($_POST['cust_dob'] ?? '');
    $anniversary = trim($_POST['cust_anniversary'] ?? '');

    if (empty($gender)) {
        $error = 'Please select your gender.';
    } elseif (empty($dob)) {
        $error = 'Please enter your date of birth.';
    } elseif (empty($anniversary)) {
        $error = 'Please enter your anniversary date (or birthday if single).';
    } else {
        // Prepare SQL updates
        $dob_val = "'" . mysqli_real_escape_string($conn, $dob) . "'";
        $ann_val = "'" . mysqli_real_escape_string($conn, $anniversary) . "'";

        $sql = "UPDATE `hr_customer` SET 
            `cust_gender` = '$gender',
            `cust_dob` = $dob_val,
            `cust_anniversary` = $ann_val
            WHERE cust_id = '$cust_id'";
        
        if (update_query($sql)) {
            // Re-fetch customer to make sure we have the updated state
            $customer = select_row("SELECT cust_name, cust_mobile, cust_dob, cust_anniversary, cust_gender, loyalty_profile_bonus_given FROM hr_customer WHERE cust_id = '$cust_id'");
            
            // Check if profile is complete (DOB + Gender + Anniversary)
            $has_dob = !empty($customer['cust_dob']) && $customer['cust_dob'] != '0000-00-00';
            $has_gender = !empty($customer['cust_gender']);
            $has_ann = !empty($customer['cust_anniversary']) && $customer['cust_anniversary'] != '0000-00-00';

            $profile_bonus_awarded = false;
            
            if ($has_dob && $has_gender && $has_ann) {
                if ($loyalty_enabled && !$already_awarded && $profile_points > 0) {
                    $expiry = date('Y-m-d', strtotime('+365 days'));
                    insert_query("INSERT INTO hr_customer_points SET
                        cust_id='$cust_id', salon_id='$salon_id', invoice_id='$inv_id', points='$profile_points',
                        type='earn', remark='Profile completion bonus',
                        expiry_date='$expiry', created_at=NOW()");
                    
                    update_query("UPDATE hr_customer SET loyalty_profile_bonus_given=1 WHERE cust_id='$cust_id'");
                    $profile_bonus_awarded = true;
                    $already_awarded = true;
                }
            }
            $submitted = true;
        } else {
            $error = 'Something went wrong while saving your details. Please try again.';
        }
    }
}

$salon_name = $invoice['salon_name'] ?? 'Our Salon';
$cust_name = $customer['cust_name'] ?? 'Guest';
$cust_dob = $customer['cust_dob'] ?? '';
$cust_anniversary = $customer['cust_anniversary'] ?? '';
$cust_gender = $customer['cust_gender'] ?? '';

// Check if dates are unset/default in DB
if ($cust_dob == '0000-00-00' || $cust_dob == '1970-01-01') $cust_dob = '';
if ($cust_anniversary == '0000-00-00' || $cust_anniversary == '1970-01-01') $cust_anniversary = '';

$is_currently_complete = !empty($cust_dob) && !empty($cust_gender) && !empty($cust_anniversary);

// Fetch current points balance
$current_points = 0;
if ($customer && $invoice) {
    $current_points = get_customer_points_balance((int)$invoice['cust_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile – <?= htmlspecialchars($salon_name) ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 36px 28px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            text-align: center;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .logo-wrap {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px; color: white;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.3);
        }
        h1 { font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 6px; }
        .subtitle { color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5; }
        
        .promo-banner {
            background: #f5f3ff;
            border: 1px dashed #7c3aed;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }
        .promo-banner i { font-size: 28px; color: #7c3aed; }
        .promo-banner .title { font-weight: 700; color: #5b21b6; font-size: 14px; }
        .promo-banner .desc { color: #6d28d9; font-size: 12px; margin-top: 2px; }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .form-group label i { font-size: 16px; color: #4f46e5; }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            color: #1e293b;
            background: white;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        
        button[type=submit] {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white; border: none; border-radius: 14px;
            font-size: 16px; font-weight: 700; cursor: pointer;
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.25);
            transition: transform 0.2s, opacity 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 10px;
        }
        button[type=submit]:hover { opacity: 0.95; }
        button[type=submit]:active { transform: scale(0.98); }

        .success-wrap { padding: 16px 0; }
        .success-wrap .icon {
            font-size: 64px;
            color: #10b981;
            margin-bottom: 16px;
            animation: pop 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes pop {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .success-wrap h2 { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        .success-wrap p { color: #64748b; font-size: 14px; line-height: 1.5; margin-bottom: 24px; }
        
        .points-pill {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 12px 24px;
            border-radius: 16px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            margin-bottom: 20px;
        }
        .points-pill .val { font-size: 24px; font-weight: 800; color: #047857; }
        .points-pill .lbl { font-size: 11px; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px; }

        .error-msg {
            background: #fee2e2;
            color: #b91c1c;
            padding: 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
        }
        .error-msg i { font-size: 18px; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo-wrap"><i class="ph-fill ph-sparkles"></i></div>
    <h1><?= htmlspecialchars($salon_name) ?></h1>

    <?php if (!$invoice || !$customer): ?>
        <div class="subtitle" style="margin-top: 15px;">Invalid or expired profile completion link.</div>

    <?php elseif ($submitted): ?>
        <div class="success-wrap">
            <div class="icon"><i class="ph-fill ph-check-circle"></i></div>
            <h2>Profile Updated!</h2>
            <p>Thank you for completing your profile details, <?= htmlspecialchars($cust_name) ?>!</p>
            
            <div class="points-pill">
                <span class="val"><?= number_format($current_points, 0) ?> pts</span>
                <span class="lbl">Total Loyalty Balance</span>
            </div>

            <p style="color: #64748b; font-size: 13px;">You can redeem these points on your next visit for amazing discounts! 💎</p>
        </div>

    <?php elseif ($is_currently_complete && $already_awarded): ?>
        <div class="success-wrap">
            <div class="icon" style="color: #4f46e5;"><i class="ph-fill ph-shield-check"></i></div>
            <h2>Profile Already Completed</h2>
            <p>Your profile is fully up-to-date and complete, <?= htmlspecialchars($cust_name) ?>!</p>
            
            <div class="points-pill" style="background:#f0fdfa; border-color:#ccfbf1;">
                <span class="val" style="color:#0f766e;"><?= number_format($current_points, 0) ?> pts</span>
                <span class="lbl" style="color:#115e59;">Total Loyalty Balance</span>
            </div>

            <p style="color: #64748b; font-size: 13px;">Thank you for being our valued member. See you soon!</p>
        </div>

    <?php else: ?>
        <div class="subtitle">Hi <strong><?= htmlspecialchars($cust_name) ?></strong>! Complete your profile to keep receiving premium rewards.</div>

        <?php if ($loyalty_enabled && $profile_points > 0 && !$already_awarded): ?>
            <div class="promo-banner">
                <i class="ph-fill ph-gift"></i>
                <div>
                    <div class="title">Get <?= number_format($profile_points, 0) ?> Bonus Points!</div>
                    <div class="desc">Awarded instantly once your profile is completed.</div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error-msg"><i class="ph-fill ph-warning-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="cust_gender"><i class="ph-fill ph-gender-intersex"></i> Gender</label>
                <select name="cust_gender" id="cust_gender" class="form-control" required>
                    <option value="">-- Select Gender --</option>
                    <option value="Male" <?= $cust_gender == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $cust_gender == 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= $cust_gender == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cust_dob"><i class="ph-fill ph-cake"></i> Date of Birth</label>
                <input type="date" name="cust_dob" id="cust_dob" class="form-control" value="<?= htmlspecialchars($cust_dob) ?>" required>
            </div>

            <div class="form-group">
                <label for="cust_anniversary"><i class="ph-fill ph-heart"></i> Anniversary Date <span style="font-weight:normal; font-size:11px; color:#64748b;">(or Birthday if single)</span></label>
                <input type="date" name="cust_anniversary" id="cust_anniversary" class="form-control" value="<?= htmlspecialchars($cust_anniversary) ?>" required>
            </div>

            <button type="submit"><i class="ph ph-circle-wavy-check"></i> Complete Profile & Claim Bonus</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>

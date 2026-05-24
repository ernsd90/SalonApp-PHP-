<?php
session_start();
include 'config.php';
include 'function.php';

$inv_id = isset($_GET['inv']) ? (int)$_GET['inv'] : 0;
$submitted = false;
$error = '';
$invoice = null;
$already_rated = false;

if ($inv_id > 0) {
    $invoice = select_row("SELECT i.invoice_id, i.cust_id, i.salon_id, c.cust_name, c.cust_mobile, s.salon_name, s.google_review_link
        FROM hr_invoice i
        JOIN hr_customer c ON i.cust_id = c.cust_id
        JOIN hr_salon s ON i.salon_id = s.salon_id
        WHERE i.invoice_id = '$inv_id' AND i.delete_bill = 0");

    // Check if already rated
    if ($invoice) {
        $exists = select_row("SELECT feedback_id FROM hr_feedback WHERE invoice_id = '$inv_id' LIMIT 1");
        if ($exists) $already_rated = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $inv_id > 0 && $invoice && !$already_rated) {
    $rating   = (int)($_POST['rating'] ?? 0);
    $comments = mysqli_real_escape_string($conn, trim($_POST['comments'] ?? ''));
    $salon_id = (int)$invoice['salon_id'];
    $cust_id  = (int)$invoice['cust_id'];
    $c_name   = mysqli_real_escape_string($conn, $invoice['cust_name']);
    $c_mob    = mysqli_real_escape_string($conn, $invoice['cust_mobile']);

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating before submitting.';
    } else {
        insert_query("INSERT INTO hr_feedback SET
            salon_id='$salon_id', invoice_id='$inv_id', cust_id='$cust_id',
            cust_name='$c_name', cust_mob='$c_mob',
            experience='$rating', message='$comments',
            rating='$rating', comments='$comments',
            created_date=NOW(), created_at=NOW()");
        $submitted = true;
    }
}

$salon_name = $invoice['salon_name'] ?? 'Our Salon';
$cust_name = $invoice['cust_name'] ?? 'Guest';
$google_review_link = $invoice['google_review_link'] ?? '';
// Store the rating submitted during this session to determine the response
$submitted_rating = isset($rating) ? $rating : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Your Experience – <?= htmlspecialchars($salon_name) ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .logo-wrap {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px; color: white;
        }
        h1 { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 6px; }
        .subtitle { color: #64748b; font-size: 14px; margin-bottom: 28px; }
        .star-row { display: flex; justify-content: center; gap: 12px; margin-bottom: 24px; }
        .star-row input[type=radio] { display: none; }
        .star-row label {
            font-size: 42px;
            cursor: pointer;
            color: #cbd5e1;
            transition: color 0.2s, transform 0.2s;
            line-height: 1;
        }
        .star-row input[type=radio]:checked ~ label,
        .star-row label:hover,
        .star-row label:hover ~ label { color: #f59e0b; }
        .star-row { flex-direction: row-reverse; }
        .star-row label:hover, .star-row label:hover ~ label { transform: scale(1.15); }
        textarea {
            width: 100%; border: 1.5px solid #e2e8f0;
            border-radius: 12px; padding: 14px; font-size: 14px;
            font-family: inherit; resize: none; height: 110px;
            transition: border 0.2s; outline: none; margin-bottom: 20px;
            color: #1e293b;
        }
        textarea:focus { border-color: #667eea; }
        button[type=submit] {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; border: none; border-radius: 14px;
            font-size: 16px; font-weight: 700; cursor: pointer;
            transition: opacity 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        button[type=submit]:hover { opacity: 0.9; }
        .success-wrap { padding: 20px 0; }
        .success-wrap .icon { font-size: 64px; color: #22c55e; margin-bottom: 16px; }
        .success-wrap h2 { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        .success-wrap p { color: #64748b; font-size: 14px; }
        .error-msg { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .already-msg { background: #fffbeb; color: #92400e; padding: 16px; border-radius: 12px; font-size: 14px; }
        .cust-badge { background: #f0f9ff; color: #0369a1; border-radius: 10px; padding: 10px 16px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo-wrap"><i class="ph-fill ph-scissors"></i></div>
    <h1><?= htmlspecialchars($salon_name) ?></h1>

    <?php if (!$invoice): ?>
        <div class="subtitle">Invalid or expired feedback link.</div>

    <?php elseif ($already_rated): ?>
        <div class="subtitle">Thank you, <?= htmlspecialchars($cust_name) ?>!</div>
        <div class="already-msg">
            <i class="ph-fill ph-check-circle"></i> You've already submitted feedback for this visit. We appreciate your time!
        </div>

    <?php elseif ($submitted): ?>
        <div class="success-wrap">
            <?php if ($submitted_rating >= 4 && !empty($google_review_link)): ?>
                <div class="icon" style="color: #f59e0b;"><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i></div>
                <h2>We're thrilled you loved it!</h2>
                <p style="margin-bottom:20px;">Would you mind taking a quick second to support our business by leaving a review on Google? It means the world to us!</p>
                <a href="<?= htmlspecialchars($google_review_link) ?>" target="_blank" style="display:inline-block;background:#3b82f6;color:white;padding:14px 24px;border-radius:12px;text-decoration:none;font-weight:700;font-size:16px;"><i class="ph-fill ph-google-logo"></i> Leave a Google Review</a>
            <?php else: ?>
                <div class="icon"><i class="ph-fill ph-heart"></i></div>
                <h2>Thank You, <?= htmlspecialchars($cust_name) ?>!</h2>
                <p>Your feedback helps us improve. We look forward to seeing you again at <?= htmlspecialchars($salon_name) ?>.</p>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <div class="subtitle">Hi <strong><?= htmlspecialchars($cust_name) ?></strong>! How was your experience today?</div>

        <?php if ($error): ?>
        <div class="error-msg"><i class="ph ph-warning"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="star-row">
                <input type="radio" name="rating" id="s5" value="5">
                <label for="s5">★</label>
                <input type="radio" name="rating" id="s4" value="4">
                <label for="s4">★</label>
                <input type="radio" name="rating" id="s3" value="3">
                <label for="s3">★</label>
                <input type="radio" name="rating" id="s2" value="2">
                <label for="s2">★</label>
                <input type="radio" name="rating" id="s1" value="1">
                <label for="s1">★</label>
            </div>
            <textarea name="comments" placeholder="Share your experience (optional)..."></textarea>
            <button type="submit"><i class="ph ph-paper-plane-tilt"></i> Submit Feedback</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>

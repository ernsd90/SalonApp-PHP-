<?php
include 'header.php';
include_once 'loyalty_functions.php';

// Today's date parts for birthday/anniversary matching
$today_md = date('m-d');      // MM-DD
$today_m  = date('m');        // month number

// Birthday customers today
$bday_today = select_array("SELECT cust_id, cust_name, cust_mobile, cust_dob,
    (SELECT COALESCE(SUM(grand_total),0) FROM hr_invoice WHERE cust_id=c.cust_id AND delete_bill=0) as lifetime_spend
    FROM hr_customer c
    WHERE salon_id='$salon_id' AND cust_dob IS NOT NULL AND cust_dob != '0000-00-00'
    AND DATE_FORMAT(cust_dob,'%m-%d') = '$today_md'
    ORDER BY cust_name");

// Anniversary customers today
$anniv_today = select_array("SELECT cust_id, cust_name, cust_mobile, cust_anniversary,
    (SELECT COALESCE(SUM(grand_total),0) FROM hr_invoice WHERE cust_id=c.cust_id AND delete_bill=0) as lifetime_spend
    FROM hr_customer c
    WHERE salon_id='$salon_id' AND cust_anniversary IS NOT NULL AND cust_anniversary != '0000-00-00'
    AND DATE_FORMAT(cust_anniversary,'%m-%d') = '$today_md'
    ORDER BY cust_name");

// Upcoming birthdays (next 7 days, excluding today)
$bday_upcoming = select_array("SELECT cust_id, cust_name, cust_mobile, cust_dob,
    DATE_FORMAT(cust_dob,'%d %b') as bday_display,
    DATEDIFF(
        DATE_ADD(DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(cust_dob,'%m-%d'))), INTERVAL IF(DATE_FORMAT(cust_dob,'%m-%d') < DATE_FORMAT(CURDATE(),'%m-%d'), 1, 0) YEAR),
        CURDATE()
    ) as days_until
    FROM hr_customer c
    WHERE salon_id='$salon_id' AND cust_dob IS NOT NULL AND cust_dob != '0000-00-00'
    AND DATE_FORMAT(cust_dob,'%m-%d') != '$today_md'
    HAVING days_until BETWEEN 1 AND 7
    ORDER BY days_until ASC");

$salon_name_row = select_row("SELECT salon_name, salon_contact FROM hr_salon WHERE salon_id='$salon_id'");
$salon_nm = $salon_name_row['salon_name'];
?>

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;margin-bottom:4px;">📣 Campaign Engine</h1>
    <p style="color:var(--text-muted);font-size:14px;">Birthday & Anniversary outreach, churn win-back — manual WhatsApp campaigns at a click.</p>
</div>

<!-- Today Summary -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:28px;">
    <div style="background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1.5px solid #fed7aa;border-radius:18px;padding:24px;text-align:center;">
        <i class="ph-fill ph-cake" style="font-size:36px;color:#ea580c;"></i>
        <div style="font-size:36px;font-weight:800;color:#9a3412;margin-top:8px;"><?= count($bday_today) ?></div>
        <div style="font-size:13px;color:#9a3412;font-weight:700;">Birthdays Today</div>
    </div>
    <div style="background:linear-gradient(135deg,#fdf4ff,#fae8ff);border:1.5px solid #e9d5ff;border-radius:18px;padding:24px;text-align:center;">
        <i class="ph-fill ph-heart" style="font-size:36px;color:#9333ea;"></i>
        <div style="font-size:36px;font-weight:800;color:#6b21a8;margin-top:8px;"><?= count($anniv_today) ?></div>
        <div style="font-size:13px;color:#6b21a8;font-weight:700;">Anniversaries Today</div>
    </div>
    <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1.5px solid #bfdbfe;border-radius:18px;padding:24px;text-align:center;">
        <i class="ph-fill ph-calendar-check" style="font-size:36px;color:#2563eb;"></i>
        <div style="font-size:36px;font-weight:800;color:#1e40af;margin-top:8px;"><?= count($bday_upcoming) ?></div>
        <div style="font-size:13px;color:#1e40af;font-weight:700;">Birthdays Next 7 Days</div>
    </div>
    <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #bbf7d0;border-radius:18px;padding:24px;text-align:center;">
        <i class="ph-fill ph-whatsapp-logo" style="font-size:36px;color:#15803d;"></i>
        <div style="font-size:36px;font-weight:800;color:#14532d;margin-top:8px;"><?= count($bday_today)+count($anniv_today) ?></div>
        <div style="font-size:13px;color:#14532d;font-weight:700;">Pending Today</div>
    </div>
</div>

<!-- BIRTHDAYS TODAY -->
<?php if(!empty($bday_today)): ?>
<div style="background:white;border-radius:20px;border:1px solid #fed7aa;box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:24px;">
    <div style="padding:18px 24px;border-bottom:1px solid #fed7aa;background:linear-gradient(90deg,#fff7ed,#fffbeb);display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            <i class="ph-fill ph-cake" style="font-size:22px;color:#ea580c;"></i>
            <h3 style="font-size:16px;font-weight:800;margin:0;color:#9a3412;">🎂 Birthdays Today — <?= date('d M Y') ?></h3>
        </div>
        <button onclick="sendBulkBirthday()" style="background:#ea580c;color:white;border:none;padding:8px 18px;border-radius:10px;font-weight:700;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:6px;">
            <i class="ph ph-whatsapp-logo"></i> Send All (<?= count($bday_today) ?>)
        </button>
    </div>
    <div style="padding:16px 24px;display:flex;flex-direction:column;gap:10px;">
        <?php foreach($bday_today as $c):
            $lifetime = (float)$c['lifetime_spend'];
            $tier = get_customer_tier_db($lifetime, (int)$salon_id);
            $points = get_customer_points_balance((int)$c['cust_id']);
            $wa_phone = preg_replace('/\D/','',$c['cust_mobile']);
            if(strlen($wa_phone)===10) $wa_phone='91'.$wa_phone;
            $first = ucfirst(strtolower(explode(' ',trim($c['cust_name']))[0]));
            $wa_msg = "🎂 Happy Birthday, {$first}!\n\nWishing you a wonderful day filled with joy and beautiful moments! 🌟\n\nAs a special Birthday gift from *{$salon_nm}*, your next visit earns DOUBLE loyalty points! 💎\n\nBook your pampering session: {$salon_name_row['salon_contact']}";
        ?>
        <div style="display:flex;align-items:center;gap:16px;padding:14px 16px;background:#fff7ed;border-radius:14px;border:1px solid #fed7aa;">
            <div style="width:46px;height:46px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;flex-shrink:0;">
                <?= strtoupper(substr($c['cust_name'],0,1)) ?>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($c['cust_name']) ?></div>
                <div style="font-size:12px;color:#64748b;"><?= htmlspecialchars($c['cust_mobile']) ?> &nbsp;|&nbsp; DOB: <?= date('d M', strtotime($c['cust_dob'])) ?></div>
                <div style="margin-top:4px;display:flex;gap:6px;align-items:center;">
                    <span style="background:<?=$tier['bg_color']?>;color:<?=$tier['color']?>;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;"><?=$tier['tier_name']?></span>
                    <?php if($points>0): ?><span style="background:#f5f3ff;color:#7c3aed;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;"><?= number_format($points,0) ?> pts</span><?php endif; ?>
                    <span style="font-size:11px;color:#94a3b8;">₹<?= number_format($lifetime,0) ?> lifetime</span>
                </div>
            </div>
            <a href="https://wa.me/<?= $wa_phone ?>?text=<?= rawurlencode($wa_msg) ?>" target="_blank"
               style="background:#25D366;color:white;padding:8px 16px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:6px;flex-shrink:0;">
                <i class="ph-fill ph-whatsapp-logo"></i> Send Wish
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ANNIVERSARIES TODAY -->
<?php if(!empty($anniv_today)): ?>
<div style="background:white;border-radius:20px;border:1px solid #e9d5ff;box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:24px;">
    <div style="padding:18px 24px;border-bottom:1px solid #e9d5ff;background:linear-gradient(90deg,#fdf4ff,#f5f3ff);display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            <i class="ph-fill ph-heart" style="font-size:22px;color:#9333ea;"></i>
            <h3 style="font-size:16px;font-weight:800;margin:0;color:#6b21a8;">💞 Anniversaries Today</h3>
        </div>
    </div>
    <div style="padding:16px 24px;display:flex;flex-direction:column;gap:10px;">
        <?php foreach($anniv_today as $c):
            $wa_phone = preg_replace('/\D/','',$c['cust_mobile']);
            if(strlen($wa_phone)===10) $wa_phone='91'.$wa_phone;
            $first = ucfirst(strtolower(explode(' ',trim($c['cust_name']))[0]));
            $wa_msg = "💞 Happy Anniversary, {$first}!\n\nWishing you and your loved ones a day full of love and beautiful memories! 🌹\n\nCelebrate in style — treat yourself to a relaxing session at *{$salon_nm}*! ✨\n\nBook now: {$salon_name_row['salon_contact']}";
        ?>
        <div style="display:flex;align-items:center;gap:16px;padding:14px 16px;background:#fdf4ff;border-radius:14px;border:1px solid #e9d5ff;">
            <div style="width:46px;height:46px;background:linear-gradient(135deg,#c084fc,#9333ea);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;flex-shrink:0;">
                <?= strtoupper(substr($c['cust_name'],0,1)) ?>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($c['cust_name']) ?></div>
                <div style="font-size:12px;color:#64748b;"><?= htmlspecialchars($c['cust_mobile']) ?> &nbsp;|&nbsp; Anniversary: <?= date('d M', strtotime($c['cust_anniversary'])) ?></div>
            </div>
            <a href="https://wa.me/<?= $wa_phone ?>?text=<?= rawurlencode($wa_msg) ?>" target="_blank"
               style="background:#9333ea;color:white;padding:8px 16px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:6px;flex-shrink:0;">
                <i class="ph-fill ph-whatsapp-logo"></i> Send Wish
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- UPCOMING BIRTHDAYS -->
<?php if(!empty($bday_upcoming)): ?>
<div style="background:white;border-radius:20px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:24px;">
    <div style="padding:18px 24px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:10px;">
        <i class="ph-fill ph-calendar-check" style="font-size:22px;color:#2563eb;"></i>
        <h3 style="font-size:16px;font-weight:800;margin:0;">📅 Upcoming Birthdays — Next 7 Days</h3>
    </div>
    <div style="padding:16px 24px;display:flex;flex-direction:column;gap:10px;">
        <?php foreach($bday_upcoming as $c):
            $wa_phone = preg_replace('/\D/','',$c['cust_mobile']);
            if(strlen($wa_phone)===10) $wa_phone='91'.$wa_phone;
            $first = ucfirst(strtolower(explode(' ',trim($c['cust_name']))[0]));
            $in_days = (int)$c['days_until'];
            $wa_msg = "🎂 Hi {$first}! Your birthday is just {$in_days} day".($in_days>1?'s':'')." away!\n\nPlan a special pre-birthday treat at *{$salon_nm}* — you deserve to look and feel your best! 💅✨\n\nBook now: {$salon_name_row['salon_contact']}";
        ?>
        <div style="display:flex;align-items:center;gap:16px;padding:14px 16px;background:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;">
            <div style="width:46px;height:46px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#1d4ed8;font-weight:800;font-size:18px;flex-shrink:0;">
                <?= $c['days_until'] ?>d
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($c['cust_name']) ?></div>
                <div style="font-size:12px;color:#64748b;"><?= htmlspecialchars($c['cust_mobile']) ?> &nbsp;|&nbsp; 🎂 <?= $c['bday_display'] ?> <span style="color:#2563eb;font-weight:600;">(in <?= $in_days ?> day<?= $in_days>1?'s':'' ?>)</span></div>
            </div>
            <a href="https://wa.me/<?= $wa_phone ?>?text=<?= rawurlencode($wa_msg) ?>" target="_blank"
               style="background:#2563eb;color:white;padding:8px 16px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:6px;flex-shrink:0;">
                <i class="ph-fill ph-whatsapp-logo"></i> Remind
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if(empty($bday_today) && empty($anniv_today) && empty($bday_upcoming)): ?>
<div style="background:white;border-radius:20px;border:1px solid var(--border-color);padding:60px;text-align:center;">
    <i class="ph ph-smiley" style="font-size:48px;color:#94a3b8;"></i>
    <h3 style="margin:16px 0 8px;color:#475569;">Nothing for Today or This Week</h3>
    <p style="color:#94a3b8;">No birthdays or anniversaries in the next 7 days. Make sure DOB and Anniversary are filled in customer profiles.</p>
</div>
<?php endif; ?>

<script>
function sendBulkBirthday() {
    var links = document.querySelectorAll('[href*="wa.me"]');
    if(!links.length) return;
    var confirmed = confirm('This will open ' + links.length + ' WhatsApp chat windows. Proceed?');
    if(!confirmed) return;
    links.forEach(function(l, i) {
        setTimeout(function(){ window.open(l.href, '_blank'); }, i * 800);
    });
}
</script>

<?php include 'footer.php'; ?>

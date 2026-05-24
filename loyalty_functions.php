<?php
/**
 * Loyalty Engine Helper Functions
 *
 * Tiers are fully configurable per salon in hr_loyalty_tiers table.
 * Points system: % of invoice amount → points (1 point = ₹1 value)
 * Example: Bronze 5% → ₹500 bill = 25 points = ₹25 discount value
 *
 * pkg and wallet payments do NOT earn points (already discounted).
 */

/**
 * Get all tiers for a salon from DB, ordered by min_spend ASC
 */
function get_salon_tiers(int $salon_id): array {
    $rows = select_array("SELECT * FROM hr_loyalty_tiers WHERE salon_id='$salon_id' ORDER BY min_spend ASC");
    return $rows ?: [];
}

/**
 * Resolve which tier a customer is in based on their lifetime spend
 * Returns the matching tier row from DB (or a safe default if none configured)
 */
function get_customer_tier_db(float $lifetime_spend, int $salon_id): array {
    $tiers = get_salon_tiers($salon_id);
    if (empty($tiers)) {
        // Safe fallback if no tiers configured yet
        return ['tier_name'=>'Bronze','cashback_percent'=>5,'color'=>'#9a3412','bg_color'=>'#fff7ed','icon'=>'ph-star','min_spend'=>0,'next_spend'=>null];
    }
    $matched = $tiers[0];
    foreach ($tiers as $tier) {
        if ($lifetime_spend >= (float)$tier['min_spend']) {
            $matched = $tier;
        }
    }
    // Compute next threshold
    $found_current = false;
    $matched['next_spend'] = null;
    foreach ($tiers as $tier) {
        if ($found_current) { $matched['next_spend'] = (float)$tier['min_spend']; break; }
        if ($tier['tier_id'] == $matched['tier_id']) $found_current = true;
    }
    return $matched;
}

/** Alias for backwards compatibility */
function get_customer_tier(float $lifetime_spend, int $salon_id = 0): array {
    global $salon_id;
    $sid = $salon_id > 0 ? $salon_id : (int)($GLOBALS['salon_id'] ?? 0);
    $t = get_customer_tier_db($lifetime_spend, $sid);
    return [
        'name'             => $t['tier_name'],
        'cashback_percent' => (float)$t['cashback_percent'],
        'bg'               => $t['bg_color'],
        'color'            => $t['color'],
        'icon'             => $t['icon'],
        'next'             => $t['next_spend'],
    ];
}

function get_customer_points_balance(int $cust_id): float {
    $res = select_row("SELECT COALESCE(
        SUM(CASE WHEN type='earn' THEN points ELSE 0 END) -
        SUM(CASE WHEN type IN ('redeem','expire') THEN points ELSE 0 END), 0
    ) as balance FROM hr_customer_points WHERE cust_id='$cust_id'");
    return max(0, (float)($res['balance'] ?? 0));
}

function get_customer_lifetime_spend(int $cust_id): float {
    $res = select_row("SELECT COALESCE(SUM(grand_total),0) as total
        FROM hr_invoice WHERE cust_id='$cust_id' AND delete_bill=0");
    return (float)($res['total'] ?? 0);
}

/**
 * Award cashback points on a paid invoice.
 * Points = (grand_total * cashback_percent / 100), rounded to 2 decimals.
 * 1 point = ₹1 discount value.
 */
function award_points(int $cust_id, int $salon_id, float $grand_total, int $invoice_id): float {
    if ($grand_total <= 0) return 0;

    $lifetime = get_customer_lifetime_spend($cust_id);
    $tier     = get_customer_tier_db($lifetime, $salon_id);

    $pct     = (float)$tier['cashback_percent'];
    $earned  = round($grand_total * $pct / 100, 2);
    if ($earned <= 0) return 0;

    $expiry    = date('Y-m-d', strtotime('+12 months'));
    $tier_name = $tier['tier_name'];

    insert_query("INSERT INTO hr_customer_points SET
        salon_id='$salon_id', cust_id='$cust_id', invoice_id='$invoice_id',
        points='$earned', type='earn',
        remark='Cashback {$pct}% on Invoice #$invoice_id ($tier_name Tier)',
        expiry_date='$expiry'");

    return $earned;
}

/**
 * Redeem points as discount. 1 point = ₹1.
 */
function redeem_points(int $cust_id, int $salon_id, float $points_to_use, int $invoice_id): float {
    $balance       = get_customer_points_balance($cust_id);
    $actual_redeem = round(min($points_to_use, $balance), 2);
    if ($actual_redeem <= 0) return 0;

    insert_query("INSERT INTO hr_customer_points SET
        salon_id='$salon_id', cust_id='$cust_id', invoice_id='$invoice_id',
        points='$actual_redeem', type='redeem',
        remark='Redeemed on Invoice #$invoice_id'");

    insert_query("INSERT INTO hr_loyalty_redemptions SET
        salon_id='$salon_id', cust_id='$cust_id', invoice_id='$invoice_id',
        points_used='$actual_redeem', discount_amount='$actual_redeem'");

    return $actual_redeem;
}

/**
 * Expire points whose expiry_date has passed. Call from cron or manual trigger.
 */
function expire_old_points(): void {
    // For each customer, check if their total expired earnings exceed their total deductions (redeem + expire)
    $custs = select_array("SELECT DISTINCT cust_id, salon_id FROM hr_customer_points WHERE type='earn' AND expiry_date < CURDATE()");
    
    foreach ($custs as $row) {
        $cust_id  = (int)$row['cust_id'];
        $salon_id = (int)$row['salon_id'];
        
        $expired_earn = (float)(select_row("SELECT SUM(points) as pts FROM hr_customer_points WHERE cust_id='$cust_id' AND type='earn' AND expiry_date < CURDATE()")['pts'] ?? 0);
        $deductions = (float)(select_row("SELECT SUM(points) as pts FROM hr_customer_points WHERE cust_id='$cust_id' AND type IN ('redeem', 'expire')")['pts'] ?? 0);
        
        $net_expired = $expired_earn - $deductions;
        
        if ($net_expired > 0) {
            $balance = get_customer_points_balance($cust_id);
            $to_expire = min($net_expired, $balance);
            if ($to_expire > 0) {
                insert_query("INSERT INTO hr_customer_points SET
                    salon_id='$salon_id', cust_id='$cust_id',
                    points='$to_expire', type='expire',
                    remark='Points expired (past 12 months)'");
            }
        }
    }
}

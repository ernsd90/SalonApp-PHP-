<?php
$content = file_get_contents('invoices.php');

$start = strpos($content, '<div class="metrics-grid"');
if ($start !== false) {
    // 6 metric-cards exist.
    $end = strpos($content, '<style>', $start);
    if ($end !== false) {
        $pre = substr($content, 0, $start);
        $post = substr($content, $end);

$newTiles = <<<EOT
<!-- Overview Cards -->
<div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 32px;">
    
    <div class="metric-card" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; border-radius: 20px; padding: 20px; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);">
        <p style="margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; opacity: 0.8;">Total Revenue</p>
        <h2 id="sum_grand_total" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700;">₹0</h2>
        <div style="font-size: 12px; margin-top: 8px; opacity: 0.9; display: flex; gap: 8px;">
            <span>Cash: <b id="sum_grand_cash">₹0</b></span> | 
            <span>Online/CC: <b id="sum_grand_cc">₹0</b></span>
        </div>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Service Sales</p>
        <h2 id="sum_service_total" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text-main);">₹0</h2>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Product Sales</p>
        <h2 id="sum_product_total" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text-main);">₹0</h2>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Redumtion (Membership & Pkg)</p>
        <h2 id="sum_reduction_sale" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text-main);">₹0</h2>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Membership & Package Sale</p>
        <h2 id="sum_membership_pkg" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text-main);">₹0</h2>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Total Customers</p>
        <h2 id="sum_customers" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--success);">0</h2>
    </div>
</div>\n
EOT;

        $content = $pre . $newTiles . $post;
        file_put_contents('invoices.php', $content);
        echo "Tiles replaced successfully!\n";
    }
} else {
    echo "Tile anchor not found\n";
}
?>

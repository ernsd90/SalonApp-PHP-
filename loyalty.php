<?php
include 'header.php';
include_once 'loyalty_functions.php';

$tiers = get_salon_tiers((int)$salon_id);
$ls = select_row("SELECT * FROM hr_loyalty_settings WHERE salon_id='$salon_id'");
if (!$ls) {
    insert_query("INSERT IGNORE INTO hr_loyalty_settings (salon_id,loyalty_enabled,profile_complete_points) VALUES ('$salon_id',1,50)");
    $ls = ['loyalty_enabled'=>1,'profile_complete_points'=>50];
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;margin-bottom:4px;">Loyalty & Rewards Program</h1>
    <p style="color:var(--text-muted);font-size:14px;">Configure your tiers and track customer points, cashback, and redemption history.</p>
</div>

<!-- Tabs -->
<div style="display:flex;gap:8px;margin-bottom:24px;border-bottom:2px solid var(--border-color);padding-bottom:0;">
    <?php foreach([['tab-overview','📊 Overview'],['tab-tiers','⚙️ Tiers'],['tab-customers','👥 Customer Ledger'],['tab-settings','🔧 Settings']] as $i=>[$tid,$tlbl]): ?>
    <button class="tab-btn" data-tab="<?=$tid?>" style="padding:12px 20px;border:none;background:none;font-weight:600;font-size:14px;cursor:pointer;border-bottom:3px solid <?=$i===0?'var(--primary)':'transparent'?>;color:<?=$i===0?'var(--primary)':'var(--text-muted)'?>;margin-bottom:-2px;border-radius:8px 8px 0 0;transition:.2s;"><?=$tlbl?></button>
    <?php endforeach; ?>
</div>

<!-- TAB 1: Overview & KPIs -->
<div id="tab-overview" class="tab-panel">

    <!-- Live Tier Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;">
        <?php foreach($tiers as $t):
            $next = null;
            foreach($tiers as $nt) { if((float)$nt['min_spend'] > (float)$t['min_spend']) { $next = $nt; break; } }
        ?>
        <div style="background:<?=$t['bg_color']?>;border:1.5px solid <?=$t['color']?>33;border-radius:16px;padding:20px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                <i class="ph-fill <?=$t['icon']?>" style="font-size:22px;color:<?=$t['color']?>;"></i>
                <span style="font-weight:800;font-size:15px;color:<?=$t['color']?>;"><?=htmlspecialchars($t['tier_name'])?></span>
                <span style="margin-left:auto;background:<?=$t['color']?>22;color:<?=$t['color']?>;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?=$t['cashback_percent']?>%</span>
            </div>
            <div style="font-size:12px;color:<?=$t['color']?>;font-weight:600;">From ₹<?=number_format($t['min_spend'],0)?><?=$next?' – ₹'.number_format($next['min_spend']-1,0):'+' ?></div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;"><?=$t['cashback_percent']?>% of bill → points &nbsp;|&nbsp; 1pt = ₹1</div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- KPIs -->
    <?php
    $kpis = select_row("SELECT
        COUNT(DISTINCT cust_id) as total_members,
        SUM(CASE WHEN type='earn'   THEN points ELSE 0 END) as total_earned,
        SUM(CASE WHEN type='redeem' THEN points ELSE 0 END) as total_redeemed,
        SUM(CASE WHEN type='expire' THEN points ELSE 0 END) as total_expired
        FROM hr_customer_points WHERE salon_id='$salon_id'");
    $outstanding = max(0, (float)($kpis['total_earned']??0) - (float)($kpis['total_redeemed']??0) - (float)($kpis['total_expired']??0));
    ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">
        <div style="background:#1e293b;color:white;padding:24px;border-radius:18px;">
            <div style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Members with Points</div>
            <div style="font-size:36px;font-weight:800;"><?=number_format($kpis['total_members']??0)?></div>
        </div>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:24px;border-radius:18px;">
            <div style="font-size:11px;color:#16a34a;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Total Points Issued</div>
            <div style="font-size:36px;font-weight:800;color:#14532d;"><?=number_format($kpis['total_earned']??0,0)?></div>
        </div>
        <div style="background:#eff6ff;border:1px solid #bfdbfe;padding:24px;border-radius:18px;">
            <div style="font-size:11px;color:#1d4ed8;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Points Redeemed</div>
            <div style="font-size:36px;font-weight:800;color:#1e3a8a;"><?=number_format($kpis['total_redeemed']??0,0)?></div>
        </div>
        <div style="background:#fef2f2;border:1px solid #fecaca;padding:24px;border-radius:18px;">
            <div style="font-size:11px;color:#dc2626;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Outstanding Liability (₹)</div>
            <div style="font-size:36px;font-weight:800;color:#7f1d1d;">₹<?=number_format($outstanding,0)?></div>
        </div>
    </div>
</div>

<!-- TAB 2: Tier Configuration -->
<div id="tab-tiers" class="tab-panel" style="display:none;">
    <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

        <!-- Existing Tiers List -->
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;">
            <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);">
                <h3 style="margin:0;font-size:16px;font-weight:700;">Your Loyalty Tiers</h3>
                <p style="margin:4px 0 0;font-size:13px;color:var(--text-muted);">Customers are automatically assigned based on their lifetime spend.</p>
            </div>
            <div style="padding:20px 24px;" id="tiers-list">
                <?php foreach($tiers as $t): ?>
                <div class="tier-row" data-id="<?=$t['tier_id']?>" style="display:flex;align-items:center;gap:16px;padding:16px;background:<?=$t['bg_color']?>;border-radius:14px;margin-bottom:12px;border:1px solid <?=$t['color']?>22;">
                    <div style="width:44px;height:44px;border-radius:12px;background:<?=$t['color']?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="ph-fill <?=$t['icon']?>" style="font-size:22px;color:<?=$t['color']?>;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:700;color:<?=$t['color']?>;font-size:15px;"><?=htmlspecialchars($t['tier_name'])?></div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;">From ₹<?=number_format($t['min_spend'],0)?> lifetime spend &nbsp;|&nbsp; <?=$t['cashback_percent']?>% cashback</div>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button onclick="editTier(<?=$t['tier_id']?>)" style="background:#e0e7ff;color:#4f46e5;border:none;padding:6px 12px;border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;"><i class="ph ph-pencil-simple"></i></button>
                        <button onclick="deleteTier(<?=$t['tier_id']?>, '<?=htmlspecialchars($t['tier_name'])?>')" style="background:#fee2e2;color:#dc2626;border:none;padding:6px 12px;border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;"><i class="ph ph-trash"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($tiers)): ?>
                <p style="color:var(--text-muted);text-align:center;padding:20px;">No tiers configured yet. Add your first tier →</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add / Edit Tier Form -->
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:24px;position:sticky;top:20px;">
            <h3 id="tier-form-title" style="font-size:16px;font-weight:700;margin:0 0 20px;">Add New Tier</h3>
            <input type="hidden" id="edit_tier_id" value="">

            <div class="form-group">
                <label>Tier Name <span style="color:red">*</span></label>
                <input type="text" id="tf_name" class="form-control" placeholder="e.g. Bronze, Silver, Gold">
            </div>
            <div class="form-group">
                <label>Minimum Lifetime Spend (₹) <span style="color:red">*</span></label>
                <input type="number" id="tf_min_spend" class="form-control" min="0" value="0" placeholder="0 = all customers qualify">
            </div>
            <div class="form-group">
                <label>Cashback % on each bill <span style="color:red">*</span></label>
                <div style="position:relative;">
                    <input type="number" id="tf_pct" class="form-control" step="0.5" min="0" max="100" placeholder="e.g. 5" style="padding-right:40px;">
                    <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:700;">%</span>
                </div>
                <p style="font-size:12px;color:var(--text-muted);margin-top:4px;"><i class="ph ph-info"></i> e.g. 5% on ₹1000 bill = 50 points = ₹50 discount value</p>
            </div>
            <div class="form-group">
                <label>Accent Colour</label>
                <div style="display:flex;gap:10px;align-items:center;">
                    <input type="color" id="tf_color" value="#9a3412" style="width:48px;height:40px;border:1px solid var(--border-color);border-radius:8px;cursor:pointer;padding:2px;">
                    <span style="font-size:13px;color:var(--text-muted);">Used for badge text and icons</span>
                </div>
            </div>
            <div class="form-group">
                <label>Background Colour</label>
                <div style="display:flex;gap:10px;align-items:center;">
                    <input type="color" id="tf_bg" value="#fff7ed" style="width:48px;height:40px;border:1px solid var(--border-color);border-radius:8px;cursor:pointer;padding:2px;">
                    <span style="font-size:13px;color:var(--text-muted);">Used for tier card background</span>
                </div>
            </div>
            <div class="form-group">
                <label>Icon Class (Phosphor)</label>
                <select id="tf_icon" class="form-control">
                    <option value="ph-star">⭐ Star</option>
                    <option value="ph-star-half">🌟 Star Half</option>
                    <option value="ph-medal">🥇 Medal</option>
                    <option value="ph-crown-simple">👑 Crown</option>
                    <option value="ph-diamond">💎 Diamond</option>
                    <option value="ph-trophy">🏆 Trophy</option>
                    <option value="ph-lightning">⚡ Lightning</option>
                    <option value="ph-fire">🔥 Fire</option>
                    <option value="ph-heart">❤️ Heart</option>
                </select>
            </div>

            <div style="display:flex;gap:10px;margin-top:4px;">
                <button onclick="saveTier()" class="btn-primary" style="flex:1;margin:0;padding:12px;">
                    <i class="ph ph-floppy-disk"></i> Save Tier
                </button>
                <button onclick="resetTierForm()" class="btn-secondary" style="width:auto;margin:0;padding:12px 16px;">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TAB 3: Customer Ledger -->
<div id="tab-customers" class="tab-panel" style="display:none;">
    <div style="background:white;border-radius:20px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:16px;font-weight:700;">Customer Points Ledger</h3>
            <button id="btn_expire_points" class="btn-secondary" style="margin:0;padding:8px 16px;width:auto;font-size:13px;display:flex;align-items:center;gap:6px;">
                <i class="ph ph-clock-counter-clockwise"></i> Expire Old Points
            </button>
        </div>
        <div style="padding:24px;">
            <table id="loyalty_table" class="table-modern" style="width:100%;">
                <thead>
                    <tr>
                        <th>Customer</th><th>Mobile</th><th>Tier</th>
                        <th>Points Balance</th><th>Lifetime Spend</th><th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Point History Modal -->
<div class="modal-overlay" id="pointsModalOverlay">
    <div class="modal-dialog" id="pointsModalContent" style="max-width:700px;max-height:90vh;overflow-y:auto;"></div>
</div>

<style>
.table-modern{width:100%;border-collapse:separate;border-spacing:0;}
.table-modern th{background:#f8fafc;color:var(--text-muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:14px 16px;border-bottom:1px solid #e2e8f0;text-align:left;}
.table-modern td{padding:14px 16px;font-size:14px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.table-modern tbody tr:hover td{background:#f8fafc;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal-dialog{background:white;border-radius:20px;width:100%;max-width:600px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);animation:fadeUp .3s ease-out;}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.tab-btn:hover{background:#f8fafc;}
</style>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
// ─── Tabs ──────────────────────────────────────────────────────────────────
$('.tab-btn').click(function(){
    var t = $(this).data('tab');
    $('.tab-panel').hide(); $('#'+t).show();
    $('.tab-btn').css({'border-bottom-color':'transparent','color':'var(--text-muted)'});
    $(this).css({'border-bottom-color':'var(--primary)','color':'var(--primary)'});
    if(t === 'tab-customers' && !$.fn.DataTable.isDataTable('#loyalty_table')) initTable();
});

// ─── Tier CRUD ─────────────────────────────────────────────────────────────
function saveTier(){
    var name = $('#tf_name').val().trim();
    var min  = $('#tf_min_spend').val();
    var pct  = $('#tf_pct').val();
    if(!name || pct==='') { alert('Please fill in Tier Name and Cashback %.'); return; }

    var data = {
        method: 'save_tier',
        tier_id:  $('#edit_tier_id').val(),
        tier_name: name,
        min_spend: min,
        cashback_percent: pct,
        color: $('#tf_color').val(),
        bg_color: $('#tf_bg').val(),
        icon: $('#tf_icon').val()
    };
    $.post('ajax/loyalty_ajax.php', data, function(res){
        var r = JSON.parse(res);
        if(r.error) { alert('Error: ' + r.msg); return; }
        alert(r.msg);
        location.reload();
    });
}

function editTier(tid){
    $.post('ajax/loyalty_ajax.php', { method:'get_tier', tier_id: tid }, function(res){
        var t = JSON.parse(res);
        $('#edit_tier_id').val(t.tier_id);
        $('#tf_name').val(t.tier_name);
        $('#tf_min_spend').val(t.min_spend);
        $('#tf_pct').val(t.cashback_percent);
        $('#tf_color').val(t.color);
        $('#tf_bg').val(t.bg_color);
        $('#tf_icon').val(t.icon);
        $('#tier-form-title').text('Edit Tier: ' + t.tier_name);
        // Scroll to form
        $('html,body').animate({scrollTop: $('#tier-form-title').offset().top - 80}, 300);
    });
}

function deleteTier(tid, name){
    if(!confirm('Delete tier "' + name + '"? This will not affect existing points records.')) return;
    $.post('ajax/loyalty_ajax.php', { method:'delete_tier', tier_id: tid }, function(res){
        var r = JSON.parse(res);
        if(r.error){ alert('Error: '+r.msg); return; }
        alert(r.msg);
        location.reload();
    });
}

function resetTierForm(){
    $('#edit_tier_id').val('');
    $('#tf_name,#tf_pct').val('');
    $('#tf_min_spend').val('0');
    $('#tf_color').val('#9a3412');
    $('#tf_bg').val('#fff7ed');
    $('#tf_icon').val('ph-star');
    $('#tier-form-title').text('Add New Tier');
}

// ─── Customer Ledger DataTable ─────────────────────────────────────────────
var tbl;
function initTable(){
    tbl = $('#loyalty_table').DataTable({
        processing: true, serverSide: true, responsive: true, pageLength: 25,
        ajax: { url: 'ajax/loyalty_ajax.php', type: 'POST', data: { method: 'get_loyalty_list' }},
        columns: [
            { data: 'customer_info', orderable: false },
            { data: 'mobile' },
            { data: 'tier', orderable: false },
            { data: 'points_display', orderable: false },
            { data: 'lifetime_spend', orderable: false },
            { data: 'action', orderable: false }
        ]
    });
}

// ─── Modal & History ───────────────────────────────────────────────────────
$(document).on('click', '.btn-view-history', function(){
    var cust_id = $(this).data('cust');
    $('#pointsModalContent').html('<div style="padding:40px;text-align:center;"><i class="ph ph-spinner ph-spin" style="font-size:32px;color:var(--primary);"></i></div>');
    $('#pointsModalOverlay').addClass('active');
    $.ajax({ url: 'ajax/loyalty_ajax.php', type: 'POST', data: { method: 'get_point_history', cust_id: cust_id },
        success: function(res){ $('#pointsModalContent').html(res); }});
});
$(document).on('click', '.close-modal', function(){ $('#pointsModalOverlay').removeClass('active'); });

$(document).on('click', '.btn-loyalty-block', function(){
    if(!confirm('Block this customer from earning and redeeming loyalty points?')) return;
    var cust_id = $(this).data('cust');
    $.post('ajax/loyalty_ajax.php', { method: 'block_loyalty_customer', cust_id: cust_id, status: 1 }, function(res){
        var r = JSON.parse(res);
        if(!r.error && tbl) tbl.draw(false);
    });
});

$(document).on('click', '.btn-loyalty-unblock', function(){
    if(!confirm('Unblock this customer? They will be able to earn and redeem points again.')) return;
    var cust_id = $(this).data('cust');
    $.post('ajax/loyalty_ajax.php', { method: 'block_loyalty_customer', cust_id: cust_id, status: 0 }, function(res){
        var r = JSON.parse(res);
        if(!r.error && tbl) tbl.draw(false);
    });
});

$('#btn_expire_points').click(function(){
    var confirmText = prompt('This will permanently deduct all points that have passed their 12-month expiry date.\n\nType "EXPIRE" to confirm:');
    if (confirmText !== 'EXPIRE') {
        if (confirmText !== null) alert('Action cancelled: Incorrect confirmation word.');
        return;
    }
    var btn = $(this); btn.prop('disabled',true).html('<i class="ph ph-spinner ph-spin"></i> Processing...');
    $.post('ajax/loyalty_ajax.php', { method:'run_expire_points' }, function(res){
        var r = JSON.parse(res);
        alert(r.msg);
        btn.prop('disabled',false).html('<i class="ph ph-clock-counter-clockwise"></i> Expire Old Points');
        if(tbl) tbl.draw();
    });
});

// ─── Settings Toggle ───────────────────────────────────────────────────────
$(document).on('change', '#ls_enabled', function(){
    var on = $(this).is(':checked');
    $('#ls_toggle_track').css('background', on ? '#22c55e' : '#cbd5e1');
    $('#ls_toggle_knob').css('left', on ? '29px' : '3px');
});

function saveLoyaltySettings(){
    var enabled = $('#ls_enabled').is(':checked') ? 1 : 0;
    var pts     = parseInt($('#ls_profile_pts').val()) || 0;
    $.post('ajax/loyalty_ajax.php', { method:'save_loyalty_settings', loyalty_enabled:enabled, profile_complete_points:pts }, function(res){
        var r = JSON.parse(res);
        if(r.error){ alert('Error: '+r.msg); return; }
        var badge = enabled
            ? '<span style="background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">✅ Loyalty ACTIVE</span>'
            : '<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">⛔ Loyalty DISABLED</span>';
        alert(r.msg + '\n\nStatus: ' + (enabled ? 'ACTIVE ✅' : 'DISABLED ⛔'));
    });
}
</script>

<!-- TAB 4: Settings -->
<div id="tab-settings" class="tab-panel" style="display:none;">
    <div style="max-width:600px;">
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:28px;margin-bottom:24px;">
            <h3 style="font-size:16px;font-weight:800;margin:0 0 20px;display:flex;align-items:center;gap:8px;"><i class="ph-fill ph-toggle-right" style="color:var(--primary);"></i> Loyalty Program Status</h3>

            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;background:#f8fafc;border-radius:14px;border:1px solid var(--border-color);">
                <div>
                    <div style="font-weight:700;font-size:15px;">Enable Loyalty & Rewards</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">When disabled, no points are earned or redeemed at checkout.</div>
                </div>
                <label style="position:relative;width:56px;height:30px;cursor:pointer;flex-shrink:0;">
                    <input type="checkbox" id="ls_enabled" <?= $ls['loyalty_enabled']?'checked':'' ?> style="opacity:0;width:0;height:0;">
                    <span id="ls_toggle_track" style="position:absolute;inset:0;border-radius:30px;background:<?= $ls['loyalty_enabled']?'#22c55e':'#cbd5e1' ?>;transition:.3s;">
                        <span id="ls_toggle_knob" style="position:absolute;top:3px;left:<?= $ls['loyalty_enabled']?'29':'3' ?>px;width:24px;height:24px;background:white;border-radius:50%;box-shadow:0 2px 4px rgba(0,0,0,.2);transition:.3s;"></span>
                    </span>
                </label>
            </div>
        </div>

        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:28px;margin-bottom:24px;">
            <h3 style="font-size:16px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:8px;"><i class="ph-fill ph-user-circle-check" style="color:#7c3aed;"></i> Profile Completion Bonus</h3>
            <p style="font-size:13px;color:var(--text-muted);margin:0 0 20px;">Reward customers who fill in their Date of Birth, Anniversary, and Gender. Points are awarded once per customer.</p>

            <div class="form-group">
                <label style="font-weight:700;">Bonus Points Awarded</label>
                <div style="display:flex;gap:12px;align-items:center;">
                    <input type="number" id="ls_profile_pts" class="form-control" style="max-width:160px;" value="<?= (int)$ls['profile_complete_points'] ?>" min="0" max="10000" placeholder="e.g. 50">
                    <span style="font-size:13px;color:var(--text-muted);">pts (= ₹<?= (int)$ls['profile_complete_points'] ?> discount value)</span>
                </div>
                <p style="font-size:12px;color:var(--text-muted);margin-top:6px;"><i class="ph ph-info"></i> Set to 0 to disable the profile completion bonus.</p>
            </div>
        </div>

        <button onclick="saveLoyaltySettings()" class="btn-primary" style="width:auto;padding:12px 32px;">
            <i class="ph ph-floppy-disk"></i> Save Settings
        </button>
    </div>
</div>

<?php include 'footer.php'; ?>

<?php include 'header.php'; ?>

<!-- DataTables Required CSS/JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Staff Members (Stylists)</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Manage the stylists and employees who perform services at your salon.</p>
</div>

<!-- Global Monthly Target Card -->
<div class="card-modern" id="target_card" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border-radius: var(--border-radius); border: none; box-shadow: 0 4px 20px rgba(99,102,241,0.3); overflow: hidden; margin-bottom: 24px;">
    <!-- Row 1: Multiplier -->
    <div style="padding: 20px 24px 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <i class="ph-bold ph-target" style="font-size: 22px; color: rgba(255,255,255,0.9);"></i>
                <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: white;">Global Monthly Revenue Target</h3>
            </div>
            <p style="color: rgba(255,255,255,0.75); font-size: 13px; margin: 0;">
                Target = <strong style="color:white;">N × each staff's salary</strong>. Only the ticked revenue types below count toward achievement.
            </p>
            <div id="target_preview" style="margin-top: 6px; color: rgba(255,255,255,0.6); font-size: 12px;"></div>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 10px 16px; display: flex; align-items: center; gap: 10px;">
                <span style="color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 600; white-space: nowrap;">Salary ×</span>
                <input type="number" id="global_target_input" placeholder="5" min="0.5" max="50" step="0.5" value="5"
                    oninput="updateTargetPreview()"
                    style="padding: 8px 12px; border-radius: 8px; border: none; font-size: 20px; font-weight: 800; width: 90px; outline: none; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); color: #6366f1;">
                <span style="color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 600;">= Target</span>
            </div>
            <button onclick="saveGlobalTarget()" id="save_target_btn"
                style="background: white; color: #6366f1; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 14px; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                <i class="ph ph-floppy-disk"></i> Save
            </button>
        </div>
    </div>

    <!-- Row 2: Revenue components that count towards target -->
    <div style="padding: 0 24px 18px;">
        <div style="background: rgba(0,0,0,0.15); border-radius: 10px; padding: 12px 16px;">
            <div style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 10px;">
                <i class="ph ph-check-square"></i> Count towards target achievement:
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;" id="component_chips">
                <?php
                $all_components = [
                    'services'    => ['label' => 'Services',    'icon' => 'ph-scissors'],
                    'redemptions' => ['label' => 'Redemptions', 'icon' => 'ph-wallet'],
                    'packages'    => ['label' => 'Packages',    'icon' => 'ph-package'],
                    'memberships' => ['label' => 'Memberships', 'icon' => 'ph-crown'],
                    'products'    => ['label' => 'Products',    'icon' => 'ph-shopping-bag'],
                ];
                foreach ($all_components as $key => $comp): ?>
                <label id="chip_<?= $key ?>" onclick="toggleChip('<?= $key ?>', this)" style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; background: rgba(255,255,255,0.18); border: 1.5px solid rgba(255,255,255,0.3); color: white; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; transition: all 0.2s; user-select:none;">
                    <input type="checkbox" name="target_component" value="<?= $key ?>" id="chk_<?= $key ?>" style="display:none;" checked>
                    <i class="ph <?= $comp['icon'] ?>"></i>
                    <?= $comp['label'] ?>
                    <i class="ph ph-check chip_check_icon" style="font-size:12px; margin-left:2px;"></i>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="target_feedback" style="padding: 0 24px 12px; font-size: 13px; color: rgba(255,255,255,0.85); display: none;"></div>
</div>



<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px;">
    
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Stylist Directory</h3>
        <button class="btn-primary" style="width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;" onclick="loadModal('staff_edit.php');">
            <i class="ph-bold ph-plus"></i> Add Stylist
        </button>
    </div>

    <div style="padding: 24px;">
        <div class="table-responsive">
            <table id="get_staff" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Name</th>
                        <th>Mobile No.</th>
                        <th>Joining Date</th>
                        <th>Salary/Commission</th>
                        <th>Notification</th>
                        <th>Status</th>
                        <th style="width: 160px;">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<style>
/* Modern Table Reset */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern th { background: #f8fafc; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 1px solid var(--border-color); text-align: left; }
.table-modern td { padding: 16px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border-color); vertical-align: middle; }
.table-modern tbody tr:hover td { background: #f8fafc; }

/* Modal Overlay */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: fadeUp 0.3s ease-out forwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

<!-- Custom V3 Modal Wrapper -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent">
        <!-- Content loaded via Ajax -->
    </div>
</div>

<script>
$(document).ready(function() {
    var get_staff = $('#get_staff').DataTable({
        "processing": true,
        "serverSide": true,
        responsive: true,
        "ajax": {
            "url": "ajax/user_ajax.php",
            "type": "POST",
            "data": { "method": "get_staff" }
        },
        "columns": [
            { "data": "staff_id" },
            { 
               "data": "staff_name",
               "render": function(data) {
                   return '<span style="font-weight: 600; color: var(--text-main);">' + data + '</span>';
               }
            },
            { "data": "staff_mob" },
            { "data": "joining_date" },
            { 
               "data": "staff_salary",
               "render": function(data) {
                   return '<span style="color: var(--primary); font-weight: 600;">₹' + data + '</span>';
               }
            },
            { 
               "data": "notify_daily_sale",
               "render": function(data) {
                   if(data == 1) return '<span style="padding: 3px 8px; background: #ede9fe; color: #7c3aed; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="ph ph-bell-ringing"></i> On</span>';
                   return '<span style="padding: 3px 8px; background: #f1f5f9; color: #94a3b8; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="ph ph-bell-slash"></i> Off</span>';
               }
            },
            { 
               "data": "staff_status",
               "render": function(data) {
                   if(data == 1) return '<span style="padding: 4px 10px; background: #dcfce7; color: #16a34a; border-radius: 20px; font-size: 12px; font-weight: 600;">Active</span>';
                   return '<span style="padding: 4px 10px; background: #fee2e2; color: #dc2626; border-radius: 20px; font-size: 12px; font-weight: 600;">Inactive</span>';
               }
            },
            { 
               "data": "action",
               "render": function(data) {
                   return data.replace(/btn-gradient-info/g, 'btn-edit').replace(/btn-gradient-danger/g, 'btn-delete');
               }
            }
        ]
    });

    // Load existing multiplier + components
    $.post('ajax/user_ajax.php', { method: 'get_global_target' }, function(res) {
        try {
            var d = JSON.parse(res);
            if (d.multiplier) {
                $('#global_target_input').val(d.multiplier);
                updateTargetPreview();
            }
            if (d.components) {
                var active = d.components.split(',');
                // First deselect all, then select matching
                ['services','redemptions','packages','memberships','products'].forEach(function(k) {
                    var isOn = active.indexOf(k) !== -1;
                    setChipState(k, isOn);
                });
            }
        } catch(e) {}
    });

    $(document).on('click', '.close-modal', function(){
        $('#commonModalOverlay').removeClass('active');
    });
});

function updateTargetPreview() {
    var mult = parseFloat($('#global_target_input').val()) || 5;
    $('#target_preview').text('e.g. Staff with ₹10,000 salary → Monthly target ₹' + (10000 * mult).toLocaleString('en-IN'));
}

// ── Chip toggle ──────────────────────────────────────────────
function setChipState(key, isOn) {
    var chk = document.getElementById('chk_' + key);
    var chip = document.getElementById('chip_' + key);
    if (!chk || !chip) return;
    chk.checked = isOn;
    if (isOn) {
        chip.style.background = 'rgba(255,255,255,0.85)';
        chip.style.color = '#6366f1';
        chip.style.borderColor = 'white';
        chip.style.fontWeight = '700';
    } else {
        chip.style.background = 'rgba(255,255,255,0.07)';
        chip.style.color = 'rgba(255,255,255,0.45)';
        chip.style.borderColor = 'rgba(255,255,255,0.15)';
        chip.style.fontWeight = '500';
    }
}

function toggleChip(key, labelEl) {
    var chk = document.getElementById('chk_' + key);
    setChipState(key, !chk.checked);
}

// ── Save ─────────────────────────────────────────────────────
function saveGlobalTarget() {
    var multiplier = $('#global_target_input').val();
    if (!multiplier || isNaN(multiplier) || parseFloat(multiplier) <= 0) {
        showTargetFeedback('⚠ Please enter a valid multiplier (e.g. 5 for 5× salary).', false);
        return;
    }
    // Collect ticked components
    var selected = [];
    $('input[name="target_component"]:checked').each(function() { selected.push($(this).val()); });
    if (selected.length === 0) {
        showTargetFeedback('⚠ Please select at least one revenue type to count towards target.', false);
        return;
    }
    var btn = $('#save_target_btn');
    btn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);
    $.post('ajax/user_ajax.php', { method: 'save_global_target', multiplier: multiplier, components: selected.join(',') }, function(res) {
        btn.html('<i class="ph ph-floppy-disk"></i> Save').prop('disabled', false);
        try {
            var d = JSON.parse(res);
            if (!d.error) {
                var labels = { services: 'Services', redemptions: 'Redemptions', packages: 'Packages', memberships: 'Memberships', products: 'Products' };
                var names = selected.map(function(k) { return labels[k] || k; }).join(' + ');
                showTargetFeedback('✔ Saved! Target = ' + multiplier + '× salary · Counts: ' + names, true);
            } else {
                showTargetFeedback('✖ ' + d.msg, false);
            }
        } catch(e) {
            showTargetFeedback('Saved!', true);
        }
    });
}

function showTargetFeedback(msg, success) {
    var el = $('#target_feedback');
    el.text(msg).show();
    setTimeout(function(){ el.fadeOut(); }, 4000);
}

function sendStaffReportWa(btn, staffId) {
    var $btn = $(btn);
    var originalHtml = $btn.html();
    
    if (!confirm("Are you sure you want to send the performance report to this staff member via WhatsApp?")) {
        return;
    }
    
    $btn.html('<i class="ph ph-spinner ph-spin"></i> Sending...').prop('disabled', true);
    
    $.post('ajax/user_ajax.php', { method: 'send_staff_report_wa', staff_id: staffId }, function(res) {
        $btn.html(originalHtml).prop('disabled', false);
        try {
            var d = JSON.parse(res);
            if (!d.error) {
                alert(d.msg);
            } else {
                alert("Error: " + d.msg);
            }
        } catch(e) {
            alert("Unexpected response from server: " + res);
        }
    }).fail(function() {
        $btn.html(originalHtml).prop('disabled', false);
        alert("A network error occurred. Please try again.");
    });
}

function loadModal(url) {
    $('#commonModalContent').html('<div style="padding: 40px; text-align: center;"><i class="ph ph-spinner ph-spin" style="font-size: 32px; color: var(--primary);"></i><p>Loading...</p></div>');
    $('#commonModalOverlay').addClass('active');
    
    $.ajax({
        url: url,
        success: function(data) {
            $('#commonModalContent').html(data);
        },
        error: function() {
            $('#commonModalContent').html('<div style="padding: 24px; text-align: center; color: red;">Failed to load data. <button class="close-modal btn-primary" style="margin-top:16px;">Close</button></div>');
        }
    });
}

$(document).on('click', '.modalButtonCommon', function(e){
    e.preventDefault();
    var targetUrl = $(this).attr('data-href');
    if(targetUrl) loadModal(targetUrl);
});

$(document).on('submit', 'form.ajax-form', function(e){
    e.preventDefault();
    var form = $(this);
    var targetUrl = form.attr('data-action-url');
    var submitBtn = form.find('button[type="submit"]');
    var originalText = submitBtn.html();
    
    submitBtn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);

    $.ajax({
        type: "POST",
        url: targetUrl,
        data: form.serialize(),
        success: function(res) {
            var obj = JSON.parse(res);
            if (obj.error == 1) {
                alert("Error: " + obj.msg);
                submitBtn.html(originalText).prop('disabled', false);
            } else {
                alert("Success: " + obj.msg);
                $('#commonModalOverlay').removeClass('active');
                if ($.fn.DataTable.isDataTable('#get_staff')) {
                    $('#get_staff').DataTable().draw(false);
                }
            }
        },
        error: function() {
            alert("A critical network error occurred.");
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});
</script>

<?php include 'footer.php'; ?>

<?php
include 'header.php'; 
?>

<!-- DataTables Required CSS/JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Service Catalog</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Manage the services you offer, prices, and automated reminder schedules.</p>
</div>

<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px;">
    
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-main);">Available Services</h3>
        <div style="display: flex; gap: 12px;">
            <a href="service_cat.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
                <i class="ph-bold ph-folder-open"></i> Manage Categories
            </a>
            <button class="btn-primary" style="width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;" onclick="loadModal('services_edit.php');">
                <i class="ph-bold ph-plus"></i> Add Service
            </button>
        </div>
    </div>

    <div style="padding: 24px;">
        <div class="table-responsive">
            <table id="get_service" class="table-modern" width="100%" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Service Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Reminder (Days)</th>
                        <th>Status</th>
                        <th style="min-width: 180px;">Actions</th>
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

.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: fadeUp 0.3s ease-out forwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

<!-- Service Edit Modal -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<!-- Variations Management Modal -->
<div class="modal-overlay" id="variationsModalOverlay">
    <div class="modal-dialog" id="variationsModalContent" style="max-width: 640px; max-height: 85vh; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; background: #f0fdf4; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph-bold ph-tag" style="color: #16a34a; font-size: 18px;"></i>
                </div>
                <div>
                    <h3 id="var_modal_title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Service Variations</h3>
                    <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Set different prices for the same service</p>
                </div>
            </div>
            <button type="button" id="closeVariationsModal" style="background: none; border: none; font-size: 22px; color: var(--text-muted); cursor: pointer; line-height: 1;"><i class="ph ph-x"></i></button>
        </div>

        <!-- Body -->
        <div style="overflow-y: auto; flex: 1; padding: 20px 24px;">

            <!-- Add Variation Form -->
            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                <div style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 12px;">
                    <i class="ph ph-plus-circle"></i> Add / Edit Variation
                </div>
                <input type="hidden" id="var_editing_id" value="0">
                <input type="hidden" id="var_service_id" value="">
                <div style="display: grid; grid-template-columns: 1fr 140px auto; gap: 10px; align-items: flex-end;">
                    <div>
                        <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Variation Name</label>
                        <input type="text" id="var_name_input" class="form-control" placeholder="e.g. Kids, Adult, Short, Long" style="background: white;">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Price (₹)</label>
                        <input type="number" id="var_price_input" class="form-control" step="0.01" min="0" placeholder="0.00" style="background: white;">
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button type="button" id="btn_save_variation" class="btn-primary" style="margin: 0; height: 48px; padding: 0 16px; white-space: nowrap; font-size: 13px; box-shadow: none;">
                            <i class="ph-bold ph-floppy-disk"></i> Save
                        </button>
                        <button type="button" id="btn_cancel_var_edit" style="display:none; background: #f1f5f9; border: none; height: 48px; padding: 0 12px; border-radius: 10px; cursor: pointer; color: var(--text-muted); font-size: 13px;">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Variations List -->
            <div id="var_list_wrap">
                <div style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px;">
                    <i class="ph ph-list-bullets"></i> Existing Variations
                </div>
                <div id="var_list_body">
                    <div style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 14px;">
                        <i class="ph ph-tag" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                        No variations yet. Add one above.
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer tip -->
        <div style="padding: 14px 24px; border-top: 1px solid var(--border-color); background: #f8fafc; flex-shrink: 0; font-size: 12px; color: var(--text-muted);">
            <i class="ph ph-info"></i> Variations let you price the same service differently (e.g. Kids ₹200 / Adult ₹400). Staff picks the variation during billing.
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var get_service = $('#get_service').DataTable({
        "processing": true,
        "serverSide": true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": { "method": "get_service" }
        },
        "columns": [
            { "data": "service_id" },
            { 
                "data": "service_name",
                "render": function(data, type, row) {
                    var display = row.service_name_display || data;
                    return '<div style="font-weight:600;"><i class="ph ph-sparkle" style="color:var(--text-muted); font-size: 18px; vertical-align:middle; margin-right:6px;"></i> ' + display + '</div>';
                }
            },
            { "data": "service_catName",
              "render": function(data) {
                  return '<span style="color: var(--text-muted); font-size: 13px;">' + (data ? data : 'Uncategorized') + '</span>';
              }
            },
            { 
                "data": "service_price",
                "render": function(data) {
                    return '<span style="font-weight:600; color:var(--text-main);">₹' + data + '</span>';
                }
            },
            { 
                "data": "service_reminder",
                "render": function(data) {
                    return data > 0 ? data + ' days' : '<span style="color:var(--text-muted)">Disabled</span>';
                }
            },
            { 
                "data": "service_status",
                "render": function(data) {
                    return data == 1 ? '<span class="badge badge-success" style="background:#dcfce7; color:#16a34a; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">Active</span>' : '<span class="badge badge-danger" style="background:#fee2e2; color:#dc2626; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">Disabled</span>';
                }
            },
            { 
                "data": "action",
                "render": function(data) {
                    return data;
                }
            }
        ]
    });

    $(document).on('click', '.close-modal', function(){ $('#commonModalOverlay').removeClass('active'); });
});

function loadModal(url) {
    $('#commonModalContent').html('<div style="padding: 40px; text-align: center;"><i class="ph ph-spinner ph-spin" style="font-size: 32px; color: var(--primary);"></i><p>Loading...</p></div>');
    $('#commonModalOverlay').addClass('active');
    $.ajax({url: url, success: function(data) { $('#commonModalContent').html(data); }});
}

$(document).on('click', '.modalButtonCommon', function(e){
    e.preventDefault();
    if($(this).attr('data-href')) loadModal($(this).attr('data-href'));
});

$(document).on('submit', 'form.ajax-form', function(e){
    e.preventDefault();
    var form = $(this);
    var targetUrl = form.attr('action') || form.attr('data-action-url') || 'ajax/salon_ajax.php';
    var submitBtn = form.find('button[type="submit"]');
    var originalText = submitBtn.html();
    
    submitBtn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);

    $.ajax({
        type: "POST", url: targetUrl, data: form.serialize(),
        success: function(res) {
            try {
                var obj = JSON.parse(res);
                if (obj.error == 1) {
                    alert("Error: " + obj.msg);
                    submitBtn.html(originalText).prop('disabled', false);
                } else {
                    alert("Success: " + obj.msg);
                    $('#commonModalOverlay').removeClass('active');
                    if ($.fn.DataTable.isDataTable('#get_service')) $('#get_service').DataTable().draw(false);
                }
            } catch(e) {
                alert("Server Error occurred.");
                submitBtn.html(originalText).prop('disabled', false);
            }
        }
    });
});

function toggleServiceStatus(service_id, status) {
    if(confirm("Are you sure you want to " + (status === 1 ? "activate" : "deactivate") + " this service?")) {
        $.ajax({
            type: "POST",
            url: "ajax/salon_ajax.php",
            data: { method: 'toggle_service_status', service_id: service_id, status: status },
            success: function(res) {
                var obj = JSON.parse(res);
                if (obj.error == 1) {
                    alert("Error: " + obj.msg);
                } else {
                    if ($.fn.DataTable.isDataTable('#get_service')) $('#get_service').DataTable().draw(false);
                }
            }
        });
    }
}

/* ─── Variations Modal ─────────────────────────────────────────────── */

var _var_current_service_id = 0;

function openVariationsModal(service_id, service_name) {
    _var_current_service_id = service_id;
    $('#var_service_id').val(service_id);
    $('#var_modal_title').text('Variations — ' + service_name);
    // Reset form
    resetVarForm();
    // Load existing
    loadVariations(service_id);
    $('#variationsModalOverlay').addClass('active');
}

function loadVariations(service_id) {
    $('#var_list_body').html('<div style="text-align:center;padding:24px;color:var(--text-muted);"><i class="ph ph-spinner ph-spin"></i> Loading...</div>');
    $.ajax({
        url: 'ajax/salon_ajax.php',
        type: 'POST',
        data: { method: 'get_service_variations', service_id: service_id },
        success: function(res) {
            try {
                var vars = JSON.parse(res);
                renderVarList(vars);
            } catch(e) { $('#var_list_body').html('<p style="color:var(--danger)">Error loading variations.</p>'); }
        }
    });
}

function renderVarList(vars) {
    if (!vars || vars.length === 0) {
        $('#var_list_body').html('<div style="text-align:center;padding:30px;color:var(--text-muted);font-size:14px;"><i class="ph ph-tag" style="font-size:32px;display:block;margin-bottom:8px;"></i>No variations yet. Add one above.</div>');
        return;
    }
    var html = '<div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">';
    vars.forEach(function(v, idx) {
        var bg = idx % 2 === 0 ? 'white' : '#f8fafc';
        html += '<div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:' + bg + ';border-bottom:1px solid var(--border-color);">' +
            '<div style="flex:1;">' +
            '<span style="font-weight:600;color:var(--text-main);font-size:14px;">' + escHtml(v.var_name) + '</span>' +
            '</div>' +
            '<div style="font-size:16px;font-weight:700;color:var(--primary);">\u20b9' + parseFloat(v.var_price).toFixed(2) + '</div>' +
            '<button type="button" onclick="editVariation(' + v.var_id + ',\'' + escHtml(v.var_name).replace(/'/g,"\\\'") + '\',' + v.var_price + ')" style="background:#e0e7ff;color:#4f46e5;border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:14px;display:inline-flex;align-items:center;justify-content:center;" title="Edit"><i class="ph ph-pencil-simple"></i></button>' +
            '<button type="button" onclick="deleteVariation(' + v.var_id + ',\'' + escHtml(v.var_name).replace(/'/g,"\\\'") + '\')" style="background:#fee2e2;color:#dc2626;border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:14px;display:inline-flex;align-items:center;justify-content:center;" title="Delete"><i class="ph ph-trash"></i></button>' +
            '</div>';
    });
    html += '</div>';
    $('#var_list_body').html(html);
}

function editVariation(var_id, var_name, var_price) {
    $('#var_editing_id').val(var_id);
    $('#var_name_input').val(var_name).focus();
    $('#var_price_input').val(parseFloat(var_price).toFixed(2));
    $('#btn_cancel_var_edit').show();
    $('#btn_save_variation').html('<i class="ph-bold ph-floppy-disk"></i> Update');
}

function deleteVariation(var_id, var_name) {
    if(!confirm('Delete variation "' + var_name + '"? This cannot be undone.')) return;
    $.ajax({
        url: 'ajax/salon_ajax.php', type: 'POST',
        data: { method: 'delete_service_variation', var_id: var_id },
        success: function(res) {
            var obj = JSON.parse(res);
            if(obj.error == 1) { alert(obj.msg); return; }
            loadVariations(_var_current_service_id);
            if($.fn.DataTable.isDataTable('#get_service')) $('#get_service').DataTable().draw(false);
        }
    });
}

function resetVarForm() {
    $('#var_editing_id').val(0);
    $('#var_name_input').val('');
    $('#var_price_input').val('');
    $('#btn_cancel_var_edit').hide();
    $('#btn_save_variation').html('<i class="ph-bold ph-floppy-disk"></i> Save');
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

$(document).ready(function() {
    // Save variation
    $('#btn_save_variation').click(function() {
        var name  = $('#var_name_input').val().trim();
        var price = $('#var_price_input').val().trim();
        if(!name)  { alert('Please enter a variation name.'); return; }
        if(price === '' || isNaN(price)) { alert('Please enter a valid price.'); return; }

        var btn = $(this);
        btn.html('<i class="ph ph-spinner ph-spin"></i>').prop('disabled', true);

        $.ajax({
            url: 'ajax/salon_ajax.php', type: 'POST',
            data: {
                method:     'save_service_variation',
                var_id:     $('#var_editing_id').val(),
                service_id: $('#var_service_id').val(),
                var_name:   name,
                var_price:  price,
                sort_order: 0
            },
            success: function(res) {
                var obj = JSON.parse(res);
                btn.html('<i class="ph-bold ph-floppy-disk"></i> Save').prop('disabled', false);
                if(obj.error == 1) { alert(obj.msg); return; }
                resetVarForm();
                loadVariations(_var_current_service_id);
                if($.fn.DataTable.isDataTable('#get_service')) $('#get_service').DataTable().draw(false);
            },
            error: function() { btn.html('<i class="ph-bold ph-floppy-disk"></i> Save').prop('disabled', false); alert('Network error.'); }
        });
    });

    // Cancel edit
    $('#btn_cancel_var_edit').click(function() { resetVarForm(); });

    // Close modal
    $('#closeVariationsModal').click(function() { $('#variationsModalOverlay').removeClass('active'); });
    $(document).on('click', '#variationsModalOverlay', function(e) {
        if($(e.target).is('#variationsModalOverlay')) $('#variationsModalOverlay').removeClass('active');
    });
    // Enter key in inputs
    $('#var_name_input, #var_price_input').on('keydown', function(e) {
        if(e.key === 'Enter') { e.preventDefault(); $('#btn_save_variation').click(); }
    });
});
</script>

<?php include 'footer.php'; ?>

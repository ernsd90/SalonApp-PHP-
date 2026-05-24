<?php
include 'header.php'; 

if(!is_superadmin()) {
    echo "<div style='padding:40px; text-align:center;'><h2>Access Denied</h2><p>Only Superadmins can manage outlets.</p></div>";
    include 'footer.php';
    exit;
}
?>

<!-- DataTables Required CSS/JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 4px;">Manage Outlets</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Govern salon branches, branches addresses, and globally applied communication APIs.</p>
</div>

<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px;">
    
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Registered Salons</h3>
        <button class="btn-primary" style="width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;" onclick="loadModal('salon_edit.php');">
            <i class="ph-bold ph-plus"></i> Register Outlet
        </button>
    </div>

    <div style="padding: 24px;">
        <div class="table-responsive">
            <table id="get_salon" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Outlet Name</th>
                        <th style="width: 200px;">Address</th>
                        <th>Mobile</th>
                        <th>GST Details</th>
                        <th>Make.com</th>
                        <th>Status</th>
                        <th style="width: 120px;">Action</th>
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

/* Modal Stub - We will implement a custom modal or use a lighter library */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 680px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: fadeUp 0.3s ease-out forwards; max-height: 90vh; overflow-y: auto; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

<!-- Custom V3 Modal Wrapper -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<script>
$(document).ready(function() {
    var get_salon = $('#get_salon').DataTable({
        "processing": true,
        "serverSide": true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": { "method": "get_salon" }
        },
        "columns": [
            { "data": "salon_id" },
            { "data": "salon_name" },
            { 
               "data": "salon_address",
               "render": function(data) {
                   return '<div style="white-space: normal; line-height: 1.4; font-size: 13px;">' + (data || 'N/A') + '</div>';
               }
            },
            { "data": "salon_mobile" },
            { 
               "data": "gst_enable",
               "render": function(data, type, row) {
                   if(data == 1) return '<span style="color:var(--success); font-weight:600;"><i class="ph ph-check-circle"></i> Enabled</span> (' + row.salon_gst + ') <br><small style="color:var(--text-muted);">' + parseFloat(row.gst_percentage).toFixed(2) + '% Rate</small>';
                   return '<span style="color:var(--text-muted);"><i class="ph ph-minus-circle"></i> Disabled</span>';
               }
            },
            {
               "data": "make_enable",
               "render": function(data) {
                   if(data == 1) return '<span style="padding:3px 10px; background:#dcfce7; color:#16a34a; border-radius:20px; font-size:12px; font-weight:600;"><i class="ph ph-check"></i> On</span>';
                   return '<span style="padding:3px 10px; background:#f1f5f9; color:#64748b; border-radius:20px; font-size:12px; font-weight:600;">Off</span>';
               }
            },
            {
               "data": "salon_status",
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

    // ── Modal: close on backdrop click ─────────────────────────────────────
    $('#commonModalOverlay').on('click', function(e) {
        if ($(e.target).is('#commonModalOverlay')) closeModal();
    });
});

// ── Modal helpers (outside document.ready so dynamically loaded content works) ─
function closeModal() {
    $('#commonModalOverlay').removeClass('active');
}

// Close on any .close-modal element — delegated so injected HTML is covered
$(document).on('click', '.close-modal', function(e) {
    e.preventDefault();
    closeModal();
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
    var targetUrl = form.attr('data-action-url');
    var submitBtn = form.find('button[type="submit"]');
    var originalText = submitBtn.html();
    
    submitBtn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);

    $.ajax({
        type: "POST", url: targetUrl, data: form.serialize(),
        success: function(res) {
            var obj = JSON.parse(res);
            if (obj.error == 1) {
                alert("Error: " + obj.msg);
                submitBtn.html(originalText).prop('disabled', false);
            } else {
                alert("Success: " + obj.msg);
                closeModal();
                if ($.fn.DataTable.isDataTable('#get_salon')) $('#get_salon').DataTable().draw(false);
            }
        }
    });
});

function toggleSalonStatus(salon_id, status) {
    if(confirm("Are you sure you want to " + (status === 1 ? "activate" : "deactivate") + " this outlet?")) {
        $.ajax({
            type: "POST",
            url: "ajax/salon_ajax.php",
            data: { method: 'toggle_salon_status', salon_id: salon_id, status: status },
            success: function(res) {
                var obj = JSON.parse(res);
                if (obj.error == 1) {
                    alert("Error: " + obj.msg);
                } else {
                    if ($.fn.DataTable.isDataTable('#get_salon')) $('#get_salon').DataTable().draw(false);
                }
            }
        });
    }
}
</script>

<?php include 'footer.php'; ?>

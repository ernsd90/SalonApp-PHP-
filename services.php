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
            <table id="get_service" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Service Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Reminder (Days)</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
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

<!-- Custom V3 Modal Wrapper -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
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
                    return '<div style="font-weight:600;"><i class="ph ph-sparkle" style="color:var(--text-muted); font-size: 18px; vertical-align:middle; margin-right:6px;"></i> ' + data + '</div>';
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
</script>

<?php include 'footer.php'; ?>

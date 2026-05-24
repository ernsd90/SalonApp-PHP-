<?php
include 'header.php'; 
?>

<!-- DataTables Required CSS/JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 4px;">
        <a href="services.php" style="color: var(--text-muted); font-size: 20px; text-decoration: none;"><i class="ph ph-arrow-left"></i></a>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">Service Categories</h1>
    </div>
    <p style="color: var(--text-muted); font-size: 14px; margin-left: 36px;">Organize your services into clean, unified categories.</p>
</div>

<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px;">
    
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-main);">Category List</h3>
        <button class="btn-primary" style="width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;" onclick="loadModal('service_category_edit.php');">
            <i class="ph-bold ph-plus"></i> Add Category
        </button>
    </div>

    <div style="padding: 24px;">
        <div class="table-responsive">
            <table id="get_service_cat" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Category Name</th>
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
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 400px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: fadeUp 0.3s ease-out forwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.btn-edit { background: #e0e7ff; color: #4f46e5; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; margin-right: 4px; font-size: 13px; cursor: pointer; transition: 0.2s; }
.btn-edit:hover { background: #c7d2fe; }
.btn-delete { background: #fee2e2; color: #dc2626; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; margin-right: 4px; font-size: 13px; cursor: pointer; transition: 0.2s; }
.btn-delete:hover { background: #fecaca; }
</style>

<!-- Custom V3 Modal Wrapper -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<script>
$(document).ready(function() {
    var get_service_cat = $('#get_service_cat').DataTable({
        "processing": true,
        "serverSide": true,
        responsive: true,
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": { "method": "get_serviceCat" }
        },
        "columns": [
            { "data": "service_catid" },
            { 
                "data": "service_catName",
                "render": function(data) {
                    return '<div style="font-weight:600;"><i class="ph ph-folder" style="color:var(--text-muted); font-size: 18px; vertical-align:middle; margin-right:6px;"></i> ' + data + '</div>';
                }
            },
            { 
                "data": "action",
                "render": function(data) {
                    return data.replace(/btn-outline-info/g, 'btn-edit').replace(/btn-outline-danger/g, 'btn-delete').replace(/<i class="fa fa-edit "><\/i>/g, 'Edit').replace(/<i class="fa fa-trash "><\/i>/g, 'Delete');
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
    var targetUrl = form.attr('data-action-url') || 'ajax/salon_ajax.php';
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
                    if ($.fn.DataTable.isDataTable('#get_service_cat')) $('#get_service_cat').DataTable().draw(false);
                }
            } catch(e) {
                alert("Server Error occurred.");
                submitBtn.html(originalText).prop('disabled', false);
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?>

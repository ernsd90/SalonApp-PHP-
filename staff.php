<?php include 'header.php'; ?>

<!-- DataTables Required CSS/JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Staff Members (Stylists)</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Manage the stylists and employees who perform services at your salon.</p>
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
               "data": "staff_status",
               "render": function(data) {
                   if(data == 1) return '<span style="padding: 4px 10px; background: #dcfce7; color: #16a34a; border-radius: 20px; font-size: 12px; font-weight: 600;">Active</span>';
                   return '<span style="padding: 4px 10px; background: #fee2e2; color: #dc2626; border-radius: 20px; font-size: 12px; font-weight: 600;">Inactive</span>';
               }
            },
            { 
               "data": "action",
               "render": function(data) {
                   // Clean up old bootstrap button classes returned by the server
                   return data.replace(/btn-gradient-info/g, 'btn-edit').replace(/btn-gradient-danger/g, 'btn-delete');
               }
            }
        ]
    });

    $(document).on('click', '.close-modal', function(){
        $('#commonModalOverlay').removeClass('active');
    });
});

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

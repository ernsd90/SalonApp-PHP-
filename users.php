<?php include 'header.php'; ?>

<!-- DataTables Required CSS/JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Staff & Users</h1>
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Manage your outlet employees, system users, and their access permissions.</p>
    </div>
    <a href="user_role.php" class="btn-primary" style="text-decoration: none; padding: 10px 16px; margin: 0; display: inline-flex; align-items: center; gap: 8px;">
        <i class="ph-bold ph-shield-check"></i> Manage Roles & Permissions
    </a>
</div>

<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px;">
    
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">System Users</h3>
        <button class="btn-primary" style="width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;" onclick="loadModal('user_edit.php');">
            <i class="ph-bold ph-plus"></i> Add User
        </button>
    </div>

    <div style="padding: 24px;">
        <div class="table-responsive">
            <table id="get_user" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<style>
/* Modern Table Reset */
.table-modern {
    width: 100% !important;
    border-collapse: separate;
    border-spacing: 0;
}
.table-modern th {
    background: #f8fafc;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    text-align: left;
}
.table-modern td {
    padding: 16px;
    font-size: 14px;
    color: var(--text-main);
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}
.table-modern tbody tr:hover td {
    background: #f8fafc;
}

/* Modal Stub - We will implement a custom modal or use a lighter library */
.modal-overlay {
    display: none;
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 100;
    align-items: center; justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal-dialog {
    background: white; border-radius: 20px; width: 100%; max-width: 600px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden;
    animation: fadeUp 0.3s ease-out forwards;
}
</style>

<!-- Custom V3 Modal Wrapper -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent">
        <!-- Content loaded via Ajax -->
    </div>
</div>

<script>
$(document).ready(function() {
    var get_user = $('#get_user').DataTable({
        "processing": true,
        "serverSide": true,
        responsive: true,
        "ajax": {
            "url": "ajax/user_ajax.php",
            "type": "POST",
            "data": { "method": "get_user" }
        },
        "columns": [
            { "data": "user_id" },
            { "data": "full_name" },
            { "data": "username" },
            { "data": "password" },
            { "data": "user_role" },
            { 
               "data": "action",
               "render": function(data) {
                   // Strip legacy bootstrap button classes and apply modern styles
                   return data.replace(/btn-gradient-info/g, 'btn-edit').replace(/btn-gradient-danger/g, 'btn-delete');
               }
            }
        ]
    });

    // Close Modal
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

// Intercept clicks on dynamically loaded content (action buttons)
$(document).on('click', '.modalButtonCommon', function(e){
    e.preventDefault();
    var targetUrl = $(this).attr('data-href');
    if(targetUrl) {
        loadModal(targetUrl);
    }
});

// Generic Ajax Form Submission Handler
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
                if ($.fn.DataTable.isDataTable('#get_user')) {
                    $('#get_user').DataTable().draw(false);
                }
            }
        },
        error: function() {
            alert("A critical network error occurred.");
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

function loginAsUser(userId) {
    if(confirm("Are you sure you want to login as this user?")) {
        $.ajax({
            url: "ajax/user_ajax.php",
            type: "POST",
            data: { method: "login_as_user", login_user_id: userId },
            success: function(response) {
                var res = JSON.parse(response);
                if (res.error == 0) {
                    alert(res.msg);
                    window.location.reload();
                } else {
                    alert("Error: " + res.msg);
                }
            },
            error: function() {
                alert("A critical network error occurred.");
            }
        });
    }
}
</script>

<?php include 'footer.php'; ?>

<?php
include 'header.php'; 
?>

<!-- DataTables Required CSS/JS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 4px;">Customer Database</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Manage client profiles, view their transaction histories, and adjust wallet balances.</p>
</div>

<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px;">
    
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Client Directory</h3>
        <button class="btn-primary" style="width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;" onclick="loadModal('customer_edit.php');">
            <i class="ph-bold ph-plus"></i> Add Customer
        </button>
    </div>

    <div style="padding: 24px;">
        <div class="table-responsive">
            <table id="get_customer" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Client Details</th>
                        <th>Wallet Balance</th>
                        <th>Outstanding Debt</th>
                        <th>Last Visit</th>
                        <th>Lifetime Spend</th>
                        <th>Loyalty Points</th>
                        <th style="width: 180px;">Actions</th>
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
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: fadeUp 0.3s ease-out forwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

<!-- Custom V3 Modal Wrapper -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<script>
// Helper functions for premium initials avatars
function getInitials(name) {
    if (!name) return 'C';
    var parts = name.split(' ');
    var initials = parts[0].charAt(0);
    if (parts.length > 1) {
        initials += parts[parts.length - 1].charAt(0);
    }
    return initials.toUpperCase();
}

function getAvatarColor(name) {
    var colors = [
        { bg: '#fee2e2', text: '#dc2626' }, // Red
        { bg: '#ffedd5', text: '#ea580c' }, // Orange
        { bg: '#fef9c3', text: '#ca8a04' }, // Yellow
        { bg: '#dcfce7', text: '#16a34a' }, // Green
        { bg: '#e0f2fe', text: '#0284c7' }, // Blue
        { bg: '#e0e7ff', text: '#4f46e5' }, // Indigo
        { bg: '#f3e8ff', text: '#9333ea' }  // Purple
    ];
    if (!name) return colors[0];
    var sum = 0;
    for (var i = 0; i < name.length; i++) {
        sum += name.charCodeAt(i);
    }
    return colors[sum % colors.length];
}

$(document).ready(function() {
    var get_customer = $('#get_customer').DataTable({
        "processing": true,
        "serverSide": true,
        responsive: true,
        "ajax": {
            "url": "ajax/customer_ajax.php",
            "type": "POST",
            "data": { "method": "get_customer" }
        },
        "columns": [
            { 
                "data": "cust_name",
                "render": function(data, type, row) {
                    var initials = getInitials(data);
                    var colors = getAvatarColor(data);
                    return `
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: ${colors.bg}; color: ${colors.text}; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                ${initials}
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">${data}</div>
                                <div style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                    <i class="ph ph-phone" style="font-size: 12px; vertical-align: middle;"></i> ${row.cust_mobile}
                                </div>
                            </div>
                        </div>
                    `;
                }
            },
            { 
               "data": "cust_wallet",
               "render": function(data) {
                   var val = parseFloat(data);
                   if(val > 0) return '<span style="display:inline-flex;align-items:center;background:#dcfce7;color:#16a34a;padding:4px 10px;border-radius:12px;font-size:13px;font-weight:600;">₹' + val.toFixed(2) + '</span>';
                   return '<span style="color:var(--text-muted);font-size:13px;">₹0.00</span>';
               }
            },
            { 
               "data": "cust_outstanding",
               "render": function(data) {
                   var val = parseFloat(data);
                   if(val > 0) return '<span style="display:inline-flex;align-items:center;background:#fee2e2;color:#dc2626;padding:4px 10px;border-radius:12px;font-size:13px;font-weight:600;">₹' + val.toFixed(2) + '</span>';
                   return '<span style="color:var(--text-muted);font-size:13px;">₹0.00</span>';
               }
            },
            { "data": "last_visit" },
            { "data": "lifetime_spend" },
            { 
               "data": "loyalty_points",
               "render": function(data) {
                   var pts = parseInt(data);
                   if(pts > 0) return '<span style="display:inline-flex;align-items:center;gap:4px;background:#f3e8ff;color:#7c3aed;padding:4px 10px;border-radius:12px;font-size:13px;font-weight:600;"><i class="ph-fill ph-crown" style="font-size:12px;"></i> ' + data + '</span>';
                   return '<span style="color:var(--text-muted);font-size:13px;">0 pts</span>';
               }
            },
            { 
               "data": "action",
               "render": function(data, type, row) {
                    // Transform legacy buttons to premium aesthetic
                    return data.replace(/btn-gradient-info/g, 'btn-edit')
                               .replace(/btn-gradient-success/g, 'btn-view')
                               .replace(/btn-gradient-danger/g, 'btn-delete');
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
                $('#commonModalOverlay').removeClass('active');
                if ($.fn.DataTable.isDataTable('#get_customer')) $('#get_customer').DataTable().draw(false);
            }
        }
    });
});
</script>

<!-- Add V3 Premium button fixes for DataTables return rows -->
<style>
/* Premium action buttons styling */
.btn-edit, .btn-view, .btn-delete, .btn-wa, .btn-followup {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    border-radius: 8px !important;
    font-size: 15px !important;
    transition: all 0.2s ease !important;
    cursor: pointer;
    border: none;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
.btn-edit:hover, .btn-view:hover, .btn-delete:hover, .btn-wa:hover, .btn-followup:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.btn-edit { background: #e0e7ff !important; color: #4f46e5 !important; margin-right: 4px; }
.btn-edit:hover { background: #c7d2fe !important; }
.btn-view { background: #dcfce7 !important; color: #16a34a !important; margin-right: 4px; }
.btn-view:hover { background: #bbf7d0 !important; }
.btn-delete { background: #fee2e2 !important; color: #dc2626 !important; margin-right: 4px; }
.btn-delete:hover { background: #fecaca !important; }
.btn-wa { background: #25D366 !important; color: white !important; margin-right: 4px; }
.btn-wa:hover { background: #22c55e !important; }
.btn-followup { background: #ffedd5 !important; color: #ea580c !important; margin-right: 4px; }
.btn-followup:hover { background: #fed7aa !important; }

/* Custom Search & Filters layout for Premium look */
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 13.5px;
    background: #f8fafc;
    transition: all 0.2s ease;
    outline: none;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
}
.dataTables_wrapper .dataTables_length select {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13.5px;
    background: #f8fafc;
    outline: none;
}
.table-modern tbody tr {
    transition: background 0.15s ease;
}
.table-modern tbody tr:hover td {
    background: #fcfdfe !important;
}
</style>

<?php include 'footer.php'; ?>

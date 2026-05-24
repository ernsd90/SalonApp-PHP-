<?php 
include 'header.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">Active Job Cards</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 14px;">Manage ongoing customer salon sessions, split assignments, and generate final bills.</p>
    </div>
    
    <a href="job_card.php" class="btn-primary" style="text-decoration: none; padding: 10px 20px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px; transition: 0.2s;">
        <i class="ph-bold ph-plus"></i> New Job Card
    </a>
</div>

<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
    <div style="padding: 24px;">
        <table class="table-modern" id="get_job_cards" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 10%;">Card #ID</th>
                    <th style="width: 20%;">Date Created</th>
                    <th style="width: 20%;">Customer Name</th>
                    <th style="width: 15%;">Mobile</th>
                    <th style="width: 15%;">Status</th>
                    <th style="width: 20%; text-align: left;">Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables injected -->
            </tbody>
        </table>
    </div>
</div>

<!-- DataTables styling -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<!-- DataTables & dependencies -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<style>
/* Modern Table Scoping */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern th { color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 2px solid var(--border-color); text-align: left; }
.table-modern td { padding: 12px 16px; font-size: 14px; position: relative; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
</style>

<script>
$(document).ready(function() {
    var get_job_cards = $('#get_job_cards').DataTable({
        "processing": true,
        "serverSide": true,
        responsive: true,
        "order": [[ 0, "desc" ]],
        "ajax": {
            "url": "ajax/salon_ajax.php",
            "type": "POST",
            "data": { "method": "get_job_cards" }
        },
        "columns": [
            { 
                "data": "job_card_id",
                "render": function(data, type, row) {
                    return '<div style="font-weight:700; color:var(--text-main);">#' + data + '</div>';
                }
            },
            { 
                "data": "created_date",
                "render": function(data, type, row) {
                    return '<div style="color:var(--text-muted); font-size: 13px;"><i class="ph ph-calendar-blank" style="vertical-align:middle; margin-right:4px;"></i> ' + data + '</div>';
                }
            },
            { 
                "data": "cust_name",
                "render": function(data, type, row) {
                    return '<div style="font-weight:600;"><i class="ph ph-user" style="color:var(--text-muted); vertical-align:middle; margin-right:4px;"></i> ' + data + '</div>';
                }
            },
            { 
                "data": "cust_mobile"
            },
            { 
                "data": "jobcard_status"
            },
            { 
                "data": "action",
                "orderable": false
            }
        ]
    });
});
</script>

<?php include 'footer.php'; ?>

<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;margin-bottom:4px;">Membership Plans</h1>
    <p style="color:var(--text-muted);font-size:14px;">Create and manage prepaid wallet-based membership plans for your salon.</p>
</div>

<!-- Stats Row -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:28px;" id="stats-row">
    <div class="stat-card-mini" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border-radius:16px;padding:20px 24px;">
        <div style="font-size:12px;font-weight:600;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Total Plans</div>
        <div id="stat-total-plans" style="font-size:28px;font-weight:800;margin-top:6px;">—</div>
    </div>
    <div class="stat-card-mini" style="background:linear-gradient(135deg,#059669,#10b981);color:white;border-radius:16px;padding:20px 24px;">
        <div style="font-size:12px;font-weight:600;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Active Memberships</div>
        <div id="stat-active" style="font-size:28px;font-weight:800;margin-top:6px;">—</div>
    </div>
    <div class="stat-card-mini" style="background:linear-gradient(135deg,#d97706,#f59e0b);color:white;border-radius:16px;padding:20px 24px;">
        <div style="font-size:12px;font-weight:600;opacity:.8;text-transform:uppercase;letter-spacing:.5px;">Wallet Liability</div>
        <div id="stat-liability" style="font-size:28px;font-weight:800;margin-top:6px;">—</div>
    </div>
</div>

<div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:30px;">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
        <h3 style="font-size:16px;font-weight:600;margin:0;display:flex;align-items:center;gap:8px;">
            <i class="ph-fill ph-identification-badge" style="color:var(--primary);font-size:22px;"></i> Membership Plans
        </h3>
        <button class="btn-primary" style="width:auto;padding:10px 18px;margin:0;font-size:14px;display:flex;align-items:center;gap:8px;" onclick="openModal('membership_plan_edit.php')">
            <i class="ph-bold ph-plus"></i> Add Plan
        </button>
    </div>
    <div style="padding:24px;">
        <table id="membership_plans_table" class="table-modern" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Plan Name</th>
                    <th>Customer Pays</th>
                    <th>Wallet Credit</th>
                    <th>Validity</th>
                    <th>Status</th>
                    <th style="width:220px;">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="planModalOverlay">
    <div class="modal-dialog" id="planModalContent"></div>
</div>

<style>
.badge-success-sm { background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
.badge-danger-sm  { background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
.btn-edit     { background:#e0e7ff;color:#4f46e5;border:none;padding:6px 12px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s; }
.btn-edit:hover { background:#c7d2fe; }
.btn-delete   { background:#fee2e2;color:#dc2626;border:none;padding:6px 12px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s; }
.btn-delete:hover { background:#fecaca; }
.btn-deactivate { background:#fef3c7;color:#92400e;border:none;padding:6px 12px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s; }
.btn-activate   { background:#d1fae5;color:#065f46;border:none;padding:6px 12px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s; }
.table-modern { width:100%;border-collapse:separate;border-spacing:0; }
.table-modern th { background:#f8fafc;color:var(--text-muted);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:12px 16px;border-bottom:1px solid var(--border-color);text-align:left; }
.table-modern td { padding:14px 16px;font-size:14px;color:var(--text-main);border-bottom:1px solid var(--border-color);vertical-align:middle; }
.table-modern tbody tr:hover td { background:#f8fafc; }
.modal-overlay { display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);z-index:100;align-items:center;justify-content:center; }
.modal-overlay.active { display:flex; }
.modal-dialog { background:white;border-radius:20px;width:100%;max-width:580px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);overflow:hidden;animation:fadeUp .3s ease-out forwards;max-height:90vh;overflow-y:auto; }
@keyframes fadeUp { from{opacity:0;transform:translateY(15px)} to{opacity:1;transform:translateY(0)} }
</style>

<script>
$(document).ready(function(){
    var tbl = $('#membership_plans_table').DataTable({
        processing: true, serverSide: true,
        ajax: { url: 'ajax/membership_ajax.php', type: 'POST', data: { method: 'get_membership_plans' } },
        columns: [
            { data: 'plan_id', width: '50px' },
            { data: 'plan_name', render: d => '<span style="font-weight:600;">'+d+'</span>' },
            { data: 'plan_price' },
            { data: 'wallet_credit', render: d => '<span style="color:var(--success);font-weight:600;">'+d+'</span>' },
            { data: 'validity' },
            { data: 'status' },
            { data: 'action', orderable: false }
        ]
    });

    // Load stats
    $.post('ajax/membership_ajax.php', {method:'membership_report_data'}, function(res){
        var r = JSON.parse(res);
        if(r.error == 0){
            $('#stat-active').text(r.active_count);
            $('#stat-liability').text('₹' + parseFloat(r.wallet_liability).toLocaleString('en-IN', {maximumFractionDigits:0}));
        }
    });
    $.post('ajax/membership_ajax.php', {method:'get_membership_plans', start:0, length:1}, function(res){
        var r = JSON.parse(res);
        if(r.recordsTotal !== undefined) $('#stat-total-plans').text(r.recordsTotal);
    });

    $(document).on('click', '.close-modal, .modal-overlay', function(e){
        if(e.target === this || $(this).hasClass('close-modal')) {
            $('#planModalOverlay').removeClass('active');
        }
    });

    // Route modalButtonCommon (Edit button) through local openModal
    $(document).on('click', '.modalButtonCommon', function(e){
        e.preventDefault(); e.stopImmediatePropagation();
        var url = $(this).attr('data-href');
        if(url) openModal(url);
    });

    $(document).on('submit', 'form.ajax-form', function(e){
        e.preventDefault();
        var btn = $(this).find('button[type=submit]');
        btn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);
        $.ajax({ type:'POST', url:$(this).attr('data-action-url') || 'ajax/membership_ajax.php', data:$(this).serialize(),
            success:function(res){
                var obj = JSON.parse(res);
                if(obj.error == 1){ alert('Error: '+obj.msg); btn.html('Save Plan').prop('disabled',false); }
                else { alert(obj.msg); $('#planModalOverlay').removeClass('active'); tbl.draw(false); }
            }
        });
    });
});

function openModal(url) {
    $('#planModalContent').html('<div style="padding:40px;text-align:center;"><i class="ph ph-spinner ph-spin" style="font-size:32px;color:var(--primary);"></i><p>Loading...</p></div>');
    $('#planModalOverlay').addClass('active');
    $.ajax({ url: url, success: function(data){ $('#planModalContent').html(data); }});
}

function toggleMembershipPlan(plan_id, status) {
    $.post('ajax/membership_ajax.php', {method:'toggle_membership_plan_status', plan_id:plan_id, status:status}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        $('#membership_plans_table').DataTable().draw(false);
    });
}

function deleteMembershipPlan(plan_id) {
    if(!confirm('Delete this membership plan? This cannot be undone.')) return;
    $.post('ajax/membership_ajax.php', {method:'delete_membership_plan', id:plan_id}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        $('#membership_plans_table').DataTable().draw(false);
    });
}
</script>

<?php include 'footer.php'; ?>

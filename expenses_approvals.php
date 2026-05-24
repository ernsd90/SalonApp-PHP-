<?php
include 'header.php';
?>

<style>
.approval-card { background: white; border-radius: 16px; padding: 24px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 24px; }
.approval-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
.approval-table th { background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 16px 8px 16px; text-align: left; }
.approval-table td { padding: 16px; font-size: 14px; color: var(--text-main); background: #f8fafc; vertical-align: middle; }
.approval-table tr td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
.approval-table tr td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
.btn-approve { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
.btn-approve:hover { background: #bbf7d0; }
.btn-reject { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; margin-left: 8px; }
.btn-reject:hover { background: #fecaca; }

.modal-action { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; }
.modal-action.active { display: flex; }
.modal-action-dialog { background: white; border-radius: 20px; width: 100%; max-width: 400px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
</style>

<div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Pending Approvals</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Review and manage expenses awaiting manager approval.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="expenses.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
            <i class="ph-bold ph-arrow-left"></i> Back to Logs
        </a>
    </div>
</div>

<div class="approval-card">
    <div style="overflow-x:auto;">
        <table class="approval-table" id="approvalTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Vendor</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="6" style="text-align:center;background:transparent;"><i class="ph ph-spinner ph-spin"></i> Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Action Modal -->
<div class="modal-action" id="actionModal">
    <div class="modal-action-dialog">
        <h3 style="margin-top:0; font-size:18px; font-weight:600;" id="modalTitle">Review Expense</h3>
        <input type="hidden" id="actionExpId">
        <input type="hidden" id="actionType">
        <div style="margin-bottom:16px;">
            <label style="font-size:13px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:8px;">Comments (Optional)</label>
            <textarea id="actionComments" class="form-control" style="width:100%; height:80px; border-radius:8px; border:1px solid var(--border-color); padding:12px; font-family:inherit; resize:none;" placeholder="Add a note..."></textarea>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <button class="btn-primary" style="background:#f1f5f9; color:var(--text-main); box-shadow:none; padding:8px 16px; width:auto; border:none;" onclick="$('#actionModal').removeClass('active');">Cancel</button>
            <button class="btn-primary" id="btnConfirmAction" style="width:auto; padding:8px 16px; margin:0;">Confirm</button>
        </div>
    </div>
</div>

<script>
function formatMoney(n) { return '₹' + Number(n).toFixed(2); }

function loadApprovals() {
    $.post('ajax/expenses_ajax.php', { method: 'get_pending_approvals' }, function(res) {
        try {
            const result = JSON.parse(res);
            let html = '';
            if (result.data.length === 0) {
                html = '<tr><td colspan="6" style="text-align:center; background:white; padding:40px; color:var(--text-muted); border-radius:12px;"><i class="ph-bold ph-check-circle" style="font-size:48px; color:#10b981; margin-bottom:16px; display:block;"></i>All caught up! No pending approvals.</td></tr>';
            } else {
                result.data.forEach(e => {
                    html += `<tr>
                        <td style="white-space:nowrap; color:var(--text-muted);"><i class="ph ph-calendar-blank"></i> ${e.exp_date}</td>
                        <td style="font-weight:600;">${e.category_name || 'General'}</td>
                        <td>${e.exp_name}</td>
                        <td><i class="ph ph-storefront" style="color:var(--text-muted);"></i> ${e.vendor_name || '-'}</td>
                        <td style="font-weight:700; color:var(--danger);">-₹${Number(e.exp_total).toFixed(2)}</td>
                        <td style="white-space:nowrap;">
                            <button class="btn-approve" onclick="openAction(${e.exp_id}, 'approved')"><i class="ph-bold ph-check"></i> Approve</button>
                            <button class="btn-reject" onclick="openAction(${e.exp_id}, 'rejected')"><i class="ph-bold ph-x"></i> Reject</button>
                        </td>
                    </tr>`;
                });
            }
            $('#approvalTable tbody').html(html);
        } catch(e) {
            console.error(e);
        }
    });
}

function openAction(id, type) {
    $('#actionExpId').val(id);
    $('#actionType').val(type);
    $('#actionComments').val('');
    $('#modalTitle').text(type === 'approved' ? 'Approve Expense' : 'Reject Expense');
    $('#btnConfirmAction').css('background', type === 'approved' ? '#10b981' : '#dc2626').text(type === 'approved' ? 'Approve' : 'Reject');
    $('#actionModal').addClass('active');
}

$('#btnConfirmAction').click(function() {
    let btn = $(this);
    let original = btn.html();
    btn.html('<i class="ph ph-spinner ph-spin"></i>').prop('disabled', true);
    
    $.post('ajax/expenses_ajax.php', {
        method: 'update_approval',
        exp_id: $('#actionExpId').val(),
        status: $('#actionType').val(),
        comments: $('#actionComments').val()
    }, function(res) {
        btn.html(original).prop('disabled', false);
        $('#actionModal').removeClass('active');
        try {
            let result = JSON.parse(res);
            if(result.status === 'success') {
                let toast = $('<div style="position:fixed; top:20px; right:20px; background:#10b981; color:white; padding:12px 24px; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); z-index:9999; font-weight:600;"><i class="ph-bold ph-check-circle"></i> '+result.msg+'</div>');
                $('body').append(toast);
                setTimeout(() => toast.fadeOut(300, function(){$(this).remove();}), 3000);
                loadApprovals();
            } else {
                alert('Error: ' + result.msg);
            }
        } catch(e) {
            alert('Server error.');
        }
    });
});

$(document).ready(function() {
    loadApprovals();
});
</script>

<?php include 'footer.php'; ?>

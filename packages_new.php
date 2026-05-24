<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;margin-bottom:4px;">Service Packages</h1>
    <p style="color:var(--text-muted);font-size:14px;">Create pre-bundled service packages with defined session counts. Customers pay upfront and redeem at each visit.</p>
</div>

<div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:30px;">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
        <h3 style="font-size:16px;font-weight:600;margin:0;display:flex;align-items:center;gap:8px;">
            <i class="ph-fill ph-package" style="color:var(--primary);font-size:22px;"></i> Package Catalog
        </h3>
        <button class="btn-primary" style="width:auto;padding:10px 18px;margin:0;font-size:14px;display:flex;align-items:center;gap:8px;" onclick="openPkgModal('package_new_edit.php')">
            <i class="ph-bold ph-plus"></i> Add Package
        </button>
    </div>
    <div style="padding:24px;">
        <table id="packages_new_table" class="table-modern" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Package Name</th>
                    <th>Services Included</th>
                    <th>MRP</th>
                    <th>Selling Price</th>
                    <th>Savings</th>
                    <th>Validity</th>
                    <th>Status</th>
                    <th style="width:230px;">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal-overlay" id="pkgModalOverlay">
    <div class="modal-dialog" id="pkgModalContent" style="max-width:720px;"></div>
</div>

<style>
.badge-success-sm{background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
.badge-danger-sm{background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
.btn-edit{background:#e0e7ff;color:#4f46e5;border:none;padding:6px 12px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s;}
.btn-edit:hover{background:#c7d2fe;}
.btn-delete{background:#fee2e2;color:#dc2626;border:none;padding:6px 12px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s;}
.btn-delete:hover{background:#fecaca;}
.btn-deactivate{background:#fef3c7;color:#92400e;border:none;padding:6px 12px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s;}
.btn-activate{background:#d1fae5;color:#065f46;border:none;padding:6px 12px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s;}
.table-modern{width:100%;border-collapse:separate;border-spacing:0;}
.table-modern th{background:#f8fafc;color:var(--text-muted);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:12px 16px;border-bottom:1px solid var(--border-color);text-align:left;}
.table-modern td{padding:14px 16px;font-size:14px;color:var(--text-main);border-bottom:1px solid var(--border-color);vertical-align:middle;}
.table-modern tbody tr:hover td{background:#f8fafc;}
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);z-index:100;align-items:flex-start;justify-content:center;padding:40px 20px;overflow-y:auto;}
.modal-overlay.active{display:flex;}
.modal-dialog{background:white;border-radius:20px;width:100%;max-width:720px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);overflow:hidden;animation:fadeUp .3s ease-out forwards;}
@keyframes fadeUp{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:translateY(0)}}
</style>

<script>
$(document).ready(function(){
    var tbl = $('#packages_new_table').DataTable({
        processing: true, serverSide: true,
        ajax: { url: 'ajax/membership_ajax.php', type: 'POST', data: { method: 'get_packages_new' } },
        columns: [
            { data: 'pkg_id', width: '50px' },
            { data: 'package_name', render: d => '<span style="font-weight:600;">'+d+'</span>' },
            { data: 'services', render: d => '<span style="font-size:13px;color:var(--text-muted);">'+d+'</span>' },
            { data: 'mrp_total', render: d => '<span style="text-decoration:line-through;color:var(--text-muted);">'+d+'</span>' },
            { data: 'selling_price', render: d => '<span style="color:var(--primary);font-weight:700;">'+d+'</span>' },
            { data: 'savings', render: d => '<span style="color:var(--success);font-weight:600;">'+d+'</span>' },
            { data: 'validity' },
            { data: 'status' },
            { data: 'action', orderable: false }
        ]
    });

    $(document).on('click', '.close-modal, #pkgModalOverlay', function(e){
        if(e.target === this || $(this).hasClass('close-modal')) $('#pkgModalOverlay').removeClass('active');
    });

    // Route modalButtonCommon (Edit button) through local openPkgModal
    $(document).on('click', '.modalButtonCommon', function(e){
        e.preventDefault(); e.stopImmediatePropagation();
        var url = $(this).attr('data-href');
        if(url) openPkgModal(url);
    });

    $(document).on('submit', 'form.ajax-form', function(e){
        e.preventDefault();
        var btn = $(this).find('button[type=submit]');
        btn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);
        $.ajax({ type:'POST', url:'ajax/membership_ajax.php', data:$(this).serialize(),
            success:function(res){
                var obj = JSON.parse(res);
                if(obj.error==1){ alert('Error: '+obj.msg); btn.html('Save Package').prop('disabled',false); }
                else { alert(obj.msg); $('#pkgModalOverlay').removeClass('active'); tbl.draw(false); }
            }
        });
    });
});

function openPkgModal(url) {
    $('#pkgModalContent').html('<div style="padding:40px;text-align:center;"><i class="ph ph-spinner ph-spin" style="font-size:32px;color:var(--primary);"></i></div>');
    $('#pkgModalOverlay').addClass('active');
    $.ajax({ url: url, success: function(data){ $('#pkgModalContent').html(data); } });
}

function togglePackageNew(pkg_id, status) {
    $.post('ajax/membership_ajax.php', {method:'toggle_package_new_status', pkg_id:pkg_id, status:status}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        $('#packages_new_table').DataTable().draw(false);
    });
}

function deletePackageNew(pkg_id) {
    if(!confirm('Delete this package? This cannot be undone.')) return;
    $.post('ajax/membership_ajax.php', {method:'delete_package_new', id:pkg_id}, function(res){
        var r = JSON.parse(res); alert(r.msg);
        $('#packages_new_table').DataTable().draw(false);
    });
}
</script>

<?php include 'footer.php'; ?>

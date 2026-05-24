<?php include 'header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;color:var(--text-main);margin-bottom:4px;">Vendors</h1>
    <p style="color:var(--text-muted);font-size:14px;">Manage your product & supply vendors.</p>
</div>

<div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
        <h3 style="font-size:16px;font-weight:600;margin:0;">Vendor Directory</h3>
        <button class="btn-primary" style="width:auto;padding:10px 16px;margin:0;font-size:14px;display:flex;align-items:center;gap:8px;" onclick="loadModal('vendor_edit.php');">
            <i class="ph-bold ph-plus"></i> Add Vendor
        </button>
    </div>
    <div style="padding:24px;">
        <table id="vendors_table" class="table-modern" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vendor Name</th>
                    <th>Phone</th>
                    <th>GST No.</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th style="width:90px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $salon_id = get_session_data('salon_id');
            $vendors = select_array("SELECT * FROM hr_vendor WHERE (salon_id='$salon_id' OR salon_id=0) ORDER BY id DESC");
            foreach ((array)$vendors as $v): ?>
            <tr>
                <td><?= $v['id'] ?></td>
                <td><strong><?= htmlspecialchars($v['vendor_name']) ?></strong>
                    <?php if($v['vendor_email']): ?><br><small style="color:var(--text-muted);"><?= htmlspecialchars($v['vendor_email']) ?></small><?php endif; ?>
                </td>
                <td><?= $v['vendor_phone'] ?: '—' ?></td>
                <td><?= $v['vendor_gst'] ? '<code style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:12px;">'.htmlspecialchars($v['vendor_gst']).'</code>' : '—' ?></td>
                <td style="font-size:13px;color:var(--text-muted);"><?= $v['vendor_address'] ? htmlspecialchars(substr($v['vendor_address'],0,50)) : '—' ?></td>
                <td><?= $v['status'] ? '<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">Active</span>' : '<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">Inactive</span>' ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button type="button" class="modalButtonCommon" data-href="vendor_edit.php?vendor_id=<?= $v['id'] ?>" style="background:#e0e7ff;color:#4f46e5;border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:15px;"><i class="ph ph-pencil"></i></button>
                        <a href="vendor_ledger.php?vendor_id=<?= $v['id'] ?>" style="background:#f1f5f9;color:var(--text-main);width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:15px;" title="Ledger"><i class="ph ph-book-open"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.table-modern { width:100%;border-collapse:separate;border-spacing:0; }
.table-modern th { background:#f8fafc;color:var(--text-muted);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;padding:12px 16px;border-bottom:1px solid var(--border-color);text-align:left; }
.table-modern td { padding:14px 16px;font-size:14px;color:var(--text-main);border-bottom:1px solid var(--border-color);vertical-align:middle; }
.table-modern tbody tr:hover td { background:#f8fafc; }
.modal-overlay { display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);z-index:100;align-items:center;justify-content:center; }
.modal-overlay.active { display:flex; }
.modal-dialog { background:white;border-radius:20px;width:100%;max-width:560px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.4);overflow:hidden;animation:fadeUp 0.25s ease; }
@keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
</style>

<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<script>
$(document).ready(function(){
    $('#vendors_table').DataTable({ responsive:true, order:[[0,'desc']] });
});
function loadModal(url) {
    $('#commonModalContent').html('<div style="padding:40px;text-align:center;"><i class="ph ph-spinner ph-spin" style="font-size:32px;color:var(--primary);"></i><p>Loading...</p></div>');
    $('#commonModalOverlay').addClass('active');
    $.ajax({ url:url, success:function(d){ $('#commonModalContent').html(d); } });
}
$(document).on('click','.modalButtonCommon',function(e){ e.preventDefault(); loadModal($(this).attr('data-href')); });
$(document).on('click','.close-modal',function(){ $('#commonModalOverlay').removeClass('active'); });
$(document).on('submit','form.ajax-form',function(e){
    e.preventDefault();
    var form=$(this), btn=form.find('button[type="submit"]'), orig=btn.html();
    btn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled',true);
    $.ajax({ type:'POST', url:form.attr('data-action-url'), data:form.serialize(),
        success:function(res){
            try{ var o=JSON.parse(res);
                if(o.error==1){ alert('Error: '+o.msg); btn.html(orig).prop('disabled',false); }
                else { alert(o.msg); $('#commonModalOverlay').removeClass('active'); location.reload(); }
            }catch(e){ alert('Server error'); btn.html(orig).prop('disabled',false); }
        }
    });
});
</script>
<?php include 'footer.php'; ?>

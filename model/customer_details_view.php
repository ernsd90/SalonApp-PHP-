<?php
include "../config.php";
include "../function.php";
require_once "../loyalty_functions.php";

extract($_REQUEST);

$sql = "SELECT * FROM `hr_customer` where  `cust_id`='".$cust_id."'";
$user = select_row($sql);

foreach($user as $var => $value){
    $$var = $value;
}

// Fetch loyalty balance
$pts_balance = get_customer_points_balance((int)$cust_id);
?>

<style>
/* Override modal width dynamically for standard detail overlays */
#commonModalOverlay .modal-dialog {
    max-width: 920px !important;
    width: 95vw !important;
    transition: max-width 0.3s ease-in-out;
}
.modal-body-scrollable {
    max-height: 68vh;
    overflow-y: auto;
    padding: 24px 28px !important;
}
/* Style transaction table */
#get_customer_details {
    width: 100% !important;
    border-collapse: separate;
    border-spacing: 0;
}
#get_customer_details th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border-bottom: 2px solid #e2e8f0;
}
#get_customer_details td {
    padding: 14px 16px;
    font-size: 13.5px;
    color: #1e293b;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
#get_customer_details tbody tr:hover td {
    background: #f8fafc;
}
</style>

<div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 28px; border-bottom: 1px solid var(--border-color);">
    <div>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="ph-fill ph-user-gear" style="color: var(--primary); font-size: 22px;"></i> 
            <?php echo htmlspecialchars($cust_name); ?> Dashboard
        </h3>
        <span style="font-size: 12px; color: var(--text-muted); margin-top: 4px; display: block;">Mobile: <?php echo htmlspecialchars($cust_mobile); ?></span>
    </div>
    <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer; transition: 0.2s;"><i class="ph ph-x"></i></button>
</div>

<div class="modal-body-scrollable">
    
    <!-- Top Stats Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 28px;">
        <!-- Wallet Card -->
        <div style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); border: 1px solid #86efac; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.06);">
            <div style="width: 48px; height: 48px; background: #16a34a; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.2);">
                <i class="ph-fill ph-wallet"></i>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #14532d; text-transform: uppercase; letter-spacing: 0.5px;">Wallet Balance</div>
                <div style="font-size: 20px; font-weight: 800; color: #166534; margin-top: 4px;">₹<?php echo number_format((float)$cust_wallet, 2); ?></div>
            </div>
        </div>
        
        <!-- Debt Card -->
        <div style="background: linear-gradient(135deg, #fee2e2, #fecaca); border: 1px solid #fca5a5; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 12px rgba(244, 63, 94, 0.06);">
            <div style="width: 48px; height: 48px; background: #dc2626; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 10px rgba(220, 38, 38, 0.2);">
                <i class="ph-fill ph-hand-coins"></i>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #7f1d1d; text-transform: uppercase; letter-spacing: 0.5px;">Outstanding Debt</div>
                <div style="font-size: 20px; font-weight: 800; color: #991b1b; margin-top: 4px;">₹<?php echo number_format((float)$cust_outstanding, 2); ?></div>
            </div>
        </div>

        <!-- Loyalty Card -->
        <div style="background: linear-gradient(135deg, #f3e8ff, #e9d5ff); border: 1px solid #d8b4fe; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.06);">
            <div style="width: 48px; height: 48px; background: #7c3aed; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 10px rgba(124, 58, 237, 0.2);">
                <i class="ph-fill ph-crown"></i>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #5b21b6; text-transform: uppercase; letter-spacing: 0.5px;">Loyalty Balance</div>
                <div style="font-size: 20px; font-weight: 800; color: #6d28d9; margin-top: 4px;"><?php echo number_format($pts_balance, 0); ?> pts</div>
            </div>
        </div>
    </div>

    <!-- Transaction Ledger section -->
    <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="ph ph-receipt" style="color: var(--primary); font-size: 18px;"></i> Wallet Transaction History
    </h4>

    <div class="table-responsive">
        <table id="get_customer_details" style="width:100%;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Credit</th>
                    <th>Debit</th>
                    <th>Balance</th>
                    <th style="width: 80px;">Action</th>
                </tr>
            </thead>
        </table>
    </div>

</div>

<div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding: 20px 28px;">
    <button type="button" class="close-modal form-control" style="width: auto; background: white; margin: 0; padding: 10px 24px; border: 1px solid var(--border-color); font-weight: 600;">Close View</button>
</div>

<script>
if ($.fn.DataTable.isDataTable('#get_customer_details')) {
    $('#get_customer_details').DataTable().destroy();
}

$('#get_customer_details').DataTable({
    "processing": true,
    "serverSide": true,
    responsive: true,
    scrollX: true,
    iDisplayLength: 10,
    "ajax": {
        "url": "ajax/customer_ajax.php",
        "type": "POST",
        "data": function(data) {
            data.method = "get_customer_details";
            data.cust_id = '<?php echo $cust_id; ?>';
        }
    },
    "order": [
        [0, "desc"]
    ],
    "columns": [
        { 
            "data": "created_date",
            "render": function(data) {
                return '<span style="color:#64748b; font-weight:600;"><i class="ph ph-calendar" style="margin-right:4px;"></i> ' + data + '</span>';
            }
        },
        { 
            "data": "credit",
            "render": function(data) {
                var num = parseFloat(data);
                if (num > 0) return '<span style="color:#10b981; font-weight:700;">+₹' + num.toFixed(2) + '</span>';
                return '<span style="color:#94a3b8;">-</span>';
            }
        },
        { 
            "data": "debit",
            "render": function(data) {
                var num = parseFloat(data);
                if (num > 0) return '<span style="color:#ef4444; font-weight:700;">-₹' + num.toFixed(2) + '</span>';
                return '<span style="color:#94a3b8;">-</span>';
            }
        },
        { 
            "data": "balance",
            "render": function(data) {
                var num = parseFloat(data);
                return '<span style="color:#0f172a; font-weight:800;">₹' + num.toFixed(2) + '</span>';
            }
        },
        { 
            "data": "action",
            "orderable": false,
            "render": function(data) {
                // Transform view details action button to premium view styling
                return data.replace(/btn-gradient-success/g, 'btn-view');
            }
        }
    ]
});
</script>

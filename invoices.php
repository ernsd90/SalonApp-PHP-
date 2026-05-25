<?php include 'header.php'; ?>

<!-- DataTables CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<!-- Date Range Picker CSS/JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- Select2 CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Invoice History</h1>
    <p style="color: var(--text-muted); font-size: 14px;">View generated bills, reprints, and track daily sales.</p>
</div>

<!-- Overview Cards -->
<!-- Overview Cards -->
<!-- Overview Cards -->
<!-- Overview Cards -->
<!-- Overview Cards -->
<div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 32px;">
    
    <div class="metric-card" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; border-radius: 20px; padding: 20px; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);">
        <p style="margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; opacity: 0.8;">Total Revenue</p>
        <h2 id="sum_grand_total" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700;">₹0</h2>
        <div style="font-size: 12px; margin-top: 8px; opacity: 0.9; display: flex; flex-direction: column; gap: 4px;">
            <span>Cash: <b id="sum_grand_cash">₹0</b></span>
            <span>Online/CC: <b id="sum_grand_cc">₹0</b></span>
        </div>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Service Sales</p>
        <h2 id="sum_service_total" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text-main);">₹0</h2>
        <div style="font-size: 12px; margin-top: 8px; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
            <span>Cash: <b id="sum_service_cash">₹0</b></span>
            <span>Online/CC: <b id="sum_service_cc">₹0</b></span>
            <span id="sum_service_wallet_row" style="display:none;">Wallet (Split): <b id="sum_service_wallet" style="color:#7c3aed;">₹0</b></span>
        </div>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Product Sales</p>
        <h2 id="sum_product_total" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text-main);">₹0</h2>
        <div style="font-size: 12px; margin-top: 8px; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
            <span>Cash: <b id="sum_product_cash">₹0</b></span>
            <span>Online/CC: <b id="sum_product_cc">₹0</b></span>
        </div>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Redumtion sale</p>
        <p style="margin: 0; font-size: 10px; color: var(--text-muted);">(Membership & Package services sale)</p>
        <h2 id="sum_reduction_sale" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text-main);">₹0</h2>
        <div style="font-size: 12px; margin-top: 8px; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
            <span>Package: <b id="sum_reduction_pkg">₹0</b></span>
            <span>Wallet: <b id="sum_reduction_wallet">₹0</b></span>
        </div>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Membership & Package Sale</p>
        <h2 id="sum_membership_pkg" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text-main);">₹0</h2>
        <div style="font-size: 12px; margin-top: 8px; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
            <span>Cash: <b id="sum_membership_pkg_cash">₹0</b></span>
            <span>Online/CC: <b id="sum_membership_pkg_cc">₹0</b></span>
        </div>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Total Customers</p>
        <h2 id="sum_customers" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--success);">0</h2>
    </div>

    <div class="metric-card" style="background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <p style="margin: 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Total Discount</p>
        <h2 id="sum_discount" style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--danger);">₹0</h2>
    </div>
</div>
<style>
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-v3 { background: white; border-radius: 20px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: fadeUp 0.3s ease-out forwards; max-height: 90vh; display: flex; flex-direction: column; }
.modal-v3.modal-lg { max-width: 800px; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.modal-body-scroll { overflow-y: auto; padding: 24px; }
</style>

<!-- Filter Section -->
<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 250px;">
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Date Filter</label>
            <select id="date_period_filter" class="form-control" style="background: #f8fafc; margin-bottom: 8px;">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <?php
                    // Dynamically generate the 3 previous months
                    for ($i = 1; $i <= 3; $i++) {
                        $timestamp = strtotime("-$i month");
                        $monthLabel = date('M y', $timestamp);
                        $monthVal = date('Y-m', $timestamp); // standard value formatting
                        echo '<option value="month_'.$monthVal.'">'.$monthLabel.'</option>';
                    }
                ?>
                <option value="custom">Custom Date</option>
            </select>
            
            <!-- Hidden by default, shows only when 'Custom Date' is selected -->
            <div id="custom_date_wrapper" style="display: none; position: relative;">
                <i class="ph ph-calendar-blank" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); z-index: 10;"></i>
                <input type="text" id="dateRangePicker" class="form-control" style="padding-left: 44px; background: #f8fafc;" />
            </div>
            
            <input type="hidden" id="search_fromdate" value="<?= date('d-m-Y') ?>">
            <input type="hidden" id="search_todate" value="<?= date('d-m-Y') ?>">
        </div>

        <div style="flex: 1; min-width: 200px;">
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Services</label>
            <select id="reportservice_id" class="form-control select2" multiple="multiple" style="width: 100%;">
                <?php
                $services = select_array("SELECT * FROM `hr_services` where salon_id='".$salon_id."' and service_status=1");
                if($services){
                    foreach($services as $s){ 
                        echo '<option value="'.$s['service_id'].'">'.$s['service_name'].'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div style="flex: 1; min-width: 200px;">
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Staff Assigned</label>
            <select id="reportstaff_id" class="form-control" style="background: #f8fafc;">
                <option value="">All Staff</option>
                <?php
                $staff = select_array("SELECT * FROM `hr_staff` where salon_id='".$salon_id."' and staff_status=1");
                if($staff){
                    foreach($staff as $s){ 
                        echo '<option value="'.$s['staff_id'].'">'.$s['staff_name'].'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <button id="btnFilter" class="btn-primary" style="margin: 0; height: 48px; padding: 0 24px; box-shadow: none;">
            <i class="ph ph-funnel"></i> Filter
        </button>

    </div>
</div>

<!-- List Section -->
<style>
/* Modern Table Scoping */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern th { color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 2px solid var(--border-color); text-align: left; white-space: nowrap; }
.table-modern td { padding: 16px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; }
.table-modern tbody tr:hover td { background: #f8fafc; }
</style>
<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-bottom: 50px;">
    <div style="padding: 24px; overflow-x: auto;">
        <table id="get_salerecord" class="table-modern" style="width:100%">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Method</th>
                    <th>Discount</th>
                    <th>Total (₹)</th>
                    <th>Tip</th>
                    <th>Date</th>
                    <th style="width: 110px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


<!-- View Invoice Modal -->
<div class="modal-overlay" id="invoiceViewModal">
    <div class="modal-v3 modal-lg">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid var(--border-color); background: #f8fafc;">
            <h3 style="margin:0; font-size:16px; font-weight:600;">Invoice Details</h3>
            <button class="close-modal" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-muted);"><i class="ph ph-x"></i></button>
        </div>
        <div class="modal-body-scroll" id="invoice_content_area"></div>
    </div>
</div>

<!-- Delete Bill Modal -->
<div class="modal-overlay" id="modalDeleteInvoice">
    <div class="modal-v3">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid var(--border-color); background: #fef2f2;">
            <h5 style="margin:0; font-size:16px; font-weight:700; color:var(--danger);">Delete Invoice</h5>
            <button class="close-modal" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--danger);"><i class="ph ph-x"></i></button>
        </div>
        <div class="modal-body-scroll">
             <form id="report_form">
                 <input type="hidden" name="method" value="invoice_delete">
                 <input type="hidden" name="invoice_id" id="del_invoice_id">
                 <p style="margin-bottom: 16px;">Are you sure you want to delete this invoice? This action cannot be undone and will revert staff commissions and inventory.</p>
                 
                 <div class="form-group">
                    <label>Reason for deletion</label>
                    <textarea name="delete_reason" class="form-control" required rows="2"></textarea>
                 </div>

                 <div class="form-group" style="margin-top: 12px;">
                    <label>Delete Password</label>
                    <input type="password" name="delete_pwd" class="form-control" required placeholder="Enter delete password" autocomplete="new-password">
                 </div>

                 <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                     <button type="button" class="btn btn-light close-modal" style="padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border-color); background: white; cursor: pointer;">Cancel</button>
                     <button type="submit" class="btn btn-danger" style="padding: 10px 16px; border-radius: 8px; border: none; background: var(--danger); color: white; cursor: pointer; font-weight: 600;">Confirm Deletion</button>
                 </div>
             </form>
        </div>
    </div>
</div>

<!-- Change Payment Method Modal -->
<div class="modal-overlay" id="modalChangePayment">
    <div class="modal-v3">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid var(--border-color); background: #f0f9ff;">
            <h5 style="margin:0; font-size:16px; font-weight:700; color: #0369a1;"><i class="ph ph-credit-card" style="margin-right:6px;"></i>Change Payment Method</h5>
            <button class="close-modal" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-muted);"><i class="ph ph-x"></i></button>
        </div>
        <div class="modal-body-scroll">
             <form id="payment_method_form">
                 <input type="hidden" name="method" value="update_invoice">
                 <input type="hidden" name="invoice_id" id="pay_invoice_id">
                 
                 <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Select New Payment Method</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px;">
                        <?php
                        $payment_options = select_array("SELECT * FROM hr_payment_methods WHERE status=1 ORDER BY sort_order ASC");
                        if($payment_options):
                            foreach($payment_options as $opt):
                                $val = $opt['method_key'];
                                $label = $opt['method_name'];
                                $icon = isset($opt['icon']) && $opt['icon'] ? $opt['icon'] : 'ph-credit-card';
                                $color = isset($opt['color']) && $opt['color'] ? $opt['color'] : '#0284c7';
                        ?>
                        <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border-radius:10px; border:2px solid #e2e8f0; cursor:pointer; transition:all 0.2s;" 
                               onmouseover="this.style.borderColor='<?=$color?>'" onmouseout="this.nextElementSibling ? null : this.style.borderColor='#e2e8f0'">
                            <input type="radio" name="payment_mode" value="<?=$val?>" style="accent-color:<?=$color?>; width:16px; height:16px;" required>
                            <i class="ph-bold <?=$icon?>" style="color:<?=$color?>; font-size:18px;"></i>
                            <span style="font-weight:600; font-size:13px;"><?=$label?></span>
                        </label>
                        <?php 
                            endforeach; 
                        endif;
                        ?>
                    </div>
                 </div>

                 <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                     <button type="button" class="btn btn-light close-modal" style="padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border-color); background: white; cursor: pointer;">Cancel</button>
                     <button type="submit" style="padding: 10px 20px; border-radius: 8px; border: none; background: #0284c7; color: white; cursor: pointer; font-weight: 600;"><i class="ph ph-check"></i> Update Payment</button>
                 </div>
             </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function () {
    
    // Initialize Select2 for multi-selects
    $('#reportservice_id').select2({
        placeholder: "All Services Selected",
        allowClear: true
    });

    // Initialize Date Range Picker
    $('#dateRangePicker').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        locale: { format: 'DD-MM-YYYY' }
    }, function(start, end, label) {
        $('#search_fromdate').val(start.format('DD-MM-YYYY'));
        $('#search_todate').val(end.format('DD-MM-YYYY'));
        if($('#date_period_filter').val() !== 'custom') {
            $('#date_period_filter').val('custom').trigger('change');
        }
    });

    $('#date_period_filter').change(function() {
        var val = $(this).val();
        var start = moment();
        var end = moment();
        
        if (val === 'custom') {
            $('#custom_date_wrapper').slideDown(200);
            return;
        } else {
            $('#custom_date_wrapper').slideUp(200);
        }

        if (val === 'today') {
            start = moment();
            end = moment();
        } else if (val === 'yesterday') {
            start = moment().subtract(1, 'days');
            end = moment().subtract(1, 'days');
        } else if (val === 'this_week') {
            start = moment().startOf('week');
            end = moment().endOf('week');
        } else if (val === 'this_month') {
            start = moment().startOf('month');
            end = moment().endOf('month');
        } else if (val.startsWith('month_')) {
            // Dynamic past month
            var monthSplit = val.split('_')[1]; // YYYY-MM
            start = moment(monthSplit, 'YYYY-MM').startOf('month');
            end = moment(monthSplit, 'YYYY-MM').endOf('month');
        }

        $('#dateRangePicker').data('daterangepicker').setStartDate(start);
        $('#dateRangePicker').data('daterangepicker').setEndDate(end);
        $('#search_fromdate').val(start.format('DD-MM-YYYY'));
        $('#search_todate').val(end.format('DD-MM-YYYY'));
        
        if (typeof table !== 'undefined') table.draw();
        if (typeof fetchSummary === 'function') fetchSummary();
    });

    // Initialize DataTable
    var table = $('#get_salerecord').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ordering": true,
        "order": [[ 0, "desc" ]], // Default descending on Invoice ID
        "ajax": {
            "url": "ajax/report_ajax.php",
            "type": "POST",
            "data": function (d) {
                d.method = "get_salerecord";
                d.fromdate = $('#search_fromdate').val();
                d.todate = $('#search_todate').val();
                d.staff_id = $('#reportstaff_id').val();
                d.service_id = $('#reportservice_id').val(); // Array of service IDs
            }
        },
        "columns": [
            { 
                "data": "invoice_number",
                "render": function(data) { return '<span style="font-weight:700; color:var(--primary);">#' + data + '</span>'; }
            },
            { 
                "data": "cust_name",
                "render": function(data) { return '<span style="font-weight:600; color:var(--text-main);">'+data+'</span>'; }
            },
            { "data": "cust_mob" },
            { 
                "data": "payment_mode",
                "render": function(data) { 
                    var color = data === 'cash' || data === 'Cash' ? 'var(--success)' : 'var(--primary)';
                    return '<span style="color:'+color+'; font-weight:600; text-transform:uppercase; font-size:12px;">'+data+'</span>'; 
                }
            },
            { "data": "discount" },
            { 
                "data": "grand_total",
                "render": function(data) { return '₹'+parseFloat(data).toFixed(2); }
            },
            { 
                "data": "tips",
                "render": function(data) { return data ? '₹'+parseFloat(data).toFixed(2) : '₹0.00'; }
            },
            { 
                "data": "invoice_date",
                "render": function(data) {
                    return '<span style="color:var(--text-main); font-size:13px;"><i class="ph ph-calendar-blank"></i> ' + data + '</span>';
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    var btns = `<div style="display:flex; gap:6px; justify-content:center;">
                                <button class="btn-view" data-id="${row.invoice_id}" style="background:#f1f5f9; color:var(--primary); border:none; width:32px; height:32px; border-radius:8px; cursor:pointer;" title="View"><i class="ph-bold ph-eye"></i></button>
                                <a href="print_invoice.php?invoice_id=${row.invoice_id}" target="_blank" style="background:#f1f5f9; color:var(--text-main); display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; text-decoration:none;" title="Print"><i class="ph-bold ph-printer"></i></a>
                                <a href="print_invoice.php?invoice_id=${row.invoice_id}&wa=1" target="_blank" data-log-module="Invoice History WA Action" class="wa-track-click" style="background:#dcfce7; color:#22c55e; display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; text-decoration:none;" title="WhatsApp"><i class="ph-bold ph-whatsapp-logo"></i></a>`;
                    <?php if(check_user_permission("report","edit",$user_id)): ?>
                    var pMode = (row.payment_mode || '').toLowerCase();
                    if (pMode !== 'pkg' && pMode !== 'wallet' && pMode !== 'package') {
                        btns += `<button class="btn-change-payment" data-id="${row.invoice_id}" style="background:#e0f2fe; color:#0284c7; border:none; width:32px; height:32px; border-radius:8px; cursor:pointer;" title="Change Payment Method"><i class="ph-bold ph-credit-card"></i></button>`;
                    }
                    <?php endif; ?>
                    <?php if(check_user_permission("report","delete",$user_id)): ?>
                    btns += `<button class="btn-delete" data-id="${row.invoice_id}" style="background:#fee2e2; color:var(--danger); border:none; width:32px; height:32px; border-radius:8px; cursor:pointer;" title="Delete"><i class="ph-bold ph-trash"></i></button>`;
                    <?php endif; ?>
                    btns += `</div>`;
                    return btns;
                }
            },
        ],
        "createdRow": function (row, data, dataIndex) {
            if (data['delete_bill'] == 1) {
                $(row).find('td').css({'opacity': '0.5', 'text-decoration': 'line-through'});
            }
        }
    });

    // Fetch Analytics Summary
    function fetchSummary() {
        $.ajax({
            type: "POST",
            url: "ajax/report_ajax.php",
            data: { 
                method: "summary_sale",
                fromdate: $('#search_fromdate').val(),
                todate: $('#search_todate').val(),
                staff_id: $('#reportstaff_id').val(),
                service_id: $('#reportservice_id').val()
            },
            success: function(res) {
                try {
                    var obj = JSON.parse(res);
                    $('#sum_grand_total').text('₹' + Number(obj.grand_total || 0).toFixed(2));
                    $('#sum_grand_cash').text('₹' + Number(obj.grand_cash || 0).toFixed(2));
                    $('#sum_grand_cc').text('₹' + Number(obj.grand_cc || 0).toFixed(2));
                    $('#sum_service_total').text('₹' + Number(obj.service_total || 0).toFixed(2));
                    $('#sum_service_cash').text('₹' + Number(obj.service_cash || 0).toFixed(2));
                    $('#sum_service_cc').text('₹' + Number(obj.service_cc || 0).toFixed(2));
                    var svcWallet = Number(obj.service_wallet || 0);
                    if (svcWallet > 0) {
                        $('#sum_service_wallet').text('₹' + svcWallet.toFixed(2));
                        $('#sum_service_wallet_row').show();
                    } else {
                        $('#sum_service_wallet_row').hide();
                    }
                    
                    $('#sum_product_total').text('₹' + Number(obj.product_total || 0).toFixed(2));
                    $('#sum_product_cash').text('₹' + Number(obj.product_cash || 0).toFixed(2));
                    $('#sum_product_cc').text('₹' + Number(obj.product_cc || 0).toFixed(2));
                    
                    $('#sum_reduction_sale').text('₹' + Number(obj.reduction_sale || 0).toFixed(2));
                    $('#sum_reduction_pkg').text('₹' + Number(obj.reduction_pkg || 0).toFixed(2));
                    $('#sum_reduction_wallet').text('₹' + Number(obj.reduction_wallet || 0).toFixed(2));
                    
                    $('#sum_membership_pkg').text('₹' + Number(obj.membership_pkg || 0).toFixed(2));
                    $('#sum_membership_pkg_cash').text('₹' + Number(obj.membership_pkg_cash || 0).toFixed(2));
                    $('#sum_membership_pkg_cc').text('₹' + Number(obj.membership_pkg_cc || 0).toFixed(2));
                    
                    $('#sum_discount').text('₹' + Number(obj.discount_total || 0).toFixed(2));
                    $('#sum_customers').text(obj.total_customer || '0');
                } catch(e) {}
            }
        });
    }

    // Initial load
    fetchSummary();

    // Handle Filter Button
    $('#btnFilter').click(function() {
        table.draw();
        fetchSummary();
    });

    // View Action
    $(document).on('click', '.btn-view', function() {
        var id = $(this).attr('data-id');
        $('#invoice_content_area').html('<div style="padding:40px; text-align:center;"><i class="ph ph-spinner ph-spin" style="font-size:32px; color:var(--primary);"></i><p>Loading...</p></div>');
        $('#invoiceViewModal').addClass('active');
        
        $.ajax({
            url: "invoice_view.php",
            type: "GET",
            data: { invoice_id: id },
            success: function(res) {
                $('#invoice_content_area').html(res);
            }
        });
    });

    // Delete Action
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).attr('data-id');
        $('#del_invoice_id').val(id);
        $('#report_form textarea[name="delete_reason"]').val('');
        $('#report_form input[name="delete_pwd"]').val('');
        $('#modalDeleteInvoice').addClass('active');
    });

    // Change Payment Method Action
    $(document).on('click', '.btn-change-payment', function() {
        var id = $(this).attr('data-id');
        $('#pay_invoice_id').val(id);
        $('#payment_method_form input[type="radio"]').prop('checked', false);
        $('#modalChangePayment').addClass('active');
    });

    // Close modals
    $(document).on('click', '.close-modal', function() {
        $('.modal-overlay').removeClass('active');
    });

    // Submit Delete Form
    $('#report_form').submit(function(e) {
        e.preventDefault();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.html('<i class="ph ph-spinner ph-spin"></i>').prop('disabled', true);
        
        $.ajax({
            type: "POST",
            url: "ajax/report_ajax.php",
            data: $(this).serialize(),
            success: function(res) {
                try {
                    var obj = JSON.parse(res);
                    if (obj.error == 1) {
                        alert(obj.msg);
                    } else {
                        $('#modalDeleteInvoice').removeClass('active');
                        table.draw();
                        fetchSummary();
                    }
                } catch (e) {
                    alert('Error deleting bill.');
                }
                submitBtn.html(originalText).prop('disabled', false);
            },
            error: function() {
                alert('Network Error.');
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Submit Change Payment Method Form
    $('#payment_method_form').submit(function(e) {
        e.preventDefault();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.html('<i class="ph ph-spinner ph-spin"></i>').prop('disabled', true);

        $.ajax({
            type: "POST",
            url: "ajax/report_ajax.php",
            data: $(this).serialize(),
            success: function(res) {
                try {
                    var obj = JSON.parse(res);
                    if (obj.error == 1) {
                        alert(obj.msg);
                    } else {
                        $('#modalChangePayment').removeClass('active');
                        table.draw();
                        fetchSummary();
                    }
                } catch(e) {
                    alert('Payment method updated successfully.');
                    $('#modalChangePayment').removeClass('active');
                    table.draw();
                }
                submitBtn.html(originalText).prop('disabled', false);
            },
            error: function() {
                alert('Network Error.');
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

});
</script>

<?php include 'footer.php'; ?>

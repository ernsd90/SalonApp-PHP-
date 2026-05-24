<?php include 'header.php'; ?>

<!-- DataTables Required CSS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 4px;">CRM Analytics Report</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Detailed insights into customer acquisition, lifetime value, and engagement.</p>
</div>

<!-- Filter Section -->
<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 20px 24px; margin-bottom: 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        <div style="font-weight: 600; color: var(--text-muted); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Acquisition Period</div>
        <div style="position: relative; cursor: pointer; min-width: 250px;" id="crm_daterange">
            <i class="ph ph-calendar-blank" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            <div class="form-control" style="padding-left: 44px; background: #f8fafc; height: 44px; display: flex; align-items: center; justify-content: space-between;">
                <span id="datelabel" style="font-size:14px; font-weight:600; color:var(--text-main);">All Time</span>
                <i class="ph ph-caret-down text-muted" style="margin-left:auto;"></i>
            </div>
            <input type="hidden" id="filter_from" value="">
            <input type="hidden" id="filter_to" value="">
        </div>
        <button class="btn-primary" id="btn_apply_filter" style="margin: 0; padding: 10px 20px; width: auto; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-funnel"></i> Apply Filter
        </button>
    </div>
    <div>
        <button class="btn-secondary" id="btn_export_csv" style="margin: 0; padding: 10px 20px; width: auto; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-file-csv"></i> Export CSV
        </button>
    </div>
</div>

<!-- KPI Section -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 24px;">
    <div style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 24px; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">Total Customers</div>
            <div style="background: rgba(255,255,255,0.1); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;"><i class="ph-fill ph-users" style="font-size: 20px; color: #38bdf8;"></i></div>
        </div>
        <div style="font-size: 36px; font-weight: 800;" id="kpi_total_customers"><i class="ph ph-spinner ph-spin text-muted"></i></div>
    </div>
    
    <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0; padding: 24px; border-radius: 20px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #16a34a;">Avg. Lifetime Value</div>
            <div style="background: #bbf7d0; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;"><i class="ph-fill ph-trend-up" style="font-size: 20px; color: #15803d;"></i></div>
        </div>
        <div style="font-size: 36px; font-weight: 800; color: #14532d;" id="kpi_avg_ltv"><i class="ph ph-spinner ph-spin text-muted"></i></div>
        <div style="font-size: 12px; color: #16a34a; font-weight: 600; margin-top: 4px;">Total Revenue / Customers</div>
    </div>

    <div style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); border: 1px solid #cbd5e1; padding: 24px; border-radius: 20px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #475569;">Total Wallet Liab.</div>
            <div style="background: #cbd5e1; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;"><i class="ph-fill ph-wallet" style="font-size: 20px; color: #334155;"></i></div>
        </div>
        <div style="font-size: 36px; font-weight: 800; color: #0f172a;" id="kpi_total_wallet"><i class="ph ph-spinner ph-spin text-muted"></i></div>
    </div>

    <div style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1px solid #fecaca; padding: 24px; border-radius: 20px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #dc2626;">Total Outstanding Debt</div>
            <div style="background: #fecaca; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;"><i class="ph-fill ph-warning-circle" style="font-size: 20px; color: #b91c1c;"></i></div>
        </div>
        <div style="font-size: 36px; font-weight: 800; color: #7f1d1d;" id="kpi_total_debt"><i class="ph ph-spinner ph-spin text-muted"></i></div>
    </div>
</div>

<!-- Detailed Table -->
<div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px;">
    <div style="padding: 24px; border-bottom: 1px solid var(--border-color);">
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text-main);">Customer Analytics Ledger</h3>
    </div>
    <div style="padding: 24px;">
        <div class="table-responsive">
            <table id="crm_table" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Mobile</th>
                        <th>Segment</th>
                        <th>Wallet</th>
                        <th>Debt</th>
                        <th>Visits</th>
                        <th>Last Visit</th>
                        <th>Lifetime Spend</th>
                        <th>Subscriptions</th>
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
.table-modern th { background: #f8fafc; color: var(--text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px; border-bottom: 1px solid #e2e8f0; text-align: left; }
.table-modern td { padding: 16px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.table-modern tbody tr:hover td { background: #f8fafc; }

/* Modal CSS */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-dialog { background: white; border-radius: 20px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: fadeUp 0.3s ease-out forwards; max-height: 90vh; overflow-y: auto;}
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

<!-- Custom V3 Modal Wrapper -->
<div class="modal-overlay" id="commonModalOverlay">
    <div class="modal-dialog" id="commonModalContent"></div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    
    // Setup DateRangePicker
    var start = moment().subtract(29, 'days');
    var end = moment();
    var isFiltered = false;

    function cb(start, end, label) {
        if(label === 'All Time' || !start || !end) {
            $('#datelabel').html('All Time');
            $('#filter_from').val('');
            $('#filter_to').val('');
            isFiltered = false;
        } else {
            $('#datelabel').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('#filter_from').val(start.format('YYYY-MM-DD'));
            $('#filter_to').val(end.format('YYYY-MM-DD'));
            isFiltered = true;
        }
    }

    $('#crm_daterange').daterangepicker({
        autoUpdateInput: false,
        ranges: {
           'All Time': [null, null],
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);
    
    // Initialize with 'This Month' filter by default
    cb(moment().startOf('month'), moment().endOf('month'), 'This Month');

    var dataTable = $('#crm_table').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "pageLength": 15,
        "ajax": {
            "url": "ajax/crm_ajax.php",
            "type": "POST",
            "data": function(d) {
                d.method = "get_crm_data";
                d.from_date = $('#filter_from').val();
                d.to_date = $('#filter_to').val();
            }
        },
        "columns": [
            { "data": "customer_info", "orderable": false },
            { "data": "mobile" },
            { "data": "segment", "orderable": false },
            { "data": "wallet" },
            { "data": "debt" },
            { "data": "visits" },
            { "data": "last_visit", "orderable": false },
            { "data": "total_spent", "orderable": false },
            { "data": "subscriptions", "orderable": false },
            { "data": "action", "orderable": false }
        ]
    });

    function loadKPIs() {
        $.ajax({
            url: "ajax/crm_ajax.php",
            type: "POST",
            data: {
                method: "get_crm_kpis",
                from_date: $('#filter_from').val(),
                to_date: $('#filter_to').val()
            },
            success: function(res) {
                var data = JSON.parse(res);
                if(data.error === 0) {
                    $('#kpi_total_customers').text(data.total_customers.toLocaleString('en-IN'));
                    $('#kpi_avg_ltv').text('₹' + data.avg_ltv.toLocaleString('en-IN', {maximumFractionDigits: 0}));
                    $('#kpi_total_wallet').text('₹' + data.total_wallet.toLocaleString('en-IN', {maximumFractionDigits: 0}));
                    $('#kpi_total_debt').text('₹' + data.total_debt.toLocaleString('en-IN', {maximumFractionDigits: 0}));
                }
            }
        });
    }

    // Load initial data
    loadKPIs();

    $('#btn_apply_filter').click(function() {
        dataTable.draw();
        loadKPIs();
    });

    // CSV Export Logic
    $('#btn_export_csv').click(function() {
        var btn = $(this);
        var origText = btn.html();
        btn.html('<i class="ph ph-spinner ph-spin"></i> Exporting...');
        btn.prop('disabled', true);
        
        // We'll call the API but with length=-1 to get all records, or a specific CSV generator
        // To be safe, we just use length=-1
        $.ajax({
            url: "ajax/crm_ajax.php",
            type: "POST",
            data: {
                method: "get_crm_data",
                from_date: $('#filter_from').val(),
                to_date: $('#filter_to').val(),
                length: -1, // Export all matching
                start: 0,
                draw: 1,
                order: [{column: 0, dir: 'desc'}]
            },
            success: function(res) {
                var json = JSON.parse(res);
                var data = json.data;
                var csv = 'Customer Name,Mobile,Joined Date,Wallet Balance,Outstanding Debt,Total Visits,Lifetime Spend\n';
                
                data.forEach(function(row) {
                    // Extract text from HTML
                    var tmp = document.createElement("DIV");
                    
                    tmp.innerHTML = row.customer_info; var name = tmp.textContent || tmp.innerText;
                    name = name.split('•')[0].trim(); // Simple cleanup
                    
                    tmp.innerHTML = row.wallet; var wallet = tmp.textContent || tmp.innerText;
                    wallet = wallet.replace(/₹/g, '').trim();
                    
                    tmp.innerHTML = row.debt; var debt = tmp.textContent || tmp.innerText;
                    debt = debt.replace(/₹/g, '').trim();
                    
                    tmp.innerHTML = row.visits; var visits = tmp.textContent || tmp.innerText;
                    
                    tmp.innerHTML = row.total_spent; var spent = tmp.textContent || tmp.innerText;
                    spent = spent.replace(/₹/g, '').trim();
                    
                    csv += `"${name}","${row.mobile}","${row.joined}","${wallet}","${debt}","${visits}","${spent}"\n`;
                });
                
                var a = document.createElement('a');
                a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
                a.download = 'crm_detailed_report_' + moment().format('YYYY-MM-DD') + '.csv';
                a.click();
                
                btn.html(origText);
                btn.prop('disabled', false);
            }
        });
    });

    // Modal Handlers
    function loadModal(url) {
        $('#commonModalContent').html('<div style="padding: 40px; text-align: center;"><i class="ph ph-spinner ph-spin" style="font-size: 32px; color: var(--primary);"></i><p>Loading Profile...</p></div>');
        $('#commonModalOverlay').addClass('active');
        $.ajax({url: url, success: function(data) { $('#commonModalContent').html(data); }});
    }

    $(document).on('click', '.modalButtonCommon', function(e){
        e.preventDefault();
        if($(this).attr('data-href')) loadModal($(this).attr('data-href'));
    });

    $(document).on('click', '.close-modal', function(){ 
        $('#commonModalOverlay').removeClass('active'); 
    });

});
</script>

<?php include 'footer.php'; ?>

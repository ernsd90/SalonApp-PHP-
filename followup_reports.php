<?php include 'header.php'; ?>

<!-- DataTables Required CSS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 4px;">Follow-ups &amp; Tasks</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Log customer interactions, schedule reminders, and track conversion statuses.</p>
</div>

<!-- Unified Filter Section -->
<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 20px 24px; margin-bottom: 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        
        <!-- Follow-up Period -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <div style="font-weight: 700; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Follow-up Period</div>
            <div style="position: relative; cursor: pointer; min-width: 250px;" id="crm_daterange">
                <i class="ph ph-calendar-blank" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <div class="form-control" style="padding-left: 44px; background: #f8fafc; height: 40px; display: flex; align-items: center; justify-content: space-between; border-radius: 8px;">
                    <span id="datelabel" style="font-size:13px; font-weight:600; color:var(--text-main);">All Time</span>
                    <i class="ph ph-caret-down text-muted" style="margin-left:auto;"></i>
                </div>
                <input type="hidden" id="filter_from" value="">
                <input type="hidden" id="filter_to" value="">
            </div>
        </div>

        <!-- Type Filter -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <div style="font-weight: 700; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Interaction Type</div>
            <select id="filter_followup_type" class="form-control" style="padding: 6px 12px; height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid var(--border-color); background: #f8fafc; min-width: 130px; font-weight: 600; color: var(--text-main);">
                <option value="">All Types</option>
                <option value="Call">Call</option>
                <option value="Message">Message</option>
            </select>
        </div>

        <!-- Status Filter -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <div style="font-weight: 700; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Response / Status</div>
            <select id="filter_followup_status" class="form-control" style="padding: 6px 12px; height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid var(--border-color); background: #f8fafc; min-width: 160px; font-weight: 600; color: var(--text-main);">
                <option value="">All Statuses</option>
                <option value="Connected">Connected</option>
                <option value="No Answer">No Answer</option>
                <option value="Busy">Busy</option>
                <option value="Sent">Sent</option>
                <option value="Replied">Replied</option>
                <option value="No Reply">No Reply</option>
                <option value="Interested">Interested</option>
                <option value="Not Interested">Not Interested</option>
                <option value="Converted">Converted</option>
                <option value="Lost">Lost</option>
                <option value="Failed">Failed</option>
            </select>
        </div>

        <div style="display: flex; align-items: flex-end; height: 40px; margin-top: auto;">
            <button class="btn-primary" id="btn_apply_followup_filter" style="margin: 0; padding: 10px 20px; height: 40px; display: flex; align-items: center; gap: 8px; font-size: 13px; border-radius: 8px;">
                <i class="ph ph-funnel"></i> Apply Filter
            </button>
        </div>
    </div>
    
    <div style="display: flex; align-items: flex-end; height: 40px; margin-top: auto;">
        <button class="btn-secondary" id="btn_export_csv" style="margin: 0; padding: 10px 20px; height: 40px; display: flex; align-items: center; gap: 8px; font-size: 13px; border-radius: 8px;">
            <i class="ph ph-file-csv"></i> Export CSV
        </button>
    </div>
</div>

<!-- KPI Section -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 24px;">
    <!-- Total Follow-ups -->
    <div style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 24px; border-radius: 20px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">Total Follow-ups</div>
            <div style="background: rgba(255,255,255,0.1); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-phone-call" style="font-size: 20px; color: #38bdf8;"></i>
            </div>
        </div>
        <div style="font-size: 32px; font-weight: 800;" id="f_kpi_total">
            <i class="ph ph-spinner ph-spin text-muted" style="font-size: 24px;"></i>
        </div>
    </div>
    
    <!-- Calls -->
    <div style="background: linear-gradient(135deg, #fff7ed, #ffedd5); border: 1px solid #fed7aa; padding: 24px; border-radius: 20px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #c2410c;">Calls</div>
            <div style="background: #fed7aa; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-phone" style="font-size: 20px; color: #ea580c;"></i>
            </div>
        </div>
        <div style="font-size: 32px; font-weight: 800; color: #7c2d12;" id="f_kpi_calls">
            <i class="ph ph-spinner ph-spin text-muted" style="font-size: 24px;"></i>
        </div>
    </div>

    <!-- Messages -->
    <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 1px solid #bae6fd; padding: 24px; border-radius: 20px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #0369a1;">Messages</div>
            <div style="background: #bae6fd; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-chats-teardrop" style="font-size: 20px; color: #0284c7;"></i>
            </div>
        </div>
        <div style="font-size: 32px; font-weight: 800; color: #0c4a6e;" id="f_kpi_messages">
            <i class="ph ph-spinner ph-spin text-muted" style="font-size: 24px;"></i>
        </div>
    </div>

    <!-- Conversions -->
    <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0; padding: 24px; border-radius: 20px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #15803d;">Conversions</div>
            <div style="background: #bbf7d0; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="ph-fill ph-trend-up" style="font-size: 20px; color: #16a34a;"></i>
            </div>
        </div>
        <div style="font-size: 32px; font-weight: 800; color: #14532d;" id="f_kpi_conversions">
            <i class="ph ph-spinner ph-spin text-muted" style="font-size: 24px;"></i>
        </div>
    </div>
</div>

<!-- Main Split Layout -->
<div style="display: grid; grid-template-columns: 3fr 1fr; gap: 24px; align-items: start; margin-bottom: 30px;">
    
    <!-- Left: Historical Logs Table -->
    <div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 24px; overflow: hidden;">
        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: #0f172a;">Historical Communication Logs</h3>
        <div class="table-responsive">
            <table id="followups_table" class="table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Mobile</th>
                        <th>Type</th>
                        <th style="min-width: 180px;">Notes</th>
                        <th>Response/Status</th>
                        <th>Follow-up Date</th>
                        <th>Logged By</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Right: Upcoming Tasks Widget -->
    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm);">
        <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-alarm" style="color: #ea580c; font-size: 18px;"></i>
            Upcoming Tasks
        </h4>
        <p style="font-size: 11px; color: var(--text-muted); margin: 0 0 16px 0;">Clients scheduled for call/message today or overdue.</p>
        
        <div id="upcoming_tasks_list" style="display: flex; flex-direction: column; gap: 12px; max-height: 540px; overflow-y: auto; padding-right: 4px;">
            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-style: italic; font-size: 12px;">Loading tasks...</div>
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

/* Scrollbar styles for tasks */
#upcoming_tasks_list::-webkit-scrollbar { width: 4px; }
#upcoming_tasks_list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
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

    // Initialize follow-ups DataTable
    var followupsTable = $('#followups_table').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "pageLength": 15,
        "ajax": {
            "url": "ajax/crm_ajax.php",
            "type": "POST",
            "data": function(d) {
                d.method = "get_followup_logs";
                d.from_date = $('#filter_from').val();
                d.to_date = $('#filter_to').val();
                d.type = $('#filter_followup_type').val();
                d.status = $('#filter_followup_status').val();
            }
        },
        "columns": [
            { "data": "created_at" },
            { "data": "cust_name" },
            { "data": "cust_mobile" },
            { "data": "type" },
            { "data": "notes" },
            { "data": "status" },
            { "data": "followup_date" },
            { "data": "logged_by" }
        ],
        "order": [[ 0, "desc" ]]
    });

    // Load KPIs function
    function loadFollowupKPIs() {
        $('#f_kpi_total').html('<i class="ph ph-spinner ph-spin text-muted" style="font-size: 20px;"></i>');
        $('#f_kpi_calls').html('<i class="ph ph-spinner ph-spin text-muted" style="font-size: 20px;"></i>');
        $('#f_kpi_messages').html('<i class="ph ph-spinner ph-spin text-muted" style="font-size: 20px;"></i>');
        $('#f_kpi_conversions').html('<i class="ph ph-spinner ph-spin text-muted" style="font-size: 20px;"></i>');

        $.ajax({
            url: "ajax/crm_ajax.php",
            type: "POST",
            data: {
                method: "get_followup_kpis",
                from_date: $('#filter_from').val(),
                to_date: $('#filter_to').val()
            },
            success: function(res) {
                var data = JSON.parse(res);
                if(data.error === 0) {
                    $('#f_kpi_total').text(data.total);
                    $('#f_kpi_calls').text(data.calls);
                    $('#f_kpi_messages').text(data.messages);
                    $('#f_kpi_conversions').text(data.conversions);
                } else {
                    $('#f_kpi_total, #f_kpi_calls, #f_kpi_messages, #f_kpi_conversions').text('0');
                }
            },
            error: function() {
                $('#f_kpi_total, #f_kpi_calls, #f_kpi_messages, #f_kpi_conversions').text('N/A');
            }
        });
    }

    // Load Upcoming Tasks
    function loadUpcomingTasks() {
        var container = $('#upcoming_tasks_list');
        container.html('<div style="text-align: center; padding: 20px; color: var(--text-muted); font-style: italic; font-size: 12px;"><i class="ph ph-spinner ph-spin" style="font-size: 16px;"></i> Loading tasks...</div>');
        
        $.ajax({
            url: "ajax/crm_ajax.php",
            type: "POST",
            data: {
                method: "get_upcoming_followups"
            },
            success: function(res) {
                var data = JSON.parse(res);
                container.empty();
                if(data.error === 0 && data.data.length > 0) {
                    data.data.forEach(function(task) {
                        var dateBadgeColor = task.is_overdue ? '#fee2e2' : '#ffedd5';
                        var dateTextColor = task.is_overdue ? '#dc2626' : '#ea580c';
                        var taskHtml = `
                            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 8px; box-shadow: var(--shadow-xs);">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <div style="font-weight: 700; font-size: 13px; color: var(--text-main);">${task.cust_name}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">${task.cust_mobile}</div>
                                    </div>
                                    <span style="background: ${dateBadgeColor}; color: ${dateTextColor}; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                        ${task.followup_date_formatted}
                                    </span>
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted); line-height: 1.3;">
                                    <strong>Last Note:</strong> ${task.notes || 'No notes'}
                                </div>
                                <div style="display: flex; gap: 8px; justify-content: flex-end; border-top: 1px solid #f1f5f9; padding-top: 8px; margin-top: 4px;">
                                    <a href="https://wa.me/91${task.cust_mobile}" target="_blank" style="background: #25d366; color: white; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; text-decoration: none;" title="WhatsApp">
                                        <i class="ph-fill ph-whatsapp-logo"></i>
                                    </a>
                                    <button class="modalButtonCommon" data-href="customer_followup.php?cust_id=${task.cust_id}" style="background: #ffedd5; color: #ea580c; border: none; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; cursor: pointer;" title="Log Action">
                                        <i class="ph ph-phone"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        container.append(taskHtml);
                    });
                } else {
                    container.html('<div style="text-align: center; padding: 20px; color: var(--text-muted); font-style: italic; font-size: 12px;">No upcoming tasks!</div>');
                }
            },
            error: function() {
                container.html('<div style="text-align: center; padding: 20px; color: var(--text-danger); font-style: italic; font-size: 12px;">Failed to load tasks!</div>');
            }
        });
    }

    // Load initial data
    loadFollowupKPIs();
    loadUpcomingTasks();

    // Apply Filters
    $('#btn_apply_followup_filter').click(function() {
        followupsTable.draw();
        loadFollowupKPIs();
    });

    // CSV Export
    $('#btn_export_csv').click(function() {
        var btn = $(this);
        var origText = btn.html();
        btn.html('<i class="ph ph-spinner ph-spin"></i> Exporting...');
        btn.prop('disabled', true);
        
        $.ajax({
            url: "ajax/crm_ajax.php",
            type: "POST",
            data: {
                method: "get_followup_logs",
                from_date: $('#filter_from').val(),
                to_date: $('#filter_to').val(),
                type: $('#filter_followup_type').val(),
                status: $('#filter_followup_status').val(),
                length: -1, // Export all matching
                start: 0,
                draw: 1,
                order: [{column: 0, dir: 'desc'}]
            },
            success: function(res) {
                var json = JSON.parse(res);
                var data = json.data;
                var csv = 'Date,Customer,Mobile,Type,Notes,Response/Status,Next Follow-up Date,Logged By\n';
                
                data.forEach(function(row) {
                    var tmp = document.createElement("DIV");
                    
                    tmp.innerHTML = row.created_at; var date = tmp.textContent || tmp.innerText;
                    tmp.innerHTML = row.cust_name; var name = tmp.textContent || tmp.innerText;
                    var mobile = row.cust_mobile;
                    var type = row.type;
                    tmp.innerHTML = row.notes; var notes = tmp.textContent || tmp.innerText;
                    notes = notes.replace(/"/g, '""'); // Escape double quotes
                    var status = row.status;
                    var followup_date = row.followup_date || 'N/A';
                    var logged_by = row.logged_by;
                    
                    csv += `"${date}","${name}","${mobile}","${type}","${notes}","${status}","${followup_date}","${logged_by}"\n`;
                });
                
                var a = document.createElement('a');
                a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
                a.download = 'crm_followups_report_' + moment().format('YYYY-MM-DD') + '.csv';
                a.click();
                
                btn.html(origText);
                btn.prop('disabled', false);
            },
            error: function() {
                alert("Failed to export CSV. Please try again.");
                btn.html(origText);
                btn.prop('disabled', false);
            }
        });
    });

    // Modal Handlers
    function loadModal(url) {
        $('#commonModalContent').html('<div style="padding: 40px; text-align: center;"><i class="ph ph-spinner ph-spin" style="font-size: 32px; color: var(--primary);"></i><p>Loading Engagement Card...</p></div>');
        $('#commonModalOverlay').addClass('active');
        $.ajax({
            url: url, 
            success: function(data) { 
                $('#commonModalContent').html(data); 
            },
            error: function() {
                $('#commonModalContent').html('<div style="padding: 24px; text-align: center; color: red;"><p>Failed to load form. Please try again.</p><button class="btn-secondary close-modal" style="width: auto; margin-top: 12px;">Close</button></div>');
            }
        });
    }

    $(document).on('click', '.modalButtonCommon', function(e){
        e.preventDefault();
        if($(this).attr('data-href')) loadModal($(this).attr('data-href'));
    });

    $(document).on('click', '.close-modal', function(){ 
        $('#commonModalOverlay').removeClass('active'); 
    });

    // Form submission inside modal
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
                    
                    // Redraw logs and refresh panels
                    followupsTable.draw(false);
                    loadFollowupKPIs();
                    loadUpcomingTasks();
                }
            },
            error: function() {
                alert("An error occurred while saving the log.");
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

});
</script>

<?php include 'footer.php'; ?>

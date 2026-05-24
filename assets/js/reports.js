$(document).ready(function () {
    // Global date variables to prevent race conditions during DataTables init
    var globalFromDate = moment().format('YYYY-MM-DD');
    var globalToDate = moment().format('YYYY-MM-DD');

    // 1. Initialize DateRangePicker
    var start = moment();
    var end = moment();

    function cb(start, end) {
        if (start.format('YYYY-MM-DD') == end.format('YYYY-MM-DD')) {
            if (start.format('YYYY-MM-DD') == moment().format('YYYY-MM-DD')) {
                $('#datelabel').html('Today');
            } else if (start.format('YYYY-MM-DD') == moment().subtract(1, 'days').format('YYYY-MM-DD')) {
                $('#datelabel').html('Yesterday');
            } else {
                $('#datelabel').html(start.format('D MMM YYYY'));
            }
        } else {
            $('#datelabel').html(start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY'));
        }

        // Update globals used by DataTables
        globalFromDate = start.format('YYYY-MM-DD');
        globalToDate = end.format('YYYY-MM-DD');

        // When date changes, reload the active tab
        loadActiveTab();
    }

    $('#reportdaterange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    // Initial load: directly call for the Sales tab since Bootstrap may not have
    // set the .active class on the nav-link before cb() fires on page load
    cb(start, end);

    // 2. Tab Change Listener — pass the event target directly to avoid timing issues
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('data-bs-target');
        loadTabContent(target);
    });

    // 3. Main Data Loader
    function loadActiveTab() {
        var activeTab = $('button[data-bs-toggle="pill"].active').attr('data-bs-target') ||
            $('button[data-bs-toggle="pill"][aria-selected="true"]').attr('data-bs-target') ||
            '#tab-sales';
        loadTabContent(activeTab);
    }

    function loadTabContent(activeTab) {
        if (!activeTab) activeTab = '#tab-sales';

        // Pass the format expected by the legacy AJAX handlers
        var apiFromDate = moment(globalFromDate).format('DD-MM-YYYY');
        var apiToDate = moment(globalToDate).format('DD-MM-YYYY');

        if (activeTab === '#tab-sales') {
            loadSalesSummary(apiFromDate, apiToDate);
        } else if (activeTab === '#tab-pnl') {
            loadPNLSummary(apiFromDate, apiToDate);
        } else if (activeTab === '#tab-ledger') {
            loadDetailedLedger(apiFromDate, apiToDate);
        } else if (activeTab === '#tab-services') {
            loadServices(apiFromDate, apiToDate);
        } else if (activeTab === '#tab-staff') {
            loadStaffPerformance(apiFromDate, apiToDate);
        } else if (activeTab === '#tab-expenses') {
            loadExpenses(apiFromDate, apiToDate);
        }
    }

    // --- DataTables Instances ---
    var salesTable, ledgerTable, servicesTable, staffTable, expensesTable;

    function loadSalesSummary(fromdate, todate) {
        $.ajax({
            url: 'ajax/report_ajax.php',
            type: 'POST',
            data: { method: 'summary_sale', fromdate: fromdate, todate: todate, refrence_by: '0', staff_id: '0' },
            success: function (res) {
                try {
                    var obj = JSON.parse(res);
                    if (obj.error != 1) {
                        $('#total_revenue').html(obj.grand_total || '0');
                        $('#total_customers').html(obj.total_customer || '0');
                        $('#cash_collections').html(obj.total_cash || '0');
                        $('#digital_cards').html(obj.total_cc || '0');
                    }
                } catch (e) {
                    console.error("Failed to parse Sales Summary JSON: ", res);
                }
            },
            error: function (err) {
                console.error("AJAX Error in Sales Summary: ", err);
            }
        });

        if (!salesTable) {
            salesTable = initSalesTable('#salesTable');
        } else {
            salesTable.ajax.reload();
        }
    }

    function initSalesTable(selector) {
        return $(selector).DataTable({
            processing: true, serverSide: true, responsive: true, destroy: true,
            ajax: {
                url: "ajax/report_ajax.php", type: "POST",
                data: function (d) {
                    d.fromdate = moment(globalFromDate).format('DD-MM-YYYY');
                    d.todate = moment(globalToDate).format('DD-MM-YYYY');
                    d.method = "get_salerecord";
                    d.refrence_by = '0';
                    d.staff_id = '0';
                }
            },
            columns: [
                { data: "invoice_id", name: "ID", render: function (data) { return '<span style="font-weight:600;color:var(--primary);">#' + data + '</span>'; } },
                { data: "invoice_date", name: "Date" },
                { data: "cust_name", name: "Client", render: function (data, type, row) { return '<span style="font-weight:600;">' + (data || '') + '</span> <small class="text-muted">' + (row.cust_mob || '') + '</small>'; } },
                { data: "grand_total", name: "Amount", render: function (data) { return '<span style="font-weight:700;color:#16a34a;">₹' + data + '</span>'; } },
                { data: "payment_mode", name: "Mode", render: function (data) { return '<span style="text-transform:uppercase;font-size:12px;font-weight:600;color:var(--text-muted);">' + (data || '') + '</span>'; } }
            ]
        });
    }

    function loadPNLSummary(fromdate, todate) {
        // Reset UI with loaders
        $('#pnl_income_body').html('<div class="text-center text-muted" style="padding:20px;"><i class="ph ph-spinner ph-spin"></i> Loading...</div>');
        $('#pnl_expense_body').html('<div class="text-center text-muted" style="padding:20px;"><i class="ph ph-spinner ph-spin"></i> Loading...</div>');
        $('#pnl_total_income').html('₹0.00');
        $('#pnl_total_expense').html('₹0.00');
        $('#pnl_net_profit').html('<i class="ph ph-spinner ph-spin"></i>');
        $('#pnl_profit_badge').hide();

        $.ajax({
            url: 'ajax/report_ajax.php',
            type: 'POST',
            data: { method: 'get_pnl_report', fromdate: fromdate, todate: todate },
            success: function (res) {
                try {
                    var obj = JSON.parse(res);
                    if (obj.error == 0) {

                        // Render Income
                        var incHtml = '';
                        if (obj.income.service > 0) incHtml += '<div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9;"><span>Service Sales</span><strong style="color:var(--text-main);">₹' + parseFloat(obj.income.service).toFixed(2) + '</strong></div>';
                        if (obj.income.product > 0) incHtml += '<div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9;"><span>Product Sales</span><strong style="color:var(--text-main);">₹' + parseFloat(obj.income.product).toFixed(2) + '</strong></div>';
                        if (obj.income.membership > 0) incHtml += '<div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9;"><span>Memberships</span><strong style="color:var(--text-main);">₹' + parseFloat(obj.income.membership).toFixed(2) + '</strong></div>';
                        if (obj.income.package > 0) incHtml += '<div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9;"><span>Packages</span><strong style="color:var(--text-main);">₹' + parseFloat(obj.income.package).toFixed(2) + '</strong></div>';

                        incHtml = incHtml || '<div class="text-center text-muted" style="padding:20px;">No income data found.</div>';
                        $('#pnl_income_body').html(incHtml);
                        $('#pnl_total_income').html('₹' + parseFloat(obj.total_income).toFixed(2));

                        // Render Expense
                        var expHtml = '';
                        if (obj.expenses && obj.expenses.length > 0) {
                            obj.expenses.forEach(function (e) {
                                expHtml += '<div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9;"><span>' + e.category + '</span><strong style="color:var(--text-main);">₹' + parseFloat(e.amount).toFixed(2) + '</strong></div>';
                            });
                        } else {
                            expHtml = '<div class="text-center text-muted" style="padding:20px;">No expense data found.</div>';
                        }
                        $('#pnl_expense_body').html(expHtml);
                        $('#pnl_total_expense').html('₹' + parseFloat(obj.total_expense).toFixed(2));

                        // Render Net Profit
                        var np = parseFloat(obj.net_profit);
                        $('#pnl_net_profit').html((np < 0 ? '-₹' : '₹') + Math.abs(np).toFixed(2));

                        var bColor = np >= 0 ? '#10b981' : '#ef4444';
                        var bText = np >= 0 ? '<i class="ph ph-trend-up"></i> NET PROFIT' : '<i class="ph ph-trend-down"></i> NET LOSS';
                        $('#pnl_profit_badge').html(bText).css('color', bColor).show();

                    }
                } catch (e) {
                    console.error("Failed to parse PNL JSON format: ", res);
                }
            }
        });
    }

    function initLedgerTable(selector) {
        return $(selector).DataTable({
            processing: true, serverSide: true, responsive: true, destroy: true,
            ajax: {
                url: "ajax/report_ajax.php", type: "POST",
                data: function (d) {
                    d.fromdate = moment(globalFromDate).format('DD-MM-YYYY');
                    d.todate = moment(globalToDate).format('DD-MM-YYYY');
                    d.method = "get_salerecord";
                    d.refrence_by = '0';
                    d.staff_id = '0';
                }
            },
            columns: [
                { data: "invoice_id", name: "ID", render: function (data) { return '<span style="font-weight:600;color:var(--primary);">#' + data + '</span>'; } },
                { data: "invoice_date", name: "Date" },
                { data: "cust_name", name: "Client", render: function (data, type, row) { return '<span style="font-weight:600;">' + data + '</span> <small class="text-muted">' + row.cust_mob + '</small>'; } },
                { data: "grand_total", name: "Amount", render: function (data) { return '<span style="font-weight:700;color:#16a34a;">₹' + data + '</span>'; } },
                { data: "payment_mode", name: "Mode", render: function (data) { return '<span style="text-transform:uppercase;font-size:12px;font-weight:600;color:var(--text-muted);">' + data + '</span>'; } },
                { data: null, defaultContent: "—", orderable: false },
                { data: "action", orderable: false }
            ],
            drawCallback: function (settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }

    function loadDetailedLedger(fromdate, todate) {
        if (!$.fn.DataTable.isDataTable('#ledgerTable')) {
            initLedgerTable('#ledgerTable');
        } else {
            $('#ledgerTable').DataTable().ajax.reload();
        }
    }

    function loadServices(fromdate, todate) {
        if (!servicesTable) {
            servicesTable = $('#servicesTable').DataTable({
                processing: true, serverSide: true, responsive: true,
                ajax: {
                    url: "ajax/report_ajax.php", type: "POST",
                    data: function (d) {
                        d.fromdate = moment(globalFromDate).format('DD-MM-YYYY');
                        d.todate = moment(globalToDate).format('DD-MM-YYYY');
                        d.method = "get_servicerecord";
                        d.pkg_service = 0; d.membership_include = 0;
                        d.refrence_by = '0'; d.staff_id = '0';
                    }
                },
                columns: [
                    { data: "sevice_name", render: function (data) { return '<span style="font-weight:600;">' + data + '</span>'; } },
                    { data: "service_price", render: function (data) { return '₹' + data; } },
                    { data: "service_qty", render: function (data) { return '<span style="font-weight:600;color:var(--primary);">' + data + 'x</span>'; } },
                    { data: "service_grand", render: function (data) { return '<span style="font-weight:700;color:#16a34a;">₹' + data + '</span>'; } }
                ]
            });
        } else {
            servicesTable.ajax.reload();
        }
    }

    function loadStaffPerformance(fromdate, todate) {
        if (!staffTable) {
            staffTable = $('#staffTable').DataTable({
                processing: true, serverSide: true, responsive: true,
                ajax: {
                    url: "ajax/report_ajax.php", type: "POST",
                    data: function (d) {
                        d.fromdate = moment(globalFromDate).format('DD-MM-YYYY');
                        d.todate = moment(globalToDate).format('DD-MM-YYYY');
                        d.method = "get_staffrecord";
                        d.refrence_by = '0'; d.staff_id = '0';
                    }
                },
                columns: [
                    { data: "staff_name", render: function (data, type, row) { 
                        if (typeof data === 'string' && data.indexOf('Grand Total') !== -1) {
                            return data;
                        }
                        return '<span style="font-weight:600;">' + data + '</span><br><span style="font-size:11px; color:var(--text-muted); font-weight:600;">Salary: ₹' + (row.staff_salary || 0) + '</span>'; 
                    } },
                    { data: "total_customer" },
                    { data: "service_sale", render: function (data) { return '₹' + data; } },
                    { data: "total_pkg_service", render: function (data) { return '₹' + data; } },
                    { data: "total_pkgsell", render: function (data) { return '₹' + data; } },
                    { data: "total_memsell", render: function (data) { return '₹' + data; } },
                    { data: "product_total", render: function (data) { return '₹' + data; } },
                    { data: "grand_total", render: function (data) { return '<span style="font-weight:700;color:#16a34a;">₹' + data + '</span>'; } }
                ]
            });
        } else {
            staffTable.ajax.reload();
        }
    }

    function loadExpenses(fromdate, todate) {
        if (!expensesTable) {
            expensesTable = $('#expensesTable').DataTable({
                processing: true, serverSide: true, responsive: true,
                ajax: {
                    url: "ajax/salon_ajax.php", type: "POST",
                    data: function (d) {
                        d.fromdate = moment(globalFromDate).format('DD-MM-YYYY');
                        d.todate = moment(globalToDate).format('DD-MM-YYYY');
                        d.method = "get_expenses";
                        d.category_id = ''; d.payment_mode = '';
                    }
                },
                columns: [
                    { data: "exp_date" },
                    { data: "category_name", render: function (data) { return '<span style="font-weight:600;">' + (data || 'General') + '</span>'; } },
                    { data: "exp_note", render: function (data, type, row) { return '<span class="text-muted">' + (data || '—') + '</span>'; } },
                    { data: "payment_mode", render: function (data) { return '<span style="text-transform:uppercase;font-size:12px;font-weight:600;color:var(--primary);">' + (data || '') + '</span>'; } },
                    { data: "exp_total", render: function (data) { return '<span style="font-weight:700;color:#dc2626;">₹' + data + '</span>'; } }
                ]
            });
        } else {
            expensesTable.ajax.reload();
        }
    }

});

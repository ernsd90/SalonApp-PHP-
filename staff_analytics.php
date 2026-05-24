<?php
include "header.php";
$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Premium Dashboard Styles */
    :root {
        --dash-bg: #f8fafc;
        --card-bg: rgba(255, 255, 255, 0.9);
        --primary-gradient: linear-gradient(135deg, #6366f1, #a855f7, #ec4899);
        --success-gradient: linear-gradient(135deg, #10b981, #34d399);
        --warning-gradient: linear-gradient(135deg, #f59e0b, #fbbf24);
        --info-gradient: linear-gradient(135deg, #3b82f6, #60a5fa);
    }
    
    body { background-color: var(--dash-bg); }

    .analytics-container {
        padding: 24px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .dashboard-title {
        font-size: 28px;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }

    .filters-bar {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        padding: 16px 24px;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .filter-group label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group select, .filter-group input {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        outline: none;
        transition: all 0.2s;
    }

    .filter-group select:focus, .filter-group input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .btn-apply {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .btn-apply:hover { opacity: 0.9; }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
        background: var(--primary-gradient);
    }

    .kpi-card.success::before { background: var(--success-gradient); }
    .kpi-card.warning::before { background: var(--warning-gradient); }
    .kpi-card.info::before { background: var(--info-gradient); }

    .kpi-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
    }

    .kpi-value {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
    }

    /* Charts Area */
    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    @media (max-width: 992px) {
        .charts-grid { grid-template-columns: 1fr; }
    }

    .chart-card {
        background: var(--card-bg);
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .chart-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 16px;
    }

    /* AI Insights */
    .insights-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 16px;
    }

    .insight-card {
        padding: 16px;
        border-radius: 12px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .insight-card.warning { background: #fffbeb; border: 1px solid #fde68a; }
    .insight-card.success { background: #ecfdf5; border: 1px solid #a7f3d0; }
    .insight-card.info { background: #eff6ff; border: 1px solid #bfdbfe; }

    .insight-icon {
        font-size: 24px;
    }
    .insight-card.warning .insight-icon { color: #d97706; }
    .insight-card.success .insight-icon { color: #059669; }
    .insight-card.info .insight-icon { color: #2563eb; }

    .insight-content h4 {
        margin: 0 0 4px 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }

    .insight-content p {
        margin: 0;
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
    }

    /* Data Table */
    .metrics-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }
    .metrics-table th, .metrics-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }
    .metrics-table th {
        font-weight: 600;
        color: #64748b;
        background: #f8fafc;
    }
    .metrics-table tbody tr:hover {
        background: #f1f5f9;
    }
</style>

<div class="analytics-container">
    
    <div class="dashboard-header">
        <h1 class="dashboard-title"><i class="ph-duotone ph-chart-polar"></i> Staff Analytics & Insights</h1>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
        <div class="filter-group">
            <label>From Date</label>
            <input type="date" id="filter_from" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="filter-group">
            <label>To Date</label>
            <input type="date" id="filter_to" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="filter-group">
            <label>Department</label>
            <input type="text" id="filter_dept" placeholder="All">
        </div>
        <div class="filter-group">
            <label>Role</label>
            <input type="text" id="filter_role" placeholder="All">
        </div>
        <div class="filter-group">
            <label>Gender</label>
            <select id="filter_gender">
                <option value="">All</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Seniority</label>
            <select id="filter_senior">
                <option value="">All</option>
                <option value="Junior">Junior</option>
                <option value="Senior">Senior</option>
                <option value="Master">Master</option>
            </select>
        </div>
        <button class="btn-apply" onclick="loadDashboard()"><i class="ph ph-funnel"></i> Apply Filters</button>
    </div>

    <!-- KPI Grid -->
    <div class="kpi-grid">
        <div class="kpi-card success">
            <div class="kpi-label">Top Performer (Revenue)</div>
            <div class="kpi-value" id="kpi_top_performer">-</div>
            <div style="font-size:12px;color:#64748b;" id="kpi_highest_revenue">₹0</div>
        </div>
        <div class="kpi-card info">
            <div class="kpi-label">Most Booked Staff</div>
            <div class="kpi-value" id="kpi_most_booked">-</div>
            <div style="font-size:12px;color:#64748b;">Highest number of services</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Target Achievement</div>
            <div class="kpi-value" id="kpi_target_pct">0%</div>
            <div style="font-size:12px;color:#64748b;" id="kpi_target_text">Generated ₹0 of ₹0</div>
        </div>
        <div class="kpi-card warning">
            <div class="kpi-label">Active Staff</div>
            <div class="kpi-value" id="kpi_total_staff">0</div>
            <div style="font-size:12px;color:#64748b;">Matching filters</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3 class="chart-title">Revenue by Staff</h3>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
        <div class="chart-card">
            <h3 class="chart-title">Popular Services</h3>
            <canvas id="servicesChart" height="220"></canvas>
        </div>
    </div>

    <!-- Performance Table & AI Insights -->
    <div class="charts-grid">
        <div class="chart-card" style="overflow-x:auto;">
            <h3 class="chart-title">Staff Performance Metrics</h3>
            <table class="metrics-table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Completed Services</th>
                        <th>Total Revenue</th>
                        <th>Avg. Billing Value</th>
                        <th>Avg. Per Service</th>
                    </tr>
                </thead>
                <tbody id="metrics_tbody">
                    <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="chart-card">
            <h3 class="chart-title"><i class="ph-duotone ph-sparkle" style="color:#a855f7;"></i> AI & Smart Insights</h3>
            <div class="insights-grid" id="insights_container" style="display:flex; flex-direction:column; gap:12px;">
                <!-- Insights rendered here -->
                <div style="padding:20px;text-align:center;color:#64748b;">Analyzing data...</div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Staff Report -->
    <div class="charts-grid" style="grid-template-columns: 1fr;">
        <div class="chart-card" style="overflow-x:auto;">
            <h3 class="chart-title">Comprehensive Staff Revenue Report</h3>
            <table class="metrics-table" style="white-space: nowrap;">
                <thead>
                    <tr>
                        <th>Stylist</th>
                        <th style="text-align:center;">Clients</th>
                        <th style="text-align:right;">Services</th>
                        <th style="text-align:center;">Service (Redemp.)</th>
                        <th style="text-align:right;">Package Sold</th>
                        <th style="text-align:right;">Membership Sold</th>
                        <th style="text-align:right;">Products Sold</th>
                        <th style="text-align:right; font-weight:800; color:#1e293b;">Total Generated</th>
                    </tr>
                </thead>
                <tbody id="comprehensive_tbody">
                    <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Service Breakdown -->
    <div class="charts-grid" style="grid-template-columns: 1fr;">
        <div class="chart-card" style="overflow-x:auto;">
            <h3 class="chart-title">Service Breakdown by Staff</h3>
            <table class="metrics-table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Service Name</th>
                        <th style="text-align:center;">Times (Wallet/Pkg)</th>
                        <th style="text-align:right;">Rev (Wallet/Pkg)</th>
                        <th style="text-align:center;">Times (Other)</th>
                        <th style="text-align:right;">Rev (Other)</th>
                        <th style="text-align:center; font-weight:800; color:#1e293b;">Total Times</th>
                        <th style="text-align:right; font-weight:800; color:#1e293b;">Total Revenue</th>
                    </tr>
                </thead>
                <tbody id="breakdown_tbody">
                    <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    let revenueChartInst = null;
    let servicesChartInst = null;

    $(document).ready(function() {
        loadDashboard();

        // Event delegation for expanding/collapsing staff rows
        $(document).on('click', '.staff-header', function() {
            let targetClass = $(this).data('target');
            let icon = $(this).find('.toggle-icon');
            
            $('.' + targetClass).toggle();
            
            if (icon.hasClass('ph-caret-up')) {
                icon.removeClass('ph-caret-up').addClass('ph-caret-down');
            } else {
                icon.removeClass('ph-caret-down').addClass('ph-caret-up');
            }
        });
    });

    function getFilters() {
        return {
            from_date: $('#filter_from').val(),
            to_date: $('#filter_to').val(),
            department: $('#filter_dept').val(),
            staff_role: $('#filter_role').val(),
            gender: $('#filter_gender').val(),
            seniority: $('#filter_senior').val()
        };
    }

    function loadDashboard() {
        let filters = getFilters();
        
        // 1. Load KPIs
        filters.method = 'get_dashboard_kpis';
        $.post('ajax/staff_analytics_ajax.php', filters, function(res) {
            let data = JSON.parse(res);
            $('#kpi_top_performer').text(data.top_performer);
            $('#kpi_highest_revenue').text('₹' + data.highest_revenue.toLocaleString());
            $('#kpi_most_booked').text(data.most_booked);
            $('#kpi_target_pct').text(data.target_pct + '%');
            $('#kpi_target_text').text(`Generated ₹${data.total_generated.toLocaleString()} of ₹${data.monthly_target.toLocaleString()}`);
            $('#kpi_total_staff').text(data.total_staff);

            renderRevenueChart(data.revenue_data);
        });

        // 2. Load Performance Metrics
        filters.method = 'get_performance_metrics';
        $.post('ajax/staff_analytics_ajax.php', filters, function(res) {
            let data = JSON.parse(res);
            let html = '';
            if(data.data && data.data.length > 0) {
                data.data.forEach(m => {
                    html += `<tr>
                        <td style="font-weight:600;color:#1e293b;">${m.staff_name}</td>
                        <td>${m.completed_services}</td>
                        <td style="color:#059669;font-weight:600;">₹${m.total_revenue.toLocaleString()}</td>
                        <td>₹${m.avg_billing.toLocaleString()}</td>
                        <td>₹${m.avg_per_service.toLocaleString()}</td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="5" style="text-align:center;">No data available for selected filters.</td></tr>';
            }
            $('#metrics_tbody').html(html);
        });

        // 3. Load Service Analytics & Breakdown
        filters.method = 'get_service_analytics';
        $.post('ajax/staff_analytics_ajax.php', filters, function(res) {
            let data = JSON.parse(res);
            renderServicesChart(data.popular_services);

            let html = '';
            if(data.breakdown && data.breakdown.length > 0) {
                // Pre-calculate staff totals
                let staffTotals = {};
                data.breakdown.forEach(b => {
                    if(!staffTotals[b.staff_name]) staffTotals[b.staff_name] = {times: 0, rev: 0};
                    staffTotals[b.staff_name].times += parseInt(b.total_count);
                    staffTotals[b.staff_name].rev += parseFloat(b.total_revenue);
                });

                let currentStaff = '';
                let staffIndex = 0;
                data.breakdown.forEach(b => {
                    if (b.staff_name !== currentStaff) {
                        staffIndex++;
                        let st = staffTotals[b.staff_name];
                        html += `<tr class="staff-header" data-target="staff-${staffIndex}" style="background:#f8fafc; cursor:pointer;">
                            <td colspan="8" style="padding:16px 12px 8px 12px;">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="background:var(--primary-gradient); color:white; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:12px;">
                                            ${b.staff_name.charAt(0)}
                                        </div>
                                        <span style="font-weight:700; color:#1e293b; font-size:15px;">${b.staff_name}</span>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:16px;">
                                        <span style="font-size:13px; font-weight:600; color:#475569;">Total Services: <span style="color:#0f172a; font-weight:800;">${st.times}</span></span>
                                        <span style="font-size:13px; font-weight:600; color:#475569;">Total Rev: <span style="color:#059669; font-weight:800;">₹${st.rev.toLocaleString()}</span></span>
                                        <i class="ph ph-caret-down toggle-icon" style="color:#64748b; font-size:16px;"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                        currentStaff = b.staff_name;
                    }
                    html += `<tr class="breakdown-row staff-${staffIndex}" style="display:none;">
                        <td style="width: 40px; border-bottom:1px dashed #e2e8f0;"></td>
                        <td style="font-weight:600; color:#475569; border-bottom:1px dashed #e2e8f0;">
                            <span style="background:#e0e7ff; color:#4f46e5; padding:2px 8px; border-radius:12px; font-size:12px; margin-right:8px;"><i class="ph ph-scissors"></i></span> 
                            ${b.service_name}
                        </td>
                        <td style="border-bottom:1px dashed #e2e8f0; text-align:center;">
                            <span style="background:#f1f5f9; padding:4px 10px; border-radius:8px; font-weight:700; font-size:13px;">${b.redemp_count}x</span>
                        </td>
                        <td style="color:#059669; font-weight:700; font-size:14px; border-bottom:1px dashed #e2e8f0; text-align:right;">
                            ₹${parseFloat(b.redemp_revenue).toLocaleString()}
                        </td>
                        <td style="border-bottom:1px dashed #e2e8f0; text-align:center;">
                            <span style="background:#f8fafc; padding:4px 10px; border-radius:8px; font-weight:700; font-size:13px;">${b.other_count}x</span>
                        </td>
                        <td style="color:#059669; font-weight:700; font-size:14px; border-bottom:1px dashed #e2e8f0; text-align:right;">
                            ₹${parseFloat(b.other_revenue).toLocaleString()}
                        </td>
                        <td style="border-bottom:1px dashed #e2e8f0; text-align:center;">
                            <span style="background:#e2e8f0; color:#0f172a; padding:4px 10px; border-radius:8px; font-weight:800; font-size:13px;">${b.total_count}x</span>
                        </td>
                        <td style="color:#059669; font-weight:800; font-size:15px; border-bottom:1px dashed #e2e8f0; text-align:right; background:#f8fafc;">
                            ₹${parseFloat(b.total_revenue).toLocaleString()}
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="8" style="text-align:center;">No data available.</td></tr>';
            }
            $('#breakdown_tbody').html(html);
        });

        // 4. Load AI Insights
        filters.method = 'get_ai_insights';
        $.post('ajax/staff_analytics_ajax.php', filters, function(res) {
            let data = JSON.parse(res);
            let html = '';
            if(data.data && data.data.length > 0) {
                data.data.forEach(insight => {
                    let icon = 'ph-info';
                    if(insight.type === 'warning') icon = 'ph-warning-circle';
                    if(insight.type === 'success') icon = 'ph-check-circle';
                    
                    html += `<div class="insight-card ${insight.type}">
                        <div class="insight-icon"><i class="ph-fill ${icon}"></i></div>
                        <div class="insight-content">
                            <h4>${insight.title}</h4>
                            <p>${insight.message}</p>
                        </div>
                    </div>`;
                });
            }
            $('#insights_container').html(html);
        });

        // 5. Load Comprehensive Report
        filters.method = 'get_comprehensive_report';
        $.post('ajax/staff_analytics_ajax.php', filters, function(res) {
            let data = JSON.parse(res);
            let html = '';
            if(data.data && data.data.length > 0) {
                let tClients = 0, tServices = 0, tRedemptions = 0, tPkgs = 0, tMems = 0, tProds = 0, tGen = 0;
                
                data.data.forEach(m => {
                    tClients += parseInt(m.clients) || 0;
                    tServices += parseFloat(m.services_rev) || 0;
                    tRedemptions += parseInt(m.redemptions) || 0;
                    tPkgs += parseFloat(m.packages_sold) || 0;
                    tMems += parseFloat(m.memberships_sold) || 0;
                    tProds += parseFloat(m.products_sold) || 0;
                    tGen += parseFloat(m.total_generated) || 0;

                    html += `<tr style="background:#fff;">
                        <td style="font-weight:600;color:#1e293b;">${m.staff_name}</td>
                        <td style="text-align:center;"><span style="background:#f1f5f9; padding:2px 8px; border-radius:12px; font-weight:600;">${m.clients}</span></td>
                        <td style="text-align:right;">₹${parseFloat(m.services_rev).toLocaleString()}</td>
                        <td style="text-align:center;">${m.redemptions}</td>
                        <td style="text-align:right;">₹${parseFloat(m.packages_sold).toLocaleString()}</td>
                        <td style="text-align:right;">₹${parseFloat(m.memberships_sold).toLocaleString()}</td>
                        <td style="text-align:right;">₹${parseFloat(m.products_sold).toLocaleString()}</td>
                        <td style="text-align:right; color:#059669; font-weight:800; font-size:15px; background:#ecfdf5;">₹${parseFloat(m.total_generated).toLocaleString()}</td>
                    </tr>`;
                });

                html += `<tr style="background:var(--primary-gradient); color:white;">
                    <td style="font-weight:800; font-size:14px; padding:12px;">GRAND TOTAL</td>
                    <td style="text-align:center; font-weight:800; font-size:14px;">${tClients}</td>
                    <td style="text-align:right; font-weight:800; font-size:14px;">₹${tServices.toLocaleString()}</td>
                    <td style="text-align:center; font-weight:800; font-size:14px;">${tRedemptions}</td>
                    <td style="text-align:right; font-weight:800; font-size:14px;">₹${tPkgs.toLocaleString()}</td>
                    <td style="text-align:right; font-weight:800; font-size:14px;">₹${tMems.toLocaleString()}</td>
                    <td style="text-align:right; font-weight:800; font-size:14px;">₹${tProds.toLocaleString()}</td>
                    <td style="text-align:right; font-weight:900; font-size:16px;">₹${tGen.toLocaleString()}</td>
                </tr>`;
            } else {
                html = '<tr><td colspan="8" style="text-align:center;">No data available for selected filters.</td></tr>';
            }
            $('#comprehensive_tbody').html(html);
        });
    }

    function renderRevenueChart(revenueData) {
        if(revenueChartInst) revenueChartInst.destroy();
        
        let labels = revenueData.map(d => d.staff_name);
        let data = revenueData.map(d => parseFloat(d.revenue));

        const ctx = document.getElementById('revenueChart').getContext('2d');
        revenueChartInst = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: data,
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    function renderServicesChart(popularData) {
        if(servicesChartInst) servicesChartInst.destroy();
        
        let labels = popularData.map(d => d.service_name);
        let data = popularData.map(d => parseInt(d.count));

        const ctx = document.getElementById('servicesChart').getContext('2d');
        servicesChartInst = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#6366f1', '#ec4899', '#8b5cf6', '#14b8a6', '#f59e0b',
                        '#3b82f6', '#10b981', '#f43f5e', '#64748b', '#84cc16'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                cutout: '70%'
            }
        });
    }
</script>

<?php include "footer.php"; ?>

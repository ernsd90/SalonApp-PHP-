<?php
include 'header.php';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="dashboard-header" style="margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;margin-bottom:4px;">Advanced Analytics</h1>
    <p style="color:var(--text-muted);font-size:14px;">Churn Risk, Service Performance Mix, and Guest Sentiment — all in one place.</p>
</div>

<!-- Global Date Filter (applies to P-Mix and Sentiment; Churn is always all-time) -->
<div style="background:white;border-radius:16px;border:1px solid var(--border-color);padding:16px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
    <i class="ph ph-funnel" style="color:var(--primary);font-size:20px;"></i>
    <span style="font-size:12px;color:var(--text-muted);background:#f1f5f9;padding:4px 10px;border-radius:20px;"><i class="ph ph-info"></i> Applies to P-Mix &amp; Sentiment only</span>
    <label style="font-weight:600;font-size:14px;margin:0;">From</label>
    <input type="date" id="gFrom" class="form-control" style="width:160px;" value="">
    <label style="font-weight:600;font-size:14px;margin:0;">To</label>
    <input type="date" id="gTo" class="form-control" style="width:160px;" value="">
    <button id="btn_apply" class="btn-primary" style="margin:0;padding:10px 24px;width:auto;">Apply</button>
    <button id="btn_reset" class="btn-secondary" style="margin:0;padding:10px 16px;width:auto;">All Time</button>
</div>

<!-- Tabs -->
<div style="display:flex;gap:4px;margin-bottom:24px;border-bottom:2px solid var(--border-color);">
    <button class="atab-btn active" data-tab="tab-churn" style="padding:12px 20px;border:none;background:none;font-weight:700;font-size:14px;cursor:pointer;border-bottom:3px solid var(--primary);color:var(--primary);margin-bottom:-2px;border-radius:8px 8px 0 0;">📉 Churn & At-Risk</button>
    <button class="atab-btn" data-tab="tab-pmix" style="padding:12px 20px;border:none;background:none;font-weight:600;font-size:14px;cursor:pointer;border-bottom:3px solid transparent;color:var(--text-muted);margin-bottom:-2px;border-radius:8px 8px 0 0;">📊 Service P-Mix</button>
    <button class="atab-btn" data-tab="tab-sentiment" style="padding:12px 20px;border:none;background:none;font-weight:600;font-size:14px;cursor:pointer;border-bottom:3px solid transparent;color:var(--text-muted);margin-bottom:-2px;border-radius:8px 8px 0 0;">⭐ Guest Sentiment</button>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB 1: Churn & At-Risk Dashboard                          -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="tab-churn" class="atab-panel">
    <!-- Churn KPIs -->
    <div id="churn-kpis" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
        <div class="kpi-skeleton" style="height:100px;border-radius:16px;background:#f1f5f9;animation:pulse 1.5s infinite;"></div>
        <div class="kpi-skeleton" style="height:100px;border-radius:16px;background:#f1f5f9;animation:pulse 1.5s infinite;"></div>
        <div class="kpi-skeleton" style="height:100px;border-radius:16px;background:#f1f5f9;animation:pulse 1.5s infinite;"></div>
        <div class="kpi-skeleton" style="height:100px;border-radius:16px;background:#f1f5f9;animation:pulse 1.5s infinite;"></div>
    </div>

    <!-- Churn Donut + List side by side -->
    <div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start;">
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);padding:24px;">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;">Segment Distribution</h3>
            <canvas id="churnDonut" height="260"></canvas>
            <div id="churn-legend" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;"></div>
        </div>
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);overflow:hidden;">
            <div style="padding:18px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:15px;font-weight:700;margin:0;">At-Risk Customers</h3>
                <div style="display:flex;gap:8px;">
                    <button class="churn-filter-btn active" data-days="30" style="background:#fee2e2;color:#dc2626;border:none;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;">30 Days</button>
                    <button class="churn-filter-btn" data-days="60" style="background:#f1f5f9;color:#475569;border:none;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;">60 Days</button>
                    <button class="churn-filter-btn" data-days="90" style="background:#f1f5f9;color:#475569;border:none;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;">90 Days</button>
                    <button class="churn-filter-btn" data-days="180" style="background:#f1f5f9;color:#475569;border:none;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;">6 Months</button>
                </div>
            </div>
            <div style="padding:0;">
                <table id="churn_table" class="table-modern" style="width:100%;">
                    <thead><tr><th>Customer</th><th>Mobile</th><th>Last Visit</th><th>Days Silent</th><th>Total Spend</th><th>Action</th></tr></thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB 2: Service P-Mix                                       -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="tab-pmix" class="atab-panel" style="display:none;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);padding:24px;">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;">Revenue by Service Category</h3>
            <canvas id="pmixBar" height="280"></canvas>
        </div>
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);padding:24px;">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;">Top vs Underperformer Split</h3>
            <canvas id="pmixDonut" height="280"></canvas>
        </div>
    </div>
    <div style="background:white;border-radius:20px;border:1px solid var(--border-color);overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid var(--border-color);">
            <h3 style="font-size:15px;font-weight:700;margin:0;">Service Performance Table</h3>
        </div>
        <div style="padding:0;">
            <table id="pmix_table" class="table-modern" style="width:100%;">
                <thead><tr><th>Service</th><th>Category</th><th>Times Sold</th><th>Revenue</th><th>Avg Price</th><th>Performance</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB 3: Guest Sentiment                                     -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="tab-sentiment" class="atab-panel" style="display:none;">
    <!-- Sentiment KPIs -->
    <div id="sentiment-kpis" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;"></div>

    <div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);padding:24px;">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;">Rating Distribution</h3>
            <canvas id="ratingBar" height="280"></canvas>
        </div>
        <div style="background:white;border-radius:20px;border:1px solid var(--border-color);overflow:hidden;">
            <div style="padding:18px 24px;border-bottom:1px solid var(--border-color);">
                <h3 style="font-size:15px;font-weight:700;margin:0;">Recent Guest Reviews</h3>
            </div>
            <div id="review-list" style="padding:20px;max-height:500px;overflow-y:auto;">
                <p style="color:var(--text-muted);text-align:center;">Loading reviews...</p>
            </div>
        </div>
    </div>
</div>

<style>
.atab-btn{transition:.2s;}
.atab-btn:hover{background:#f8fafc;}
.table-modern{width:100%;border-collapse:separate;border-spacing:0;}
.table-modern th{background:#f8fafc;color:var(--text-muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:12px 16px;border-bottom:1px solid #e2e8f0;text-align:left;}
.table-modern td{padding:12px 16px;font-size:13px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.table-modern tbody tr:hover td{background:#f8fafc;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.churn-filter-btn{transition:.2s;}
</style>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
var churnDays = 30;
var churnTable, pmixTable;
var donutChart, barChart, ratingChart, pmixDonutChart;

function getFrom(){ return $('#gFrom').val(); }
function getTo()  { return $('#gTo').val(); }

// ─── Tabs ────────────────────────────────────────────────────────────────────
$('.atab-btn').click(function(){
    var t = $(this).data('tab');
    $('.atab-panel').hide(); $('#'+t).show();
    $('.atab-btn').css({'border-bottom-color':'transparent','color':'var(--text-muted)','font-weight':'600'});
    $(this).css({'border-bottom-color':'var(--primary)','color':'var(--primary)','font-weight':'700'});
    if(t==='tab-pmix' && !pmixTable) initPmix();
    if(t==='tab-sentiment') loadSentiment();
});

$('#btn_apply').click(function(){ refresh(); });
$('#btn_reset').click(function(){ $('#gFrom').val(''); $('#gTo').val(''); refresh(); });

function refresh(){
    loadChurnKpis(); if(churnTable) churnTable.draw();
    if(pmixTable) { pmixTable.draw(); loadPmixCharts(); }
    if($('#tab-sentiment').is(':visible')) loadSentiment();
}

// ═══════════════════════════════════════════════════
// CHURN
// ═══════════════════════════════════════════════════
function loadChurnKpis(){
    $.post('ajax/analytics_ajax.php', {method:'churn_kpis', from:getFrom(), to:getTo()}, function(res){
        var d = JSON.parse(res);
        var cards = [
            {label:'Active (≤30d)', val:d.active,   bg:'#f0fdf4',bc:'#bbf7d0',c:'#14532d'},
            {label:'At Risk (31-90d)',val:d.at_risk, bg:'#fffbeb',bc:'#fde68a',c:'#92400e'},
            {label:'Lapsed (90d+)',  val:d.lapsed,  bg:'#fef2f2',bc:'#fecaca',c:'#7f1d1d'},
            {label:'Never Visited',  val:d.never,   bg:'#f8fafc',bc:'#e2e8f0',c:'#475569'},
        ];
        var html = cards.map(function(c){
            return '<div style="background:'+c.bg+';border:1.5px solid '+c.bc+';border-radius:16px;padding:20px;">'+
                '<div style="font-size:11px;color:'+c.c+';font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">'+c.label+'</div>'+
                '<div style="font-size:36px;font-weight:800;color:'+c.c+';">'+c.val+'</div></div>';
        }).join('');
        $('#churn-kpis').html(html);

        // Donut
        if(donutChart) donutChart.destroy();
        var ctx = document.getElementById('churnDonut').getContext('2d');
        donutChart = new Chart(ctx, {
            type:'doughnut',
            data:{ labels:['Active','At Risk','Lapsed','Never'],
                datasets:[{data:[d.active,d.at_risk,d.lapsed,d.never],
                    backgroundColor:['#22c55e','#f59e0b','#ef4444','#94a3b8'],
                    borderWidth:3, borderColor:'#fff'}]},
            options:{plugins:{legend:{display:false}},cutout:'70%'}
        });
        var legendHtml = [
            {c:'#22c55e',l:'Active (≤30d)',v:d.active},
            {c:'#f59e0b',l:'At Risk (31-90d)',v:d.at_risk},
            {c:'#ef4444',l:'Lapsed (90d+)',v:d.lapsed},
            {c:'#94a3b8',l:'Never Visited',v:d.never}
        ].map(function(x){
            return '<div style="display:flex;align-items:center;gap:8px;font-size:13px;">'+
                '<span style="width:12px;height:12px;border-radius:50%;background:'+x.c+';flex-shrink:0;"></span>'+
                '<span style="color:#64748b;">'+x.l+'</span><span style="margin-left:auto;font-weight:700;">'+x.v+'</span></div>';
        }).join('');
        $('#churn-legend').html(legendHtml);
    });
}

// Churn filter buttons
$('.churn-filter-btn').click(function(){
    churnDays = $(this).data('days');
    $('.churn-filter-btn').css({'background':'#f1f5f9','color':'#475569'});
    $(this).css({'background':'#fee2e2','color':'#dc2626'});
    if(churnTable) churnTable.draw();
});

function initChurnTable(){
    churnTable = $('#churn_table').DataTable({
        processing:true, serverSide:true, responsive:true, pageLength:15,
        ajax:{ url:'ajax/analytics_ajax.php', type:'POST',
            data:function(d){ d.method='churn_list'; d.days=churnDays; d.from=getFrom(); d.to=getTo(); }},
        columns:[
            {data:'customer_info', orderable:false},
            {data:'mobile'},
            {data:'last_visit'},
            {data:'days_silent'},
            {data:'total_spent'},
            {data:'action', orderable:false}
        ]
    });
}

// ═══════════════════════════════════════════════════
// P-MIX
// ═══════════════════════════════════════════════════
function initPmix(){
    pmixTable = $('#pmix_table').DataTable({
        processing:true, serverSide:true, responsive:true, pageLength:20,
        ajax:{ url:'ajax/analytics_ajax.php', type:'POST',
            data:function(d){ d.method='pmix_list'; d.from=getFrom(); d.to=getTo(); }},
        order:[[3,'desc']],
        columns:[
            {data:'service', orderable:true},
            {data:'category', orderable:true},
            {data:'times_sold'},
            {data:'revenue'},
            {data:'avg_price'},
            {data:'performance', orderable:false}
        ]
    });
    loadPmixCharts();
}

function loadPmixCharts(){
    $.post('ajax/analytics_ajax.php', {method:'pmix_charts', from:getFrom(), to:getTo()}, function(res){
        var d = JSON.parse(res);

        // Bar chart - category revenue
        if(barChart) barChart.destroy();
        var bCtx = document.getElementById('pmixBar').getContext('2d');
        barChart = new Chart(bCtx, {
            type:'bar',
            data:{
                labels: d.categories.map(function(c){ return c.name; }),
                datasets:[{
                    label:'Revenue (₹)',
                    data: d.categories.map(function(c){ return c.revenue; }),
                    backgroundColor:'rgba(99,102,241,0.8)',
                    borderRadius:8, borderSkipped:false
                }]
            },
            options:{responsive:true,plugins:{legend:{display:false}},
                scales:{y:{ticks:{callback:function(v){return '₹'+v.toLocaleString('en-IN');}}}}}
        });

        // Donut - top 20% vs rest
        if(pmixDonutChart) pmixDonutChart.destroy();
        var dCtx = document.getElementById('pmixDonut').getContext('2d');
        pmixDonutChart = new Chart(dCtx, {
            type:'doughnut',
            data:{
                labels:['Top 20% Services','Other 80%'],
                datasets:[{
                    data:[d.top20_revenue, d.rest80_revenue],
                    backgroundColor:['#6366f1','#e2e8f0'],
                    borderWidth:3, borderColor:'#fff'
                }]
            },
            options:{plugins:{legend:{position:'bottom'}},cutout:'65%'}
        });
    });
}

// ═══════════════════════════════════════════════════
// SENTIMENT
// ═══════════════════════════════════════════════════
function loadSentiment(){
    $.post('ajax/analytics_ajax.php', {method:'sentiment_data', from:getFrom(), to:getTo()}, function(res){
        var d = JSON.parse(res);
        var avg = d.avg_rating || 0;
        var stars = '★'.repeat(Math.round(avg)) + '☆'.repeat(5-Math.round(avg));
        var kpiHtml =
            '<div style="background:#fefce8;border:1.5px solid #fde68a;border-radius:16px;padding:20px;text-align:center;">'+
                '<div style="font-size:11px;color:#92400e;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Avg Rating</div>'+
                '<div style="font-size:40px;font-weight:800;color:#713f12;">'+parseFloat(avg).toFixed(1)+'</div>'+
                '<div style="color:#f59e0b;font-size:20px;letter-spacing:2px;">'+stars+'</div></div>'+
            '<div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:16px;padding:20px;text-align:center;">'+
                '<div style="font-size:11px;color:#14532d;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Total Reviews</div>'+
                '<div style="font-size:40px;font-weight:800;color:#14532d;">'+d.total+'</div></div>'+
            '<div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:16px;padding:20px;text-align:center;">'+
                '<div style="font-size:11px;color:#1d4ed8;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Positive (4-5 ★)</div>'+
                '<div style="font-size:40px;font-weight:800;color:#1d4ed8;">'+d.positive+'</div>'+
                '<div style="font-size:12px;color:#3b82f6;">'+d.positive_pct+'%</div></div>'+
            '<div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:16px;padding:20px;text-align:center;">'+
                '<div style="font-size:11px;color:#7f1d1d;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Negative (1-2 ★)</div>'+
                '<div style="font-size:40px;font-weight:800;color:#dc2626;">'+d.negative+'</div>'+
                '<div style="font-size:12px;color:#dc2626;">'+d.negative_pct+'%</div></div>';
        $('#sentiment-kpis').html(kpiHtml);

        // Rating bar chart
        if(ratingChart) ratingChart.destroy();
        var rCtx = document.getElementById('ratingBar').getContext('2d');
        ratingChart = new Chart(rCtx, {
            type:'bar',
            data:{
                labels:['1 ★','2 ★','3 ★','4 ★','5 ★'],
                datasets:[{
                    data:[d.r1,d.r2,d.r3,d.r4,d.r5],
                    backgroundColor:['#ef4444','#f97316','#eab308','#84cc16','#22c55e'],
                    borderRadius:8, borderSkipped:false
                }]
            },
            options:{responsive:true,plugins:{legend:{display:false}},
                scales:{y:{beginAtZero:true,ticks:{precision:0}}}}
        });

        // Reviews list
        var reviewHtml = (d.reviews || []).map(function(r){
            var stars = '★'.repeat(parseInt(r.rating||0)) + '☆'.repeat(5-parseInt(r.rating||0));
            var col = r.rating >= 4 ? '#16a34a' : (r.rating >= 3 ? '#d97706' : '#dc2626');
            return '<div style="padding:14px;border-bottom:1px solid #f1f5f9;">'+
                '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">'+
                    '<div style="font-weight:700;font-size:13px;">'+r.cust_name+'</div>'+
                    '<div style="color:'+col+';font-size:16px;letter-spacing:1px;">'+stars+'</div></div>'+
                '<div style="font-size:12px;color:#64748b;">'+r.comments+'</div>'+
                '<div style="font-size:11px;color:#94a3b8;margin-top:4px;">'+r.created_at+'</div></div>';
        }).join('') || '<p style="color:var(--text-muted);text-align:center;padding:20px;">No reviews yet for this period.</p>';
        $('#review-list').html(reviewHtml);
    });
}

// ─── Init ─────────────────────────────────────────────────────────────────────
$(function(){
    loadChurnKpis();
    initChurnTable();
});
</script>

<?php include 'footer.php'; ?>

<?php
include 'header.php';
?>

<style>
.budget-card { background: white; border-radius: 16px; padding: 24px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 24px; }
.budget-table { width: 100%; border-collapse: collapse; }
.budget-table th { background: #f8fafc; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid var(--border-color); text-align: left; }
.budget-table td { padding: 16px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border-color); vertical-align: middle; }
.budget-input { border: 1px solid var(--border-color); border-radius: 6px; padding: 8px 12px; width: 120px; font-size: 14px; outline: none; transition: 0.2s; }
.budget-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
.progress-bar { width: 100%; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; margin-top: 8px; }
.progress-fill { height: 100%; background: var(--primary); border-radius: 4px; transition: width 0.3s ease; }
.progress-fill.warning { background: #f59e0b; }
.progress-fill.danger { background: #dc2626; }
</style>

<div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Budget Management</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Set and track monthly spending limits per category.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="expenses_dashboard.php" class="btn-primary" style="background: white; color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 10px 16px; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: none;">
            <i class="ph-bold ph-chart-pie-slice"></i> Dashboard
        </a>
    </div>
</div>

<div class="card-modern" style="background:white;border-radius:16px;border:1px solid var(--border-color);box-shadow:0 4px 6px -1px rgba(0, 0, 0, 0.05);padding:18px 24px;margin-bottom:24px; display:flex; gap:16px; align-items:flex-end;">
    <div style="display:flex; flex-direction:column; gap:4px;">
        <label style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Month</label>
        <select id="b_month" class="form-control" style="height:38px;width:150px;background:#f8fafc;font-size:13px;">
            <?php 
            for($m=1; $m<=12; $m++){
                $sel = ($m == date('n')) ? 'selected' : '';
                echo "<option value='$m' $sel>".date('F', mktime(0,0,0,$m,1))."</option>";
            }
            ?>
        </select>
    </div>
    <div style="display:flex; flex-direction:column; gap:4px;">
        <label style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Year</label>
        <select id="b_year" class="form-control" style="height:38px;width:120px;background:#f8fafc;font-size:13px;">
            <?php 
            $cy = date('Y');
            for($y=$cy-2; $y<=$cy+2; $y++){
                $sel = ($y == $cy) ? 'selected' : '';
                echo "<option value='$y' $sel>$y</option>";
            }
            ?>
        </select>
    </div>
    <button id="btnLoadBudgets" class="btn-primary" style="height:38px;padding:0 24px;box-shadow:none;width:auto;font-size:13px;">
        <i class="ph-bold ph-arrows-clockwise"></i> Load
    </button>
</div>

<div class="budget-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Category Budgets</h3>
        <button id="btnSaveBudgets" class="btn-primary" style="height:38px;padding:0 24px;box-shadow:none;width:auto;font-size:13px;background:#10b981;">
            <i class="ph-bold ph-floppy-disk"></i> Save Budgets
        </button>
    </div>
    
    <div style="overflow-x:auto;">
        <table class="budget-table" id="budgetTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Planned Budget (₹)</th>
                    <th>Actual Spent (₹)</th>
                    <th>Remaining (₹)</th>
                    <th style="width:200px;">Usage Progress</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
function formatMoney(n) { return Number(n).toFixed(2); }

function loadBudgets() {
    $('#budgetTable tbody').html('<tr><td colspan="5" style="text-align:center;"><i class="ph ph-spinner ph-spin"></i> Loading...</td></tr>');
    
    $.post('ajax/expenses_budgets_ajax.php', {
        method: 'get_budgets',
        month: $('#b_month').val(),
        year: $('#b_year').val()
    }, function(res) {
        try {
            const result = JSON.parse(res);
            let html = '';
            
            result.data.forEach(b => {
                let pClass = '';
                if (b.usage_percent > 90) pClass = 'danger';
                else if (b.usage_percent > 75) pClass = 'warning';
                
                let w = Math.min(b.usage_percent, 100);
                
                html += `<tr>
                    <td style="font-weight:600;">${b.category_name}</td>
                    <td>
                        <input type="number" class="budget-input bud-val" data-id="${b.cat_id}" value="${b.budget_amount}" min="0" step="100" />
                    </td>
                    <td>₹${formatMoney(b.spent_amount)}</td>
                    <td style="color:${b.remaining < 0 ? '#dc2626' : 'inherit'}; font-weight:600;">
                        ₹${formatMoney(b.remaining)}
                    </td>
                    <td>
                        <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--text-muted); margin-bottom:4px;">
                            <span>${b.usage_percent.toFixed(1)}% Used</span>
                            ${b.remaining < 0 ? '<span style="color:#dc2626;"><i class="ph-bold ph-warning-circle"></i> Over Budget</span>' : ''}
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill ${pClass}" style="width: ${w}%;"></div>
                        </div>
                    </td>
                </tr>`;
            });
            
            if (html === '') html = '<tr><td colspan="5" style="text-align:center;">No categories found.</td></tr>';
            $('#budgetTable tbody').html(html);
        } catch(e) {
            console.error(e);
        }
    });
}

$('#btnLoadBudgets').click(loadBudgets);

$('#btnSaveBudgets').click(function() {
    let btn = $(this);
    let originalText = btn.html();
    btn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled', true);
    
    let budgetsData = {};
    $('.bud-val').each(function() {
        let catId = $(this).data('id');
        let val = $(this).val() || 0;
        budgetsData[catId] = val;
    });
    
    $.post('ajax/expenses_budgets_ajax.php', {
        method: 'save_budget',
        month: $('#b_month').val(),
        year: $('#b_year').val(),
        budgets: budgetsData
    }, function(res) {
        btn.html(originalText).prop('disabled', false);
        try {
            let result = JSON.parse(res);
            if (result.status === 'success') {
                // Toaster or alert
                let toast = $('<div style="position:fixed; top:20px; right:20px; background:#10b981; color:white; padding:12px 24px; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); z-index:9999; font-weight:600;"><i class="ph-bold ph-check-circle"></i> '+result.msg+'</div>');
                $('body').append(toast);
                setTimeout(() => toast.fadeOut(300, function(){$(this).remove();}), 3000);
                loadBudgets(); // reload to update progress bars
            }
        } catch(e) {
            alert('Error saving budgets.');
        }
    });
});

$(document).ready(function() {
    loadBudgets();
});
</script>

<?php include 'footer.php'; ?>

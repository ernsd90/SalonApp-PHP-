<?php
session_start();
include '../config.php';
include '../function.php';

$salon_id = get_session_data('salon_id') ?? 80;
$method = $_POST['method'] ?? '';

if ($method == 'get_budgets') {
    $month = (int)($_POST['month'] ?? date('m'));
    $year = (int)($_POST['year'] ?? date('Y'));

    // Get all categories for this salon
    $cats_sql = "SELECT exp_catId, category_name FROM hr_expenses_category WHERE salon_id='$salon_id' ORDER BY category_name ASC";
    $cats_res = mysqli_query($conn, $cats_sql);
    
    $data = [];
    while($cat = mysqli_fetch_assoc($cats_res)) {
        $cat_id = $cat['exp_catId'];
        $cat_name = $cat['category_name'];

        // Get budgeted amount
        $bud_sql = "SELECT amount FROM hr_expense_budgets WHERE salon_id='$salon_id' AND exp_catId='$cat_id' AND budget_month='$month' AND budget_year='$year'";
        $bud_res = mysqli_query($conn, $bud_sql);
        $budget_amount = ($bud_res && $row = mysqli_fetch_assoc($bud_res)) ? (float)$row['amount'] : 0;

        // Get actual spent amount
        $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $spent_sql = "SELECT SUM(exp_total) as total FROM hr_expenses WHERE salon_id='$salon_id' AND exp_catId='$cat_id' AND DATE(exp_date) BETWEEN '$start_date' AND '$end_date' AND approval_status != 'rejected'";
        $spent_res = mysqli_query($conn, $spent_sql);
        $spent_amount = ($spent_res && $row = mysqli_fetch_assoc($spent_res)) ? (float)$row['total'] : 0;

        $usage_percent = $budget_amount > 0 ? ($spent_amount / $budget_amount) * 100 : 0;
        $remaining = $budget_amount - $spent_amount;

        $data[] = [
            'cat_id' => $cat_id,
            'category_name' => $cat_name,
            'budget_amount' => $budget_amount,
            'spent_amount' => $spent_amount,
            'usage_percent' => $usage_percent,
            'remaining' => $remaining
        ];
    }
    
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

if ($method == 'save_budget') {
    $month = (int)($_POST['month'] ?? date('m'));
    $year = (int)($_POST['year'] ?? date('Y'));
    $budgets = $_POST['budgets'] ?? []; // format: [cat_id => amount]

    if (is_array($budgets)) {
        foreach ($budgets as $cat_id => $amount) {
            $cat_id = (int)$cat_id;
            $amount = (float)$amount;

            // Check if exists
            $check_sql = "SELECT id FROM hr_expense_budgets WHERE salon_id='$salon_id' AND exp_catId='$cat_id' AND budget_month='$month' AND budget_year='$year'";
            $check_res = mysqli_query($conn, $check_sql);
            
            if ($check_res && mysqli_num_rows($check_res) > 0) {
                // Update
                mysqli_query($conn, "UPDATE hr_expense_budgets SET amount='$amount' WHERE salon_id='$salon_id' AND exp_catId='$cat_id' AND budget_month='$month' AND budget_year='$year'");
            } else {
                // Insert
                if ($amount > 0) {
                    mysqli_query($conn, "INSERT INTO hr_expense_budgets (salon_id, exp_catId, budget_month, budget_year, amount) VALUES ('$salon_id', '$cat_id', '$month', '$year', '$amount')");
                }
            }
        }
    }
    echo json_encode(['status' => 'success', 'msg' => 'Budgets saved successfully.']);
    exit;
}
?>

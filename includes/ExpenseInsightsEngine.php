<?php

class ExpenseInsightsEngine {
    private $conn;
    private $salon_id;

    public function __construct($conn, $salon_id) {
        $this->conn = $conn;
        $this->salon_id = $salon_id;
    }

    public function generateInsights($from_date = null, $to_date = null) {
        $insights = [];
        
        // Use current month by default if no date provided
        if (!$from_date || !$to_date) {
            $from_date = date('Y-m-01');
            $to_date = date('Y-m-d');
        }

        // 1. Current vs Previous Month Expense
        $current_month_total = $this->getTotalExpenses($from_date, $to_date);
        
        $prev_from = date('Y-m-d', strtotime('-1 month', strtotime($from_date)));
        $prev_to = date('Y-m-d', strtotime('-1 month', strtotime($to_date)));
        $prev_month_total = $this->getTotalExpenses($prev_from, $prev_to);

        if ($prev_month_total > 0) {
            $diff_percent = (($current_month_total - $prev_month_total) / $prev_month_total) * 100;
            if ($diff_percent > 10) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'ph-trend-up',
                    'text' => "Total expenses have increased by " . number_format($diff_percent, 1) . "% compared to the same period last month."
                ];
            } elseif ($diff_percent < -10) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'ph-trend-down',
                    'text' => "Great job! Expenses are down by " . number_format(abs($diff_percent), 1) . "% compared to last month."
                ];
            }
        }

        // 2. Highest Category Spike
        $category_spike = $this->getCategorySpike($from_date, $to_date, $prev_from, $prev_to);
        if ($category_spike) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'ph-warning-circle',
                'text' => "{$category_spike['category']} expenses jumped " . number_format($category_spike['increase_percent'], 1) . "% this month."
            ];
        }

        // 3. Unusually high Cash expenses
        $cash_ratio = $this->getCashRatio($from_date, $to_date);
        if ($cash_ratio > 50) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'ph-money',
                'text' => "Cash transactions make up " . number_format($cash_ratio, 1) . "% of recent expenses. Consider encouraging digital payments for better tracking."
            ];
        }

        // 4. Pending Approvals
        $pending_count = $this->getPendingApprovalsCount();
        if ($pending_count > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'ph-clock',
                'text' => "You have $pending_count expenses waiting for approval."
            ];
        }

        return $insights;
    }

    private function getTotalExpenses($from, $to) {
        $sql = "SELECT SUM(exp_total) as total FROM hr_expenses WHERE salon_id = '{$this->salon_id}' AND DATE(exp_date) BETWEEN '$from' AND '$to' AND approval_status != 'rejected'";
        $res = mysqli_query($this->conn, $sql);
        if($res && $row = mysqli_fetch_assoc($res)) {
            return (float)$row['total'];
        }
        return 0;
    }

    private function getCategorySpike($cur_from, $cur_to, $prev_from, $prev_to) {
        // Current month category totals
        $sql = "SELECT c.category_name, SUM(e.exp_total) as total, e.exp_catId 
                FROM hr_expenses e 
                LEFT JOIN hr_expenses_category c ON e.exp_catId = c.exp_catId 
                WHERE e.salon_id = '{$this->salon_id}' AND DATE(e.exp_date) BETWEEN '$cur_from' AND '$cur_to' AND e.approval_status != 'rejected'
                GROUP BY e.exp_catId";
        
        $res = mysqli_query($this->conn, $sql);
        $highest_spike = null;
        $max_spike_percent = 20; // Only report if increase is > 20%

        while ($row = mysqli_fetch_assoc($res)) {
            $cat_id = $row['exp_catId'];
            $cur_total = (float)$row['total'];
            
            if ($cur_total < 500) continue; // Ignore very small amounts

            // Get prev total for this category
            $prev_sql = "SELECT SUM(exp_total) as total FROM hr_expenses 
                         WHERE salon_id = '{$this->salon_id}' AND exp_catId = '$cat_id' 
                         AND DATE(exp_date) BETWEEN '$prev_from' AND '$prev_to' AND approval_status != 'rejected'";
            $prev_res = mysqli_query($this->conn, $prev_sql);
            $prev_total = ($prev_res && $p_row = mysqli_fetch_assoc($prev_res)) ? (float)$p_row['total'] : 0;

            if ($prev_total > 0) {
                $increase = (($cur_total - $prev_total) / $prev_total) * 100;
                if ($increase > $max_spike_percent) {
                    $max_spike_percent = $increase;
                    $highest_spike = [
                        'category' => $row['category_name'] ?: 'General',
                        'increase_percent' => $increase
                    ];
                }
            }
        }
        return $highest_spike;
    }

    private function getCashRatio($from, $to) {
        $sql = "SELECT payment_mode, SUM(exp_total) as total FROM hr_expenses 
                WHERE salon_id = '{$this->salon_id}' AND DATE(exp_date) BETWEEN '$from' AND '$to' AND approval_status != 'rejected'
                GROUP BY payment_mode";
        $res = mysqli_query($this->conn, $sql);
        $cash_total = 0;
        $overall_total = 0;
        while ($row = mysqli_fetch_assoc($res)) {
            $tot = (float)$row['total'];
            $overall_total += $tot;
            if (strtolower(trim($row['payment_mode'])) === 'cash') {
                $cash_total += $tot;
            }
        }
        if ($overall_total > 0) {
            return ($cash_total / $overall_total) * 100;
        }
        return 0;
    }

    private function getPendingApprovalsCount() {
        $sql = "SELECT COUNT(*) as cnt FROM hr_expenses WHERE salon_id = '{$this->salon_id}' AND approval_status = 'pending'";
        $res = mysqli_query($this->conn, $sql);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            return (int)$row['cnt'];
        }
        return 0;
    }
}
?>

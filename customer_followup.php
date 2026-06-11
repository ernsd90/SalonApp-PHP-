<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'config.php';
include 'function.php';

$cust_id = (int)($_GET['cust_id'] ?? 0);
if (!$cust_id) {
    echo "<div style='padding:20px;color:red;text-align:center;'>Invalid Customer ID</div>";
    exit;
}

// Fetch customer basic info + LTV + Last visit
$sql = "SELECT c.*,
               (SELECT MAX(invoice_date) FROM hr_invoice WHERE cust_id = c.cust_id AND delete_bill = 0) as last_visit,
               (SELECT SUM(grand_total) FROM hr_invoice WHERE cust_id = c.cust_id AND delete_bill = 0) as lifetime_spend
        FROM hr_customer c 
        WHERE c.cust_id = '$cust_id' AND c.salon_id = '".get_session_data('salon_id')."'";
$customer = select_row($sql);

if (!$customer) {
    echo "<div style='padding:20px;color:red;text-align:center;'>Customer not found.</div>";
    exit;
}

// Fetch follow-up history
$history_sql = "SELECT f.*, u.username 
                FROM hr_customer_followups f
                LEFT JOIN hr_user u ON f.user_id = u.user_id
                WHERE f.cust_id = '$cust_id' AND f.salon_id = '".get_session_data('salon_id')."'
                ORDER BY f.created_at DESC";
$history = select_array($history_sql);
?>

<style>
/* Override default modal width for premium multi-column layout */
#commonModalOverlay .modal-dialog {
    max-width: 800px !important;
}
</style>

<div style="display: flex; flex-direction: column; height: 100%; max-height: 90vh;">
    <!-- Modal Header -->
    <div style="padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
        <div>
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">Customer Engagement &amp; Follow-up</h3>
            <span style="font-size: 13px; color: var(--text-muted);">Manage communications and track conversions</span>
        </div>
        <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer; display: flex; align-items: center;"><i class="ph ph-x"></i></button>
    </div>

    <!-- Modal Body -->
    <div style="padding: 24px; overflow-y: auto; flex: 1;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
            <!-- Column 1: Customer Profile + Form -->
            <div>
                <!-- Customer Quick Info Card -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="font-weight: 700; font-size: 16px; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="ph-fill ph-user-circle" style="color: var(--primary); font-size: 20px;"></i>
                        <?= htmlspecialchars($customer['cust_name']) ?>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; font-size: 13px;">
                        <div><span style="color: var(--text-muted);">Mobile:</span> <br><span style="font-weight: 600;"><?= htmlspecialchars($customer['cust_mobile']) ?></span></div>
                        <div><span style="color: var(--text-muted);">LTV Spent:</span> <br><span style="font-weight: 600; color: #16a34a;">₹<?= number_format((float)$customer['lifetime_spend'], 2) ?></span></div>
                        <div><span style="color: var(--text-muted);">Wallet:</span> <br><span style="font-weight: 600; color: #0f172a;">₹<?= number_format((float)$customer['cust_wallet'], 2) ?></span></div>
                        <div>
                            <span style="color: var(--text-muted);">Last Visit:</span> <br>
                            <span style="font-weight: 600;">
                                <?php
                                if ($customer['last_visit']) {
                                    $diff = time() - strtotime($customer['last_visit']);
                                    $days = floor($diff / (60 * 60 * 24));
                                    echo ($days == 0) ? "Today" : (($days == 1) ? "1 day ago" : "$days days ago");
                                } else {
                                    echo "Never";
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Add Follow-up Form -->
                <form class="ajax-form" data-action-url="ajax/customer_ajax.php">
                    <input type="hidden" name="method" value="save_followup">
                    <input type="hidden" name="cust_id" value="<?= $cust_id ?>">

                    <!-- Communication Type -->
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px;">Interaction Type</label>
                        <div style="display: flex; gap: 10px;">
                            <label style="flex: 1; border: 2px solid var(--border-color); border-radius: 8px; padding: 10px; text-align: center; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; font-size: 14px;" class="type-btn active-type">
                                <input type="radio" name="type" value="Call" checked style="display: none;">
                                <i class="ph ph-phone"></i> Call
                            </label>
                            <label style="flex: 1; border: 2px solid var(--border-color); border-radius: 8px; padding: 10px; text-align: center; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; font-size: 14px;" class="type-btn">
                                <input type="radio" name="type" value="Message" style="display: none;">
                                <i class="ph ph-chat-teardrop-text"></i> Message
                            </label>
                        </div>
                    </div>

                    <!-- Status/Response Dropdown -->
                    <div style="margin-bottom: 16px;">
                        <label for="statusSelect" style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Response / Status</label>
                        <select name="status" id="statusSelect" class="form-control" style="width: 100%; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; height: 42px; font-size: 14px; background: white;" required>
                            <!-- Call Options -->
                            <option value="Connected">Connected / Answered</option>
                            <option value="No Answer">No Answer</option>
                            <option value="Busy">Busy / Callback</option>
                            <option value="Interested">Interested</option>
                            <option value="Not Interested">Not Interested</option>
                            <option value="Converted">Converted</option>
                            <option value="Lost">Lost</option>
                        </select>
                        <input type="hidden" name="response" id="responseValue" value="Connected">
                    </div>

                    <!-- Next Follow-up Date -->
                    <div style="margin-bottom: 16px;">
                        <label for="followupDate" style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Next Follow-up Date (Optional)</label>
                        <input type="date" name="followup_date" id="followupDate" class="form-control" style="width: 100%; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; height: 42px; font-size: 14px; background: white;" min="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Notes -->
                    <div style="margin-bottom: 20px;">
                        <label for="notesText" style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Discussion Notes / Message Copy</label>
                        <textarea name="notes" id="notesText" class="form-control" style="width: 100%; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; min-height: 80px; font-size: 14px; background: white;" placeholder="Summarize response details or paste message content..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; margin: 0; padding: 12px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="ph ph-floppy-disk"></i> Save Log
                    </button>
                </form>
            </div>

            <!-- Column 2: Follow-up History -->
            <div>
                <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">Interaction Logs</h4>
                
                <div style="max-height: 480px; overflow-y: auto; padding-right: 4px;">
                    <?php if (empty($history)): ?>
                        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted); background: #f8fafc; border-radius: 12px; border: 1px dashed var(--border-color);">
                            <i class="ph ph-chat-centered-dots" style="font-size: 48px; margin-bottom: 12px; display: block; opacity: 0.3; color: var(--primary);"></i>
                            <p style="margin: 0; font-size: 13px; font-style: italic;">No communication logs found for this customer.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <?php foreach ($history as $log): ?>
                                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; box-shadow: var(--shadow-sm);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <?php if ($log['type'] == 'Call'): ?>
                                                <span style="background: #ffedd5; color: #ea580c; border-radius: 4px; padding: 2px 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-phone"></i> Call</span>
                                            <?php else: ?>
                                                <span style="background: #e0f2fe; color: #0284c7; border-radius: 4px; padding: 2px 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-chat-teardrop-text"></i> Msg</span>
                                            <?php endif; ?>
                                            <span style="background: #f1f5f9; color: #475569; border-radius: 4px; padding: 2px 6px; font-size: 11px; font-weight: 600;"><?= htmlspecialchars($log['status']) ?></span>
                                        </div>
                                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;"><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></span>
                                    </div>
                                    <p style="margin: 0 0 8px 0; font-size: 13px; color: #1e293b; line-height: 1.4; white-space: pre-wrap;"><?= htmlspecialchars($log['notes']) ?></p>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-muted); border-top: 1px dashed #f1f5f9; padding-top: 8px;">
                                        <span>By: <strong><?= htmlspecialchars($log['username'] ?? 'System') ?></strong></span>
                                        <?php if ($log['followup_date'] && $log['followup_date'] != '0000-00-00'): ?>
                                            <span style="color: #ea580c; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="ph ph-calendar-blank"></i> Next: <?= date('d M Y', strtotime($log['followup_date'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.type-btn {
    transition: all 0.2s ease;
}
.type-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.active-type {
    background: var(--primary-light, #ede9fe) !important;
    border-color: var(--primary) !important;
    color: var(--primary) !important;
}
</style>

<script>
$(document).ready(function() {
    // Communication Type Toggle
    $('.type-btn').click(function() {
        $('.type-btn').removeClass('active-type');
        $(this).addClass('active-type');
        $(this).find('input[type="radio"]').prop('checked', true);
        
        var selectedType = $(this).find('input[type="radio"]').val();
        
        // Dynamically adjust response options based on type
        var statusSelect = $('#statusSelect');
        statusSelect.empty();
        
        if (selectedType === 'Call') {
            statusSelect.append('<option value="Connected">Connected / Answered</option>');
            statusSelect.append('<option value="No Answer">No Answer</option>');
            statusSelect.append('<option value="Busy">Busy / Callback</option>');
            statusSelect.append('<option value="Interested">Interested</option>');
            statusSelect.append('<option value="Not Interested">Not Interested</option>');
            statusSelect.append('<option value="Converted">Converted</option>');
            statusSelect.append('<option value="Lost">Lost</option>');
        } else {
            statusSelect.append('<option value="Sent">Sent</option>');
            statusSelect.append('<option value="Replied">Read & Replied</option>');
            statusSelect.append('<option value="No Reply">Read & No Reply</option>');
            statusSelect.append('<option value="Interested">Interested</option>');
            statusSelect.append('<option value="Not Interested">Not Interested</option>');
            statusSelect.append('<option value="Converted">Converted</option>');
            statusSelect.append('<option value="Failed">Failed / Invalid Number</option>');
        }
        $('#responseValue').val(statusSelect.val());
    });

    $('#statusSelect').change(function() {
        $('#responseValue').val($(this).val());
    });
});
</script>

<?php
include "header.php";
?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"/>

<!-- Page Title -->
<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Outstanding Debts</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Track pending payments across POS invoices, memberships, and packages.</p>
</div>

<!-- Filter Section -->
<div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; justify-content: space-between;">
        
        <div style="font-size: 16px; font-weight: 600; color: var(--text-main);">
            <i class="ph ph-warning-circle text-danger" style="margin-right: 8px;"></i>
            Pending Debt Report
        </div>

        <!-- Date Filter -->
        <div style="min-width: 250px;">
            <div style="position: relative; cursor:pointer;" id="reportdaterange">
                <i class="ph ph-calendar-blank" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <div class="form-control" style="padding-left: 44px; background: #f8fafc; height: 44px; display: flex; align-items: center; justify-content: space-between;">
                    <span id="datelabel" style="font-size:14px; font-weight:600; color:var(--text-main);">All Time</span>
                    <i class="ph ph-caret-down text-muted" style="margin-left:auto;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="card-modern" style="background: white; border-radius: 20px; border: 1px solid #fecaca; box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 50px;">
    <div style="padding: 24px; border-bottom: 1px solid #fecaca; background: #fef2f2;">
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: #dc2626;">Outstanding Debt Details</h3>
    </div>
    <div style="padding: 24px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle custom-table" id="outstandingTable" width="100%">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Inv/Ref #</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Total Bill</th>
                        <th>Paid Amount</th>
                        <th>Outstanding</th>
                        <th>Days Pending</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Scoped styles */
.custom-table {
    border-collapse: separate;
    border-spacing: 0;
}
.custom-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.custom-table tbody td {
    padding: 16px;
    color: #334155;
    font-size: 14px;
    font-weight: 500;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.custom-table tbody tr:hover td {
    background-color: #f8fafc;
}
</style>

<!-- Pay Outstanding Modal -->
<div class="modal-overlay" id="modalPayOutstanding" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-v3" style="background: white; border-radius: 20px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid var(--border-color); background: #f0fdf4;">
            <h5 style="margin:0; font-size:16px; font-weight:700; color: #16a34a;"><i class="ph ph-currency-inr" style="margin-right:6px;"></i>Receive Payment</h5>
            <button class="close-modal" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-muted);"><i class="ph ph-x"></i></button>
        </div>
        <div class="modal-body-scroll" style="padding: 24px; max-height: 80vh; overflow-y: auto;">
             <form id="pay_outstanding_form">
                 <input type="hidden" name="method" value="pay_outstanding_invoice">
                 <input type="hidden" name="type" id="pay_type">
                 <input type="hidden" name="invoice_id" id="pay_invoice_id">
                 
                 <div style="background: #f8fafc; padding: 16px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;">Client</div>
                    <div style="font-size: 16px; font-weight: 700; color: #0f172a;" id="pay_client_name">--</div>
                    <div style="display: flex; justify-content: space-between; margin-top: 12px;">
                        <div>
                            <div style="font-size: 12px; color: #64748b;">Ref #</div>
                            <div style="font-size: 14px; font-weight: 600;" id="pay_ref_id">--</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 12px; color: #64748b;">Pending Debt</div>
                            <div style="font-size: 18px; font-weight: 700; color: #dc2626;" id="pay_pending_amt">₹0</div>
                        </div>
                    </div>
                 </div>

                 <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Payment Amount</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 600; color: #64748b;">₹</span>
                        <input type="number" id="pay_amount" name="pay_amount" class="form-control" style="padding-left: 32px; font-size: 18px; font-weight: 600; height: 48px;" required min="1" step="0.01">
                    </div>
                 </div>

                 <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Payment Method</label>
                    <select name="payment_mode" class="form-control" style="height: 48px;" required>
                        <option value="cash">Cash</option>
                        <option value="cc">Credit/Debit Card</option>
                        <option value="upi">UPI / QR Code</option>
                        <option value="google_pay">Google Pay</option>
                        <option value="paytm">Paytm</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                 </div>

                 <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                     <button type="button" class="btn btn-light close-modal" style="padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border-color); background: white; cursor: pointer;">Cancel</button>
                     <button type="submit" style="padding: 10px 20px; border-radius: 8px; border: none; background: #16a34a; color: white; cursor: pointer; font-weight: 600;"><i class="ph ph-check-circle"></i> Confirm Payment</button>
                 </div>
             </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script src="assets/js/outstanding.js?v=<?= time() ?>"></script>

<?php include "footer.php"; ?>

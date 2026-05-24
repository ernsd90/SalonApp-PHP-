<?php include 'header.php'; ?>
<?php
$salon_id = get_session_data('salon_id');
$vendors  = select_array("SELECT id, vendor_name FROM hr_vendor WHERE (salon_id='$salon_id' OR salon_id=0) AND status=1 ORDER BY vendor_name ASC");
$pay_methods = select_array("SELECT * FROM `hr_payment_methods` WHERE (`salon_id`='$salon_id' OR `is_global`=1) AND `status`=1 ORDER BY `sort_order` ASC");
if(!$pay_methods) $pay_methods = [['method_key'=>'cash','method_name'=>'Cash'],['method_key'=>'card','method_name'=>'Card'],['method_key'=>'upi','method_name'=>'UPI']];
?>

<div class="dashboard-header" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="purchase_bills.php" style="color:var(--text-muted);font-size:20px;"><i class="ph ph-arrow-left"></i></a>
        <div>
            <h1 style="font-size:24px;font-weight:700;color:var(--text-main);margin-bottom:2px;">New Purchase Bill</h1>
            <p style="color:var(--text-muted);font-size:14px;margin:0;">Create a new supplier purchase invoice.</p>
        </div>
    </div>
</div>

<form id="billForm">
<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

    <!-- LEFT: Bill Details + Line Items -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Header Info -->
        <div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:24px;">
            <h3 style="font-size:15px;font-weight:600;margin:0 0 20px 0;">Bill Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Vendor <span style="color:var(--danger);">*</span></label>
                    <select id="vendor_id" name="vendor_id" class="form-control" required>
                        <option value="">Select Vendor</option>
                        <?php foreach($vendors as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['vendor_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Bill Date <span style="color:var(--danger);">*</span></label>
                    <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Supplier Invoice No.</label>
                    <input type="text" name="invoice_no" class="form-control" placeholder="Vendor's bill number">
                </div>
            </div>
        </div>

        <!-- Product Line Items -->
        <div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);overflow:hidden;">
            <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:15px;font-weight:600;margin:0;">Products / Items</h3>
                <button type="button" id="addRow" class="btn-primary" style="width:auto;padding:8px 14px;margin:0;font-size:13px;">
                    <i class="ph-bold ph-plus"></i> Add Row
                </button>
            </div>
            <div style="overflow:visible;">
                <table style="width:100%;border-collapse:collapse;" id="productTable">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:10px 16px;text-align:left;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Product Name</th>
                            <th style="padding:10px 16px;width:150px;text-align:left;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Category</th>
                            <th style="padding:10px 16px;width:90px;text-align:center;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Qty</th>
                            <th style="padding:10px 16px;width:130px;text-align:right;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Unit Price (₹)</th>
                            <th style="padding:10px 16px;width:130px;text-align:right;font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;border-bottom:1px solid var(--border-color);">Total (₹)</th>
                            <th style="padding:10px 16px;width:40px;border-bottom:1px solid var(--border-color);"></th>
                        </tr>
                    </thead>
                    <tbody id="productRows">
                        <!-- Rows injected by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:24px;">
            <div class="form-group" style="margin:0;">
                <label>Internal Notes (optional)</label>
                <textarea name="bill_note" class="form-control" rows="2" placeholder="Any remarks about this bill..."></textarea>
            </div>
        </div>
    </div>

    <!-- RIGHT: Summary & Payment -->
    <div style="display:flex;flex-direction:column;gap:20px;position:sticky;top:80px;">
        <div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:24px;">
            <h3 style="font-size:15px;font-weight:600;margin:0 0 20px 0;">Bill Summary</h3>

            <div style="display:flex;flex-direction:column;gap:14px;">
                <div style="display:flex;justify-content:space-between;font-size:14px;color:var(--text-muted);">
                    <span>Subtotal</span><span id="disp_subtotal" style="font-weight:600;color:var(--text-main);">₹0.00</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;">
                    <label style="margin:0;color:var(--text-muted);">Discount (%)</label>
                    <input type="number" id="inp_discount" name="discount" min="0" max="100" step="0.01" value="0" style="width:90px;padding:6px 10px;border-radius:8px;border:1px solid var(--border-color);text-align:right;font-size:14px;" oninput="recalc()">
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;">
                    <label style="margin:0;color:var(--text-muted);">GST (%)</label>
                    <input type="number" id="inp_gst" name="gst_pct" min="0" max="100" step="0.01" value="0" style="width:90px;padding:6px 10px;border-radius:8px;border:1px solid var(--border-color);text-align:right;font-size:14px;" oninput="recalc()">
                </div>
                <div style="border-top:1px dashed var(--border-color);padding-top:14px;display:flex;justify-content:space-between;font-size:18px;font-weight:700;">
                    <span>Grand Total</span><span id="disp_grand" style="color:var(--primary);">₹0.00</span>
                </div>

                <input type="hidden" name="subtotal" id="h_subtotal" value="0">
                <input type="hidden" name="gst" id="h_gst" value="0">
                <input type="hidden" name="grand_total" id="h_grand" value="0">
            </div>
        </div>

        <div class="card-modern" style="background:white;border-radius:var(--border-radius);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);padding:24px;">
            <h3 style="font-size:15px;font-weight:600;margin:0 0 20px 0;">Payment</h3>
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div class="form-group" style="margin:0;">
                    <label>Pay Now (₹) <small style="color:var(--text-muted);">Leave 0 to save as credit</small></label>
                    <input type="number" name="pay_now" id="inp_pay" min="0" step="0.01" value="0" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Payment Mode</label>
                    <select name="payment_mode" class="form-control">
                        <?php foreach($pay_methods as $pm): ?>
                        <option value="<?= htmlspecialchars($pm['method_key']) ?>"><?= htmlspecialchars($pm['method_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" id="saveBillBtn" class="btn-primary" style="margin-top:8px;">
                    <i class="ph ph-floppy-disk"></i> Save Purchase Bill
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<style>
.product-row td { padding:10px 16px;border-bottom:1px solid var(--border-color); }
.product-row input { padding:7px 10px;border:1px solid var(--border-color);border-radius:8px;font-size:14px;width:100%;background:white; }
.product-row input:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.ac-wrap { position:relative; }
.ac-dropdown { position:fixed;background:white;border:1px solid var(--border-color);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:9999;max-height:220px;overflow-y:auto;display:none;min-width:260px; }
.ac-dropdown.show { display:block; }
.ac-item { padding:10px 14px;cursor:pointer;font-size:13px;display:flex;justify-content:space-between;align-items:center;gap:8px; }
.ac-item:hover,.ac-item.active { background:#f0f4ff; }
.ac-item .ac-name { font-weight:600;color:var(--text-main); }
.ac-item .ac-price { color:var(--primary);font-weight:700;font-size:12px;background:var(--primary-light);padding:2px 8px;border-radius:10px;white-space:nowrap; }
.ac-item .ac-tag { font-size:10px;color:var(--text-muted);background:#f1f5f9;padding:1px 6px;border-radius:6px; }
</style>

<script>
var rowIndex = 0;

function addProductRow(name, type, qty, mrp) {
    name = name || ''; type = type || ''; qty = qty || 1; mrp = mrp || '';
    var i = rowIndex++;
    var row = '<tr class="product-row" id="row'+i+'">' +
        '<td><div class="ac-wrap">' +
            '<input type="text" class="product-name-input" data-row="'+i+'" name="products['+i+'][product_name]" value="'+name+'" placeholder="Type to search product..." autocomplete="off" required>' +
            '<div class="ac-dropdown" id="acdrop'+i+'"></div>' +
        '</div></td>' +
        '<td><select name="products['+i+'][product_type]" style="padding:7px 10px;border:1px solid var(--border-color);border-radius:8px;font-size:14px;width:100%;background:white;cursor:pointer;"><option value="store" selected>Store</option><option value="retail">Retail</option></select></td>' +
        '<td><input type="number" class="qty-inp" data-row="'+i+'" name="products['+i+'][qty]" value="'+qty+'" min="0.01" step="0.01" style="text-align:center;"></td>' +
        '<td><input type="number" class="mrp-inp" data-row="'+i+'" name="products['+i+'][mrp]" value="'+mrp+'" min="0" step="0.01" style="text-align:right;" placeholder="0.00"></td>' +
        '<td style="text-align:right;"><strong id="rowtotal'+i+'" style="color:var(--text-main);">₹0.00</strong></td>' +
        '<td style="text-align:center;"><button type="button" onclick="removeRow('+i+')" style="background:#fee2e2;color:#dc2626;border:none;width:28px;height:28px;border-radius:6px;cursor:pointer;"><i class="ph ph-trash"></i></button></td>' +
    '</tr>';
    $('#productRows').append(row);
    if(mrp) calcRow(i);
}

function calcRow(i) {
    var qty = parseFloat($('[name="products['+i+'][qty]"]').val()) || 0;
    var mrp = parseFloat($('[name="products['+i+'][mrp]"]').val()) || 0;
    var t = qty * mrp;
    $('#rowtotal'+i).text('₹'+t.toFixed(2));
    recalc();
}

function removeRow(i) { $('#row'+i).remove(); recalc(); }

function recalc() {
    var sub = 0;
    $('.product-row').each(function(){
        var i = $(this).attr('id').replace('row','');
        var qty = parseFloat($('[name="products['+i+'][qty]"]').val()) || 0;
        var mrp = parseFloat($('[name="products['+i+'][mrp]"]').val()) || 0;
        sub += qty * mrp;
    });
    var disc_pct = parseFloat($('#inp_discount').val()) || 0;
    var gst_pct  = parseFloat($('#inp_gst').val()) || 0;
    var after_disc = sub - (sub * disc_pct / 100);
    var gst_amt    = after_disc * gst_pct / 100;
    var grand      = after_disc + gst_amt;
    $('#disp_subtotal').text('₹'+sub.toFixed(2));
    $('#disp_grand').text('₹'+grand.toFixed(2));
    $('#h_subtotal').val(sub.toFixed(2));
    $('#h_gst').val(gst_amt.toFixed(2));
    $('#h_grand').val(grand.toFixed(2));
}

$(document).ready(function(){
    addProductRow(); // Start with one empty row
    $('#addRow').click(function(){ addProductRow(); });
    $('#inp_discount, #inp_gst').on('input', recalc);

    // ===== PRODUCT AUTOCOMPLETE =====
    var acTimer = null;
    $(document).on('input', '.product-name-input', function(){
        var inp = $(this), row = inp.data('row'), q = inp.val().trim();
        var drop = $('#acdrop'+row);
        clearTimeout(acTimer);
        if(q.length < 1){ drop.removeClass('show').empty(); return; }

        // Position drop under the input using fixed coords
        var rect = inp[0].getBoundingClientRect();
        drop.css({ top: rect.bottom + 'px', left: rect.left + 'px', width: rect.width + 'px' });

        acTimer = setTimeout(function(){
            $.get('ajax/inventory_ajax.php', {method:'search_products', q:q}, function(res){
                try{
                    var o = JSON.parse(res);
                    drop.empty();
                    if(!o.results || !o.results.length){
                        drop.html('<div class="ac-item"><span class="ac-name" style="color:var(--text-muted);">No matches — type to create new</span></div>').addClass('show');
                        return;
                    }
                    o.results.forEach(function(p){
                        var tag = p.src==='history' ? '<span class="ac-tag">prev</span>' : '<span class="ac-tag">catalogue</span>';
                        var price = p.price>0 ? '<span class="ac-price">₹'+parseFloat(p.price).toFixed(2)+'</span>' : '';
                        var item = $('<div class="ac-item"></div>')
                            .html('<div><span class="ac-name">'+$('<div>').text(p.name).html()+'</span> '+tag+'</div>'+price)
                            .data('name', p.name).data('price', p.price).data('row', row);
                        drop.append(item);
                    });
                    drop.addClass('show');
                }catch(e){}
            });
        }, 250);
    });

    // Select from dropdown
    $(document).on('click', '.ac-item', function(){
        var name = $(this).data('name'), price = $(this).data('price'), row = $(this).data('row');
        if(!name) return;
        $('[name="products['+row+'][product_name]"]').val(name);
        if(price > 0) $('[name="products['+row+'][mrp]"]').val(parseFloat(price).toFixed(2));
        $('#acdrop'+row).removeClass('show').empty();
        calcRow(row);
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e){
        if(!$(e.target).closest('.ac-wrap').length) $('.ac-dropdown').removeClass('show').empty();
    });

    // Calc on qty/price change
    $(document).on('input change', '.qty-inp, .mrp-inp', function(){
        calcRow($(this).data('row'));
    });

    $('#saveBillBtn').click(function(){
        if($('.product-row').length === 0){ alert('Please add at least one product.'); return; }
        if(!$('#vendor_id').val()){ alert('Please select a vendor.'); return; }
        var total = parseFloat($('#h_grand').val()) || 0;
        var pay   = parseFloat($('[name="pay_now"]').val()) || 0;
        if(pay > total){ alert('Payment cannot exceed grand total of ₹'+total.toFixed(2)); return; }

        var btn = $(this); btn.html('<i class="ph ph-spinner ph-spin"></i> Saving...').prop('disabled',true);
        $.ajax({
            type:'POST', url:'ajax/inventory_ajax.php',
            data: $('#billForm').serialize() + '&method=create_bill',
            success:function(res){
                try{
                    var o = JSON.parse(res);
                    if(o.error==1){ alert('Error: '+o.msg); btn.html('<i class="ph ph-floppy-disk"></i> Save Purchase Bill').prop('disabled',false); }
                    else{ alert(o.msg); window.location.href='purchase_bills.php'; }
                }catch(e){ alert('Unexpected error'); btn.prop('disabled',false); }
            }
        });
    });
});
</script>
<?php include 'footer.php'; ?>

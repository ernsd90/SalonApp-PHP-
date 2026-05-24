<?php 
include 'header.php';

$job_card_id = isset($_GET['job_card_id']) ? $_GET['job_card_id'] : '';
$job_card = array();
$customer_name = "";
$customer_mobile = "";
$customer_gender = "";
$job_card_services = array();

if($job_card_id != ''){
    $query = "SELECT * FROM `hr_jobcard` WHERE `job_card_id` = '".mysqli_real_escape_string($conn, $job_card_id)."'";
    $job_card = select_row($query);

    if($job_card) {
        $query = "SELECT * FROM `hr_customer` WHERE `cust_id` = '".$job_card['cust_id']."'";
        $customer = select_row($query);
        if($customer) {
            $customer_name = $customer['cust_name'];
            $customer_mobile = $customer['cust_mobile'];
            $customer_gender = $customer['cust_gender'];
        }

        $query = "SELECT * FROM `hr_jobcardservice` WHERE `job_card_id` = '".$job_card_id."' AND `delete_status` = 'active' ORDER BY `job_card_service_id` ASC";
        $job_card_services = select_array($query) ?: array();

        foreach($job_card_services as $key => $service){
            $query = "SELECT js.staff_id FROM `hr_jobcardstaff` js WHERE js.`job_card_id` = '".$job_card_id."' AND js.`job_card_service_id` = '".$service['job_card_service_id']."' AND js.`delete_status` = 'active'";
            $job_card_staff_services = select_array($query) ?: array();
            $job_card_services[$key]['staff_services'] = array_column($job_card_staff_services, 'staff_id');
        }
    }
}

// Fetch categories, staff
$query = "SELECT * FROM `hr_servicesCategory` where salon_id='".$salon_id."' ORDER BY service_catName ASC";
$service_catNames = select_array($query);

$query = "SELECT * FROM `hr_staff` where salon_id='".$salon_id."' and staff_status=1 ORDER BY staff_name ASC";
$staff = select_array($query);
?>

<!-- DataTables & Select2 CSS/JS via CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="dashboard-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Job Card Creator</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Assign initial services to a customer when they arrive at the salon.</p>
</div>

<form action="job_card_create.php" method="post" id="jobcardform" autocomplete="off" style="margin-bottom: 50px;">
    <input type="hidden" name="job_card_id" value="<?= htmlspecialchars($job_card_id) ?>" />
    
    <!-- 1. Customer Section -->
    <div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: visible; margin-bottom: 24px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px; background: #f8fafc;">
            <i class="ph-fill ph-user-circle" style="color: var(--primary); font-size: 24px;"></i>
            <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-main);">Customer Details</h3>
        </div>
        
        <div style="padding: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
            <input type="hidden" value="" name="cust_id" class="cust_id" />
            
            <div class="form-group">
                <label>Mobile Number</label>
                <div style="position: relative;">
                    <i class="ph ph-phone" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px;"></i>
                    <input value="<?= htmlspecialchars($customer_mobile) ?>" type="text" name="cust_mob" required class="form-control cust_mob auto-search" id="cust_mob" placeholder="Search by mobile or name" style="padding-left: 44px; background: white;" autocomplete="off">
                    <div class="autocomplete-dropdown" id="mob_autocomplete" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid var(--border-color); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:9999; max-height:250px; overflow-y:auto; margin-top:4px;"></div>
                </div>
            </div>

            <div class="form-group">
                <label>Customer Name</label>
                <div style="position: relative;">
                    <input type="text" value="<?= htmlspecialchars($customer_name) ?>" name="cust_name" required class="form-control required auto-search" id="cust_name" placeholder="Full Name" style="background: white;" autocomplete="off">
                    <div class="autocomplete-dropdown" id="name_autocomplete" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid var(--border-color); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:9999; max-height:250px; overflow-y:auto; margin-top:4px;"></div>
                </div>
            </div>

            <?php if(!$job_card_id): ?>
                <div class="form-group gender_check">
                    <label>Gender</label>
                    <select name="cust_gender" class="form-control" style="background: white;">
                        <option value="Female" <?= $customer_gender == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Male" <?= $customer_gender == 'Male' ? 'selected' : '' ?>>Male</option>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. Services Section -->
    <div class="card-modern" style="background: white; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="ph-fill ph-scissors" style="color: var(--primary); font-size: 24px;"></i>
                <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-main);">Assigned Services</h3>
            </div>
            <button type="button" id="btn_add_row" class="btn-primary" style="width: auto; padding: 8px 16px; margin: 0; font-size: 13px; box-shadow: none;">
                <i class="ph-bold ph-plus"></i> Add Service
            </button>
        </div>
        
        <div style="padding: 24px; overflow-x: auto;">
            <table class="table-modern" id="item_table" style="width: 100%; min-width: 800px;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Requested Service</th>
                        <th style="width: 30%;">Assigned Staff</th>
                        <th style="width: 25%;">Optional Remark</th>
                        <th style="width: 10%; text-align: right;">Price (₹)</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody id="billing_tbody">
                    <!-- Lines injected via JS -->
                </tbody>
            </table>
        </div>
        
        <div style="padding: 24px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; align-items: center;">
            <div style="font-size: 15px; color: var(--text-muted); margin-right: 16px;">Estimated Total:</div>
            <div id="jobCardTotal" style="font-size: 24px; font-weight: 700; color: var(--primary);">₹0.00</div>
        </div>
    </div>

    <!-- 3. Finalize Job Card -->
    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" name="save_bill_print" class="btn-primary" style="width: auto; padding: 14px 28px; font-size: 16px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
            <i class="ph-bold ph-clipboard-text"></i> <?= $job_card_id ? 'Update Job Card & Print' : 'Start Service & Print Job Card' ?>
        </button>
    </div>

</form>

<style>
/* Modern Table Scoping */
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern th { color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 2px solid var(--border-color); text-align: left; }
.table-modern td { padding: 12px 16px; font-size: 14px; position: relative; border-bottom: 1px solid var(--border-color); }

/* Select2 Modern Overrides */
.select2-container--default .select2-selection--single { height: 48px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: white; display: flex; align-items: center; }
.select2-container--default .select2-selection--multiple { min-height: 48px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: white; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 48px; padding-left: 16px; color: var(--text-main); }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; right: 10px; }
.select2-container--default.select2-container--focus .select2-selection--multiple, .select2-container--default.select2-container--focus .select2-selection--single { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
</style>

<script>
// Available Services Data structure for JS
var categoriesData = <?php echo json_encode($service_catNames); ?>;
var servicesData = [];
var staffData = <?php echo json_encode($staff); ?>;

<?php 
foreach($service_catNames as $cat) {
    $q = "SELECT * FROM `hr_services` WHERE `service_catid` = '".$cat['service_catid']."' AND `service_status` = 1 ORDER BY `service_id` ASC";
    $srvs = select_array($q);
    if($srvs) {
        foreach($srvs as $s) {
            $price = floatval($s['service_price']);
            echo "servicesData.push({ id: ".$s['service_id'].", text: '".addslashes($s['service_name'])."', catid: ".$cat['service_catid'].", catName: '".addslashes($cat['service_catName'])."', price: ".$price." });\n";
        }
    }
}
?>

var rowCounter = 0;

function addBillingRow(prefillData = null) {
    rowCounter++;
    
    var tr = $('<tr class="billing-row" data-row="'+rowCounter+'"></tr>');
    
    // Service Select UI with Option Group generation
    var serviceSelectHtml = '<select name="sub_service[]" class="form-control select2-service" style="width: 100%;" required>';
    serviceSelectHtml += '<option value="0">Select Service</option>';
    
    categoriesData.forEach(function(cat) {
        var catServices = servicesData.filter(s => s.catid == cat.service_catid);
        if(catServices.length > 0) {
            serviceSelectHtml += '<optgroup label="'+cat.catName+'">';
            catServices.forEach(function(s) {
                var selected = (prefillData && prefillData.service_id == s.id) ? 'selected' : '';
                serviceSelectHtml += '<option value="'+s.id+'" data-catid="'+s.catid+'" '+selected+'>'+s.text+'</option>';
            });
            serviceSelectHtml += '</optgroup>';
        }
    });
    serviceSelectHtml += '</select>';

    // Staff Select UI
    var staffSelectHtml = '<select name="service_staff['+(rowCounter-1)+'][]" class="form-control select2-staff" multiple="multiple" style="width: 100%;" required>';
    staffData.forEach(function(st) {
        var selected = (prefillData && prefillData.staff_services && prefillData.staff_services.includes(st.staff_id)) ? 'selected' : '';
        staffSelectHtml += '<option value="'+st.staff_id+'" '+selected+'>'+st.staff_name+'</option>';
    });
    staffSelectHtml += '</select>';

    // Remark input
    var remarkVal = prefillData && prefillData.service_remark ? prefillData.service_remark : '';

    var $tdService = $('<td>' + serviceSelectHtml + '</td>');
    var $tdStaff = $('<td>' + staffSelectHtml + '</td>');
    var $tdRemark = $('<td><input name="service_remark[]" type="text" class="form-control" style="background:white; padding: 6px 12px; height: 48px;" value="'+remarkVal+'" placeholder="Optional notes..."></td>');
    
    // Price display
    var $tdPrice = $('<td style="text-align: right;"><span class="row-price-display" style="font-weight: 600; color: var(--text-main); line-height: 48px;">0.00</span></td>');

    var $tdAction = $('<td style="text-align: right;"><button type="button" class="btn-remove-row" style="background: var(--danger); color: white; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s;"><i class="ph-bold ph-trash"></i></button></td>');

    tr.append($tdService);
    tr.append($tdStaff);
    tr.append($tdRemark);
    tr.append($tdPrice);
    tr.append($tdAction);

    $('#billing_tbody').append(tr);

    // Initialize Select2
    tr.find('.select2-service').select2({ placeholder: "Select a requested service" });
    tr.find('.select2-staff').select2({ placeholder: "Assign staff", allowClear: true });
    
    // Trigger price update if prefilled
    if(prefillData && prefillData.service_id) {
        tr.find('.select2-service').trigger('change');
    }
}

function calculateJobCardTotal() {
    var total = 0;
    $('#billing_tbody .row-price-display').each(function() {
        total += parseFloat($(this).text()) || 0;
    });
    $('#jobCardTotal').text('₹' + total.toFixed(2));
}

$(document).ready(function() {
    
    // Load pre-existing data or blank row
    var existingServices = <?php echo json_encode($job_card_services); ?>;
    if(existingServices && existingServices.length > 0) {
        existingServices.forEach(function(srv) {
            addBillingRow(srv);
        });
    } else {
        addBillingRow();
    }

    $('#btn_add_row').click(function() {
        addBillingRow();
    });

    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        calculateJobCardTotal();
    });

    $(document).on('change', '.select2-service', function() {
        var serviceId = $(this).val();
        var tr = $(this).closest('tr');
        var serviceObj = servicesData.find(s => s.id == serviceId);
        
        if (serviceObj) {
            tr.find('.row-price-display').text(parseFloat(serviceObj.price).toFixed(2));
        } else {
            tr.find('.row-price-display').text("0.00");
        }
        calculateJobCardTotal();
    });

    // Customer Autocomplete block 
    var searchTimer;
    $('.auto-search').on('keyup focus', function() {
        var input = $(this);
        var val = input.val().trim();
        var dropdown = input.siblings('.autocomplete-dropdown');
        clearTimeout(searchTimer);
        
        if(val.length < 3) {
            $('.autocomplete-dropdown').hide();
            return;
        }

        searchTimer = setTimeout(function() {
            $.ajax({
                type: "POST",
                url: "ajax/customer_ajax.php",
                data: { method: 'get_customer_from_mobile', cust_mob: val, detail: 2 },
                success: function(res) {
                    try {
                        var data = JSON.parse(res);
                        dropdown.empty();
                        if(data && data.length > 0) {
                            data.forEach(function(cust) {
                                var item = $('<div class="dropdown-item" style="padding:10px 16px; cursor:pointer; border-bottom:1px solid #f1f5f9; hover:background:#f8fafc;">' +
                                    '<div style="font-weight:600; color:var(--text-main); font-size:14px;">'+cust.cust_name+'</div>' +
                                    '<div style="font-size:12px; color:var(--text-muted);">'+cust.cust_mobile+'</div>' +
                                '</div>');
                                
                                item.on('mousedown', function(e) { e.preventDefault(); });
                                item.on('click', function() {
                                    $('.autocomplete-dropdown').hide();
                                    $('.cust_id').val(cust.cust_id);
                                    $('#cust_name').val(cust.cust_name);
                                    $('#cust_mob').val(cust.cust_mobile);
                                    if(cust.cust_gender) $('select[name="cust_gender"]').val(cust.cust_gender);
                                });
                                dropdown.append(item);
                            });
                            $('.autocomplete-dropdown').hide();
                            dropdown.show();
                        } else {
                            dropdown.hide();
                            $('.cust_id').val('');
                        }
                    } catch(e) { dropdown.hide(); }
                }
            });
        }, 300);
    });

    $(document).on('click', function(e) {
        if(!$(e.target).closest('.autocomplete-dropdown, .auto-search').length) {
            $('.autocomplete-dropdown').hide();
        }
    });

    // Form Validator
    $('#jobcardform').submit(function(e) {
        var isValid = true;
        var rowCount = $('#billing_tbody tr').length;
        
        if(rowCount === 0) {
            alert('Please add at least one service to the Job Card.');
            e.preventDefault();
            return false;
        }

        $('#billing_tbody tr').each(function() {
            var service = $(this).find('.select2-service').val();
            var staff = $(this).find('.select2-staff').val();
            
            if(service == "0" || service == "") {
                 alert('Please select a valid service/product on all rows.');
                 isValid = false;
                 return false;
            }

            if(!staff || staff.length === 0) {
                 alert('Please assign at least one staff member to every service.');
                 isValid = false;
                 return false;
            }
        });

        if(!isValid) {
            e.preventDefault();
            return false;
        }

        var btn = $(this).find('button[type="submit"]');
        var originalText = btn.html();
        btn.html('<i class="ph-bold ph-spinner ph-spin"></i> Saving...').css('pointer-events', 'none');
        
        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(res) {
                try {
                    var data = JSON.parse(res);
                    if(data.error == 0) {
                        btn.html('<i class="ph-bold ph-check"></i> Saved! Redirecting...');
                        setTimeout(function() {
                            window.location.href = "print_jobcard.php?job_card_id=" + data.job_card_id;
                        }, 800);
                    } else {
                        alert("Error: " + data.msg);
                        btn.html(originalText).css('pointer-events', 'auto');
                    }
                } catch(err) {
                    alert("System error. Could not parse response.");
                    btn.html(originalText).css('pointer-events', 'auto');
                }
            }
        });
        
        return false; // Prevent standard form submit
    });

});
</script>

<?php include 'footer.php'; ?>

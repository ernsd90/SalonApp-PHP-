<?php include 'header.php';

/*-Service Category--*/
$query = "SELECT * FROM `hr_servicesCategory` where salon_id='".$salon_id."' ";
$service_catNames = select_array($query);
/*----Staff------*/
$query = "SELECT * FROM `hr_staff` where salon_id='".$salon_id."' and staff_status=1";
$staff = select_array($query);


$job_card_id = isset($_GET['job_card_id']) ? $_GET['job_card_id'] : '';
$job_card = array();
if($job_card_id != ''){
	$query = "SELECT * FROM `hr_jobcard` WHERE `job_card_id` = '".$job_card_id."'";
	$job_card = select_row($query);

	$query = "SELECT * FROM `hr_jobcardservice` WHERE `job_card_id` = '".$job_card_id."' AND `delete_status` = 'active' ORDER BY `job_card_service_id` ASC";
	$job_card_services = select_array($query);

	foreach($job_card_services as $key => $service){

		$query = "SELECT js.staff_id
				FROM `hr_jobcardstaff` js
				WHERE js.`job_card_id` = '".$job_card_id."' AND js.`job_card_service_id` = '".$service['job_card_service_id']."' AND js.`delete_status` = 'active'";
		$job_card_staff_services = select_array($query);
		$job_card_services[$key]['staff_services'] = $job_card_staff_services;
	}


	$query = "SELECT * FROM `hr_customer` WHERE `cust_id` = '".$job_card['cust_id']."'";
	$customer = select_row($query);

}
 
?>

        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
		  
			<div class="row" id="proBanner">
              <div class="col-12">
                <span >
                  <i style="display:none" class="mdi mdi-close" id="bannerClose"></i>
                </span>
              </div>
            </div>

			<form action="invoice.php" method="post" id="billingform" autocomplete="off">
			<input type="hidden" name="job_card_id" value="<?php echo isset($job_card_id) ? $job_card_id : ''; ?>" />
				<div class="row">
					<div class="col-sm-12">
						<div class="card">
						<div class="card-body">
							<div class="row">
								
							<?php include "billing_customer.php"; ?>

							<div class="col-md-12">
								<table class="table table-striped" id="item_table">
									<thead class="item-table-header">
									<tr>
										<!--th width="15%" class="text-center">Service Category</th-->
										<th width="30%" class="text-center">Service</th>
										<th width="15%" class="text-center">Staff</th>
										<th width="15%" class="text-center">Quantity</th>
										<th width="20%" class="text-center">Price</th>
                                        <?php if($gst_enable != "no"){ ?><th width="10%" class="text-center">Tax</th><?php } ?>
										<th width="10%" class="text-right">Total</th>
									</tr>
									</thead>
									<tbody>
										<?php for($i=0;$i<=8;$i++){ 
										if($i == 9){ $style="style='display:none'"; } ?>
										<tr <?php echo $style; ?> class="item tr_clone<?php echo $i; ?>">
											<!--td>
												<div class="">
													<select name="service_catid[]" id="service_catid<?php echo $i; ?>" class="search_select service_catid form-control select2-selection--single" style="width:100%">
														<option>Select Category</option>
														<?php foreach($service_catNames as $name){
															?>
															<option value="<?php echo $name['service_catid'] ;?>"><?php echo $name['service_catName'] ;?></option>
														<?php } ?>
													</select>
												</div>
											</td-->
											<td>


												<div class="">
													<input type="hidden" name="service_catid[]"  class="service_catid" />
													<select id="sub_service<?php echo $i; ?>"  name="sub_service[]" class="required search_select sub_service form-control select2-selection--single" style="width:100%">
															<option value="0">Select Service</option>
															<?php foreach($service_catNames as $name){
													
															$query = "SELECT * FROM `hr_services` WHERE `service_catid` = '".$name['service_catid']."' AND `service_status` = 1 ORDER BY `service_id` ASC";
															$services = select_array($query);
															if($services != false){
															?>
															<optgroup label="<?php echo $name['service_catName'] ;?>">
																<?php 
																	foreach($services as $service){ 
																		extract($service);
																?>
																<option data-catid="<?php echo $name['service_catid']; ?>" value="<?php echo $service_id; ?>"><?php echo $service_name; ?></option>
																<?php } ?>
															</optgroup>
															<?php } } ?>
													</select>
												</div>
											</td>
											<td>
												<div class="">
													<select  multiple name="service_staff[<?php echo $i; ?>][]" id="service_staff<?php echo $i; ?>" class=" required search_select service_staff form-control select2-selection--single " style="width:100%">
														<option value="0">Select Staff</option>
														<?php foreach($staff as $name){
															?>
															<option value="<?php echo $name['staff_id']; ?>"><?php echo $name['staff_name'] ;?></option>
														<?php } ?>
													</select>
												</div>
											</td>
											<td>
												<div class="">
													<input name="service_qty[]" type="number" class="required form-control calcEvent service_qty input-sm" id="service_qty"  step="any" min="1" value="1">
												</div>
											</td>
											<td>
												<div class="">
													<input name="service_price[]" type="number" class="required form-control calcEvent service_price input-sm" id="service_price" step="any" min="0">
                                                    <input name="service_price_org[]" type="hidden" class="service_price_org" id="service_price" step="any" min="0">
												</div>
											</td>
                                            <?php if($gst_enable != "no"){ ?>
											<td>
												<div class="">
												<input name="service_gst[]" type="hidden"  class="service_gst form-control" id="service_gst" placeholder="GST(%)" value="18">
												<span class="service_gst_txt">18%</span>
												</div>
											</td>
                                            <?php } ?>
											<td class="text-right">
												<span class="service_total_txt">0.00</span>
												<input name="service_total[]" type="hidden"  class="service_total form-control" id="service_total">
											</td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
							
							<div class="col-sm-12">
								<div class="row">
									<div class="col-md-6 p-4 " id="button_add">
										<!--div class="form-group">
											<span id="btn_add_row" class="btn btn-xs btn-info "><i class="fa fa-plus"></i> Add New Line</span>
										</div-->
										<div class="form-group">
											<label for="billing_remark">Billing Remark</label>
											<textarea  class="form-control billing_remark" id="billing_remark" name="billing_remark"></textarea> 
										</div>

									</div>
									<div class="col-md-6 p-4 " id="package">
										<table class="table">
											<tbody>
											<tr>
												<th style="width:50%">Sub Total</th>
												<td class="text-right">
													<span id="subTotal">00.00</span>
												</td>
											</tr>

                                            <?php if($gst_enable != "no"){ ?>
											<tr>
												<th>Tax</th>
												<td class="text-right">
													<span id="taxTotal">00.00</span>
												</td>
											</tr>
                                            <?php } ?>

											<tr>
												<th style="vertical-align: middle">
													Discount
													<select class="text-right input-sm discount_mode" id="discount_mode" style="width:50%" name="discount_mode">
                                                        <option value="1">%</option>
                                                        <option value="0">Amount</option>
													</select>
												</th>
												<td class="text-right">
													<div class="form-group">
													<input class="form-control text-right discount input-sm" id="discount" step="any" min="0" name="discount" type="number">
													<small id="discount_value"></small>
												</div>
												</td>
											</tr>
											<tr>
												<th>Payment Mode</th>
												<td class="text-right">
													<select class="payment_mode form-control" name="payment_mode">
														<?php foreach($payment_method as $key => $v) {?>
														<option value="<?php echo $key; ?>"><?php echo $v; ?></option>
														<?php } ?>
													</select>
												</td>
											</tr>
											<tr class="part_payment" style="display:none">
												<th>Part Payment</th>
												<td class="text-right">
												<label style="float:left">Cash:</label>
												<input class="form-control text-right part_cash input-sm" id="part_cash" step="any" min="0" name="part_cash" value="0" type="number" required>
												<br>
												<label style="float:left">Card/UPI:</label> 
												<input class="form-control text-right part_cc input-sm" id="part_cc" step="any" min="0" name="part_cc" value="0" type="number" required>
												</td>
											</tr>

											<?php if($salon_id == 200000){ ?>
											<tr>
												<th>Covid-19 Safty Fees</th>
												<td class="text-right">
													<div class="form-group">
													<input class="form-control text-right extra_tax input-sm" id="extra_tax" step="any" min="0" name="extra_tax" value="0" type="number">
												</div>
												</td>
											</tr>
											<?php }else{ ?>
												<input class="" id="extra_tax" step="any" min="0" name="extra_tax" value="0" type="hidden">
											<?php } ?>
											<input class="grandTotal" id="" name="grandTotal" value="0" type="hidden">
											<tr class="amount_due">
												<th>Grand Total:</th>
												<td class="text-right">
													<span class="currencySymbol" style="display: inline-block;"></span>
													<span id="grandTotal">00.00</span>
												</td>
											</tr>
											</tbody>
										</table>

										<!--button type="submit" name="save_bill" class="save_bill btn btn-gradient-success btn-fw">Save Bill</button-->

										<input type="submit" value="Save Bill & Print" name="save_bill_print" class="save_bill_print btn btn-gradient-success btn-fw" />


									</div>
								</div>
							</div>
							
							
						</div>
						</div>
						</div>
					</div>
				</div>
			 </form>
           
		</div>
          <!-- content-wrapper ends -->
		  
<?php include 'footer.php';?>

<script>
$(".service_staff").on("select2:close", function (e) {  
       //console.log($(this).val());
    });
</script>

<script>

$(document).ready(function() {
	<?php if($job_card_id != ''){ ?>
		
		<?php foreach($job_card_services as $key => $service){ ?>
			$("#sub_service<?php echo $key; ?>").val("<?php echo $service['service_id']; ?>").trigger("change");
			$("#service_staff<?php echo $key; ?>").val(<?php echo json_encode(array_column($service['staff_services'], 'staff_id')); ?>).trigger("change");
		<?php } ?>
		<?php } ?>
});

</script>

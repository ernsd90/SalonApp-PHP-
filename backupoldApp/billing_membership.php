<?php include 'header.php';



/*-Service Category--*/
$query = "SELECT * FROM `hr_packages` where salon_id='".$salon_id."' and package_status=1 order by pkg_id desc ";
$hr_packages = select_array($query);
/*----Staff------*/
$query = "SELECT * FROM `hr_staff` where salon_id='".$salon_id."' and staff_status=1";
$staff = select_array($query);

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

			<form action="invoice_member.php" method="post" autocomplete="off" id="membershipform">
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
										<th width="15%" class="text-center">Package</th>
										<th width="15%" class="text-center">Staff</th>
										<th width="15%" class="text-center">Customer Get</th>
										<th width="15%" class="text-center">Customer Pay</th>
									</tr>
									</thead>
									<tbody>
										<?php for($i=0;$i<=0;$i++){ ?>
										<tr class="item tr_clone<?php echo $i; ?>">
											<td>
												<div class="">
													<select name="package" id="package<?php echo $i; ?>" class="search_select package form-control select2-selection--single" style="width:100%">
														<option value="0">Select Package</option>
														<?php foreach($hr_packages as $name){
                                                            extract($name);
															?>
															<option data-pakage_validity="<?php echo $pakage_validity ;?>" data-package_name="<?php echo $package_name ;?>" data-pay="<?php echo $customer_pay ;?>" data-get="<?php echo $customer_get ;?>" data-validity="<?php echo $pakage_validity ;?>" value="<?php echo $pkg_id ;?>"><?php echo $package_name ;?></option>
														<?php } ?>
													</select>
													<input type="hidden" id="package_name" name="package_name" />
													<input type="hidden" id="pakage_validity" name="pakage_validity" />
												</div>
											</td>
											<td>
												<div class="">
													<select multiple name="service_staff[]" id="service_staff<?php echo $i; ?>" class="search_select service_staff form-control select2-selection--single " style="width:100%">
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
													<input name="customer_get" readonly type="number" class="form-control calcEvent customer_get input-sm" id="customer_get" step="any" min="0">
												</div>
											</td>
											<td>
												<div class="">
													<input name="customer_pay" readonly type="number" class="form-control calcEvent customer_pay input-sm" id="customer_pay" step="any" min="0">
												</div>
											</td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
							
							<div class="col-sm-12">
								<div class="row">
									<div class="col-md-6 p-4 " id="button_add">
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
													<input type="hidden" name="subTotal" />
												</td>
											</tr>
											<tr>
												<th>Tax</th>
												<td class="text-right">
													<span id="taxTotal">00.00</span>
													<input type="hidden" name="taxTotal" />
												</td>
											</tr>
											<tr class="">
											<th>Payment Mode</th>
												<td class="text-right">
														<select class="payment_mode form-control" name="payment_mode">
															<?php foreach($payment_method as $key => $v) { if($key != 'pkg' && $key != 'split'){ ?>
															<option value="<?php echo $key; ?>"><?php echo $v; ?></option>
															<?php } } ?>
														</select>
												</td>
											</tr>

											
											<tr class="">
												<th>Customer Pay</th>
												<td class="text-right">
												<div class="">
													<input name="customer_paying" type="number" class="form-control calcEvent customer_paying input-sm" id="customer_paying" step="any" min="0">
												</div>
												</td>
											</tr>

											<tr class="grandTotal">
												<th>Grand Total:</th>
												<td class="text-right">
													<span class="currencySymbol" style="display: inline-block;"></span>
													<span id="grandTotal">00.00</span>
													<input type="hidden" name="grandTotal" />
												</td>
											</tr>

											<tr class="amount_due">
												<th>Outstanding:</th>
												<td class="text-right">
													<span class="currencySymbol" style="display: inline-block;"></span>
													<span id="amount_due">00.00</span>
													<input type="hidden" name="amount_due" />
												</td>
											</tr>
											</tbody>
										</table>

										<button type="submit" name="save_bill" class="save_bill btn btn-gradient-success btn-fw">Save Bill</button>

										<button type="submit" name="save_bill_print" class="save_bill_print btn btn-gradient-success btn-fw">Save Bill & Print</button>
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
    $(document).ready(function(){
         $(document).on("change", ".package", function() {
             var pay = $(this).find(':selected').data("pay");
             var get = $(this).find(':selected').data("get");
             var package_name = $(this).find(':selected').data("package_name");
             var pakage_validity = $(this).find(':selected').data("pakage_validity");
             var validity = $(this).find(':selected').data("validity");
             $(".customer_get").val(get);
			 $(".customer_pay").val(pay);
			 $("#package_name").val(package_name);
			 $("#pakage_validity").val(pakage_validity);
			 
			 var tax = 18;
			 var tax_amt = (pay*tax)/100;
			 var sub_ttl = pay-tax_amt;

			 $("#subTotal").text(sub_ttl);
			 $("input[name='subTotal']").val(sub_ttl);
			 
			 $("#taxTotal").text(tax_amt);
			 $("input[name='taxTotal']").val(tax_amt);
			 
			 $("#customer_paying").val(pay);

			 $("#grandTotal").text(pay);
			 $("input[name='grandTotal']").val(pay);

			 
		 });
		 
		$(document).on("change keyup", "#customer_paying", function() {
			var pay = $(".package").find(':selected').data("pay");
			var paying = $(this).val();
			if(paying > pay){
				$("#customer_paying").val(pay);
			}

			var paying = $(this).val();
			var outstanding = pay-paying;
			$("#amount_due").text(outstanding);
			 $("input[name='amount_due']").val(outstanding);

		});
    });
</script>



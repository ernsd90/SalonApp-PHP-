<?php include 'header.php';



/*-Service Category--*/
$query = "SELECT * FROM `hr_product_brand` where salon_id='".$salon_id."' ";
$product_brand = select_array($query);
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

			<form action="invoice_product.php" method="post" id="productform" autocomplete="off">
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
										<th width="15%" class="text-center">Product</th>
										<th width="15%" class="text-center">Staff</th>
										<th width="10%" class="text-center">Quantity</th>
										<th width="10%"class="text-right">Total</th>
									</tr>
									</thead>
									<tbody>
										<?php for($i=0;$i<=3;$i++){ ?>
										<tr class="item tr_clone<?php echo $i; ?>">
											<td>
												<div class="">
													<select name="product_id[]" id="product_id<?php echo $i; ?>" class="search_select product_id form-control select2-selection--single" style="width:100%">
														<option value="0">Select Product</option>
														<?php foreach($product_brand as $name){ 
															$query = "SELECT * FROM `hr_product` where brand_id='".$name['brand_id']."' and product_qty > 0 ";
															$products = select_array($query);
															if($products != false){
														?>
															<optgroup label="<?php echo $name['brand_name'] ;?>">
															<?php 
																foreach($products as $product){ 
																	extract($product);
															?>
															<option data-price="<?php echo $product_price; ?>" value="<?php echo $product_id; ?>"><?php echo $product_name; ?></option>
														<?php } ?>
														</optgroup>
														<?php } } ?>
													</select>
												</div>
											</td>
											<td>
												<div class="">
													<select multiple name="product_staff[<?php echo $i; ?>][]" id="product_staff<?php echo $i; ?>" class="search_select product_staff form-control select2-selection--single " style="width:100%">
														<option  value="0" disabled>Select Staff</option>
														<?php foreach($staff as $name){
															?>
															<option value="<?php echo $name['staff_id']; ?>"><?php echo $name['staff_name'] ;?></option>
														<?php } ?>
													</select>
												</div>
											</td>
											<td>
												<div class="">
													<input name="service_qty[]" type="number" class="form-control calcEvent service_qty input-sm" id="service_qty"  step="any" min="0" value="1">
												</div>
											</td>
											<td class="text-right">
											<input name="service_price[]" type="number" class="form-control calcEvent service_price input-sm" id="service_price"  step="any" min="1">
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
											<th>Payment Mode</th>
												<td class="text-right">
														<select class="payment_mode form-control" name="payment_mode">
															<?php foreach($payment_method as $key => $v) { if($key != 'pkg'){ ?>
																<option value="<?php echo $key; ?>"><?php echo $v; ?></option>
															<?php } } ?>
														</select>
												</td>
											</tr>
											<input name="service_gst[]" type="hidden"  class="service_gst form-control" id="service_gst" placeholder="GST(%)" value="18">
											<input name="discount" type="hidden"  class="discount form-control" id="discount" value="0">

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

											<tr class="amount_due">
												<th>Grand Total:</th>
												<td class="text-right">
													<span class="currencySymbol" style="display: inline-block;"></span>
													<span id="grandTotal">00.00</span>
													<input type="hidden" id="grandTotal_input" name='grandtotal' />
													
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
         $(document).on("change", ".product_id", function() {
             var price = $(this).find(':selected').data("price");

			 var current_row = $(this).closest('tr');
			

			 current_row.find(".service_price").val(price);
			
			 calcTotalsProduct();
			 
		 });
		 
		 $(document).on("change keyup", ".service_price,.service_qty", function() {
			calcTotalsProduct();
		});
		
		function calcTotalsProduct() {
        var subTotal = 0;
        var total = 0;
        var amountDue = 0;
        var totalTax = 0;
        $('tr.item').each(function() {
            var quantity = parseFloat($(this).find(".service_qty").val());
            var price = parseFloat($(this).find(".service_price").val());

            var itemTotal = parseFloat(quantity * price) > 0 ? parseFloat(quantity * price) : 0;
            subTotal += parseFloat(price * quantity) > 0 ? parseFloat(price * quantity) : 0;
        });
        $('#grandTotal').text(subTotal.toFixed(2));

        $('#grandTotal_input').val(subTotal.toFixed(2));
        //$( '#amountDue' ).text( amountDue.toFixed(2) );
    }
    });
</script>



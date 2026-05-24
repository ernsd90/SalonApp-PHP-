<?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['service_id']) && is_numeric($_REQUEST['service_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_services` WHERE `service_id`='".$_REQUEST['service_id']."'";
        $user = select_row($sql);
        extract($user); 
		
	
    }
	/*-Service Category--*/
	$query = "SELECT * FROM `hr_servicesCategory` ";
    $service_catNames = select_array($query);
	/*----Staff------*/
	$query = "SELECT * FROM `hr_staff` ";
    $staff = select_array($query);
	/*----Staff------*/
	$query = "SELECT * FROM `hr_services` ";
    $services = select_array($query);
    ?>

        <form class="form-horizontal" id="salon_form" method="post">
			
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Service</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
				</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
						<div class="card ">
						<div class="card-body p-3">
                            <div class=" row">
								<div class="col-md-6">
								  <div class="form-group row">
									<label class="col-sm-12 col-form-label">Service Category</label>
									<div class="col-sm-12">
										
									  <select name="service_catid" class="form-control service_catid">
										<option>Select Category</option>
										<?php foreach($service_catNames as $name){
											?>
											<option value="<?php echo $name['service_catid'] ;?>"><?php echo $name['service_catName'] ;?></option>
										<?php } ?>
									  </select>
									</div>
								  </div>
								</div>
								<div class="col-md-6">
								  <div class="form-group row">
									<label class="col-sm-12 col-form-label">Service</label>
									<div class="col-sm-12">
									  <select id="sub_service"  name="sub_service" class=" sub_service form-control">
										<option>Select Service</option>
									  </select>
									</div>
								  </div>
								</div>
								
								<div class="col-md-6">
								  <div class="form-group row">
									<label class="col-sm-12 col-form-label">Staff</label>
									<div class="col-sm-12">
										
									  <select name="service_staff" class="service_staff form-control">
										<option>Select Staff</option>
										<?php foreach($staff as $name){
											
											?>
											<option value="<?php echo $name['staff_id']; ?>"><?php echo $name['staff_name'] ;?></option>
										<?php } ?>
									  </select>
									</div>
								  </div>
								</div>
								
								<div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="service_qty" class=" text-right control-label col-form-label">Quantity</label>
                                        <div class="">
                                            <input required name="service_qty" type="number" class="service_qty form-control" id="service_qty" placeholder="Quantity" value="1">
                                        </div>
                                    </div>
                                </div>
								
								<div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="service_price" class=" text-right control-label col-form-label">Price</label>
                                        <div>
                                            <input name="service_price" type="number" class="service_price form-control" id="service_price" placeholder="Service Price" value="">
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="service_gst" class=" text-right control-label col-form-label">GST(%)</label>
                                        <div class="">
                                            <input name="service_gst" type="number" class="service_gst form-control" id="service_gst" placeholder="GST(%)" value="18">
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="service_discount" class=" text-right control-label col-form-label">Discount <small> In %</small></label>
                                        <div class="">
                                            <input name="service_discount" type="number" class="service_discount form-control" id="service_discount" placeholder="Discount" value="">
                                        </div>
                                    </div>
                                </div> 
								
								<div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="service_total" class=" text-right control-label col-form-label">Total</label>
                                        <div class="">
                                            <input readonly name="service_total" type="number" class="service_total form-control" id="service_total" placeholder="Total" value="">
                                        </div>
                                    </div>
                                </div>
								
								
							</div>
						</div>
						</div>
                    </div>
                </div>
              <!-- row -->
              </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="update_user btn btn-primary">Save changes</button>
            </div>    
        </form>
		<script>

			$(document).ready(function(){
				$(document).on("change",".service_catid",function(){
					$.ajax({
						type: "POST",
						url: "ajax/salon_ajax.php",
						data: "method=get_sub_service&service_catid="+$(this).val(),
						success: function(res) {
							var obj = jQuery.parseJSON(res);
							if (obj.error == 1) {
								toastr.error("SERVER ERROR!!", 'Service');
							} else {
								$("#sub_service").html(obj);
							}
						},
						error: function() {
							alert("Error");
						}
					});
				});

				$(document).on("change",".sub_service",function(){
					$.ajax({
						type: "POST",
						url: "ajax/salon_ajax.php",
						data: "method=get_sub_service_detail&service_id="+$(this).val(),
						success: function(res) {
							var obj = jQuery.parseJSON(res);
							if (obj.error == 1) {
								toastr.error("SERVER ERROR!!", 'Service');
							} else {
								$(".service_price").val(obj.service_price);
								calculate_price();
							}
						},
						error: function() {
							alert("Error");
						}
					});
				});

				$(document).on("change keyup",".service_price,.service_qty,.service_gst,.service_gst,.service_discount",function(){
					calculate_price();
				});

				function calculate_price(){

					var service_price = parseInt($(".service_price").val());
					var service_qty = parseInt($(".service_qty").val());
					var service_gst = parseInt($(".service_gst").val());
					var service_discount = parseInt($(".service_discount").val());
					var service_total = $(".service_total");
					
					var total_without_gst = (service_price*service_qty);
					var gst_total = (total_without_gst*service_gst)/100;
					var grand_total = total_without_gst+gst_total;

					if($.isNumeric(service_discount)){
						var total_discount = (total_without_gst*service_discount)/100;
						grand_total = grand_total-total_discount;
					}
					
					grand_total = grand_total.toFixed(2);
					service_total.val(grand_total);
				}

				
			});
		 
		</script>
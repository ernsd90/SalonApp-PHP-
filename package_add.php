<?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['pkg_id']) && is_numeric($_REQUEST['pkg_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_package` WHERE `pkg_id`='".$_REQUEST['pkg_id']."'";
        $user = select_row($sql);
        extract($user); 
		
	
    }
    ?>

        <form class="form-horizontal" id="salon_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_package">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_package">';
                    echo '<input name="pkg_id" type="hidden" value="'.$pkg_id.'">';
                    $required = "";
                }
                echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';

             
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Create New Packages</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
						<div class="card ">
						<div class="card-body">
                            <div class=" row">
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="package_name" class=" text-right control-label col-form-label">Package Name</label>
                                        <div class="">
                                            <input required name="package_name" type="text" class="form-control" id="package_name" placeholder="Package Name" value="<?php echo $package_name; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="customer_pay" class=" text-right control-label col-form-label">Customer Pay</label>
                                        <div class="">
                                            <input required name="customer_pay" type="text" class="form-control" id="customer_pay" placeholder="Customer Pay" value="<?php echo $customer_pay; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="customer_get" class=" text-right control-label col-form-label">Customer Get</label>
                                        <div class="">
                                            <input required name="customer_get" type="text" class="form-control" id="customer_get" placeholder="Customer Get" value="<?php echo $customer_get; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="package_validity" class=" text-right control-label col-form-label">Package Validity</label>
                                        <div class="">
                                            <input required name="package_validity" type="number" class="form-control" id="package_validity" placeholder="Package Validity" value="<?php echo $package_validity; ?>"> In Months
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-6">
									<div class="form-group ">
										<div class="form-check form-check-flat form-check-primary">
											<label for="package_status" class="form-check-label">
											  <input name="package_status" id="package_status" value="1" type="checkbox" class="form-check-input" <?php if($package_status == 1){ echo 'checked'; }?>> Package Status <i class="input-helper"></i></label>
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
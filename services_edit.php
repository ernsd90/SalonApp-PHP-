    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['service_id']) && is_numeric($_REQUEST['service_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_services` WHERE `service_id`='".$_REQUEST['service_id']."'";
        $user = select_row($sql);
        extract($user); 
		
	
    }

$salon_id = get_session_data('salon_id');
	$sql2 = "SELECT * FROM `hr_servicesCategory` where salon_id='".$salon_id."' ";
    $service_catNames = select_array($sql2);
    ?>

        <form class="form-horizontal" id="salon_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_services">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_services">';
                    echo '<input name="service_id" type="hidden" value="'.$service_id.'">';
                    $required = "";
                }

                echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';

             
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Service Category</h5>
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
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="service_name" class=" text-right control-label col-form-label">Service Name</label>
                                        <div class="">
                                            <input required name="service_name" type="text" class="form-control" id="Name" placeholder="Service Name" value="<?php echo $service_name; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <label for="service_price" class=" text-right control-label col-form-label">Service price</label>
                                        <div class="">
                                            <input required name="service_price" type="text" class="form-control" id="service_price" placeholder="Service Name" value="<?php echo $service_price; ?>">
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-4">
								  <div class="form-group row">
									<label class="col-sm-12 col-form-label">Service Category Name</label>
									<div class="col-sm-12">
										
									  <select name="service_catid" class="form-control">
										<option>Select Service Category</option>
										<?php foreach($service_catNames as $name){
											
											?>
											<option <?php if($name['service_catid'] == $service_catid){echo 'selected ';}?>value="<?php echo $name['service_catid']; ?>"><?php echo $name['service_catName'] ;?></option>
										<?php } ?>
									  </select>
									</div>
								  </div>
								</div>

                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <label for="service_reminder" class=" text-right control-label col-form-label">Service Reminder</label>
                                        <div class="">
                                            <input required name="service_reminder" type="text" class="form-control" id="service_reminder" placeholder="After how many Days" value="<?php echo $service_reminder; ?>">
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-6">
									<div class="form-group ">
										<div class="form-check form-check-flat form-check-primary">
											<label class="form-check-label">
											  <input name="service_status" id="service_status" value="1" type="checkbox" class="form-check-input" <?php if($service_status == 1){ echo 'checked'; }?>> Service Status <i class="input-helper"></i></label>
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
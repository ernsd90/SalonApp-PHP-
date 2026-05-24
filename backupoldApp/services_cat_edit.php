    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['service_catid']) && is_numeric($_REQUEST['service_catid'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_servicesCategory` WHERE `service_catid`='".$_REQUEST['service_catid']."'";
        $user = select_row($sql);
        extract($user);
	
    }

    ?>

        <form class="form-horizontal" id="salon_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_services_cat">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_services_cat">';
                    echo '<input name="service_catid" type="hidden" value="'.$service_catid.'">';
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
                                        <label for="service_catName" class=" text-right control-label col-form-label">Full Name</label>
                                        <div class="">
                                            <input required name="service_catName" type="text" class="form-control" id="Name" placeholder="Service Category Name" value="<?php echo $service_catName; ?>">
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
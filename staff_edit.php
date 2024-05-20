    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['staff_id']) && is_numeric($_REQUEST['staff_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_staff` WHERE `staff_id`='".$_REQUEST['staff_id']."'";
        $user = select_row($sql);
        extract($user);
	
    }

    ?>

        <form class="form-horizontal" id="user_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_staff">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_staff">';
                    echo '<input name="staff_id" type="hidden" value="'.$staff_id.'">';
                    $required = "";
                }
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Staff Form</h5>
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
                                        <label for="Name" class=" text-right control-label col-form-label">Full Name</label>
                                        <div class="">
                                            <input required name="staff_name" type="text" class="form-control" id="Name" placeholder="Staff Name" value="<?php echo $staff_name; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="staff_mob" required class=" text-right control-label col-form-label">Mobile No<small class="text-muted">+91 999 999 9999</small></label>
                                    <div class="">
                                        <input type="number" name="staff_mob" class="form-control" id="staff_mob" value="<?php echo $staff_mob; ?>" placeholder="Mobile No">
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="joining_date" class=" text-right control-label col-form-label">Joining Date</label>
                                    <div class="">
                                        <input name="joining_date" type="date" class="form-control" value="<?php echo $joining_date; ?>" id="joining_date" placeholder="Joining Date">
                                    </div>
                                </div>
                                </div>
								<div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="staff_salary" class=" text-right control-label col-form-label"> Staff Salary</label>
                                        <div class="">
                                            <input required name="staff_salary" type="text" class="form-control" id="staff_salary" placeholder="Staff Salary" value="<?php echo $staff_salary; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group ">
									<div class="form-check form-check-flat form-check-primary">
										<label class="form-check-label">
										  <input name="staff_status" id="staff_status" value="1" type="checkbox" class="form-check-input" <?php if($staff_status == 1){ echo 'checked'; }?>>  Status <i class="input-helper"></i></label>
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
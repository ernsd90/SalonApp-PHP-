    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_user` WHERE `user_id`='".$_REQUEST['user_id']."'";
        $user = select_row($sql);
        extract($user);
		
		$user_role_id = $role_id;
        
    }

		$sql = "SELECT * FROM `hr_user_role`";
        $roles = select_array($sql);

        $sql = "SELECT salon_id as salon_ids,salon_name,salon_address FROM `hr_salon`";
        $all_salon = select_array($sql);
    ?>

        <form class="form-horizontal" id="user_form" method="post">
            <?php 
                if($user_action == "create"){

                    echo '<input name="method" type="hidden" value="create_user">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_user">';
                    echo '<input name="user_id" type="hidden" value="'.$user_id.'">';
                    $required = "";
                }
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">User Form</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
						 <div class=" card">
                            <div class=" card-body">
                            <div class=" row">

                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="Name" class=" text-right control-label col-form-label">Full Name</label>
                                        <div class="">
                                            <input required name="full_name" type="text" class="form-control" id="Name" placeholder="Full Name" value="<?php echo $full_name; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="username" required class=" text-right control-label col-form-label">Username</label>
                                    <div class="">
                                        <input type="text" name="username" class="form-control" id="username" value="<?php echo $username; ?>" placeholder="Username">
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="Password" class=" text-right control-label col-form-label">Password</label>
                                    <div class="">
                                        <input <?php echo  $required; ?> name="user_password" type="text" class="form-control" id="Password" value="<?php echo $password; ?>" placeholder="Password">
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="Confirm" class=" text-right control-label col-form-label">Salon</label>
                                    <div class="">
                                            <select required name="salon_id" id="role" class="select2 form-control custom-select" style="width: 100%; height:45px;">
                                            <?php 	foreach($all_salon as $salon){
                                                foreach ($salon as $var => $sale){
                                                    $$var = $sale;
                                                }
                                            ?>
                                                <option <?php echo ($salon_id == $salon_ids ? "selected":"");?> value="<?php echo $salon_ids;?>" ><?php echo $salon_name."(".$salon_address.")"; ?></option>
                                                
                                            <?php } ?>
                                            </select>
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mobile <small class="text-muted">+91 999 999 9999</small></label>
                                    <input value="<?php echo $user_mobile; ?>" name="user_mobile" type="text" class="form-control " id="" placeholder="Mobile">
                                </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="role" class="">Role</label>
                                    <div class="">

                                    <select required name="role_id" id="role" class="select2 form-control custom-select" style="width: 100%; height:45px;">
									<?php 	foreach($roles as $role){
											extract($role);
									?>
                                        <option <?php echo ($user_role_id == $role_id ? "selected":"");?> value="<?php echo $role_id;?>" ><?php echo $role_name; ?></option>
										
									<?php } ?>
                                    </select>
                                
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
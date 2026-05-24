    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_vendor` WHERE `id`='".$_REQUEST['id']."'";
        $user = select_row($sql);
        extract($user);
	
    }

    ?>

        <form class="form-horizontal" id="user_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_vendor">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_vendor">';
                    echo '<input name="id" type="hidden" value="'.$id.'">';
                    $required = "";
                }
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Vendor Form</h5>
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
                                        <label for="Name" class=" text-right control-label col-form-label">Vendor Name</label>
                                        <div class="">
                                            <input required name="vendor_name" type="text" class="form-control" id="Name" placeholder="Vendor Name" value="<?php echo $vendor_name; ?>">
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
                <button type="submit" class="update_vendor btn btn-primary">Save changes</button>
            </div>    
        </form>
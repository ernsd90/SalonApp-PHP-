    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['exp_catId']) && is_numeric($_REQUEST['exp_catId'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_expenses_category` WHERE `exp_catId`='".$_REQUEST['exp_catId']."'";
        $user = select_row($sql);
        extract($user);
	
    }

    ?>

        <form class="form-horizontal" id="salon_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_expenses_cat">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_expenses_cat">';
                    echo '<input name="exp_catId" type="hidden" value="'.$exp_catId.'">';
                    $required = "";
                }

                echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';

             
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Expenses Category</h5>
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
                                        <label for="category_name" class=" text-right control-label col-form-label">Expenses Category Name</label>
                                        <div class="">
                                            <input required name="category_name" type="text" class="form-control" id="category_name" placeholder="Expenses Category Name" value="<?php echo $category_name; ?>">
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
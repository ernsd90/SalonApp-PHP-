    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['brand_id']) && is_numeric($_REQUEST['brand_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_product_brand` WHERE `brand_id`='".$_REQUEST['brand_id']."'";
        $user = select_row($sql);
        extract($user);
	
    }

    ?>

        <form class="form-horizontal" id="salon_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_product_brand">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_product_brand">';
                    echo '<input name="brand_id" type="hidden" value="'.$brand_id.'">';
                    $required = "";
                }

                echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';

             
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Product Brands</h5>
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
                                        <label for="brand_name" class=" text-right control-label col-form-label">Brand Name</label>
                                        <div class="">
                                            <input required name="brand_name" type="text" class="form-control" id="brand_name" placeholder="Brand Name" value="<?php echo $brand_name; ?>">
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-6">
									<div class="form-group ">
										<div class="form-check form-check-flat form-check-primary">
											<label class="form-check-label">
											  <input name="brand_status" id="brand_status " value="1" type="hidden"></label>
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
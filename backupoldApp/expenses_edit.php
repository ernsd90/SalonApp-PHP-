    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['exp_id']) && is_numeric($_REQUEST['exp_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_expenses` WHERE `exp_id`='".$_REQUEST['exp_id']."'";
        $user = select_row($sql);
        extract($user); 
		
	
    }
	$sql2 = "SELECT * FROM `hr_expenses_category` where salon_id='".$salon_id."' ";
    $expenses_catNames = select_array($sql2);
    ?>

        <form class="form-horizontal" id="salon_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_expenses">';
                    echo '<input name="payment_mode" type="hidden" value="'.$_GET['payment_mode'].'">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_expenses">';
                    echo '<input name="exp_id" type="hidden" value="'.$exp_id.'">';
                    $required = "";
                }

                echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';

             
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Expenses</h5>
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
                                        <label for="exp_name" class=" text-right control-label col-form-label">Expenses Name</label>
                                        <div class="">
                                            <input required name="exp_name" type="text" class="form-control" id="exp_name" placeholder="Expenses Name" value="<?php echo $exp_name; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group ">
                                        <label for="exp_total" class=" text-right control-label col-form-label">Expenses Total</label>
                                        <div class="">
                                            <input required name="exp_total" type="text" class="form-control" id="exp_total" placeholder="Expenses Total" value="<?php echo $exp_total; ?>">
                                        </div>
                                    </div>
                                </div>
                                <?php if($user_action != "create"){ ?>
                                <div class="col-md-3">
                                    <div class="form-group ">
                                        <label for="exp_total" class=" text-right control-label col-form-label">Expenses Date</label>
                                        <div class="">
                                            <input required name="exp_date" type="text" class="form-control" id="exp_date" value="<?php echo $exp_date; ?>">
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
								<div class="col-md-6">
								  <div class="form-group row">
									<label class="col-sm-12 col-form-label">Expenses Category Name</label>
									<div class="col-sm-12">
										
									  <select name="exp_catId" class="form-control">
										<option>Select Expenses Category</option>
										<?php foreach($expenses_catNames as $name){
											
											?>
											<option <?php if($name['exp_catId'] == $exp_catId){echo 'selected ';}?>value="<?php echo $name['exp_catId']; ?>"><?php echo $name['category_name'] ;?></option>
										<?php } ?>
									  </select>
									</div>
								  </div>
								</div>

                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="exp_vendor" class=" text-right control-label col-form-label">Expenses Vendor</label>
                                        <div class="">
                                            <input name="exp_vendor" type="text" class="form-control" id="exp_vendor" placeholder="Expenses Vendor" value="<?php echo $exp_vendor; ?>">
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="exp_note" class=" text-right control-label col-form-label">Expenses Note</label>
                                        <div class="">
										
											<textarea id="exp_note" name="exp_note"  class="form-control"><?php echo $exp_note ; ?></textarea>
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
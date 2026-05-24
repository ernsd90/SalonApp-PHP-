    <?php 
    include "function.php";

    $user_action = "create";
    if(isset($_REQUEST['product_id']) && is_numeric($_REQUEST['product_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `hr_product` WHERE `product_id`='".$_REQUEST['product_id']."'";
        $user = select_row($sql);
        extract($user);
	
    }

    $sql = "SELECT * FROM `hr_product_brand` WHERE `salon_id`='".$salon_id."'";
    $allbrand = select_array($sql);
    ?>

        <form class="form-horizontal" id="salon_form" method="post">
            <?php 
                if($user_action == "create"){
                    echo '<input name="method" type="hidden" value="create_product">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_product">';
                    echo '<input name="product_id" type="hidden" value="'.$product_id.'">';
                    $required = "";
                }
                echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';

             
				
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Product </h5>
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
                                        <label for="product_name" class=" text-right control-label col-form-label">Select Brand</label>
                                        <div class="">
                                           <select name="brand_id" id="brand_id" class="search_selectbrand  select2-selection">
                                                <option value="">Select Brand Name</option>
                                                <?php foreach($allbrand as $brand){
                                                    $selected = ($brand['brand_id'] == $brand_id ? "selected":"");
                                                    ?>
                                                <option <?php echo $selected; ?> value="<?php echo $brand['brand_id']; ?>"><?php echo $brand['brand_name']; ?></option>
                                                <?php } ?>
                                           </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="product_name" class=" text-right control-label col-form-label">Product Name</label>
                                        <div class="">
                                            <input required name="product_name" type="text" class="form-control" id="product_name" placeholder="Product Name" value="<?php echo $product_name; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="product_name" class=" text-right control-label col-form-label">Product Price(MRP)</label>
                                        <div class="">
                                            <input required name="product_price" type="number" min="1" class="form-control" id="product_price" placeholder="Product Price" value="<?php echo $product_price; ?>">
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-6">
									<div class="form-group ">
										<div class="form-check form-check-flat form-check-primary">
											<label class="form-check-label">
											  <input name="product_status" id="product_status " value="1" type="hidden"></label>
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
$(document).ready(function() {
    $(".search_selectbrand").select2();
});
        </script>

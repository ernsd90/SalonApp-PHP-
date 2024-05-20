<?php 
    include "function.php";

    $form_page = "salon_form";
    if(isset($_REQUEST['serviceCat_id']) && is_numeric($_REQUEST['serviceCat_id'])){
		$id = $_REQUEST['serviceCat_id'];
        $method = "serviceCat_delete";
    }else if(isset($_REQUEST['pkg_id']) && is_numeric($_REQUEST['pkg_id'])){
        $id = $_REQUEST['pkg_id'];
        $method = "delete_package";
    }else if(isset($_REQUEST['inventorybill_id']) && is_numeric($_REQUEST['inventorybill_id'])){
        $id = $_REQUEST['inventorybill_id'];
        $method = "delete_inventorybill";
    }else if(isset($_REQUEST['service_id']) && is_numeric($_REQUEST['service_id'])){
		$id = $_REQUEST['service_id'];
        $method = "delete_service";
    }else if(isset($_REQUEST['service_catid']) && is_numeric($_REQUEST['service_catid'])){
		$id = $_REQUEST['service_catid'];
        $method = "delete_services_cat";
    
    }else if(isset($_REQUEST['product_id']) && is_numeric($_REQUEST['product_id'])){
		$id = $_REQUEST['product_id'];
    $method = "delete_product";
    
    }else if(isset($_REQUEST['brand_id']) && is_numeric($_REQUEST['brand_id'])){
      $id = $_REQUEST['brand_id'];
      $method = "delete_product_brand";

    }else if(isset($_REQUEST['exp_catId']) && is_numeric($_REQUEST['exp_catId'])){
		$id = $_REQUEST['exp_catId'];
    $method = "delete_expenses_cat";
    
    }else if(isset($_REQUEST['exp_id']) && is_numeric($_REQUEST['exp_id'])){
		$id = $_REQUEST['exp_id'];
    $method = "delete_expenses";
    
    }else if(isset($_REQUEST['staff_id']) && is_numeric($_REQUEST['staff_id'])){
      $id = $_REQUEST['staff_id'];
      $method = "delete_staff";
      $form_page = "user_form";
      }else{
		echo "Error!!!!";
		exit;
	}	

    ?>

        <form class="form-horizontal" id="<?php echo $form_page;  ?>" method="post">
            <?php 
                    echo '<input name="method" type="hidden" value="'.$method.'">';
                    echo '<input name="id" type="hidden" value="'.$id.'">';
            
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Delete </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                            <div class=" row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td>Are You Sure To Delete !!!!</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              <!-- row -->
              </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="delete_user btn btn-danger">Delete changes</button>
            </div>    
        </form>
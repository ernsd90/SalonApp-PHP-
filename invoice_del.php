<?php 
    include "function.php";

    $form_page = "report_form";

    if(isset($_REQUEST['invoice_id']) && is_numeric($_REQUEST['invoice_id'])){
		$invoice_id = $_REQUEST['invoice_id'];
        $method = "invoice_delete";
    }

    ?>

        <form class="form-horizontal" id="<?php echo $form_page;  ?>" method="post" autocomplete="off">
            <?php 
                    echo '<input name="method" type="hidden" value="'.$method.'">';
                    echo '<input name="invoice_id" type="hidden" value="'.$invoice_id.'">';
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Delete Invoice</h5>
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
                                        <div class="card-body">
                                            <div class=" row">
                                                <div class="col-md-12">
                                                    <div class="form-group ">
                                                        <label for="delete_reason" class=" text-right control-label col-form-label">Delete Reason</label>
                                                        <div class="">
                                                            <input autocomplete="off" value="" required name="delete_reason" type="text" class="form-control" id="delete_reason" placeholder="Why You want to delete invoice">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group ">
                                                        <label for="delete_pwd" class=" text-right control-label col-form-label">Delete Password</label>
                                                        <div class="">
                                                            <input autocomplete="new-password" value="" required name="delete_pwd" type="password" class="form-control" id="delete_pwd" placeholder="Delete Password">
                                                        </div>
                                                    </div>
                                                </div>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="delete_user btn btn-danger">Delete invoice</button>
            </div>    
        </form>
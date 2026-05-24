    <?php 
    include "../function.php";

    $user_action = "create";
    if(isset($_REQUEST['invoice_id']) && is_numeric($_REQUEST['invoice_id'])){
        $user_action = "edit";

        $sql = "SELECT invoice_id,payment_mode FROM `hr_invoice` WHERE `invoice_id`='".$_REQUEST['invoice_id']."'";
        $user = select_row($sql);
        extract($user);
    }

    ?>

        <form class="form-horizontal" id="report_form" method="post">
            <?php 

                    echo '<input name="method" type="hidden" value="update_invoice">';
                    echo '<input name="invoice_id" type="hidden" value="'.$invoice_id.'">';
                    $required = "";
                    echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Invoice Edit</h5>
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
                                        <label for="exp_name" class=" text-right control-label col-form-label">Payment Method</label>
                                        <div class="">
                                            <select class="payment_mode form-control" name="payment_mode">
                                                <?php foreach($payment_method as $key => $v) { if($key == 'pkg'){ continue; }?>
                                                    <option <?php if($payment_mode == $key){ echo "selected"; } ?> value="<?php echo $key; ?>"><?php echo $v; ?></option>
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
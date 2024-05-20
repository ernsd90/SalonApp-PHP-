<?php
include "function.php";

extract($_REQUEST);

$sql = "SELECT * FROM `hr_customer` where  `cust_id`='".$cust_id."'";
$user = select_row($sql);

foreach($user as $var => $value){
    $$var = $value;
}
?>

<form class="form-horizontal" id="customer_form" method="post">
    <?php

    echo '<input name="method" type="hidden" value="customer_update">';
    echo '<input name="cust_id" type="hidden" value="'.$cust_id.'">';
    $required = "";
    echo '<input name="cust_id" type="hidden" value="'.$cust_id.'">';
    ?>
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Customer Edit</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card ">
						<div class="card-body">

							  <div class="row">
                                  <input name="salon_id" type="hidden" value="<?php echo $salon_id; ?>">
                                  <div class="col-md-6">
                                      <div class="form-group ">
                                          <label class="">Name</label>
                                          <input class="form-control" name="cust_name" placeholder="Name" value="<?php echo $cust_name; ?>">

                                      </div>
                                  </div>
								<div class="col-md-6">
								  <div class="form-group ">
									<label class="">Phone Number</label>
									    <input class="form-control" name="cust_mobile" placeholder="Phone Number" value="<?php echo $cust_mobile; ?>">
								  </div>
								</div>

                                  <div class="col-md-6">
                                      <div class="form-group ">
                                          <label class="">Wallet</label>
                                          <input class="form-control" name="cust_wallet" placeholder="Name" value="<?php echo $cust_wallet; ?>">

                                      </div>
                                  </div>
                                  <div class="col-md-6">
                                      <div class="form-group ">
                                          <label class="">OutStanding</label>
                                          <input class="form-control" name="cust_outstanding" placeholder="Phone Number" value="<?php echo $cust_outstanding; ?>">
                                      </div>
                                  </div>

                                  <div class="col-md-6">
                                      <div class="form-group ">
                                          <label class="">Password</label>
                                          <input class="form-control" type="password" name="cust_password" placeholder="Password">
                                      </div>
                                  </div>


							  </div>
						</div>
						</div>
					</div>
				</div>
			
	</div>


    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="update_user btn btn-primary">Save changes</button>
    </div>
</form>
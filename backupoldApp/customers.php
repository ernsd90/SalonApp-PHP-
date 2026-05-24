    <?php include 'header.php';?>

        <!-- partial -->
        <div class="main-panel">
			<div class="content-wrapper">
		
				<div class="row">
					<div class="col-sm-12">
						<div class="card ">
						<div class="card-body">
							<div class="row clearfix">
								<div class="col-sm-12  p-0">
									<div class="row clearfix mb-3">
										<div class="col-sm-6  ">
											<h4 class="card-title">Customer</h4>
										</div>
										<div class="col-sm-6  ">
											<div class="clearfix">
												<button type="button" style="float:right" class="btn-sm  btn btn-success modalButtonCommon" data-toggle="modal" data-href="#"><i class="fa fa-plus-circle"></i> Add Customer</button>
											</div>
										</div>
									</div>
									
									
								<table id="get_customer" class="table table-striped table-bordered CommonModelClick">
                                        <thead>
                                            <tr>
                                                <th>Cust Id</th>
                                                <th>Name</th>
                                                <th>Mobile</th>
                                                <th>Wallet</th>
                                                <th>Outstanding</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>

								</div>
							</div>
						</div>
						</div>
					</div>
				</div>
			
			
			</div>
	
          <!-- content-wrapper ends -->
		  
<?php include 'footer.php';?>

<!-- Modal -->
<div class="modal fade " id="modalButtonCommon" tabindex="-1" role="dialog" aria-labelledby="usermodel" aria-hidden="true">
    <div class="modal-dialog movie_edit_model" role="document">
        <div class="modal-content">
            
        </div>
    </div>
</div>
<!-- Modal -->

<script>



</script>
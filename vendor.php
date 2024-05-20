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
											<h4 class="card-title">Vendor</h4>
										</div>
										<div class="col-sm-6  ">
											<div class="clearfix">
												<button type="button" style="float:right" class="btn-sm  btn btn-success modalButtonCommon" data-toggle="modal" data-href="vendor_add.php"><i class="fas fa-plus-circle"></i> Add Vendor</button>
											</div>
										</div>
									</div>
								
								<table id="get_vendor" class="table table-striped table-bordered CommonModelClick">
                                        <thead>
                                            <tr>
                                                <th>Vendor Id</th>
                                                <th>Name</th>                                               
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



<script>
</script>
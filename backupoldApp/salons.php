<?php include "header.php"; ?>

            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
           <div class="main-panel">
			<div class="content-wrapper">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class=" row">
									<div class="col-sm-12  p-0">
										<div class="row clearfix mb-3">
											<div class="col-sm-6  ">
												<h4 class="card-title">Outlets (Salons)</h4>
											</div>
											<div class="col-sm-6  ">
												<button type="button" style="float:right;" class="btn btn-sm btn btn-success modalButtonCommon" data-toggle="modal" data-href="salon_edit.php"><i class="fas fa-plus-circle"></i> Add Outlet</button>
											</div>
										</div>
										 <div class="table-responsive">
											<table id="get_salon" class="table table-striped table-bordered CommonModelClick">
												<thead class="">
													<tr>
														<th>ID</th>
														<th>Salon Name</th>
														<th>Address</th>
														<th>Contact</th>
														<th>Status</th>
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
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->

<?php include "footer.php"; ?>

<!-- Modal -->
<div class="modal fade" id="modalButtonCommon" tabindex="-1" role="dialog" aria-labelledby="usermodel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            
        </div>
    </div>
</div>
<!-- Modal -->

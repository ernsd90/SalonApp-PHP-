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
												<h4 class="card-title">User List</h4>
											</div>
											<div class="col-sm-6  ">
													<?php if(check_user_permission("user","user_create",$login_user)){ ?>
													
														<button type="button" style="float:right;" class="btn btn-sm btn btn-success modalButtonCommon" data-toggle="modal" data-href="user_edit.php"><i class="fas fa-plus-circle"></i> Add User</button>
													
													<?php } ?>
											</div>
										</div>
										 <div class="table-responsive">
											<table id="get_user" class="table table-striped table-bordered CommonModelClick">
												<thead class="">
													<tr>
														<th>User ID</th>
														<th>Name</th>
														<th>Username</th>
														<th>Mobile</th>
														<th>Role</th>
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
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
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
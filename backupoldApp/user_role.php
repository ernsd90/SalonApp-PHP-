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
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
								<div class=" row">
									<div class="col-md-12">
									<div class="row clearfix mb-3">
										<div class="col-sm-6  ">
											<h4 class="card-title">Role List</h4>
										</div>
										<div class="col-sm-6  ">
											<div class="clearfix">
												<button style="float:right" type="button" class="btn btn-sm  btn btn-success modalButtonCommon" data-toggle="modal" data-href="user_role_edit.php"><i class="fas fa-plus-circle"></i> Add User</button>
											</div>
										</div>
									</div>
								
                           
                                    <table class="table table-striped table-bordered CommonModelClick" id="get_role">
                                        <thead class="">
                                            <tr>
                                                
                                                <th>Role ID</th>
                                                <th>Role Name</th>
                                                <th>Actions</th>
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
               


<?php include "footer.php"; ?>     

<!-- Modal -->
<div class="modal fade" id="modalButtonCommon" tabindex="-1" role="dialog" aria-labelledby="usermodel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            
        </div>
    </div>
</div>
<!-- Modal -->
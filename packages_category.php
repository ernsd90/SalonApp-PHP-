    <?php include 'header.php';?>

        <!-- partial -->
        <div class="main-panel">
			<div class="content-wrapper">
		  
				<div class="row" id="proBanner">
				  <div class="col-12">
					<span >
					  <i style="display:none" class="mdi mdi-close" id="bannerClose"></i>
					</span>
				  </div>
				</div>
				<?php include 'menu.php';?>
				<div class="row">
					<div class="col-sm-12">
						<div class="card ">
						<div class="card-body">
							<form>
							<div class="row">
								<div class="col-sm-4  p-0">
									<div class="form-group">
										<label for="name">Name</label>
										<input type="text" class="form-control" id="name" placeholder="Search By Package Name">
									</div>
								</div>
								
								<div class="col-sm-4 mt-4">
									<div class="row ">
										<div class="col-sm-12">
											<button type="submit" class="btn btn-gradient-primary mr-2">Reset Search</button>
										</div>
									</div>
								</div>
							</div>
							</form>
							
							<div class="row">
								<div class="col-sm-12  p-0">
									<div class="divider mb-3" style="border: 1px solid #ccc;"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4  p-0">
									<div class="form-group">
										<a href="add-mainpackages.php" class="btn btn-gradient-dark btn-fw">Add Package Category</a>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-12  p-0 clearfix">
									<div class="divider " style="border: 1px solid #ccc;"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-12  p-0 pt-2">
									<div class=" float-right clearfix">
								<a class="btn btn-info " style="color:#fff">Download customer Excel</a>
							</div>
								</div>
							</div>
							<div class="row clearfix">
								<div class="col-sm-12  p-0">
									
									<table class="table table-striped">
									  <thead>
										<tr>
										  
										  <th> Name </th>
										  
										  <th> Status </th>
										  <th> Action </th>
										</tr>
									  </thead>
									  <tbody>
										<tr>
											<td>khsuhdh</td>
											
											<td>
												<div class="form-check form-check-success" style="margin:-7px 0 0 0">
													<label class="form-check-label">
														<input type="checkbox" class="form-check-input" checked=""> 
													</label>
												</div>
											</td>
										  <td>
											<a href="#" class="btn btn-gradient-info btn-xs">Edit</a>
											
										  </td>
										</tr>
										
										
										
									  </tbody>
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
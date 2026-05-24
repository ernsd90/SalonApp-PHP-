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
				
				<div class="row">
					<div class="col-sm-12">
						<div class="card ">
						<div class="card-body">
							<form>
							<div class="row">
								<div class="col-sm-4  p-0">
									<div class="form-group">
										<label for="name">Name</label>
										<input type="text" class="form-control" id="name" placeholder="Search By Product Name">
									</div>
								</div>
								<div class="col-sm-8 mt-3">
									<div class="row ">
										<div class="col-sm-2">
											<div class="form-check">
											  <label class="form-check-label">
												<input type="radio" class="form-check-input" name="res" id="Active" value="active" checked> Active <i class="input-helper"></i></label>
											</div>
										</div>
										<div class="col-sm-3">
											<div class="form-check">
											  <label class="form-check-label">
												<input type="radio" class="form-check-input" name="res" id="Completed" value="complete"> Completed <i class="input-helper"></i></label>
											</div>
										</div>
										<div class="col-sm-3">
											<div class="form-check">
											  <label class="form-check-label">
												<input type="radio" class="form-check-input" name="res" id="Canceled" value="cancel"> Canceled <i class="input-helper"></i></label>
											</div>
										</div>
										<div class="col-sm-4">
											<button type="submit" class="btn btn-gradient-primary mr-2">Reset Search</button>
										</div>
									</div>
								</div>
							</div>
							</form>
							
							<div class="row">
								<div class="col-sm-12  p-0">
									<div class="divider mb-2" style="border: 1px solid #ccc;"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4  p-0">
									<div class="form-group">
										<a href="jobcart.php" class="btn btn-gradient-dark btn-fw">Create Job Cart</a>
									</div>
								</div>
							</div>
							<div class="row">
								
								<div class="col-sm-12  p-0">
									<table class="table table-striped">
									  <thead>
										<tr>
										  <th> Job Cart Id </th>
										  <th> Customer Name </th>
										  <th> Phone No </th>
										  <th> Date </th>
										  <th> Start Time </th>
										  <th> End Time </th>
										  <th> Status </th>
										  <th> Action </th>
										</tr>
									  </thead>
									  <tbody>
										<tr>
										  <td>job_1264</td>
										  <td>Rakesh</td>
										  <td>9501808202</td>
										  <td>2020-02-10</td>
										  <td>11:34:20</td>
										  <td>11:00:20</td>
										  <td>Started</td>
										  <td>
											<a href="#" class="btn btn-gradient-primary btn-xs">View</a>
											<a href="#" class="btn btn-gradient-info btn-xs">Edit</a>
											<a href="#" class="btn btn-gradient-success btn-xs">Make Bill</a>
										  </td>
										</tr>
										<tr>
										  <td>job_1264</td>
										  <td>Rakesh</td>
										  <td>9501808202</td>
										  <td>2020-02-10</td>
										  <td>11:34:20</td>
										  <td>11:00:20</td>
										  <td>Started</td>
										  <td>
											<a href="#" class="btn btn-gradient-primary btn-xs">View</a>
											<a href="#" class="btn btn-gradient-info btn-xs">Edit</a>
											<a href="#" class="btn btn-gradient-success btn-xs">Make Bill</a>
										  </td>
										</tr>
										<tr>
										  <td>job_1264</td>
										  <td>Rakesh</td>
										  <td>9501808202</td>
										  <td>2020-02-10</td>
										  <td>11:34:20</td>
										  <td>11:00:20</td>
										  <td>Started</td>
										  <td>
											<a href="#" class="btn btn-gradient-primary btn-xs">View</a>
											<a href="#" class="btn btn-gradient-info btn-xs">Edit</a>
											<a href="#" class="btn btn-gradient-success btn-xs">Make Bill</a>
										  </td>
										</tr>
										<tr>
										  <td>job_1264</td>
										  <td>Rakesh</td>
										  <td>9501808202</td>
										  <td>2020-02-10</td>
										  <td>11:34:20</td>
										  <td>11:00:20</td>
										  <td>Started</td>
										  <td>
											<a href="#" class="btn btn-gradient-primary btn-xs">View</a>
											<a href="#" class="btn btn-gradient-info btn-xs">Edit</a>
											<a href="#" class="btn btn-gradient-success btn-xs">Make Bill</a>
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
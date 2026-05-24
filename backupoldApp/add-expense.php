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
								
								<div class="col-sm-12  ">
									<div class="form-group">
										<label for="phone">Expense Category</label>
										<select class="form-control form-control-sm" id="exampleFormControlSelect3">
											<option>Select</option>
											<option>Milk</option>
											<option>Other</option>
											
										</select>
									</div>
								</div>
								<div class="col-sm-12  ">
									<div class="form-group">
										<label for="name">Date</label>
										<input type="text" class="form-control" id="" placeholder="">
									</div>
								</div>
								<div class="col-sm-12  ">
									<div class="form-group">
										<label for="name">Description</label>
										<textarea class="form-control"></textarea>
									</div>
								</div>
								<div class="col-sm-12  ">
									<div class="form-group">
										<label for="name">Amount</label>
										<input type="text" class="form-control" id="" placeholder="Amount">
									</div>
								</div>
								<div class="col-sm-4 mt-4">
									<div class="row ">
										<div class="col-sm-12">
											<button type="submit" class="btn btn-gradient-primary mr-2">Submit</button>
										</div>
									</div>
								</div>
							</div>
							</form>
						</div>
						</div>
					</div>
				</div>
			
			
			</div>
	
          <!-- content-wrapper ends -->
		  
<?php include 'footer.php';?>
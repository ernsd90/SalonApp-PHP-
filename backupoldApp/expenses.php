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
											<h4 class="card-title">Cash Expenses</h4>
										</div>
										<div class="col-sm-6  ">
											<div class="clearfix">
												<button type="button" style="float:right" class="btn-sm  btn btn-success modalButtonCommon" data-toggle="modal" data-href="expenses_edit.php?payment_mode=cash"><i class="fa fa-plus-circle"></i> Add New Expenses</button>
											</div>
										</div>
									</div>


                                    <div class="row">
                                        <div class="col-md-2 stretch-card grid-margin p-1">
                                            <div class="card bg-gradient-success card-img-holder text-white">
                                                <div class="card-body p-4">
                                                    <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                                                    <h4 class="font-weight-normal mb-3">Total Count
                                                    </h4>
                                                    <p><i class="mdi mdi-account-multiple-plus  mdi-24px "></i></p>
                                                    <h2 class="mb-5 exp_number"></h2>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-2 stretch-card grid-margin p-1">
                                            <div class="card bg-gradient-danger card-img-holder text-white">
                                                <div class="card-body p-4">
                                                    <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                                                    <h4 class="font-weight-normal mb-3">Total Expence
                                                    </h4>
                                                    <p><i class="mdi mdi-bell-ring  mdi-24px "></i></p>
                                                    <h2 class="mb-5 exp_total"></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-6">
                                            <p id="date_filter">
                                                <span id="date-label-from" class="date-label">From: </span>
                                                <input class="date_range_filter date" type="text" id="exp_fromdate"  name="exp_fromdate" />
                                                <span id="date-label-to" class="date-label">  To:</span>
                                                <input class="date_range_filter date" type="text" id="exp_todate"  name="exp_todate" />
                                            </p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p id="staff_filter">

                                                <?php
                                                $sql2 = "SELECT * FROM `hr_expenses_category` where salon_id='".$salon_id."' ";
                                                $expenses_catNames = select_array($sql2);
                                                ?>

                                                <select  name="reportcategory_id" id="reportcategory_id" class=" required search_select reportcategory_id form-control select2-selection--single">
                                                    <option value="">Select Category</option>
                                                    <?php foreach($expenses_catNames as $name){
                                                        ?>
                                                        <option value="<?php echo $name['exp_catId']; ?>"><?php echo $name['category_name'] ;?></option>
                                                    <?php } ?>
                                                </select>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="table-responsives">
                                        <input  type="hidden" value="cash"  id="expence_type" />
                                        <table id="get_expenses" class="table table-striped table-bordered CommonModelClick">
                                            <thead>
                                                <tr>
                                                    <th>Exp ID</th>
                                                    <th>Category</th>
                                                    <th>Expence Name</th>
                                                    <th >Exp Total</th>
                                                    <th>Exp Note</th>
                                                    <th>Date</th>
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

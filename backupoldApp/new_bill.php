    <?php include 'header.php';?>
	<style>
	.new_p_details_d input {
    height: 30px;
}.new_p_details_d .text-right.control-label.col-form-label {
    margin: 0;
    padding: 0;
}.new_p_details_d .form-group {
    margin: 0;
}
	</style>
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
											<h4 class="card-title">Invoice</h4>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group ">
										<label for="" class=" text-right control-label col-form-label">Invoice Number</label>
										<div class="">
											<input required name="" type="text" class="form-control" id="" placeholder="" value="">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group ">
										<label for="" class=" text-right control-label col-form-label">Dealer</label>
										<div class="">
											<input required name="" type="text" class="form-control" id="" placeholder="" value="">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group ">
										<label for="" class=" text-right control-label col-form-label">Date</label>
										<div class="">
											<input required name="" type="date" class="form-control" id="" placeholder="" value="">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group row">
										<label class="col-sm-12 col-form-label">Invoice Type</label>
										<div class="col-sm-12">
											
										  <select name="exp_catId" class="form-control">
											<option>Invoice Type</option>
											
												<option value="">1</option>
												<option value="">1</option>
												<option value="">1</option>
										
										  </select>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group ">
										<label for="" class=" text-right control-label col-form-label">Total</label>
										<div class="">
											<input required name="" type="text" class="form-control" id="" placeholder="" value="">
										</div>
									</div>
								</div>
								
							
								<div class="">
									<div class="form-group ">
									<div ><button type="submit" class=" btn btn-success">Save Invoice</button></div>
									</div>
								</div>
							</div>
							
							
							<div class="row clearfix new_p_details_d" >
								<div class="col-sm-12  ">
									<div class="row clearfix mb-3">
										<div class="col-sm-6  ">
											<h4 class="card-title">Product Detail</h4>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group ">
										<label for="" class=" text-right control-label col-form-label">Product Name</label>
										<div class="">
											<input onkeypress="show_product()" required name="" type="text" class="form-control" id="" placeholder="" value="">
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group ">
										<label for="" class=" text-right control-label col-form-label">Quantity</label>
										<div class="">
											<input required name="" type="text" class="form-control" id="" placeholder="" value="">
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group ">
										<label for="" class=" text-right control-label col-form-label">MRP</label>
										<div class="">
											<input required name="" type="text" class="form-control" id="" placeholder="" value="">
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group ">
										<label for="" class=" text-right control-label col-form-label">Grand Total</label>
										<div class="">
											<input required name="" type="text" class="form-control" id="" placeholder="" value="">
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group ">
									<label for="" class=" text-right control-label col-form-label" style="opacity:0;">Add</label>
									<div id="pkkk"><button  onclick="package(0)" type="submit" class=" btn btn-primary btn-sm ">Add More</button></div>
									</div>
								</div>
								
							
								<div class="package ">
								</div>
							</div>
						
						
								<div class="mt-5">
									<div class="form-group ">
									<div ><button type="submit" class=" btn btn-success">Save Invoice</button></div>
									</div>
								</div>
						
						</div>
							
							
						</div>
					</div>
				</div>
			
			
			</div>
	
	<script>
	
		


		function package(id) { 
								var append_id = id + 1;
								$(".package").append('<div class="m-1 box_R_pack' + append_id + ' row clearfix"> <div class="col-md-3"><div class="form-group "><label for="" class=" text-right control-label col-form-label">Product Name</label><div class=""><input required name="" type="text" class="form-control" id="" placeholder="" value=""></div></div></div><div class="col-md-2"><div class="form-group "><label for="" class=" text-right control-label col-form-label">Quantity</label><div class=""><input required name="" type="text" class="form-control" id="" placeholder="" value=""></div></div></div><div class="col-md-2"><div class="form-group "><label for="" class=" text-right control-label col-form-label">MRP</label><div class=""><input required name="" type="text" class="form-control" id="" placeholder="" value=""></div></div></div><div class="col-md-3"><div class="form-group "><label for="" class=" text-right control-label col-form-label">Grand Total</label><div class=""><input required name="" type="text" class="form-control" id="" placeholder="" value=""></div></div></div>	<div class="col-md-1"><div class="form-group "> <label for="" class=" text-right control-label col-form-label" style="opacity:0;">Add</label> <button type="button" onclick="removepack(' + append_id + ')" class="btn btn-inverse-danger btn-sm">   <i class="mdi mdi-delete"></i>  </button>   </div></div> </div>');
								
								document.getElementById("pkkk").innerHTML = '<button   onclick="package('+ append_id +')"  type="submit" class="btn btn-primary btn-sm">Add More</button>';
								
							}  
							function removepack(id) { 
								$(".box_R_pack"+ id +"").remove();
								
							} 
							
						</script>
          <!-- content-wrapper ends -->
		  
<?php include 'footer.php';?>



<script>
</script>
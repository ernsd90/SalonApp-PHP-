<?php 
	include "header.php"; 

	$sql = "SELECT * FROM `hr_user_role`";
	$roles = select_array($sql);	
	$sql = "SELECT * FROM `category` where cat_type!='ep' order by cat_sort ASC";
	//$categories = select_array($sql);
	
 ?>


            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
               <div class="main-panel">
			<div class="content-wrapper">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-md-12">
                    <div class="card">
                    <div class="card-body">
                        
                        <!-- toggle part -->
                        <div id="accordian-4" class="accordion accordion-solid-header">
                            <div class="card m-t-30">
								<?php 
								$permissiontype = array("dashboard","billing","customer","expenses","cataloge","product","inventory","report","staff");
								foreach($roles as $role){

									$report = array();
									$setting = array();
									$user = array();
									$role_permission = array();
									$category = array();
									
									
									
									foreach ($role as $var => $sale){
										$$var = $sale;
									}
									$role_permission = json_decode($role_permission,true);

								
									foreach ($role_permission as $var => $sale){
										$$var = $sale;
									}
									


									//extract($report);
								?>
								
								<div class="">
                                <a class="btn_design card-header link  " data-toggle="collapse" data-parent="#accordian-4" href="#<?php echo str_replace(" ","",$role_name);?>" aria-expanded="false" aria-controls="Toggle-1">
                                    <i class="mdi mdi-arrow-down-drop-circle-outline up" aria-hidden="true"></i>
                                    <i class="mdi mdi-arrow-up-drop-circle-outline down" aria-hidden="false"></i>
                                    <span><?php echo $role_name;?></span>
                                </a>
                                <div id="<?php echo str_replace(" ","",$role_name);?>" class="collapse  multi-collapse">
                                    <div class="table-responsive">
										<form id='common_form'>
											<input type="hidden" name="role_id" value="<?php echo $role_id; ?>" />
											<input type="hidden" name="method" value="group_permission_update" />
											<table class="table">
												<thead class="thead-light">
													<tr>
														<th></th>
														<th>View</th>
														<th>Create</th>
														<th>Edit</th>
														<th>Delete </th>
													</tr>
												</thead>
												<tbody class="customtable">

												<?php foreach($permissiontype as $p_type){ 
													
													$view = '';
													$create = '';
													$edit = '';
													$delete = '';
													
													foreach ($role_permission[$p_type] as $var => $sale){
														$$var = $sale;
													}
													?>
													<tr>
														<td class="">
														<?php echo $p_type; ?>
														</td>
														<td>
															<div class="custom-control custom-checkbox mr-sm-21">
																<input value="1" name="data[<?php echo $p_type; ?>][view]" type="checkbox" class="custom-control-input" id="1<?php echo $p_type.$role_id;?>" <?php echo ($view == 1 ? "checked":"");?>>
																<label class="custom-control-label" for="1<?php echo $p_type.$role_id;?>"></label>
															</div>
														</td>
														<td>
															<div class="custom-control custom-checkbox mr-sm-21">
                                                            	<input value="1" name="data[<?php echo $p_type; ?>][create]" type="checkbox" class="custom-control-input" id="3<?php echo $p_type.$role_id;?>" <?php echo ($create == 1 ? "checked":"");?>>
                                                            	<label class="custom-control-label" for="3<?php echo $p_type.$role_id;?>"></label>
															</div>
														</td>
														<td>
                                                            <div class="custom-control custom-checkbox mr-sm-21">
                                                                <input value="1" name="data[<?php echo $p_type; ?>][edit]" type="checkbox" class="custom-control-input" id="02<?php echo $p_type.$role_id;?>" <?php echo ($edit == 1 ? "checked":"");?>>
                                                                <label class="custom-control-label" for="02<?php echo $p_type.$role_id;?>"></label>
                                                            </div>
														</td>
														<td>
															<div class="custom-control custom-checkbox mr-sm-21">
																<input value="1" name="data[<?php echo $p_type; ?>][delete]" type="checkbox" class="custom-control-input" id="2<?php echo $p_type.$role_id;?>" <?php echo ($delete == 1 ? "checked":"");?>>
																<label class="custom-control-label" for="2<?php echo $p_type.$role_id;?>"></label>
															</div>
														</td>
														
													</tr>
												<?php } ?>

													
												</tbody>
											</table>
											<div class="col-md-12">
												<div class="form-group">
													<button type="submit" name="" class="btn btn-info float-right">Save Changes</button>
													<div class="clearfix"></div>
												</div>
											</div>
										</form>
										
									</div>
                                </div>
								</div>
								<?php } ?>
                            </div>
                        </div>
                        <!-- card new -->
					</div>
                    
					</div>
					</div>
                </div>
            </div>
                <!-- row -->
              
               

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script>

$(document).ready(function(){
  $(".card-header").click(function(){
    $(this).toggleClass("opened");
  });
});
</script>
<?php include "footer.php"; ?> 
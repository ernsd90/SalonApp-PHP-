<?php 
	include "../function.php";
	$movie_id = $_GET['movie_id'];
	
	
?>
<form method="post" id="movie_common_form">
<input type="hidden" name= "method" value="update_movie_images" />

<input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>" />

<input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>" />

<div class="modal-header">
	<h5 class="modal-title" id="exampleModalLabel">Update Home Movie Poster</h5>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
<div class="modal-body">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
			
				<div class="content">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label>Select Poster Image</label>
								<input type="file" class="form-control " name="home_movie_poster">
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	
	<button type="submit" name="update_mov" class="btn btn-primary">Save Changes</button>
</div>  
</form>
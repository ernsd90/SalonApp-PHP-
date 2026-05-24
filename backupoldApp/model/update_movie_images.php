<?php 
	include "../function.php";
	$movie_id = $_GET['movie_id'];
	
	$query = ("SELECT cat_id,movie_id,download_link_720,download_link_480,download_link_360,download_link_mkv,download_sample_link,download_torrent FROM `movie_detail` WHERE movie_id='".$movie_id."' ");
	$movie = select_row($query); 
	extract($movie);
	
?>
<form method="post" id="movie_common_form">
<input type="hidden" name= "method" value="update_movie_images" />

<input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>" />

<input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>" />

<div class="modal-header">
	<h5 class="modal-title" id="exampleModalLabel">Movie Images Update</h5>
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
								<label>Download Link 720</label>
								<input type="text" class="form-control download_key" name="download_link_720" placeholder="Download Link" value="<?php echo $download_link_720; ?>">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Download Link 480</label>
								<input type="text" class="form-control download_key" name="download_link_480" placeholder="Download Link" value="<?php echo $download_link_480; ?>">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Download Link 360</label>
								<input type="text" class="form-control" name="download_link_360" placeholder="Download Link" value="<?php echo $download_link_360; ?>">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Download In MkV HD Format PC Users</label>
								<input type="text" class="form-control" name="download_link_mkv" placeholder="Download Link" value="<?php echo $download_link_mkv; ?>">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Sample link</label>
								<input type="text" class="form-control download_val" placeholder="Screen" name="download_sample_link" value="<?php echo $download_sample_link; ?>">
							</div>
						</div>
						
						<?php 
							$download_torrent = json_decode($download_torrent,true);
						?>
						<div class="col-md-6">
							<div class="form-group">
								<label>Torent Link 1</label>
								<input type="text" class="form-control" name="download_torrent[]" placeholder="Torent Link 1" value="<?php echo $download_torrent[0]; ?>">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Torent Link 2</label>
								<input type="text" class="form-control" name="download_torrent[]" placeholder="Torent Link 2" value="<?php echo $download_torrent[1]; ?>">
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
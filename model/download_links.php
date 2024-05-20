<?php 
	include "../function.php";
	$movie_id = $_GET['movie_id'];
	
	$query = ("SELECT cat_id,movie_id,download_link_720,download_link_480,download_link_360,download_link_mkv,download_sample_link,download_torrent FROM `movie_detail` WHERE movie_id='".$movie_id."' ");
	$movie = select_row($query); 
	extract($movie);
	$query = ("SELECT * FROM `movie_image` where movie_id='".$movie_id."' order by `sort` asc");
	$movie_images = select_array($query); 
?>
<form method="post" id="movie_common_form">
<input type="hidden" name= "method" value="update_movie_download" />

<input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>" />

<input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>" />

<div class="modal-header">
	<h5 class="modal-title" id="exampleModalLabel">Download Movie Link Update</h5>
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
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>Add Images By URL (Torrent)</label>
								<input type="text" name="grabimages_url" class="form-control"   placeholder="Put Link Here" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label>Image 1</label>
								<input type="text" class="form-control" placeholder="Image 1" name="image[]" value= '' />
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Image 2</label>
								<input type="text" class="form-control" placeholder="Image 2" name="image[]" value= '' />
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Image 3</label>
								<input type="text" class="form-control" placeholder="Image 3" name="image[]" value= '' />
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Image 4</label>
								<input type="text" class="form-control" placeholder="Image 4" name="image[]" value= '' />
							</div>
						</div>
					</div>
					
					<div class="row">
						<?php if($movie_images['status'] != '404') {?>
					
							<?php foreach($movie_images as $img) {?>
								<div class="col-md-3">
									<img class=" m_height" src="<?php echo "https://img.nokiahot.com/movie_images/".$img['image_name']; ?>" />
									<a href="javascript(0);" class="btn btn-fill btn-primary movie_imgage_del" data-img="<?php echo $img['id']; ?>" data-movieid="<?php echo $movie_id; ?>">Delete</a>
								</div>
							<?php }?>
						<?php } ?>
							
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
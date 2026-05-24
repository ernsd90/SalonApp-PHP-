<?php 
    include "../function.php";

    $user_action = "create";
	$sql = "SELECT * FROM `domain_group` ";
    $domain_groups = select_array($sql);
    if(isset($_REQUEST['domain_id']) && is_numeric($_REQUEST['domain_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `movie_domain` WHERE `domain_id`='".$_REQUEST['domain_id']."'";
        $movie_domain = select_row($sql);
        extract($movie_domain);

        $sql = "SELECT movie_name FROM `movie_detail` WHERE `movie_id`='".$movie_id."'";
        $movie_detail = select_row($sql);
        extract($movie_detail);
		
        
        
    }

        $movie_format = array("mkv","720","480","360");
    ?>

        <form class="form-horizontal" id="movie_common_form" method="post">
            <?php 
                if($user_action == "create"){

                    echo '<input name="method" type="hidden" value="create_movie_domain">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_movie_domain">';
                    echo '<input name="domain_id" type="hidden" value="'.$domain_id.'">';
                    $required = "";
                }
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Movie Domain</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                            <div class=" row">

                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="movie_id_search" class=" text-right control-label col-form-label">Movie Name</label>
                                        <div class="">
                                            <select name="movie_id" id="movie_id_search" class="form-control custom-select" style="width: 100%; height:36px;">
                                                <option value="<?php echo $movie_id; ?>"><?php echo unserialize($movie_name); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="domain_format" class=" text-right control-label col-form-label">Movie Format</label>
                                        <div class="">
                                            <select name="domain_format" id="domain_format" class="select2 form-control custom-select" style="width: 100%; height:36px;">
                                                <?php foreach($movie_format as $format) { ?>
                                                    <option <?php echo ($format == $domain_format ? "selected":"") ?> value="<?php echo $format; ?>"><?php echo $format; ?></option>
                                                <?php } ?>
                                            </select>
                                            
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="link_expire" required class=" text-right control-label col-form-label">Link Expire</label>
                                    <div class="">
                                        <input type="text" name="link_expire" class="form-control" id="link_expire" value="<?php echo date("Y-m-d",strtotime("+30 DAYS")) ?>" />
                                    </div>
                                </div>
                                </div>
								<?php if($user_action == 'create'){?>
                                <div class="col-md-6">
								<?php }else{?>
                                <div class="col-md-12">
								<?php } ?>
                                    <div class="form-group ">
                                        <label for="Domain" class="text-right control-label col-form-label">Enter a Domain</label>
                                        <div class="">
                                            <input  name="domain[]" type="text" class="form-control" id="Domain" placeholder="Domain" value="<?php echo $domain_name; ?>">
                                        </div>
                                    </div>
                                </div>
								<?php if($user_action == 'create'){?>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="group_name" class="text-right control-label col-form-label">Enter a Domain Group</label>
                                        <div class="">
                                            <select name="group_id" id="group_name" class="select2 form-control custom-select" style="width: 100%; height:36px;">
											<option selected="selected">Select Domain Group</option>
                                                <?php foreach($domain_groups as $domain_group) { 
													extract($domain_group);
												?>
                                                    <option value="<?php echo $id; ?>"><?php echo $group_name; ?></option>
													
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
								<?php } ?>
                             
                            </div>
                        </div>
                    </div>
                </div>
              <!-- row -->
              </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="update_domain btn btn-primary">Save changes</button>
            </div>    
        </form>
        <script>
            $(".select2").select2();

            $("#movie_id_search").select2({
                minimumInputLength: 2,
                ajax: {
                    url: "ajax/movie_ajax.php",
                    dataType: 'json',
                    type: "POST",
                    data: function (term, page) {
                        return {
                            q: term, // search term
                            col: 'movie_name',
                            method: "search_movie_name"
                        };
                    },
                    cache: true,
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.movie_name,
                                    id: item.movie_id
                                }
                            })
                        };
                    }
                }
            });
        </script>
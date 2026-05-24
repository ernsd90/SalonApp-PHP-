<?php 
    include "../function.php";

    $user_action = "create";
    if(isset($_REQUEST['domain_id']) && is_numeric($_REQUEST['domain_id'])){
        $user_action = "edit";

        $sql = "SELECT * FROM `movie_domain` WHERE `domain_id`='".$_REQUEST['domain_id']."'";
        $movie_domain = select_row($sql);
        extract($movie_domain);

        $sql = "SELECT movie_name FROM `movie_detail` WHERE `movie_id`='".$movie_id."'";
        $movie_detail = select_row($sql);
        extract($movie_detail);
    }

    $category = select_array('SELECT cat_id,cat_name FROM `category` where cat_type="movie" ORDER BY `cat_sort` ASC');

        $movie_format = array("all","mkv","720","480","360");
    ?>

        <form class="form-horizontal" id="movie_common_form" method="post">
            <?php 
                if($user_action == "create"){

                    echo '<input name="method" type="hidden" value="create_category_domain">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_category_domain">';
                    echo '<input name="domain_id" type="hidden" value="'.$domain_id.'">';
                    $required = "";
                }
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Download Domain By Category</h5>
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
                                        <label for="movie_id_search" class=" text-right control-label col-form-label">Category Name</label>
                                        <div class="">
                                            <select name="cat_id" id="movie_id_search" class="form-control custom-select" style="width: 100%; height:36px;">
                                                <?php foreach($category as $cat){ ?>
                                                <option value="<?php echo $cat['cat_id']; ?>"><?php echo $cat['cat_name']; ?></option>
                                                <?php } ?>
                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="form-group ">
                                        <label for="Domain" class="text-right control-label col-form-label">Enter a Domain</label>
                                        <div class="">
                                            <input required name="domain[]" type="text" class="form-control" id="Domain" placeholder="Domain" value="<?php echo $domain_name; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group ">
                                        <label for="latest_domain" class="text-right control-label col-form-label">Latest Domain</label>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" value="1" name="latest_domain" class="custom-control-input" id="latest_domain">
                                            <label class="custom-control-label" for="latest_domain"></label>
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

                                <div class="col-md-6 link_expire" style="display:none">
                                    <div class="form-group ">
                                        <label for="link_expire" required class=" text-right control-label col-form-label">Movie Expire In Days</label>
                                        <div class="">

                                            <select name="link_expire" class="form-control" style="width: 100%; height:36px;">
                                                <?php for($i=0;$i<=200;$i=$i+3){ ?>
                                                <option <?php echo $i=='60' ? "selected":""; ?> value="<?php echo $i; ?>"><?php echo $i; ?> Days</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                             
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

            $("#movie_id_search").select2();

            $(document).ready(function(){

                $('input[type="checkbox"]').click(function(){
                    if($(this).prop("checked") == true){
                        $(".link_expire").show();
                    }
                    else if($(this).prop("checked") == false){
                        $(".link_expire").hide();
                    }
                });

            });
        </script>
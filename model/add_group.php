<?php 
    include "../function.php";

    $user_action = "create";
    if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
        $user_action = "edit";
        $sql = "SELECT * FROM `domain_group` WHERE `id`='".$_REQUEST['id']."'";
        $new_domain = select_row($sql);
        extract($new_domain);
    }

        $movie_format = array("mkv","720","480","360");
    ?>

        <form class="form-horizontal" id="movie_common_form" method="post">
            <?php 
                if($user_action == "create"){

                    echo '<input name="method" type="hidden" value="create_new_domain_group">';
                    $required = "required";
                }else{
                    echo '<input name="method" type="hidden" value="update_new_domain_group">';
                    echo '<input name="id" type="hidden" value="'.$id.'">';
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
                                        <label for="group_name" class="text-right control-label col-form-label">Group Name</label>
                                        <div class="">
                                            <input required name="group_name" type="text" class="form-control" id="group_name" placeholder="Group Name" value="<?php echo $group_name; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="Domain" class="text-right control-label col-form-label">Domain Name</label>
                                        <div class="">
                                            <input required name="domain_name" type="text" class="form-control" id="domain_name" placeholder="Domain Name" value="<?php echo $domain_name; ?>">
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
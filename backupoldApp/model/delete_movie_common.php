<?php 
    include "function.php";

   
    if(isset($_REQUEST['movie_id']) && is_numeric($_REQUEST['movie_id'])){
        $id = $_REQUEST['movie_id'];
        $method = "delete_movie";
    }elseif(isset($_REQUEST['movie_domain_id']) && is_numeric($_REQUEST['movie_domain_id'])){
        $id = $_REQUEST['movie_domain_id'];
        $method = "delete_movie_domain";
    }
    elseif(isset($_REQUEST['category_domian_id']) && is_numeric($_REQUEST['category_domian_id'])){
        $id = $_REQUEST['category_domian_id'];
        $method = "delete_movie_category_domain";
    }
    elseif(isset($_REQUEST['domain_group_id']) && is_numeric($_REQUEST['domain_group_id'])){
        $id = $_REQUEST['domain_group_id'];
        $method = "domain_group_delete";
    }
	


    



    ?>

        <form class="form-horizontal" id="movie_common_form" method="post">
            <?php 
                    echo '<input name="method" type="hidden" value="'.$method .'">';
                    echo '<input name="id" type="hidden" value="'.$id.'">';
                    $required = "";
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Delete User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                            <div class=" row">
                                <div class="col-md-12">
                                    <div class="card">
                                         Are You Sure to Delete Record? 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              <!-- row -->
              </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="delete_user btn btn-danger">Delete changes</button>
            </div>    
        </form>
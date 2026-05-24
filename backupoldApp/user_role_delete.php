<?php 
    include "function.php";

   
    if(isset($_REQUEST['role_id']) && is_numeric($_REQUEST['role_id'])){
        $user_action = "edit";
        $sql = "SELECT * FROM `site_user_role` WHERE `role_id`='".$_REQUEST['role_id']."'";
        $user = select_row($sql);
        extract($user);
    }else{

    }

    ?>

        <form class="form-horizontal" id="user_form" method="post">
            <?php 
                    echo '<input name="method" type="hidden" value="delete_role">';
                    echo '<input name="role_id" type="hidden" value="'.$role_id.'">';
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
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td>Role</td>
                                                    <td><?php echo $role_name ?></td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
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
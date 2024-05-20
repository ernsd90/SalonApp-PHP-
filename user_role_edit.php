

        <form class="form-horizontal" id="user_form" method="post">
			
            <input name="method" type="hidden" value="create_role">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add New User Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class=" card">
                            <div class=" card-body">
                            <div class=" row">
                                <div class="col-md-12">
                                <div class="form-group ">
                                    <label for="role_name" class=" text-right control-label col-form-label">Role Name</label>
                                    <div class="">
                                        <input  <?php echo  $required; ?> name="role_name" type="text" class="form-control" id="role_name" placeholder="Role Name">
                                    </div>
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
                <button type="submit" class="update_user btn btn-primary">Save changes</button>
            </div>    
        </form>
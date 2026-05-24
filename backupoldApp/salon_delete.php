<?php 
    include "function.php";

    if(isset($_REQUEST['salon_id']) && is_numeric($_REQUEST['salon_id'])){
        $sql = "SELECT * FROM `hr_salon` WHERE `salon_id`='".$_REQUEST['salon_id']."'";
        $salon = select_row($sql);
        if($salon){
            extract($salon);
        }
    }
?>

<form class="form-horizontal" id="salon_form" method="post">
    <?php 
        echo '<input name="method" type="hidden" value="delete_salon">';
        echo '<input name="id" type="hidden" value="'.(isset($salon_id) ? $salon_id : '').'">';
    ?>
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Delete Salon</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <p>Are you sure you want to delete the outlet <strong><?php echo isset($salon_name) ? $salon_name : ''; ?></strong>?</p>
                        <p class="text-danger">Warning: This action cannot be undone, however data associated with this salon ID will remain intact in other tables but invisible without a salon.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Delete</button>
    </div>    
</form>

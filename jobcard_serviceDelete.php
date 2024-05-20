<?php 
    include "function.php";

    $form_page = "deleteForm";


    if(isset($_REQUEST['jobcard_service_id']) && is_numeric($_REQUEST['jobcard_service_id'])){
        $method = "jobcard_service_delete";
        $jobcard_id = $_REQUEST['jobcard_id'];
        $jobcard_service_id = $_REQUEST['jobcard_service_id'];
        $method = "jobcard_service_delete";
    }

    ?>

        <form class="form-horizontal" action="http://localhost/salonapp/ajax/salon_ajax.php" id="<?php echo $form_page;  ?>" method="post">
            <?php 
                    echo '<input name="method" type="hidden" value="'.$method.'">';
                    echo '<input name="jobcard_id" type="hidden" value="'.$jobcard_id.'">';
                    echo '<input name="jobcard_service_id" type="hidden" value="'.$jobcard_service_id.'">';
            
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Delete </h5>
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
                                                    <td>Are You Sure To Delete !!!!</td>
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
                <button type="button" id="cancel_btn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" id="delete_btn" class="delete_user btn btn-danger">Delete changes</button>
            </div>    
        </form>

        <script>
            $(document).ready(function() {
                $('#delete_btn').on('click', function(e) {
                    e.preventDefault();

                    // Assuming you're using a form with an ID 'deleteForm' to handle the delete request
                    var form = $('#deleteForm');
                    var url = form.attr('action'); // The URL where the form will be submitted
                    var data = form.serialize(); // Serialize the form data

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: data,
                        success: function(response) {
                            // Handle success response
                            // Refresh the page after successful deletion
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            // Handle error response
                            alert('An error occurred while deleting the item');
                        }
                    });
                });
            });

        </script>
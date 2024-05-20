<?php include 'header.php'; ?>

<?php 
include_once "function.php";
$salon_id = get_session_data('salon_id');
?>

<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card ">
                    <div class="card-body">
                        <div class="row clearfix">
                            <div class="col-sm-12  p-0">
                                <div class="row clearfix mb-3">
                                    <div class="col-sm-6  ">
                                        <h4 class="card-title"> Job Cards</h4>
                                    </div>
                                    <div class="col-sm-6  ">
                                        <div class="clearfix">
                                            <a style="float:right" class="btn-sm  btn btn-success modalButtonCommon" href="<?php DOMAIN_SOFTWARE ?>job_card.php"><i class="fa fa-plus-circle"></i> Create Job Card</a>
                                        </div>
                                    </div>
                                </div>

                                <table id="get_job_cards" class="table table-striped table-bordered">
                                    
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    
    <?php include 'footer.php'; ?>
    
    <!-- Modal -->
    <div class="modal fade " id="modalButtonCommon" tabindex="-1" role="dialog" aria-labelledby="usermodel" aria-hidden="true">
        <div class="modal-dialog movie_edit_model" role="document">
            <div class="modal-content">
                
            </div>
        </div>
    </div>
    <!-- Modal -->

</div>

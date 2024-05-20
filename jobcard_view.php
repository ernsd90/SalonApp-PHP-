<?php include 'header.php'; ?>

<?php 
include_once "function.php";
$salon_id = get_session_data('salon_id');

// Fetch all open job cards
$open_job_cards = select_array("
    SELECT jc.job_card_id, jc.created_at, c.cust_name, c.cust_mobile, s.salon_name, u.username as created_by
    FROM hr_jobcard jc
    JOIN hr_customer c ON jc.cust_id = c.cust_id
    JOIN hr_salon s ON jc.salon_id = s.salon_id
    JOIN hr_user u ON jc.created_by = u.user_id
    WHERE jc.salon_id = '".$salon_id."' 
    ORDER BY jc.created_at DESC
");
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
                                        <h4 class="card-title">Open Job Cards</h4>
                                    </div>
                                    <div class="col-sm-6  ">
                                        <div class="clearfix">
                                            <a style="float:right" class="btn-sm  btn btn-success modalButtonCommon" href="<?php DOMAIN_SOFTWARE ?>job_card.php"><i class="fa fa-plus-circle"></i> Add Job Card</a>
                                        </div>
                                    </div>
                                </div>

                                <table id="get_job_cards" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Job Card ID</th>
                                            <th>Created At</th>
                                            <th>Customer Name</th>
                                            <th>Customer Mobile</th>
                                            <th>Salon Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($open_job_cards)) { ?>
                                            <?php foreach ($open_job_cards as $job_card) { ?>
                                                <tr>
                                                    <td><?php echo $job_card['job_card_id']; ?></td>
                                                    <td><?php echo date("j F Y, g:i a", strtotime($job_card['created_at'])); ?></td>
                                                    <td><?php echo $job_card['cust_name']; ?></td>
                                                    <td><?php echo $job_card['cust_mobile']; ?></td>
                                                    <td><?php echo $job_card['salon_name']; ?></td>
                                                    <td>
                                                        <a href="<?php echo DOMAIN_SOFTWARE; ?>job_card.php?job_card_id=<?php echo $job_card['job_card_id']; ?>">Edit</a>
                                                        <a href="<?php echo DOMAIN_SOFTWARE; ?>billing_service.php?job_card_id=<?php echo $job_card['job_card_id']; ?>">Make Bill</a>
                                                    
                                                        <a href="<?php echo DOMAIN_SOFTWARE; ?>print_jobcard.php?job_card_id=<?php echo $job_card['job_card_id']; ?>" target="_blank">Print</a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="6">No open job cards found.</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
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

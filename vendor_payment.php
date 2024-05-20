<?php include 'header.php';?>

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
                                        <h4 class="card-title">Vendor Payment</h4>
                                    </div>
                                    <div class="col-sm-6  ">
                                        <div class="clearfix">

                                            <button type="button" class="btn-sm  btn btn-success modalButtonCommon" data-toggle="modal" data-href="model/vendor_payment.php"><i class="fa fa-plus-circle"></i> Vendor Payment</button>
                                        </div>
                                    </div>
                                </div>

                                <table id="get_vendor_credit_debit" class="table table-striped table-bordered CommonModelClick">
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- content-wrapper ends -->

    <?php include 'footer.php';?>
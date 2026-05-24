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
                                        <h4 class="card-title">Inventory</h4>
                                    </div>
                                    <div class="col-sm-6  ">
                                        <div class="clearfix">
                                            <a class="btn-sm  btn btn-success" href="inventory_add.php"><i class="fa fa-plus-circle"></i> Add New Bill</a>

                                        </div>
                                    </div>
                                </div>

                                <table id="get_inventory_bill" class="table table-striped table-bordered CommonModelClick">
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
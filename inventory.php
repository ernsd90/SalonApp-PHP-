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
                                            <a class="btn-sm  btn btn-success" href="inventory_print.php"><i class="fa fa-plus-circle"></i> Print</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 stretch-card grid-margin p-1">
                                        <div class="card bg-gradient-success card-img-holder text-white">
                                            <div class="card-body p-4">
                                                <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                                                <h4 class="font-weight-normal mb-3">Total Count
                                                </h4>
                                                <p><i class="mdi mdi-account-multiple-plus  mdi-24px "></i></p>
                                                <h2 class="mb-5 product_number"></h2>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 stretch-card grid-margin p-1">
                                        <div class="card bg-gradient-danger card-img-holder text-white">
                                            <div class="card-body p-4">
                                                <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                                                <h4 class="font-weight-normal mb-3">Total Product Bill </h4>
                                                <p><i class="mdi mdi-bell-ring  mdi-24px "></i></p>
                                                <h2 class="mb-5 product_total"></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row clearfix mb-3">
                                    <div class="col-sm-6">
                                        <p id="date_filter">
                                            <span id="date-label-from" class="date-label">From: </span>
                                            <input class="date_range_filter date" type="text" id="inven_fromdate"  name="inven_fromdate" />
                                            <span id="date-label-to" class="date-label">  To:</span>
                                            <input class="date_range_filter date" type="text" id="inven_todate"  name="inven_todate" />
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p id="product_filter">

                                            <?php
                                            $sql2 = "SELECT * FROM `hr_bill_product` where salon_id='".$salon_id." GROUP by product_name' ";
                                            $expenses_catNames = select_array($sql2);
                                            ?>

                                            <select  name="reportproduct_id" id="reportproduct_id" class=" required search_select reportproduct_id form-control select2-selection--single">
                                                <option value="">Select Product</option>
                                                <?php foreach($expenses_catNames as $name){
                                                    ?>
                                                    <option value="<?php echo $name['product_name']; ?>"><?php echo $name['product_name'] ;?></option>
                                                <?php } ?>
                                            </select>
                                        </p>
                                    </div>
                                </div>

                                <table id="get_inventory" class="table table-striped table-bordered CommonModelClick">
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
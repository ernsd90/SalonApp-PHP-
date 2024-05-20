<?php include 'header.php'; ?>

<?php $model_custom_class = " print_model ";



?>
<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">

        <div class="row" id="proBanner">
            <div class="col-12">
                <span>
                  <i style="display:none" class="mdi mdi-close" id="bannerClose"></i>
                </span>
            </div>
        </div>

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white mr-2">
                  <i class="mdi mdi-home"></i>
                </span>Today's Sale Record </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <span></span>Appointments <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                    </li>
                </ul>
            </nav>
        </div>


        <?php if(check_user_permission("report","edit",$user_id)){ ?>
        <div class="row">

            <!--div class="col-md-2 stretch-card grid-margin p-1">
                <div class="card bg-gradient-danger card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Services Sale
                        </h4>
                        <p><i class="mdi mdi-access-point-network mdi-24px "></i></p>
                        <h2 class="mb-5"><?php echo $total_service_sale; ?></h2>

                    </div>
                </div>
            </div-->

            <div class="col-md-2 stretch-card grid-margin p-1">
                <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-2">Customer</h4>

                        <p><h4>Cash: <span class="mb-2 total_customer_cash"></span></h4></p>
                        <p><h4>Package: <span class="mb-2 total_customer_pkg"></span></h4></p>
                        <p><h3>Total: <span class="mb-2 total_customer"></span></h3></p>


                        <!--h6 class="card-text">Increased by 60%</h6-->
                    </div>
                </div>
            </div>



            <div class="col-md-3 stretch-card grid-margin p-1">
                <div class="card bg-gradient-danger card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Service Sale </h4>

                        <p><h4>Cash: <span class="mb-2 total_cash"></span></h4></p>
                        <p><h4>Card: <span class="mb-2 total_cc"></span></h4></p>
                        <p><h3>Total: <span class="mb-2 service_total"></span></h3></p>
                    </div>
                </div>
            </div>

            <div class="col-md-2 stretch-card grid-margin p-1">
                <div class="card bg-gradient-info card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Product Sale</h4>

                        <p><h4>Cash: <span class="mb-2 product_total_cash"></span></h4></p>
                        <p><h4>Card: <span class="mb-2 product_total_cc"></span></h4></p>
                        <p><h4>Total: <span class="mb-2 product_total"></span></h4></p>

                    </div>
                </div>
            </div>

            <div class="col-md-2 stretch-card grid-margin p-1">
                <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Expence
                        </h4>
                        <p><h4>Cash: <span class="mb-2 exp_total_cash"></span></h4></p>
                        <p><h4>Card: <span class="mb-2 exp_total_cc"></span></h4></p>
                        <p><h4>Total: <span class="mb-2 exp_total"></span></h4></p>
                    </div>
                </div>
            </div>

            <!--
            <div class="col-md-2 stretch-card  grid-margin p-1">
                <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Total
                        </h4>
                        <p><i class="mdi mdi-account-multiple-plus  mdi-24px "></i></p>
                        <h2 class="mb-5 exp_total"></h2>
                    </div>
                </div>
            </div>
            -->
            <div class="col-md-3 stretch-card grid-margin p-1">
                <div class="card bg-gradient-danger card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Grand Total</h4>

                        <p><h4>Cash: <span class="mb-2 grand_cash"></span></h4></p>
                        <p><h4>Card: <span class="mb-2 grand_cc"></span></h4></p>
                        <p><h3>Total: <span class="mb-2 grand_total"></span></h3></p>


                    </div>
                </div>
            </div>
        </div>

        <?php } ?>

        <div class="row">


             <div class="col-sm-12">
                <div class="card ">
                    <div class="card-body">
                        <div class="row clearfix">
                            <div class="col-sm-12  p-0">

                                <form method="post" action="report_print.php" target="_blank" autocomplete="off">
                                    <div class="row clearfix mb-12">
                                    <?php if(check_user_permission("report","view",$user_id)) { ?>

                                    <div class="col-sm-6">
                                        <p id="date_filter">
                                            
                                                <input placeholder="From:" required class="date_range_filter date" type="text" id="search_fromdate"  name="search_fromdate" />
                                          
                                                <input  placeholder="To:" required class="date_range_filter date" type="text" id="search_todate"  name="search_todate" />
                                        </p>
                                    </div>
                                    <?php } ?>
                                    <?php if(check_user_permission("report","edit",$user_id)) { ?>
                                    <div class="col-sm-2">
                                        <p id="ref_filter">
                                            <select name="refrence_by" id="refrence_by" class=" refrence_by form-control select2-selection--single">
                                                <option value="0">Select Ref</option>
                                                <option value="walkin">WalkIn</option>
                                                <option value="insta">Instagram</option>
                                                <option value="fb">Facebook</option>
                                                <option value="google">Google</option>
                                                <?php
                                                $ref = select_array("SELECT * FROM `hr_user_owner` where salon_id=".$salon_id." and ref_enable=1");
                                                foreach ($ref as $ref_name){
                                                ?>
                                                <option value="<?php echo $ref_name['user_name']; ?>"><?php echo $ref_name['user_name']; ?></option>
                                                <?php } ?>

                                                <option value="Bajaj">Mr Bajaj</option>
                                                <option value="staff">Staff Ref</option>
                                            </select>
                                        </p>
                                    </div>

                                    <div class="col-sm-2">
                                        <p id="staff_filter">

                                            <?php
                                            $query = "SELECT * FROM `hr_staff` where salon_id='".$salon_id."' and staff_status=1";
                                            $staff = select_array($query);
                                            ?>

                                            <select  name="reportstaff_id" id="reportstaff_id" class=" required search_select reportstaff_id form-control select2-selection--single">
                                                <option value="">Select Staff</option>
                                                <?php foreach($staff as $name){
                                                    ?>
                                                    <option value="<?php echo $name['staff_id']; ?>"><?php echo $name['staff_name'] ;?></option>
                                                <?php } ?>
                                            </select>
                                        </p>
                                    </div>



                                    <div class="col-sm-2">
                                        <p id="report_monthly">
                                            <button type="submit" class="btn btn-info">Daily Report</button>
                                        </p>
                                    </div>

                                <?php } ?>

                                    </div>
                                </form>


                                <table id="get_salerecord" class="table table-striped table-bordered CommonModelClick">

                                </table>


                                <?php if(check_user_permission("report","edit",$user_id)){ ?>
                                <form method="post" action="monthly_report.php" target="_blank" autocomplete="off">
                                <div class="row clearfix mb-12">
                                    
                                    <div class="col-sm-6">
                                        <p id="date_filter">
                                                <input placeholder="From:" required class="date_range_filter date" type="text" id="search_fromdate"  name="search_fromdate" />
                                                <input  placeholder="To:" required class="date_range_filter date" type="text" id="search_todate"  name="search_todate" />
                                        </p>
                                    </div>

                                    <div class="col-sm-2">
                                        <p id="report_monthly">
                                            <button type="submit" class="btn btn-info">Monthly  Report</button>
                                        </p>
                                    </div>
                                </div>

                                </form>
                                <?php } ?>

                            </div>
                        </div>
                    </div>
                </div>

            </div>



        </div>


    </div>
    <!-- content-wrapper ends -->

    <?php include 'footer.php';?>

    <script>
    $(document).ready(function () {
       /* document.getElementById("btnPrint").onclick = function () {
            printElement(document.getElementById("printThis"));
        }*/
    });



    function printElement(elem) {
        var domClone = elem.cloneNode(true);
        
        var $printSection = document.getElementById("printSection");
        
        if (!$printSection) {
            var $printSection = document.createElement("div");
            $printSection.id = "printSection";
            document.body.appendChild($printSection);
        }
        
        $printSection.innerHTML = "";
        $printSection.appendChild(domClone);
        window.print();
    }

</script>
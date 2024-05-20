<?php include 'header.php'; ?>
<?php $model_custom_class = " print_model " ?>
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
                </span>Attandence Record </h3>

        </div>


        <?php if(check_user_permission("report","report_create",$user_id)){ ?>
        <div class="row">

            <div class="col-md-2 stretch-card grid-margin p-1">
                <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Total Days
                        </h4>
                        <p><i class="mdi mdi-account-multiple-plus  mdi-24px "></i></p>
                        <h2 class="mb-5 total_working"></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 stretch-card grid-margin p-1">
                <div class="card bg-gradient-danger card-img-holder text-white">
                    <div class="card-body p-4">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Grand Hour
                        </h4>
                        <p><i class="mdi mdi-bell-ring  mdi-24px "></i></p>
                        <h2 class="mb-5 total_hr"></h2>
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
                                <div class="row clearfix mb-3">
                                    <div class="col-sm-6">
                                        <p id="date_filter">
                                            <span id="date-label-from" class="date-label">From: </span>
                                                <input class="date_range_filter date" type="text" id="att_fromdate"  name="att_fromdate" />
                                            <span id="date-label-to" class="date-label">  To:</span>
                                                <input class="date_range_filter date" type="text" id="att_todate"  name="att_todate" />
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p id="staff_filter">

                                            <?php
                                            $query = "SELECT name FROM `hr_attendance` group BY name order by name asc ";
                                            $staff = select_array($query);
                                            ?>

                                            <select  name="reportstaff_name" id="reportstaff_name" class=" required search_select reportstaff_name form-control select2-selection--single">
                                                <option value="">Select Staff</option>
                                                <?php foreach($staff as $name){
                                                    ?>
                                                    <option value="<?php echo $name['name']; ?>"><?php echo $name['name'] ;?></option>
                                                <?php } ?>
                                            </select>
                                        </p>
                                    </div>
                                </div>
                                <table id="get_attendencerecord" class="table table-striped table-bordered CommonModelClick">
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

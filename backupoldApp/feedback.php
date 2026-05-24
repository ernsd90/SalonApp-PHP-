<?php include 'header.php'; ?>

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
                </span>Feedback</h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <span></span>Appointments <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                    </li>
                </ul>
            </nav>
        </div>
        
        <div class="row">

             <div class="col-sm-12">
                <div class="card ">
                    <div class="card-body">
                        <div class="row clearfix">
                            <div class="col-sm-12  p-0">
                                <div class="row clearfix mb-3">
                                    <div class="col-sm-12  ">

                                    </div>
                                </div>
                                <table id="get_feedback" style="width:100%" class="  table-striped table-bordered CommonModelClick">
                                    <thead>
                                        <tr>
                                            <th>Invoice Id</th>
                                            <th>Customer Name</th>
                                            <th>Mobile No</th>
                                            <th>Experince</th>
                                            <th width="20" style="width: 100px;">Message</th>
                                            <th>Time</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
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
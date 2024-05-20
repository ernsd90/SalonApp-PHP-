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
                </span>Old Sale Record </h3>
        
        </div>
      
        <div class="row">

             <div class="col-sm-12">
                <div class="card ">
                    <div class="card-body">
                        <div class="row clearfix">
                            <div class="col-sm-12  p-0">
                                <div class="row clearfix mb-3">
                                  
                                </div>
                                <table id="get_salerecord_old" class="table table-striped table-bordered CommonModelClick">
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

    <script>
    $(document).ready(function () {
        

       
    });

</script>
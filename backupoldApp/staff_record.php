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
                </span>Staff's Sale Record </h3>
        </div>

        <div class="row">
             <div class="col-sm-12">
                <div class="card ">
                    <div class="card-body">
                        <div class="row clearfix">
                            <div class="col-sm-12  p-0">
                                <?php //if(check_user_permission("report","report_create",$user_id))
                                { ?>
                                <form method="post" action="report_print.php" target="_blank" autocomplete="off">
                                <div class="row clearfix mb-3">
                                    <div class="col-sm-6">
                                        <p id="date_filter">
                                            <span id="date-label-from" class="date-label">From: </span>
                                                <input required class="date_range_filter date" type="text" id="search_fromdate"  name="search_fromdate"  value="<?php echo date("01-m-Y"); ?>"/>
                                            <span id="date-label-to" class="date-label">  To:</span>
                                                <input required class="date_range_filter date" type="text" id="search_todate"  name="search_todate" value="<?php echo date("t-m-Y"); ?>" />
                                        </p>
                                    </div>
                                </div>

                                </form>
                                <?php } ?>
                                <table id="get_staffrecord" class="table table-striped table-bordered CommonModelClick">
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


    document.getElementById("btnPrint").onclick = function () {
        printElement(document.getElementById("printThis"));
    }

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
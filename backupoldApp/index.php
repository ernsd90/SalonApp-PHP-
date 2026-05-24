<?php include 'header.php';?>

        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
		  
			<div class="row" id="proBanner">
              <div class="col-12">
                <span >
                  <i style="display:none" class="mdi mdi-close" id="bannerClose"></i>
                </span>
              </div>
            </div>
			
            <div class="page-header">
              <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white mr-2">
                  <i class="mdi mdi-home"></i>
                </span> Dashboard </h3>
              <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                  <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Appointments <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                  </li>
                </ul>
              </nav>
            </div>
        
            <div class="row">

           <?php if(check_user_permission("report","create",$user_id)){ ?>


            <div class="col-lg-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">
                      Top Categories
                      <select name="month" id="monthSelect">
                        <?php
                        for ($i = 0; $i <= 11; $i++) {
                            $month = date('F Y', strtotime("-$i months"));
                            $months = date('Y-m', strtotime("-$i months"));
                            echo "<option value='$months'>$month</option>";
                        }
                        ?>
                    </select>
                    </h4>

                      <div id="top_categories"></div>
                  </div>
                </div>
            </div>

            <div class="col-lg-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">
                      Top Service
                      <select name="month" id="monthSelect_service">
                        <?php
                        for ($i = 0; $i <= 11; $i++) {
                            $month = date('F Y', strtotime("-$i months"));
                            $months = date('Y-m', strtotime("-$i months"));
                            echo "<option value='$months'>$month</option>";
                        }
                        ?>
                    </select>
                    </h4>

                      <div id="top_services"></div>
                  </div>
                </div>
            </div>



           <div class="col-md-12 grid-margin stretch-card">
               <div class="card">
                   <div class="card-body">
                       <h2 class="card-title">Product Billing Record</h2>

                       <?php
                       $all_vendor = select_array("SELECT id as vendor_id,vendor_name FROM `hr_vendor`");
                       foreach($all_vendor as $vendor){
                           extract($vendor);

                           extract(select_row("SELECT SUM(amt_in) as amt_in,sum(amt_out) as amt_out FROM `hr_vendor_payment` where salon_id='".$salon_id."' and  bill_deleted!=1 and  vendor_id='".$vendor_id."'"));
                           if($amt_in < 1 ) continue;

                           $total_percent = ($amt_out*100)/$amt_in;

                           $total_bills += $amt_in;
                           $total_pending += $amt_out;

                           if($total_percent < 35){
                               $bar_class = "danger";
                           }elseif($total_percent < 60){
                               $bar_class = "warning";
                           }else{
                               $bar_class = "success";
                           }

                           $pending_amt = number_format($amt_out-$amt_in);
                           if($pending_amt != 0){
                           ?>
                           <div class="">
                               <div class="row">
                                   <p class="col-sm-8 mb-1 "><?php echo $vendor_name; ?></p> <p class="col-sm-4 pull-right"><?php echo number_format($amt_out) ."/".number_format($amt_in); ?> (pending: <?php echo $pending_amt; ?>)</p>
                               </div>
                               <div class="progress">
                                   <div class="progress-bar bg-gradient-<?php echo $bar_class; ?>" role="progressbar" style="width: <?php echo $total_percent; ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                               </div>
                           </div><br>
                       <?php } } ?>
                        <h2>Pending Payment: <?php echo number_format($total_bills-$total_pending); ?></h2>
                        <h2>Total Bill: <?php echo number_format($total_bills); ?></h2>

                   </div>
               </div>
           </div>

            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Monthly Recap Report</h4>
                      <div id="monthly_recap" style="height: 500px;"></div>
                  </div>
                </div>
            </div>




                <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Staff Product Sale Record</h4>

                    <?php 
                    $all_staff = select_array("SELECT * FROM `hr_staff` where salon_id='".$salon_id."' and staff_status=1");
                    foreach($all_staff as $staff){
                      foreach($staff as $var => $value){
                        $$var = $value;
                      }
                      
                      $staff_salary = ($staff_salary == 0 ? "10000":$staff_salary);

                      $a = (select_row("SELECT sum(s.totol_amt) as staff_total FROM `hr_invoice_service` as i join hr_invoice_staff as s on s.invoice_service=i.id join hr_invoice as n on n.invoice_id=s.invoice_id where s.staff_id='".$staff_id."' and MONTH(n.invoice_date) = MONTH(CURRENT_DATE()) and n.delete_bill!=1  and invoice_type='2'"));
                      
                      $staff_total = $a['staff_total'];
                      $staff_target = ($staff_salary*2);
                      
                      $total_percent = ($staff_total*100)/$staff_target;
                      
                      if($total_percent < 35){
                        $bar_class = "danger";
                      }elseif($total_percent < 60){
                        $bar_class = "warning";
                      }else{
                        $bar_class = "success";
                      }
                      if($staff_total > 0){
                    ?>
                    <div class="">
                        <div class="row">
                        <p class="col-sm-8 mb-1 "><?php echo $staff_name; ?></p> <p class="col-sm-4 pull-right"><?php echo number_format($staff_total); ?></p>
                        </div>
                        <div class="progress">
                          <div class="progress-bar bg-gradient-<?php echo $bar_class; ?>" role="progressbar" style="width: <?php echo $total_percent; ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      </div><br>
                    <?php } } ?>

                    
                    </div>
                  </div>
                </div>
           <?php } ?>
            </div>
			
		</div>
          <!-- content-wrapper ends -->
		  
<?php include 'footer.php';?>


<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

      google.charts.load('current', {packages: ['corechart', 'line','bar']});
      

      google.charts.setOnLoadCallback(monthly_recap);

      google.charts.setOnLoadCallback(top_categories);


      google.charts.setOnLoadCallback(top_services);

      
      var selectedMonth;
      $(document).ready(function() {
        $('#monthSelect').on('change', function() {
            selectedMonth = $(this).val();
            top_categories();
        });

        $('#monthSelect_service').on('change', function() {
            selectedMonth = $(this).val();
            top_services();
        });

      });


      function top_services() {

          var data = new google.visualization.DataTable();
          data.addColumn('string', 'Category');
          data.addColumn('number', 'Revenue');

          var jsonData = $.ajax({

            url: "ajax/chart_data.php",
            dataType: "json",
            async: false,
            "type": "POST",
            "data": {
              "selectedMonth": selectedMonth,
              "method": "top_services",
            },
          }).responseText;

          var array_data = (JSON.parse(jsonData));
          var data = google.visualization.arrayToDataTable(
            array_data
          );

          // Set chart options
          var options = {'title':'Top 10 Service',
                        'width':500,
                        'height':500};

          // Instantiate and draw our chart, passing in some options.
          var chart = new google.visualization.PieChart(document.getElementById('top_services'));
          chart.draw(data, options);

          }


      function top_categories() {

            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Category');
            data.addColumn('number', 'Revenue');

            var jsonData = $.ajax({

              url: "ajax/chart_data.php",
              dataType: "json",
              async: false,
              "type": "POST",
              "data": {
                "selectedMonth": selectedMonth,
                "method": "top_categories",
              },
            }).responseText;

            var array_data = (JSON.parse(jsonData));
            var data = google.visualization.arrayToDataTable(
              array_data
            );
  
            // Set chart options
            var options = {'title':'Top 10 Categories',
                          'width':500,
                          'height':500};

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.PieChart(document.getElementById('top_categories'));
            chart.draw(data, options);
            
          }


          function monthly_recap() {

            var jsonData = $.ajax({
              url: "ajax/chart_data.php",
              dataType: "json",
              async: false,
              "type": "POST",
              "data": {
                "method": "monthly_recap"
              },
            }).responseText;

            var array_data = (JSON.parse(jsonData));
            var data = google.visualization.arrayToDataTable(array_data);

            var options = {
              chart: {
                title: 'Sale Performance',
                subtitle: '2 Month Sale',
              },
              vAxis: {format: 'decimal'},
              height: 400,
            };

            var chart = new google.charts.Bar(document.getElementById('monthly_recap'));

            chart.draw(data, google.charts.Bar.convertOptions(options));
          }


          

    </script>
    

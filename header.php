 <?php 

if(isset($_COOKIE['userdata'])){
  $_SESSION['userdata'] = $_COOKIE['userdata'];
}
include "function.php"; 

if(!isset($_SESSION['userdata'])){
  header("location:".DOMAIN_SOFTWARE."login.php"); 
//print_r($_SESSION);
  exit;
}


$login_user = get_session_data('user_id');
$user_id = get_session_data('user_id');
$salon_id = get_session_data('salon_id');


extract(select_row("SELECT include_gst,salon_name,salon_address,logo,gst_enable FROM `hr_salon` where salon_id='".$salon_id."'"));

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.7, shrink-to-fit=no">
    <title>Billing Software</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
    <script>
        var salon_id = <?php echo $salon_id; ?>;
    </script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha256-siyOpF/pBWUPgIcQi17TLBkjvNgNQArcmwJB8YvkAgg=" crossorigin="anonymous" />
      <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
	 <link href="assets/libs/toastr/build/toastr.min.css" rel="stylesheet">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />

      <link href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css" rel="stylesheet">


      <script>
    var include_gst = <?php echo $include_gst; ?>;
    var salon_id = <?php echo $salon_id; ?>;
    </script>

    <style>

        .navbar .navbar-brand-wrapper .navbar-brand img {
            height: auto;
        }
    @media print
    {
        .container-scroller,.print_button_hide { display: none; }

        .print_model { display: block; }
    }
  </style>

  
  </head>
  <body class="<?php echo $sliderBar; ?>">
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
          <a class="navbar-brand brand-logo" href="/"><img src="images/<?php echo $logo; ?>" alt="logo" /></a>
          <a class="navbar-brand brand-logo-mini" href="/"><img src="assets/images/logo-mini.svg" alt="logo" /></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-stretch">
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
          </button>
          <div class="search-field d-none d-md-block">
            <form class="d-flex align-items-center h-100" action="#">
              <div class="input-group">
                <div class="input-group-prepend bg-transparent">
                  <i class="input-group-text border-0 mdi mdi-magnify"></i>
                </div>
                <input type="text" class="form-control bg-transparent border-0" placeholder="Search projects">
              </div>
            </form>
          </div>
          <ul class="navbar-nav navbar-nav-right">
            
            <li class="nav-item d-none d-lg-block full-screen-link">
              <a href="job_card.php" class="nav-link">
                Job Card
              </a>
            </li>

            <li class="nav-item d-none d-lg-block full-screen-link">
              <a class="nav-link">
                <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
              </a>
            </li>
           
            <li class="nav-item nav-logout d-none d-lg-block">
              <a class="nav-link" href="logout.php">
                <i class="mdi mdi-power"></i>
              </a>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item nav-profile">
              <a href="#" class="nav-link">
                <div class="nav-profile-image">
                  <img src="https://images.agoramedia.com/wte3.0/gcms/The-First-Salon-Haircut-722x406.jpg?width=414" alt="profile">
                  <span class="login-status online"></span>
                  <!--change to offline or busy as needed-->
                </div>
                <div class="nav-profile-text d-flex flex-column">
                  <span class="font-weight-bold mb-2"><?php echo $salon_name; ?></span>
                  <span class="text-secondary text-small"><?php echo $salon_address; ?></span>
                </div>
                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
              </a>
            </li>

            <?php if(check_user_permission("dashboard","view",$login_user)){ ?>
            <li class="nav-item">
              <a class="nav-link" href="index.php">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon"></i>
              </a>
            </li>
            <?php } ?>
              
            <?php if(check_user_permission("billing","view",$login_user)){ ?>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#Billing" aria-expanded="false" aria-controls="Billing">
                <span class="menu-title">Billing</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-cart-outline menu-icon"></i>
              </a>
              <div class="collapse" id="Billing">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="jobcard_view.php">Job Card</a></li>
                  <li class="nav-item"> <a class="nav-link" href="billing_service.php">Service Billing</a></li>
                  <li class="nav-item"> <a class="nav-link" href="billing_membership.php">Sell Membership</a></li>
                  <li class="nav-item"> <a class="nav-link" href="billing_product.php">Product Billing</a></li>
                </ul>
              </div>
            </li>
            <?php } ?>


			 <?php if(check_user_permission("customer","view",$login_user)){ ?>
			<li class="nav-item">
          <a class="nav-link" href="customers.php">
            <span class="menu-title">Customers</span>
            <i class="mdi mdi-account-multiple menu-icon"></i>
          </a>
      </li>
      <?php } ?>

      <?php if(check_user_permission("expenses","view",$login_user)){ ?>
        <li class="nav-item">
          <a class="nav-link" data-toggle="collapse" href="#Expenses" aria-expanded="false" aria-controls="Expenses">
            <span class="menu-title">Expenses</span>
            <i class="menu-arrow"></i>
            <i class="mdi mdi-scale-balance menu-icon"></i>
          </a>
          <div class="collapse" id="Expenses">
            <ul class="nav flex-column sub-menu">
              <li class="nav-item"> <a class="nav-link" href="expenses.php">Cash Expense</a></li>
                <li class="nav-item"> <a class="nav-link" href="expenses_bank.php">Bank Expense</a></li>
              <li class="nav-item"> <a class="nav-link" href="expenses_cat.php">Expenses Category</a></li>
            </ul>
          </div>
        </li>
      <?php } ?>


      <?php if(check_user_permission("cataloge","view",$login_user)){ ?>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#Cataloge" aria-expanded="false" aria-controls="Cataloge">
          <span class="menu-title">Cataloge</span>
          <i class="menu-arrow"></i>
          <i class="mdi mdi-receipt menu-icon"></i>
        </a>
        <div class="collapse" id="Cataloge">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="services.php">Services</a></li>
            <li class="nav-item"> <a class="nav-link" href="packages.php">Packages</a></li>
            <li class="nav-item"> <a class="nav-link" href="services_cat.php">Services Category</a></li>
          </ul>
        </div>
      </li>
      <?php } ?>

      <?php if(check_user_permission("product","view",$login_user)){ ?>
      <li class="nav-item">
          <a class="nav-link" data-toggle="collapse" href="#Product" aria-expanded="false" aria-controls="Product">
            <span class="menu-title">Product</span>
            <i class="menu-arrow"></i>
            <i class="mdi mdi-cart-outline menu-icon"></i>
          </a>
          <div class="collapse" id="Product">
            <ul class="nav flex-column sub-menu">
            <!--li class="nav-item"> <a class="nav-link" href="product_inventry.php">Products Inventory</a></li-->
            <li class="nav-item"> <a class="nav-link" href="product.php">All Product</a></li>
            <li class="nav-item"> <a class="nav-link" href="product_brand.php">Product Brand</a></li>
            </ul>
          </div>
        </li>
        <?php } ?>


        <?php if(check_user_permission("inventory","view",$login_user)){ ?>
          <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#Inventory" aria-expanded="false" aria-controls="Inventory">
                  <span class="menu-title">Inventory</span>
                  <i class="menu-arrow"></i>
                  <i class="mdi mdi-cart-outline menu-icon"></i>
              </a>
              <div class="collapse" id="Inventory">
                  <ul class="nav flex-column sub-menu">
                      <!--li class="nav-item"> <a class="nav-link" href="product_inventry.php">Products Inventory</a></li-->
                      <li class="nav-item"> <a class="nav-link" href="inventory.php">Inventory</a></li>
                      <li class="nav-item"> <a class="nav-link" href="vendor_payment.php">Payment Record</a></li>
                      <li class="nav-item"> <a class="nav-link" href="inventory_bill.php">All Bill</a></li>
                      <li class="nav-item"> <a class="nav-link" href="inventory_add.php">Add New Bill</a></li>
      
                  </ul>
              </div>
          </li>
        <?php } ?>


      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
          <span class="menu-title">Reports</span>
          <i class="menu-arrow"></i>
          <i class="mdi mdi-poll-box menu-icon"></i>
        </a> 
        <div class="collapse" id="ui-basic">
          <ul class="nav flex-column sub-menu">
              <li class="nav-item"> <a class="nav-link" href="sale_record.php">Sales Record</a></li>
              <li class="nav-item"> <a class="nav-link" href="service_record.php">Service Record</a></li>

              <?php if(check_user_permission("report","delete",$login_user)){ ?>
              <li class="nav-item"> <a class="nav-link" href="staff_record.php">Staff Record</a></li>
              <?php } ?>
          <?php if($salon_id == 20){ ?>
              <li class="nav-item"> <a class="nav-link" href="record_attendence.php">Attendance Record</a></li>
              <li class="nav-item"> <a class="nav-link" href="sale_record_old.php">Old Sales Record</a></li>
          <?php } ?>
              <?php if($role_id == 3){ ?>
              <li class="nav-item"> <a class="nav-link" href="cash_adjustment.php">Cash Discount</a></li>
              <?php } ?>
            <li class="nav-item"> <a class="nav-link" href="feedback.php">Feedback</a></li>
            <!--li class="nav-item"> <a class="nav-link" href="">Membership Record</a></li>
            <li class="nav-item"> <a class="nav-link" href="">Staff Record</a></li>
            <li class="nav-item"> <a class="nav-link" href="">Invoice Record</a></li>
            <li class="nav-item"> <a class="nav-link" href="">Eod Record</a></li-->
          </ul>
        </div>
      </li>
	    
      <?php if(check_user_permission("staff","view",$login_user)){ ?>
      <li class="nav-item">
          <a class="nav-link" href="staff.php">
            <span class="menu-title">Staff</span>
            <i class="mdi mdi mdi-account-box menu-icon"></i>
          </a>
      </li>
      <?php } ?>

     <?php  if($user_id==1){ ?>
	    <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#ui-user" aria-expanded="false" aria-controls="ui-user">
          <span class="menu-title">Users</span>
          <i class="menu-arrow"></i>
          <i class="mdi mdi-poll-box menu-icon"></i>
        </a>
        <div class="collapse" id="ui-user">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="user.php">User</a></li>
            <li class="nav-item"> <a class="nav-link" href="user_role.php">User Role</a></li>
            <li class="nav-item"> <a class="nav-link" href="user_role_permissions.php">User Role Permission</a></li>
          </ul>
        </div>
      </li>
     <?php } ?>

     <li class="nav-item">
          <a class="nav-link" href="logout.php">
            <span class="menu-title">Logout</span>
            <i class="mdi mdi-power"></i>
          </a>
      </li>

  
    </ul>
  </nav>
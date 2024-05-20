<?php

include "config.php";
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Billing Software</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">

    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
	 <link href="assets/libs/toastr/build/toastr.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
          <div class="row flex-grow">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left p-5">
                <div class="brand-logo">
                  <img src="assets/images/HR-LOGO.png">
                </div>
                <h6 class="font-weight-light">Sign in to continue.</h6>
				<form class="form-horizontal m-t-20" id="user_form">
                        <input type="hidden" name="method" value="user_login" />
                        <div class="row p-b-30">
                            <div class="col-12">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-success text-white" id="basic-addon1"><i class="ti-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control form-control-lg" placeholder="Email" aria-label="Username" aria-describedby="basic-addon1" name="user_email" required="">
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-warning text-white" id="basic-addon2"><i class="ti-pencil"></i></span>
                                    </div>
                                    <input type="password" class="form-control form-control-lg" placeholder="Password" aria-label="Password" name="user_password" aria-describedby="basic-addon1" required="">
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="p-t-20">
                                      
                                        <button class="btn btn-block btn-gradient-danger btn-lg font-weight-medium auth-form-btn" type="submit">Login</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
	 <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
	
    <script src="assets/libs/toastr/build/toastr.min.js"></script>
    <!-- endinject -->
	  <script>

    $("#user_form").on('submit',function(e){
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "ajax/user_ajax.php",
            data: $('#user_form').serialize(),
            success: function(res){
                var obj = jQuery.parseJSON(res);

                if(obj.error == 1){
                    toastr.error(obj.msg, 'User Info');
                }else{
                    toastr.success(obj.msg, 'User Info');
                    window.location="<?php echo DOMAIN_SOFTWARE; ?>sale_record.php";
                }
            },
            error: function(){
                alert("Error");
            }
        });

    return false;
    });
        
    </script>
    <!-- ============================================================== -->
    <!-- This page plugin js -->
    <!-- ============================================================== -->
    
  </body>
</html>
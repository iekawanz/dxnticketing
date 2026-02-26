<?php
require('application_top.php');
require('session.php');

// Check if session has expired
if (!isset($_SESSION['loginData'])) {
  echo '<script type="text/javascript">
          window.parent.location.href = "login.php";
        </script>';
  exit;
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta http-equiv="Content-Type" content="text/html">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DXN Customer Support Ticketing System</title>
  <meta name="title" content="" />
  <meta name="description" content="" />
  <meta name="keywords" content="" />
  <meta name="copyright" content="" />
  <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

  <!--******** CSS ********-->
	<link href="css/reset.css" rel="stylesheet" type="text/css" media="all"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
  <link href="vendor/fontawesome-pro-6.0.0-beta2-web/css/all.css" rel="stylesheet">
  <link href="vendor/bootstrap-5.2.3/css/bootstrap.css" rel="stylesheet" type="text/css" media="all"/>
  <link href="css/common.css" rel="stylesheet" type="text/css" media="all"/>
  <link href="css/overwrite-bootstrap.css" rel="stylesheet" type="text/css" media="all"/>
  <link href="css/animate.css" rel="stylesheet"/>
 
</head>

<body>
  <div class="layer-right">

    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-5">
      <h1>Password</h1>

      <div class="btn-toolbar d-flex align-items-center">
        <button type="button" class="btn-blue d-flex align-items-center ms-3" id="btnSavePassword"><span class="material-symbols-outlined">save</span> <p>Save new password</p></button>
      </div>
    </div>

    <!-- Form -->
    <div class="row">
      <div class="col-lg-8">
        <div class="form-general row">
            <div class="col-md-6">
                <label class="text-start">Current Password</label>
                <div class="password">
                    <input type="password" class="form-control" id="current_password" name="current_password">
                    <span class="material-symbols-outlined icon-password" id="currpass">visibility_off</span>
                </div>
                <small class="d-block text-end pe-4 lh-1 text-danger" id="showErrCurrPass">&nbsp;</small>
            </div>

            <div class="col-md-6"></div>

            <div class="col-md-6">
                <label class="text-start">New Password</label>
                <div class="password">
                    <input type="password" class="form-control" placeholder="New Password" id="new_password" name="new_password">
                    <span class="material-symbols-outlined icon-password" id="newpass">visibility_off</span>
                </div>
                <small class="d-block text-end pe-4 lh-1 text-danger" id="showErrNewPass">&nbsp;</small>
            </div>

            <div class="col-md-6"></div>

            <div class="col-md-6">
                <label class="text-start">Confirm Password</label>
                <div class="password">
                    <input type="password" class="form-control" placeholder="Confirm New Password" id="confirm_password" name="confirm_password">
                    <span class="material-symbols-outlined icon-password" id="conpass">visibility_off</span>
                </div>
                <small class="d-block text-end pe-4 lh-1 text-danger" id="showErrConfirmPass">&nbsp;</small>
            </div>
        </div>
      </div>
      <div class="col-lg-4"></div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="successModal">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
            <!-- Modal Header -->
            <div class="modal-header justify-content-end pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal body -->
            <div class="modal-body d-flex align-items-center text-center p-4">
                <div class="mx-auto">
                    <p class="mb-3">Password has been changed successfully!</p>
                    <button class="btn-blue w-100 mt-5" type="button" data-bs-dismiss="modal">OK</button>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
            </div>
            
        </div>
    </div>
  </div>




  <!-- ******** Jquery ********-->
	<script type="text/javascript" src="js/jquery-3.6.3.min.js"></script>

  <!-- Bootstrap 5.2-->
  <script type="text/javascript" src="vendor/bootstrap-5.2.3/js/bootstrap.bundle.js"></script>

  <!-- WOW Animate -->
  <script src="js/wow.js"></script>
  <script>
    new WOW().init();
  </script>

  <!-- ****** Js Path *****-->
  <script type="text/javascript" src="scripts/admin.js"></script>





</body>
</html>

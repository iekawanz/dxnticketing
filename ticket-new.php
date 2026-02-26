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
  <link href="css/reset.css" rel="stylesheet" type="text/css" media="all" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0"
    rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
  <link href="vendor/fontawesome-pro-6.0.0-beta2-web/css/all.css" rel="stylesheet">
  <link href="vendor/bootstrap-5.2.3/css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
  <link href="css/common.css" rel="stylesheet" type="text/css" media="all" />
  <link href="css/overwrite-bootstrap.css" rel="stylesheet" type="text/css" media="all" />
  <link href="css/animate.css" rel="stylesheet" />
</head>

<body>
  <div class="layer-right">

    <!-- Loader -->
    <div class="icon-loader" id="loadImage" style="margin-top: 200px; display:none;">
      <img src="images/icon-loading.gif">
    </div>
    <!-- end Loader -->

    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-5">
      <h1>New Ticket</h1>

      <div class="btn-toolbar d-flex align-items-center">
        <button type="button" class="btn-blue d-flex align-items-center ms-3" id="btnSubmitTicket"><span
            class="material-symbols-outlined">save</span>
          <p>Submit</p>
        </button>
      </div>
    </div>

    <!-- Form -->
    <div class="row">
      <div class="col-lg-8">
        <div class="form-general row">
          <div class="col-md-6">
            <label class="text-start">I would like to</label>
            <select class="form-control" id="type" name="type">
              <option value="">Choose an option</option>
              <option value="Enquire">Enquire</option>
              <option value="Request">Request</option>
              <option value="Complaint">Complaint</option>
            </select>
            <small class="d-block text-end pe-4 lh-1 text-danger" id="type_error">&nbsp;</small>
          </div>
          <div class="col-md-6">
            <label class="text-start">About</label>
            <select class="form-control" id="about" name="about">
              <option value="">Choose a suitable option</option>
              <option value="DXN SHOP (Website)">DXN SHOP (Website)</option>
              <option value="DXN SHOP (Mobile App)">DXN SHOP (Mobile App)</option>
              <option value="DXN PLUS">DXN PLUS</option>
              <option value="DXN VIDEO">DXN VIDEO</option>
              <option value="OTHERS">OTHERS</option>
            </select>
            <small class="d-block text-end pe-4 lh-1 text-danger" id="about_error">&nbsp;</small>
          </div>
          <div class="col-12">
            <label class="text-start">Subject</label>
            <input type="text" class="form-control" id="subject" name="subject">
            <small class="d-block text-end pe-4 lh-1 text-danger" id="subject_error">&nbsp;</small>
          </div>
          <div class="col-12">
            <label class="text-start">Detail</label>
            <textarea class="tiny form-control" placeholder="" rows="8" id="detail" name="detail"></textarea>
            <small class="d-block text-end pe-4 lh-1" id="detail_error">Length (Max <b>2000</b> characters)</small>
          </div>
          <div class="col-12">
            <label class="text-start">Attachment</label>
            <input type="file" id="file" name="file[]" accept="image/*, .pdf, .doc, .docx, .txt, .zip" multiple>
            <small class="d-block text-end pe-4 lh-1" id="file_error">(Max file allowed: 25MB, allow all file type pdf,
              png, jpg, docx, txt, zip)</small>
          </div>
          <?php if ($_SESSION['loginData']['level'] == 1 || $_SESSION['loginData']['level'] == 2) { ?>
            <div class="col-12">
              <label class="text-start">Assign to Country PIC</label>
              <select class="form-control" id="country_pic" name="country_pic">
                <option value="">Choose select Country PIC</option>
                <?php
                $sql_cm = "SELECT a.id as country_id, a.name as country_name, b.id as pic_id, b.name as pic_name FROM countries a ";
                $sql_cm .= "INNER JOIN admin_user b ON a.id = b.fk_countries ";
                $sql_cm .= "WHERE b.level = 3 ";
                $sql_cm .= "ORDER BY country_name ASC ";
                $query = tep_db_query($sql_cm);
                while ($r = tep_db_fetch_assoc($query)) {
                  ?>
                  <option value="<?php echo $r['country_id'] . "|" . $r['pic_id']; ?>">
                    <?php echo $r['country_name'] . ' - ( ' . $r['pic_name'] . ' )' ?>
                  </option>
                  <?php
                }
                ?>
              </select>
              <small class="d-block text-end pe-4 lh-1 text-danger" id="pic_error">&nbsp;</small>
            </div>
          <?php } ?>

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
            <p class="mb-3" id="success_msg"></p>
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

  <!-- ******** TinyMCE ********-->
  <script type="text/javascript" src="vendor/tinymce/tinymce.min.js"></script>

  <!-- Bootstrap 5.2-->
  <script type="text/javascript" src="vendor/bootstrap-5.2.3/js/bootstrap.bundle.js"></script>

  <!-- WOW Animate -->
  <script src="js/wow.js"></script>
  <script>
    new WOW().init();
  </script>

  <!-- ****** Js Path *****-->
  <script type="text/javascript" src="scripts/ticket.js?v=<?= time() ?>"></script>


  <script>
    (function ($) {
      'use strict';

      $(document).ready(function () {
        if (typeof tinymce !== 'undefined') {
          tinymce.init({
            selector: '.tiny',
            menubar: false,
            height: 300,
            forced_root_block: "",
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak ' +
              'searchreplace wordcount visualblocks visualchars code fullscreen ' +
              'insertdatetime media nonbreaking save table contextmenu directionality ' +
              'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc',
            toolbar: "undo redo | formatselect | bold italic underline strikethrough | " +
              "link | preview fullscreen | forecolor",
            branding: false,
            setup: function (editor) {
              editor.on('init', function () {
                this.getBody().querySelectorAll('a').forEach(function (link) {
                  link.removeAttribute('style'); // Remove inline styles from links
                });
                console.log('TinyMCE initialized successfully.');
              });

              editor.on("focus", function () {
                editor.getContainer().style.height = "500px"; // Expand height on focus
              });

              editor.on("blur", function () {
                editor.getContainer().style.height = "300px"; // Shrink height on blur
              });
            }
          });
        } else {
          console.error('TinyMCE is not loaded.');
        }
      });


    })(jQuery);
  </script>


</body>

</html>
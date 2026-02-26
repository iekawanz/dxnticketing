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

  <style>
    .dataTables_filter {
      display: none;
    }

    .dataTables_length {
      display: none;
    }

    .paginate_button {
      border: 0;
      color: #707070;
      padding: 0.7rem 1.4rem;
      text-decoration: none;
      font-size: 13px;
    }

    .paginate_button.current {
      color: black;
      background-color: #DAE3E5;
      border-radius: 0.8rem;
    }
  </style>
</head>

<body>
  <div class="layer-right">

    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-5">
      <h1>My Ticket</h1>

      <div class="btn-toolbar d-flex align-items-center">

      </div>
    </div>

    <!-- Form -->
    <div class="row">
      <div class="col-lg-12">
        <div class="form-general row">
          <div class="col-sm-4">
            <label class="text-start">Search</label>
            <input type="text" class="form-control" id="search" name="search">
          </div>
          <div class="col-sm-4">
            <label class="text-start">Category</label>
            <select id="filter_type" name="filter_type" class="form-control">
              <option value="">All</option>
              <option value="Enquire">Enquire</option>
              <option value="Request">Request</option>
              <option value="Complaint">Complaint</option>
            </select>
          </div>
          <div class="col-sm-4">
            <label class="text-start">Status</label>
            <select id="filter_status" name="filter_status" class="form-control">
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
        </div>


      </div>
      <div class="col-lg-4"></div>
    </div>

    <!-- Sort-->
    <div id="hidetab" style="display:none">
      <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div class="d-flex justify-content-between align-items-center">
          <p class="m-0">show</p>
          <select class="mx-2" id="entries-select">
            <option>10</option>
            <option>25</option>
            <option>50</option>
            <option>100</option>
          </select>
          <p class="m-0">entries</p>
        </div>
        <div>
          <p class="m-0">*click title for sorting</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover bg-white sortable" id="myTable">
          <thead class="table-primary">
            <tr>
              <th scope="col" class="text-center">No</th>
              <th scope="col" class="Handcursor">Ticket No</th>
              <th scope="col" class="text-center">Platform</th>
              <th scope="col" class="Handcursor">Subject</th>
              <th scope="col" class="Handcursor" style="text-align:right;">Registered Date & Time</th>
              <th scope="col" class="Handcursor"></th>
              <th scope="col" class="Handcursor"></th>
              <th scope="col" class="Handcursor"></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "SELECT a.* FROM ticket a ";
            $sql .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
            if ($_SESSION['loginData']['level'] == 3) {
              $sql .= "WHERE b.fk_countries = '" . $_SESSION['loginData']['country'] . "' ";
              $sql .= "OR a.country_pic = '" . $_SESSION['loginData']['country'] . "' ";
            }

            $query = tep_db_query($sql);
            while ($r = tep_db_fetch_assoc($query)) {
              $id = $r['id'];
              $subject = $r['subject'];
              $rating = $r['rating'];
              $regDate = date('d-m-Y H:i:s A', strtotime($r['created_date']));

              if ($_SESSION['loginData']['level'] == 1 || $_SESSION['loginData']['level'] == 2) {
                if ($r['status'] == 'In Progress') {
                  if ($r['last_response_by'] == 'PIC')
                    $color = "#ffdcdc";
                  else
                    $color = "#dcedff";
                } else {
                  $color = "#f5f5f5";
                }
              } else {
                if ($r['status'] == 'In Progress') {
                  if ($r['last_response_by'] == 'Admin')
                    $color = "#ffdcdc";
                  else
                    $color = "#dcedff";
                } else {
                  $color = "#f5f5f5";
                }
              }

              //Main Detail
              $msg = $r['detail'] . " ";
              $count = 1;
              $sql2 = "SELECT * FROM ticket_response WHERE fk_ticket = '" . $r['id'] . "'";
              $query2 = tep_db_query($sql2);
              while ($r2 = tep_db_fetch_assoc($query2)) {
                //Sub Detail
                $msg .= $r2['response'] . " ";
                $count++;
              }

              if ($r['about'] == 'DXN SHOP (Website)')
                $platform = "<img src=\"images/icon-shop.png\" style=\"height:30px;\" title=\"" . htmlspecialchars($r['about']) . "\">";
              else if ($r['about'] == 'DXN SHOP (Mobile App)')
                $platform = "<img src=\"images/icon-shop.png\" style=\"height:30px;\" title=\"" . htmlspecialchars($r['about']) . "\">";
              else if ($r['about'] == 'DXN PLUS')
                $platform = "<img src=\"images/icon-plus.png\" style=\"height:30px;\" title=\"" . htmlspecialchars($r['about']) . "\">";
              else if ($r['about'] == 'DXN VIDEO')
                $platform = "<img src=\"images/icon-video.png\" style=\"height:30px;\" title=\"" . htmlspecialchars($r['about']) . "\">";
              else
                $platform = "<span class=\"material-symbols-outlined\" title=\"" . htmlspecialchars($r['about']) . "\">more_horiz</span>";
              ?>
              <tr data-bs-toggle="collapse" data-bs-target="#collapseTicket" aria-expanded="false"
                aria-controls="collapseTicket" data-id="<?php echo $id; ?>" style="cursor: pointer;">
                <td class="text-center" style="background-color:<?= $color ?>;"></td>
                <?php if ($r['status'] == 'In Progress') { ?>
                  <td>
                    <?php echo $r['ticket_no'] . " <span class=\"d-inline-flex align-items-center\">(" . $count . " <span class=\"fs-3 lightblue\">&nbsp;✉</span>)</span>"; ?>
                  </td>
                <?php } else { ?>
                  <td>
                    <?php echo $r['ticket_no'] . " <span class=\"d-inline-flex align-items-center\">(" . $r['rating'] . " <span class=\"fs-3 text-warning\">&nbsp;★</span>)</span>"; ?>
                  </td>
                <?php } ?>
                <td class="text-center"><?= $platform ?></td>
                <td><?php echo $r['subject']; ?></td>
                <td style="text-align:right;"><?php echo $regDate; ?></td>
                <td class="text-center"><?php echo $msg; ?></td>
                <td class="text-center"><?php echo $r['modify_date']; ?></td>
                <td class="text-center"><?php echo $r['status']; ?></td>
              </tr>
              <?php
            }
            ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center">
        <p class="m-0 page-info"></p>
        <ul></ul>
      </div>
    </div>
  </div>

  <!-- Right Bar - Messsage & Ticket Info-->
  <div class="layer-ticket-right collapse collapse-horizontal" id="collapseTicket">
    <input type="hidden" id="id" name="id" value="" />
    <div class="text-end">
      <button class="btn-mini" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTicket"><span
          class="material-symbols-outlined">close</span></button>
    </div>

    <!-- Loader -->
    <div class="icon-loader" id="loadImage">
      <img src="images/icon-loading.gif">
    </div>
    <!-- end Loader -->

    <div class="layer-message form-general d-none">
      <div class="layer-header" id="subject_title">&nbsp;</div>

      <div class="layer-msg" id="response_list">
      </div>

      <div class="layer-header" id="response_title">
        Response
      </div>
      <div class="p-3" id="response_input">
        <textarea rows="2" class="tiny form-control" id="response" name="response"></textarea>
        <small class="d-block text-end pe-4 lh-1 text-danger" id="response_error">&nbsp;</small>
        <input type="file" id="file" name="file[]" class="my-3" accept="image/*, .pdf, .doc, .docx, .txt, .zip" multiple>
        <small class="d-block text-end pe-4 lh-1" id="file_error">(Max file allowed: 2MB, allow all file type pdf, png,
          jpg, docx, txt, zip)</small>
        <div>&nbsp;</div>
        <div class="d-flex justify-content-between align-items-center">
          <button type="button" id="btnSend" class="btn-blue d-flex d-none align-items-center"><span
              class="material-symbols-outlined">send</span>
            <p>Submit</p>
          </button>
          <button type="button" class="btn-outline d-flex d-none align-items-center" data-bs-toggle="modal"
            data-bs-target="#closeticketModal"><span class="material-symbols-outlined">check</span>
            <p>Close Ticket</p>
          </button>
        </div>
      </div>
      <div class="p-3" id="response_reopen">
        <div class="d-flex justify-content-end">
          <button type="button" class="btn-outline reopen d-none d-flex align-items-center" data-bs-toggle="modal"
            data-bs-target="#reopenticketModal">
            <p>Re-open Ticket</p>
          </button>
        </div>
      </div>
    </div>

    <table class="table table-hover bg-white mt-4 d-none">
      <thead class="table-primary">
        <tr>
          <th class="py-2">
            <a class="d-flex justify-content-between align-items-center text-black text-decoration-none"
              data-bs-toggle="collapse" href="#collapseInfo" role="button" aria-expanded="false"
              aria-controls="collapseInfo">
              <b>Ticket Info</b>
              <!--span class="material-symbols-outlined">chevron_right</span-->
            </a>
          </th>
        </tr>
      </thead>
      <tbody class="collapse show" id="collapseInfo">
        <tr>
          <th id="ticket_name"></th>
        </tr>
        <tr>
          <th id="ticket_level"></th>
        </tr>
        <tr>
          <th id="ticket_category"></th>
        </tr>
        <tr>
          <th id="ticket_created_date"></th>
        </tr>
        <tr>
          <th id="ticket_closed_date"></th>
        </tr>
      </tbody>
    </table>
  </div>

  <!--/*************************** MODAL ****************************************************/-->
  <div class="modal fade" id="closeticketModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
        <!-- Modal Header -->
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body pt-0">
          <div class="mx-auto text-center">
            <h2 class="modal-title">Close Ticket</h2>
            <div class="min-height">
              <p>Are you sure you want to close this ticket?</p>
            </div>

            <button type="submit" class="btn-blue" data-bs-toggle="modal" data-bs-target="#ratingModal">YES</button>

            <button type="submit" class="btn-outline ms-2" data-bs-dismiss="modal" aria-label="Close">NO</button>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer">
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="successModal2">
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

  <!-- Modal -->
  <div class="modal fade" id="successCloseTicketModal">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
        <!-- Modal Header -->
        <div class="modal-header justify-content-end pb-0">
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Modal body -->
        <div class="modal-body d-flex align-items-center text-center p-4">
          <div class="mx-auto">
            <p class="mb-3" id="successcloseticket_msg"></p>
            <button class="btn-blue w-100 mt-5" type="button" data-bs-dismiss="modal" id="btnOK">OK</button>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
        </div>

      </div>
    </div>
  </div>

  <!--/***************************END***************************************************************************/-->

  <!--/*************************** MODAL ****************************************************/-->
  <div class="modal fade" id="ratingModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
        <!-- Modal Header -->
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body pt-0">
          <div class="mx-auto text-center">
            <h2 class="modal-title">Services Rating</h2>
            <div class="min-height">
              <p>Please rate your experience</p>
              <div class="layer-rate">
                <span class="star" data-value="1" style="cursor: pointer;">&#9733;</span>
                <span class="star" data-value="2" style="cursor: pointer;">&#9733;</span>
                <span class="star" data-value="3" style="cursor: pointer;">&#9733;</span>
                <span class="star" data-value="4" style="cursor: pointer;">&#9733;</span>
                <span class="star" data-value="5" style="cursor: pointer;">&#9733;</span>
              </div>
              <div class="layer-rate mb-5">
                <span class="fs-6 grey">Terrible</span>
                <span class="fs-6 grey">Poor</span>
                <span class="fs-6 grey">Fair</span>
                <span class="fs-6 grey">Good</span>
                <span class="fs-6 grey">Amazing</span>
              </div>
            </div>
            <input type="hidden" id="rating-value" name="rating" value="">
            <input type="hidden" id="ticket_id" name="ticket_id" value="">
            <button type="submit" id="btnCloseTicket" class="btn-blue">Submit</button>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer">
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="reopenticketModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
        <!-- Modal Header -->
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body pt-0">
          <div class="mx-auto text-center">
            <h2 class="modal-title">Re-open Ticket</h2>
            <div class="min-height">
              <p>Are you sure you want to re-open this ticket?</p>
            </div>

            <button type="submit" class="btn-blue" id="btnReopenTicket">YES</button>

            <button type="submit" class="btn-outline ms-2" data-bs-dismiss="modal" aria-label="Close">NO</button>
          </div>
        </div>
        <input type="hidden" id="reopen_ticket_id" name="reopen_ticket_id" value="">
        <!-- Modal Footer -->
        <div class="modal-footer">
        </div>
      </div>
    </div>
  </div>

  <!--/***************************END***************************************************************************/-->

  <!-- ******** Jquery ********-->
  <script type="text/javascript" src="js/jquery-3.6.3.min.js"></script>

  <!-- ******** TinyMCE ********-->
  <script type="text/javascript" src="vendor/tinymce/tinymce.min.js"></script>

  <!-- Bootstrap 5.2-->
  <script type="text/javascript" src="vendor/bootstrap-5.2.3/js/bootstrap.bundle.js"></script>

  <!-- WOW Animate -->
  <script src="js/wow.js"></script>

  <!-- ****** Js Path *****-->
  <script type="text/javascript" src="scripts/admin.js?v=<?= time() ?>"></script>

  <script>
    new WOW().init();
  </script>

  <!-- ******** CHANGE TOGGLE ********-->
  <script>
    $(function () {
      $('#ChangeToggle').click(function () {
        $('#navbar-hamburger').toggleClass('hidden');
        $('#navbar-close').toggleClass('hidden');
      });
    });

  </script>

  <script src="js/datatable.js"></script>
  <script type="text/javascript" src="scripts/ticket.js?v=<?= time() ?>"></script>

  <script>
    (function ($) {
      'use strict';

      $(document).ready(function () {
        if (typeof tinymce !== 'undefined') {
          tinymce.init({
            selector: '.tiny',
            menubar: false,
            height: 150,
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
                this.getBody().querySelectorAll('a').forEach(function(link) {
                  link.removeAttribute('style'); // Remove inline styles from links
                });
                console.log('TinyMCE initialized successfully.');
              });

              editor.on("focus", function () {
                editor.getContainer().style.height = "300px"; // Expand height on focus
              });

              editor.on("blur", function () {
                editor.getContainer().style.height = "150px"; // Shrink height on blur
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
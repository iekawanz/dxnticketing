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
      <h1>Administration Info</h1>

      <div class="btn-toolbar d-flex align-items-center">
        <button type="button" class="btn-blue d-flex align-items-center ms-3" id="btnCreateAdmin" data-type="Add"><span class="material-symbols-outlined">add</span> <p>Create new Admin</p></button>
        <button type="button" class="btn-outline d-flex align-items-center ms-3" id="btnSaveAdmin" data-type="Edit"><span class="material-symbols-outlined">save</span> <p>Update</p></button>
      </div>
    </div>

    <!-- Form -->
    <div class="row">
      <div class="col-lg-8">
        <div class="form-general row">
            <div class="col-sm-4">
                <label class="text-start">Admin name</label>
                <input type="text" class="form-control" placeholder="Admin name" id="name" name="name">
                <small class="d-block text-end pe-4 lh-1 text-danger" id="name_error">&nbsp;</small>
            </div>
            <div class="col-sm-4">
              <label class="text-start">Login name</label>
              <input type="text" class="form-control" placeholder="Email" id="email" name="email">
              <input type="hidden" class="form-control" id="prev_email" name="prev_email" value="">
              <small class="d-block text-end pe-4 lh-1 text-danger" id="email_error">&nbsp;</small>
            </div>
            <div class="col-sm-4">
              <label class="text-start">Status</label>
              <select class="form-control" id="status" name="status">
                    <option value="">- Select -</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
              </select>
              <small class="d-block text-end pe-4 lh-1 text-danger" id="status_error">&nbsp;</small>
            </div>
            <div class="col-sm-4">
                <label class="text-start">Admin level</label>
                <select class="form-control" id="level" name="level">
                    <option value="">- Select -</option>
                    <option value="1">Super Admin</option>
                    <option value="2">Admin</option>
                    <option value="3">Country Manager</option>
                </select>
                <input type="hidden" class="form-control" id="prev_level" name="prev_level" value="">
                <small class="d-block text-end pe-4 lh-1 text-danger" id="level_error">&nbsp;</small>
            </div>
            <div class="col-sm-4" id="div_countries" style="display:none;">
                <label class="text-start">Countries</label>
                <select class="form-control" id="fk_countries" name="fk_countries">
                    <?php 
                      $sql_countries = "SELECT * FROM countries ORDER BY id";
                      $query_countries = tep_db_query($sql_countries);
                      while($r = tep_db_fetch_assoc($query_countries))
		                  {
                    ?>
                      <option value="<?php echo $r['id'] ?>"><?php echo $r['name']; ?></option>
                    <?php 
                      } 
                    ?>
                </select>
                <small class="d-block text-end pe-4 lh-1 text-danger" id="level_error">&nbsp;</small>
            </div>
            <input type="hidden" class="form-control" id="id" name="id" value="">
        </div>
      </div>
      <div class="col-lg-4"></div>
    </div>
    

    <!-- Listing -->
    <h2 class="my-5">Administrator Listing</h2>
    <div class="table-responsive">
      <table class="table table-hover bg-white">
        <thead class="table-primary">
          <tr>
            <th width="10%">No.</th>
            <th width="20%">Name</th>
            <th width="20%">Admin Level</th>
            <th width="30%">Countries</th>
            <th width="10%">Status</th>
            <th width="15%" class="text-end" colspan="2">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $count = 1;

          $sql =  " SELECT a.id, a.name, a.email, a.level, a.fk_countries, a.status, b.name as countries_name FROM admin_user a ";
          $sql .= " INNER JOIN countries b ON a.fk_countries = b.id ";
          $sql .= " ORDER BY a.id, b.name ASC ";
          $query = tep_db_query($sql);
          while($r = tep_db_fetch_assoc($query))
		      {
            $data = array(
              'id' => $r['id'],
              'name' => $r['name'],
              'email' => $r['email'],
              'level' => $r['level'],
              'fk_countries' => $r['fk_countries'],
              'countries_name'=>$r['countries_name'],
              'status' => $r['status']
            );
          ?>
          <tr>
            <td><?php echo $count; ?></td>
            <td><?php echo $data['name'] ?></td>
            <td>
              <?php if($data['level'] == 1){ ?>
                Super Admin
              <?php } else if($data['level'] == 2){ ?>
                Admin
              <?php }else{ ?>
                Country Manager
              <?php }?>
            </td>
            <td>
              <?php
                if($data['level'] == 3){  
              ?>
              <?php echo $data['countries_name']; ?>
              <?php } else { ?>
                -
              <?php } ?>
            </td>
            <td><?php echo $data['status'] ?></td>
            <td class="text-end"> 
                <a href="#" class="me-2"><span class="material-symbols-outlined" onclick="showAdminInfo('<?php echo $data['id']; ?>', '<?php echo $data['name']; ?>', '<?php echo $data['email']; ?>', '<?php echo $data['level']; ?>','<?php echo $data['fk_countries'] ?>','<?php echo $data['status'] ?>')">edit_note</span></a>
            </td>
            <td class="text-center"> 
              <a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" class="popUpDelete" data-id="<?php echo $data['id']; ?>"><span class="material-symbols-outlined">Delete</span></a>
            </td>
          </tr>
          <?php
            $count++;
          } 
          ?>
        </tbody>
      </table>

      <div style="height: 500px;"></div>
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

  <!-- Modal -->
  <div class="modal fade" id="deleteModal">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
            <!-- Modal Header -->
            <div class="modal-header justify-content-end pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <input type="hidden" id="admin_id" name="admin_id" value=""/>
            <!-- Modal body -->
            <div class="modal-body d-flex align-items-center text-center p-4">
                <div class="mx-auto">
                    <h4 class="mb-4">ARE YOU SURE?</h4>
                    <p class="mb-3">Are you sure that you want to delete the admin from the list?</p>
                    <button class="btn-blue w-100 mt-5" type="button" id="btnDeleteAdmin">Yes</button>
                    <button class="btn-outline w-100 mt-4" type="button" data-bs-dismiss="modal">No, I go back</button>
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

  <!-- ****** Js Path *****-->
  <script type="text/javascript" src="scripts/admin.js"></script>
  
	<script>
		new WOW().init();

    $(document).ready(function() {
      $('#btnSaveAdmin').addClass('d-none');
    });
	</script>





</body>
</html>

<?php 
require('application_top.php');

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
  <link href="css/overwrite-bootstrap.css?123" rel="stylesheet" type="text/css" media="all"/>
  <link href="css/animate.css" rel="stylesheet"/>
</head>

<body>
  <div class="layer-right">

    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-5">
        <h1>Deployment Checklist</h1>
        <div class="form-general d-flex align-items-center">
            <select class="form-control me-3" id="aboutFilter" onchange="loadData()">
                <option value="">ALL</option>
                <option value="DXN Shop" <?php echo (isset($_SESSION['checklist']['project_type']) && $_SESSION['checklist']['project_type'] == "DXN Shop") ? "selected" : ""; ?>>DXN SHOP</option>
                <option value="DXN Video" <?php echo (isset($_SESSION['checklist']['project_type']) && $_SESSION['checklist']['project_type'] == "DXN Video") ? "selected" : ""; ?>>DXN VIDEO</option>
            </select>
            <select class="form-control" style="width:30rem;" id="countryFilter" onchange="loadData()">
              <option value="" selected>ALL</option>
              <?php 
                $sql_countries = "SELECT * FROM countries ";
                $query = tep_db_query($sql_countries);

                while( $r = tep_db_fetch_assoc($query) )
                {
              ?>
                  <option value="<?php echo $r['id']; ?>" <?php echo (isset($_SESSION['checklist']['country']) && $_SESSION['checklist']['country'] == $r['id']) ? "selected" : ""; ?>><?php echo $r['name']; ?></option>
          <?php } ?>
            </select>
        </div>
    </div>

    <!----------------------- Tabs ----------------------->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="todo-tab" data-bs-toggle="tab" data-bs-target="#todo-tab-pane" type="button" role="tab" aria-controls="todo-tab-pane" aria-selected="true">To Do</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-tab-pane" type="button" role="tab" aria-controls="completed-tab-pane" aria-selected="false">Completed</button>
      </li>
    </ul>

    <!--------------------Tab Content -------------------->
    <div class="tab-content bg-transparent" id="myTabContent">
      <!-- Page To do -->
      <div class="tab-pane fade show active" id="todo-tab-pane" role="tabpanel" aria-labelledby="todo-tab" tabindex="0">
        <?php 
          $translation_orders = [];
          $payment_orders = [];
          $domain_orders = [];
          $frontend_orders = [];
          $backend_orders = [];
          $training_orders = [];
          $others_orders = [];

          $sql  = "SELECT a.id, a.name, a.comment, a.checklist_type, a.project_type, a.file, a.created_date, a.created_by, ";
          $sql .= "b.name as pic_name, c.name as country_pic_name "; 
          $sql .= "FROM checklist a ";
          $sql .= "INNER JOIN admin_user b ON b.id = a.pic ";
          $sql .= "INNER JOIN countries c ON c.id = a.country_pic ";
          $sql .= "WHERE a.status = 'To Do' ";

          if(isset($_SESSION['checklist']['project_type']) && $_SESSION['checklist']['project_type'] != '')
            $sql .= "AND a.project_type = '".$_SESSION['checklist']['project_type']."' ";

          if(isset($_SESSION['checklist']['country']) && $_SESSION['checklist']['country'] != '')
            $sql .= "AND b.fk_countries = '".$_SESSION['checklist']['country']."' ";

          $sql .= "ORDER BY a.created_date DESC ";
          $query = tep_db_query($sql);

          while( $row = tep_db_fetch_assoc($query) )
          {
            $limit = 25;
            if (strlen($row['name']) > $limit)
              $row['name_suffix'] = substr($row['name'], 0, $limit) . "...";
            else
              $row['name_suffix'] = $row['name'];

            if (strlen($row['comment']) > $limit)
              $row['comment_suffix'] = substr($row['comment'], 0, $limit) . "...";
            else
              $row['comment_suffix'] = $row['comment'];

            $row['created_date'] = date('d-m-Y', strtotime($row['created_date']));

            switch ($row['checklist_type']) {
              case 'Translation':
                  $translation_orders[] = $row;
                  break;
              case 'Payment Gateway':
                  $payment_orders[] = $row;
                  break;
              case 'Domain':
                  $domain_orders[] = $row;
                  break;
              case 'Frontend QC':
                  $frontend_orders[] = $row;
                  break;
              case 'Backend QC':
                  $backend_orders[] = $row;
                  break;
              case 'Training':
                  $training_orders[] = $row;
                  break;
              case 'Others':
                  $others_orders[] = $row;
                  break;
              default:
                  // Handle any unexpected statuses
                  break;
            }
          }
        ?>
        <!-- Listing - Translation  -->
        <div class="mb-3">
          <h3 class="lightblue">Translation</h3>
        </div>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white form-general">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach ($translation_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="translation[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal"></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                    <a href="javascript:void(0);" class="mx-2 delete-link" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">delete</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
              <!-- Add Item -->
              <tr>
                <td colspan="7">
                  <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#openModal" data-type="Translation" id="openTranslationModalButton">
                    <span class="material-symbols-outlined me-2">add</span> Add Item
                  </a>
                </td>
              </tr>
              <!-- end Add Item -->
            </tbody>
          </table>
        </div>

        <!-- Listing - Payment Gateway  -->
        <?php if(isset($_SESSION['checklist']['project_type']) && $_SESSION['checklist']['project_type'] != 'DXN Video'){ ?>
        <div class="mb-3">
          <h3 class="lightblue">Payment Gateway</h3>
        </div>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white form-general">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($payment_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="payment[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal"></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                    <a href="javascript:void(0);" class="mx-2 delete-link" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">delete</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
              <!-- Add Item -->
              <tr>
                <td colspan="7">
                  <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#openModal" data-type="Payment Gateway" id="openPaymentGatewayModalButton">
                    <span class="material-symbols-outlined me-2">add</span> Add Item
                  </a>
                </td>
              </tr>
              <!-- end Add Item -->
            </tbody>
          </table>
        </div>

      <!-- Listing - Domain  -->
        <div class="mb-3">
          <h3 class="lightblue">Domain</h3>
        </div>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($domain_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
            ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="domain[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal"></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                    <a href="javascript:void(0);" class="mx-2 delete-link" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">delete</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>

              <!-- Add Item -->
              <tr>
                <td colspan="7">
                  <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#openModal" data-type="Domain" id="openDomainModalButton">
                    <span class="material-symbols-outlined me-2">add</span> Add Item
                  </a>
                </td>
              </tr>
              <!-- end Add Item -->
            </tbody>
          </table>
        </div>
        <?php } ?>

        <!-- Listing - Frontend QC  -->
        <div class="mb-3">
          <h3 class="lightblue">Frontend QC</h3>
        </div>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($frontend_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
            ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="front[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal"></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                    <a href="javascript:void(0);" class="mx-2 delete-link" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">delete</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
               <!-- Add Item -->
               <tr>
                <td colspan="7">
                  <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#openModal" data-type="Frontend QC" id="openFrontendQCModalButton">
                    <span class="material-symbols-outlined me-2">add</span> Add Item
                  </a>
                </td>
              </tr>
              <!-- end Add Item -->
            </tbody>
          </table>
        </div>

        <!-- Listing - Backend QC  -->
        <div class="mb-3">
          <h3 class="lightblue">Backend QC</h3>
        </div>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach ($backend_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="backend[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal"></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                    <a href="javascript:void(0);" class="mx-2 delete-link" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">delete</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
               <!-- Add Item -->
               <tr>
                <td colspan="7">
                  <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#openModal" data-type="Backend QC" id="openBackendQCModalButton">
                    <span class="material-symbols-outlined me-2">add</span> Add Item
                  </a>
                </td>
              </tr>
              <!-- end Add Item -->
            </tbody>
          </table>
        </div>

        <!-- Listing - Training  -->
        <div class="mb-3">
          <h3 class="lightblue">Training</h3>
        </div>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($training_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="training[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal"></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                    <a href="javascript:void(0);" class="mx-2 delete-link" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">delete</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
             <!-- Add Item -->
             <tr>
                <td colspan="7">
                  <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#openModal" data-type="Training" id="openTrainingModalButton">
                    <span class="material-symbols-outlined me-2">add</span> Add Item
                  </a>
                </td>
              </tr>
              <!-- end Add Item -->
            </tbody>
          </table>
        </div>

        <!-- Listing - others  -->
        <div class="mb-3">
          <h3 class="lightblue">Others</h3>
        </div>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($others_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="others[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal"></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                    <a href="javascript:void(0);" class="mx-2 delete-link" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">delete</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
              <!-- Add Item -->
             <tr>
                <td colspan="7">
                  <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#openModal" data-type="others" id="openOthersModalButton">
                    <span class="material-symbols-outlined me-2">add</span> Add Item
                  </a>
                </td>
              </tr>
              <!-- end Add Item -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Page completed -->
      <div class="tab-pane fade" id="completed-tab-pane" role="tabpanel" aria-labelledby="completed-tab" tabindex="0">
      <?php 
          $translation_completed_orders = [];
          $payment_completed_orders = [];
          $domain_completed_orders = [];
          $frontend_completed_orders = [];
          $backend_completed_orders = [];
          $training_completed_orders = [];
          $others_completed_orders = [];

          $sql  = "SELECT a.id, a.name, a.comment, a.checklist_type, a.project_type, a.file, a.created_date, a.created_by, a.resolved_date, ";
          $sql .= "b.name as pic_name, c.name as country_pic_name "; 
          $sql .= "FROM checklist a ";
          $sql .= "INNER JOIN admin_user b ON b.id = a.pic ";
          $sql .= "INNER JOIN countries c ON c.id = a.country_pic ";
          $sql .= "WHERE a.status = 'Completed' ";

          if(isset($_SESSION['checklist']['project_type']) && $_SESSION['checklist']['project_type'] != '')
            $sql .= "AND a.project_type = '".$_SESSION['checklist']['project_type']."' ";

          if(isset($_SESSION['checklist']['country']) && $_SESSION['checklist']['country'] != '')
            $sql .= "AND b.fk_countries = '".$_SESSION['checklist']['country']."' ";

          $sql .= "ORDER BY a.resolved_date DESC ";
          $query = tep_db_query($sql);

          while( $row = tep_db_fetch_assoc($query) )
          {
            $limit = 25;
            if (strlen($row['name']) > $limit)
              $row['name_suffix'] = substr($row['name'], 0, $limit) . "...";
            else
              $row['name_suffix'] = $row['name'];

            if (strlen($row['comment']) > $limit)
              $row['comment_suffix'] = substr($row['comment'], 0, $limit) . "...";
            else
              $row['comment_suffix'] = $row['comment'];

            $row['created_date'] = date('d-m-Y', strtotime($row['created_date']));

            switch ($row['checklist_type']) {
              case 'Translation':
                  $translation_completed_orders[] = $row;
                  break;
              case 'Payment Gateway':
                  $payment_completed_orders[] = $row;
                  break;
              case 'Domain':
                  $domain_completed_orders[] = $row;
                  break;
              case 'Frontend QC':
                  $frontend_completed_orders[] = $row;
                  break;
              case 'Backend QC':
                  $backend_completed_orders[] = $row;
                  break;
              case 'Training':
                  $training_completed_orders[] = $row;
                  break;
              case 'Others':
                  $others_completed_orders[] = $row;
                  break;
              default:
                  // Handle any unexpected statuses
                  break;
            }
          }
        ?>
        <!-- List - Translation -->
        <div class="mb-3">
          <h3 class="lightblue">Translation</h3>
        </div>
        <?php 
        if($translation_completed_orders){
        ?>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($translation_completed_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="completed_translation[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal" checked></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="table-responsive table-fixedheight mb-5">
          <p>No result found!</p>
        </div>
        <?php } ?>

        <?php if($_SESSION['checklist']['project_type'] != 'DXN Video'){ ?>
        <!-- Listing - Payment Gateway  -->
        <div class="mb-3">
          <h3 class="lightblue">Payment Gateway</h3>
        </div>
        <?php 
        if($payment_completed_orders){
        ?>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($payment_completed_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="completed_payment[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal" checked></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="table-responsive table-fixedheight mb-5">
          <p>No result found!</p>
        </div>
        <?php } ?>

        <!-- Listing - Payment Gateway  -->
        <div class="mb-3">
          <h3 class="lightblue">Domain</h3>
        </div>
        <?php 
        if($domain_completed_orders){
        ?>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($domain_completed_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="completed_domain[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal" checked></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="table-responsive table-fixedheight mb-5">
          <p>No result found!</p>
        </div>
        <?php } ?>
        <?php } ?>

        <!-- Listing - Payment Gateway  -->
        <div class="mb-3">
          <h3 class="lightblue">Frontend QC</h3>
        </div>
        <?php 
        if($frontend_completed_orders){
        ?>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($frontend_completed_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="completed_frontend[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal" checked></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="table-responsive table-fixedheight mb-5">
          <p>No result found!</p>
        </div>
        <?php } ?>

        <!-- Listing - Payment Gateway  -->
        <div class="mb-3">
          <h3 class="lightblue">Backend QC</h3>
        </div>
        <?php 
        if($backend_completed_orders){
        ?>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($backend_completed_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="completed_backend[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal" checked></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="table-responsive table-fixedheight mb-5">
          <p>No result found!</p>
        </div>
        <?php } ?>

       
        <!-- Listing - Payment Gateway  -->
        <div class="mb-3">
          <h3 class="lightblue">Training</h3>
        </div>
        <?php 
        if($training_completed_orders){
        ?>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($training_completed_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="completed_training[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal" checked></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="table-responsive table-fixedheight mb-5">
          <p>No result found!</p>
        </div>
        <?php } ?>
        

        <!-- Listing - Payment Gateway  -->
        <div class="mb-3">
          <h3 class="lightblue">Others</h3>
        </div>
        <?php 
        if($others_completed_orders){
        ?>
        <!-- Table -->
        <div class="table-responsive table-fixedheight mb-5">
          <table class="table table-hover table-fixedwidth bg-white">
            <thead class="table-primary">
              <tr>
                <th width="5%" class="text-center">Status</th>
                <th width="15%">Name</th>
                <th width="10%">Project</th>
                <th width="20%">Country PIC</th>
                <th width="15%">Creation Date</th>
                <th width="20%">Comments</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
                foreach ($others_completed_orders as $value) 
                {
                  if($value['file'] != "")
                    $file = HTTP_SERVER."ticketing/file_upload/".$value['file'];
                  else
                    $file = "";
              ?>
                <tr>
                  <td class="text-center"><input type="checkbox" class="mx-auto" name="completed_others[]" value="<?php echo $value['id']; ?>" data-bs-toggle="modal" data-bs-target="#completeModal" checked></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['name']; ?>"><?php echo $value['name_suffix']; ?></span></td>
                  <td><?php echo $value['project_type']; ?></td>
                  <td><?php echo $value['pic_name']."( ".$value['country_pic_name']." )"; ?></td>
                  <td><?php echo $value['created_date']; ?></td>
                  <td><span data-toggle="tooltip" data-placement="top" title="<?php echo $value['comment']; ?>"><?php echo $value['comment_suffix']; ?></span></td>
                  <td class="text-center">
                    <?php if($file != ''){ ?>
                      <a href="javascript:void(0);" onclick="window.open('<?php echo $file; ?>', '_blank', 'width=800,height=600');" class="mx-2 edit-link">
                          <span class="material-symbols-outlined">picture_as_pdf</span>
                      </a>
                    <?php } ?>
                    <a href="javascript:void(0);" class="mx-2 edit-link" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $value['id']; ?>"><span class="material-symbols-outlined">edit</span></a>
                  </td>
                </tr>
              <?php 
                } 
              ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="table-responsive table-fixedheight mb-5">
          <p>No result found!</p>
        </div>
        <?php } ?>

      </div>
    </div>

    
  </div>

<div class="modal fade" id="openModal">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">     
          <input type ="hidden" id="checklist_type" name="checklist_type" value=""/>
          <!-- Loader -->
          <div class="icon-loader" id="loadImage" style="margin-top: 200px; display:none;">
            <img src="images/icon-loading.gif">
          </div>
          <!-- end Loader -->
          <!-- Modal Header -->
          <div class="modal-header">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Modal Body -->
          <div class="modal-body pt-0">
              <div class="mx-auto">
                  <div class="form-general row">
                    <div class="col-md-12">
                    <label class="text-start">Project</label>
                      <select class="form-control" id="project_type" name="project_type">
                        <option value="">Select project type</option>
                        <option value="DXN Shop">DXN SHOP</option>
                        <option value="DXN Video">DXN VIDEO</option>
                      </select>
                      <small class="d-block text-end pe-4 lh-1 text-danger" id="project_error">&nbsp;</small>
                    </div>
                    <div class="col-12">
                      <label class="text-start">Name</label>
                      <input type="text" class="form-control" id="name" name="name" value="">
                      <small class="d-block text-end pe-4 lh-1 text-danger" id="name_error">&nbsp;</small>
                    </div>
                    <div class="col-md-12">
                      <label class="text-start">Country PIC</label>
                      <select class="form-control" id="pic" name="pic">
                        <option value="">Please select country pic</option>
                        <?php 
                        $sql_cm  = "SELECT a.id as country_id, a.name as country_name, b.id as pic_id, b.name as pic_name FROM countries a ";
                        $sql_cm .= "INNER JOIN admin_user b ON a.id = b.fk_countries ";
                        $sql_cm .= "WHERE b.level = 3 ";
                        $sql_cm .= "ORDER BY country_name ASC ";
                        $query = tep_db_query($sql_cm);
                        while( $r = tep_db_fetch_assoc($query) )
                        {
                        ?>
                          <option value="<?php echo $r['pic_id']; ?>"><?php echo $r['country_name'].' - ( '.$r['pic_name'].' )' ?></option>
                        <?php 
                        }
                        ?>
                      </select>
                      <small class="d-block text-end pe-4 lh-1 text-danger" id="pic_error">&nbsp;</small>
                    </div>
                    <div class="col-12">
                      <label class="text-start">Comments</label>
                      <textarea class="form-control" placeholder="" rows="8" id="comment" name="comment"></textarea>
                      <small class="d-block text-end pe-4 lh-1" id="comment_error">Length (Max <b>2000</b> characters)</small>
                    </div>
                    <div class="col-12">
                      <label class="text-start">Attachment</label>
                      <input type="file" id="file" name="file" accept="image/*, .pdf, .doc, .docx, .txt">
                      <small class="d-block text-end pe-4 lh-1" id="file_error">(Max file allowed: 2MB, allow all file type pdf, png, jpg, docx, txt)</small>
                    </div>
                  </div>

                  <button type="submit" class="btn-blue" id="btnAddChecklist">Add</button>
                  <button type="submit" class="btn-outline ms-2" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
              </div>
          </div>
          <!-- Modal Footer -->
          <div class="modal-footer">
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="editModal">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">    
          <input type ="hidden" id="edit_checklist_type" name="edit_checklist_type" value=""/>
          <input type ="hidden" id="edit_id" name="edit_id" value=""/>
          <!-- Loader -->
          <div class="icon-loader" id="loadImage" style="margin-top: 200px; display:none;">
            <img src="images/icon-loading.gif">
          </div>
          <!-- end Loader -->
          <!-- Modal Header -->
          <div class="modal-header">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Modal Body -->
          <div class="modal-body pt-0">
              <div class="mx-auto">
                  <div class="form-general row">
                    <div class="col-md-12">
                    <label class="text-start">Project</label>
                      <select class="form-control" id="edit_project_type" name="edit_project_type">
                        <option value="">Select project type</option>
                        <option value="DXN Shop">DXN SHOP</option>
                        <option value="DXN Video">DXN VIDEO</option>
                      </select>
                      <small class="d-block text-end pe-4 lh-1 text-danger" id="edit_project_error">&nbsp;</small>
                    </div>
                    <div class="col-12">
                      <label class="text-start">Item</label>
                      <input type="text" class="form-control" id="edit_name" name="edit_name" value="">
                      <small class="d-block text-end pe-4 lh-1 text-danger" id="edit_name_error">&nbsp;</small>
                    </div>
                    <div class="col-md-12">
                      <label class="text-start">PIC</label>
                      <select class="form-control" id="edit_pic" name="edit_pic">
                        <option value="">Please select country pic</option>
                        <?php 
                        $sql_cm  = "SELECT a.id as country_id, a.name as country_name, b.id as pic_id, b.name as pic_name FROM countries a ";
                        $sql_cm .= "INNER JOIN admin_user b ON a.id = b.fk_countries ";
                        $sql_cm .= "WHERE b.level = 3 ";
                        $sql_cm .= "ORDER BY country_name ASC ";
                        $query = tep_db_query($sql_cm);
                        while( $r = tep_db_fetch_assoc($query) )
                        {
                        ?>
                          <option value="<?php echo $r['pic_id']; ?>"><?php echo $r['country_name'].' - ( '.$r['pic_name'].' )' ?></option>
                        <?php 
                        }
                        ?>
                      </select>
                      <small class="d-block text-end pe-4 lh-1 text-danger" id="edit_pic_error">&nbsp;</small>
                    </div>
                    <div class="col-12">
                      <label class="text-start">Comments</label>
                      <textarea class="form-control" placeholder="" rows="8" id="edit_comment" name="edit_comment"></textarea>
                      <small class="d-block text-end pe-4 lh-1" id="edit_comment_error">Length (Max <b>2000</b> characters)</small>
                    </div>
                    <div class="col-12">
                      <label class="text-start">Attachment</label>
                      <input type="file" id="edit_file" name="edit_file" accept="image/*, .pdf, .doc, .docx, .txt">
                      <small class="d-block text-end pe-4 lh-1" id="edit_file_error">(Max file allowed: 2MB, allow all file type pdf, png, jpg, docx, txt)</small>
                    </div>
                  </div>

                  <button type="submit" class="btn-blue" id="btnUpdateChecklist">Update</button>
                  <button type="submit" class="btn-outline ms-2" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
              </div>
          </div>
          <!-- Modal Footer -->
          <div class="modal-footer">
          </div>
      </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="successChecklistModal">
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
                    <button class="btn-blue w-100 mt-5" type="button" data-bs-dismiss="modal" id="btnOK">OK</button>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
            </div>
            
        </div>
    </div>
</div>

 <!--/*************************** MODAL ****************************************************/-->
 <div class="modal fade" id="deleteModal">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
          <input type="hidden" id="delete_id" name="delete_id" value=""/>
          <!-- Modal Header -->
          <div class="modal-header">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Modal Body -->
          <div class="modal-body pt-0">
              <div class="mx-auto text-center">
                  <h2 class="modal-title">Delete</h2>
                  <div class="min-height">
                    <p>Are you sure you want to delete this note?</p>
                  </div>

                  <button type="submit" class="btn-blue" id="btnDeleteChecklist">YES</button>

                  <button type="submit" class="btn-outline ms-2" data-bs-dismiss="modal" aria-label="Close">NO</button>
              </div>
          </div>
          <!-- Modal Footer -->
          <div class="modal-footer">
          </div>
      </div>
  </div>
</div>

<!--/***************************END***************************************************************************/-->

<!--/*************************** MODAL ****************************************************/-->
<div class="modal fade" id="completeModal">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
        <input type="hidden" name="update_status_id" id="update_status_id" value=""/>
        <input type="hidden" name="status" id="status" value=""/>
          <!-- Modal Header -->
          <div class="modal-header">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Modal Body -->
          <div class="modal-body pt-0">
              <div class="mx-auto text-center">
                  <h2 class="modal-title" id="text-title"></h2>
                  <div class="min-height">
                    <p id="text-msg"></p>
                  </div>

                  <button type="submit" class="btn-blue" id="btnUpdate">YES</button>

                  <button type="submit" class="btn-outline ms-2" id="btnCancel" data-bs-dismiss="modal" aria-label="Close">NO</button>
              </div>
          </div>
          <!-- Modal Footer -->
          <div class="modal-footer">
          </div>
      </div>
  </div>
</div>

<!--/***************************END***************************************************************************/-->

<!--/*************************** MODAL ****************************************************/-->
<div class="modal fade" id="uncompleteModal">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
          <!-- Modal Header -->
          <div class="modal-header">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Modal Body -->
          <div class="modal-body pt-0">
              <div class="mx-auto text-center">
                  <h2 class="modal-title">Uncompleted</h2>
                  <div class="min-height">
                    <p>Are you sure you want to move this note to to do list?</p>
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

<!--/***************************END***************************************************************************/-->

  <!-- ******** Jquery ********-->
	<script type="text/javascript" src="js/jquery-3.6.3.min.js"></script>

  <!-- Bootstrap 5.2-->
  <script type="text/javascript" src="vendor/bootstrap-5.2.3/js/bootstrap.bundle.js"></script>

  <!-- ****** Js Path *****-->
  <script type="text/javascript" src="scripts/checklist.js"></script>

  <!-- WOW Animate -->
  <script src="js/wow.js"></script>
	<script>
		new WOW().init();
	</script>





</body>
</html>

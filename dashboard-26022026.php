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
    <?php 
        $sql  = "SELECT SUM(CASE WHEN a.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress, ";
        $sql .= "SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed, ";
        $sql .= "SUM(a.rating) as rating ";
        $sql .= "FROM ticket a ";
        $sql .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
        $sql .= "WHERE 1=1 ";
        if($_SESSION['loginData']['level'] == 3)
        {
            $sql .= "AND (b.fk_countries = '".$_SESSION['loginData']['country']."' ";
            $sql .= "OR a.country_pic = '".$_SESSION['loginData']['country']."') ";
        }
          
        $data = tep_db_single_row($sql);

        $monthYear = date('F Y');

        $count = $data['completed'] * 5;

        if($count > 0)
          $total = $data['rating'] / $count;
        else
          $total = 0;

        $totalRating = $total * 5;

        $averageRating = number_format($totalRating, 2);

        //Today Task
        $sql2  = "SELECT SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed, ";
        $sql2 .= "SUM(CASE WHEN a.status = 'To Do' THEN 1 ELSE 0 END) as to_do ";
        $sql2 .= "FROM checklist a ";
        $sql2 .= "INNER JOIN admin_user b ON b.id = a.pic ";
        $sql2 .= "WHERE 1=1 ";

        if(isset($_SESSION['checklist']['country']) && $_SESSION['checklist']['country'] != '')
            $sql2 .= "AND b.fk_countries = '".$_SESSION['checklist']['country']."' ";

        $data2 = tep_db_single_row($sql2);
    ?>
    <div class="layer-right">
        <!-- Header -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-5">
        <h1>Dashboard</h1>
        <div class="form-general d-flex align-items-center">
            <select class="form-control me-3" id="aboutFilter" onchange="loadData()">
                <option value="">ALL</option>
                <option value="DXN SHOP (Mobile App)">DXN SHOP (Mobile App)</option>
                <option value="DXN SHOP (Website)">DXN SHOP (Website)</option>
                <option value="DXN PLUS">DXN PLUS</option>
                <option value="DXN VIDEO">DXN VIDEO</option>
                <option value="OTHERS">OTHERS</option>
            </select>
            <?php if($_SESSION['loginData']['level'] == 1 || $_SESSION['loginData']['level'] == 2)
            { 
            ?>
              <select class="form-control" style="width:30rem;" id="countryFilter" onchange="loadData()">
                <option value="" selected>ALL</option>
                <?php 
                  $sql_countries = "SELECT * FROM countries ";
                  $query = tep_db_query($sql_countries);

                  while( $r = tep_db_fetch_assoc($query) )
                  {
                ?>
                    <option value="<?php echo $r['id']; ?>"><?php echo $r['name']; ?></option>
            <?php } ?>
              </select>
            <?php 
            }
            else
            { 
              $sql2 = "SELECT id, name FROM countries WHERE id = '".$_SESSION['loginData']['country']."'";
              $data2 = tep_db_single_row($sql2);
            ?>
              <select class="form-control" style="width:14rem;" id="countryFilter" disabled>
                <option value="<?php echo $data2['id']; ?>" selected><?php echo $data2['name']; ?></option>
              </select>
            <?php 
            } 
            ?>
        </div>
        </div>

    <!-- Content -->
    <div class="layer-dashboard bg-white rounded-4 p-5">
        <div class="row">
        <div class="col-md-4">
            <div class="layer-block rounded-3 p-5" style="background-color: #3a76ef;">
            <h6 class="text-light">Total Tickets In Progress</h6>
            <h1 class="text-light my-4" id="total_in_progress"><?php echo $data['in_progress'] ?? 0; ?></h1>
            <p class="text-light m-0"><?php echo $monthYear; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="layer-block rounded-3 p-5" style="background-color: #2FBFCE;">
            <h6 class="text-light">Total Tickets Closed</h6>
            <h1 class="text-light my-4" id="total_completed"><?php echo $data['completed'] ?? 0; ?></h1>
            <p class="text-light m-0"><?php echo $monthYear; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="layer-block rounded-3 p-5" style="background-color: #63c6ff;">
            <h6 class="text-light">Average Rating</h6>
            <h1 class="text-light my-4" id="average_rating"><?php echo $averageRating; ?></h1>
            <p class="text-light m-0">rated by users</p>
            </div>
        </div>
        </div>

        <div class="row mt-5">
        <div class="col-md-6">
            <div class="p-2">
            <h6 class="text-dark mb-5">Ticket Analysis</h6>
            <div id="columnchart_material" style="width:100%; height:320px;"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-4 bg-grey rounded-3">
            <h6 class="text-dark mb-5">Today Task</h6>
            <div id="donutchart" style="width:100%;height:320px;"></div>
            </div>
        </div>
        </div>
    </div>
    </div>
  </div>

  <?php 
    $sql  = "SELECT DATE_FORMAT(a.created_date, '%b') as month, a.status, COUNT(*) as count ";
    $sql .= "FROM ticket a ";
    $sql .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
    $sql .= "WHERE 1=1 ";
    if($_SESSION['loginData']['level'] == 3)
    {
        $sql .= "AND (b.fk_countries = '".$_SESSION['loginData']['country']."' ";
        $sql .= "OR a.country_pic = '".$_SESSION['loginData']['country']."') ";
    }
    $sql .= "GROUP BY MONTH(a.created_date), a.status ";
    $sql .= "ORDER BY month ";

    $query = tep_db_query($sql);
    $data = [];
    while( $r = tep_db_fetch_assoc($query) )
    {
      $data[$r['month']][$r['status']] = (int) $r['count'];
    }

    // Array of all 12 months
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    $inProgressData = [];
    $completedData = [];

    foreach ($months as $month) 
    {
        $inProgressData[] = isset($data[$month]['In Progress']) ? $data[$month]['In Progress'] : 0;
        $completedData[] = isset($data[$month]['Completed']) ? $data[$month]['Completed'] : 0;
    }
  ?>


  <!-- ******** Jquery ********-->
	<script type="text/javascript" src="js/jquery-3.6.3.min.js"></script>

  <!-- Bootstrap 5.2-->
  <script type="text/javascript" src="vendor/bootstrap-5.2.3/js/bootstrap.bundle.js"></script>

   <!-- ****** Js Path *****-->
   <script type="text/javascript" src="scripts/dashboard.js"></script>

  <!-- WOW Animate -->
  <script src="js/wow.js"></script>
	<script>
		new WOW().init();
	</script>

  <!-- Column Chart -->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">

    var months = <?php echo json_encode($months); ?>;
    var inProgressData = <?php echo json_encode($inProgressData); ?>;
    var completedData = <?php echo json_encode($completedData); ?>;

    google.charts.load('current', {'packages':['bar']});
    google.charts.setOnLoadCallback(ticketChart);

    function ticketChart() {

      // Prepare data array with headers
      var dataArray = [['<?php echo date('Y'); ?>', 'In Progress', 'Completed']];

      // Loop through months to fill the data array
      for (var i = 0; i < months.length; i++) {
        dataArray.push([months[i], inProgressData[i] || 0, completedData[i] || 0]);
      }

      // Convert to DataTable
      var data = google.visualization.arrayToDataTable(dataArray);

      var options = {
        colors: ['#3a76ef','#2FBFCE'],
        backgroundColor: { fill:'transparent' },
        width: ['100%'],
        chart: {
          title: '',
        }
      };

      var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

      chart.draw(data, google.charts.Bar.convertOptions(options));
    }
  </script>

  <!-- Donut Chart -->
  <script type="text/javascript">
    google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart1);
    function drawChart1() {
      var data = google.visualization.arrayToDataTable([
        ['Task', 'Hours per Day'],
        ['Completed',    <?php echo $data2['completed'] ?>],
        ['ToDo',    <?php echo $data2['to_do'] ?>]
      ]);

      var options = {
        title: '',
        pieHole: 0.4,
        colors: ['#3a76ef','#2FBFCE'],
        backgroundColor: { fill:'transparent' },
        width: ['100%'],
      };

      var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
      chart.draw(data, options);
    }
  </script>

</body>
</html>

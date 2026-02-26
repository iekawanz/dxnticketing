<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require('application_top.php'); // DB connection
tep_db_connect();

$start = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 10);
$searchValue = $_GET['custom_search'] ?? '';
$filterType = $_GET['filter_type'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';
$orderColumn = $_GET['order'][0]['column'] ?? 0;
$orderDir = $_GET['order'][0]['dir'] ?? 'asc';
$columns = ['id', 'ticket_no', 'about', 'subject', 'created_date', 'status'];
$orderBy = $columns[$orderColumn] ?? 'created_date';

// Count total records
$totalQuery = tep_db_query("SELECT COUNT(*) as total FROM ticket");
$totalData = tep_db_fetch_array($totalQuery);
$totalRecords = $totalData['total'];

// Filtering
$where = 'WHERE 1=1';
if (!empty($searchValue)) {
  //$searchValue = tep_db_input($searchValue);
  $where .= " AND (ticket_no LIKE '%$searchValue%' OR subject LIKE '%$searchValue%')";
}

if (!empty($filterType)) {
  //$filterType = tep_db_input($filterType);
  $where .= " AND `type` = '$filterType'";
}

if (!empty($filterStatus)) {
  //$filterStatus = tep_db_input($filterStatus);
  $where .= " AND `status` = '$filterStatus'";
}

// Count filtered records
$filteredQuery = tep_db_query("SELECT COUNT(*) as total FROM ticket $where");
$filteredData = tep_db_fetch_array($filteredQuery);
$filteredRecords = $filteredData['total'];

// Fetch paginated records
$sql = "
  SELECT id, ticket_no, about, subject, created_date, status, last_response_by, request_close 
  FROM ticket
  $where 
  ORDER BY $orderBy $orderDir 
  LIMIT $start, $length
";
$query = tep_db_query($sql);
$data = [];

$count = 1;
while ($r = tep_db_fetch_array($query)) {
  //Count response each row.
  $sql2 = "SELECT COUNT(*) AS total FROM ticket_response WHERE fk_ticket = '" . (int) $r['id'] . "'";
  $query2 = tep_db_query($sql2);
  $resCount = tep_db_fetch_array($query2);
  $responseCount = $resCount['total'] ?? 0;

  $ticketWithIcon = $r['ticket_no'] . ' <span class="d-inline-flex align-items-center">(' . $responseCount . ' <span class="fs-3 lightblue">&nbsp;✉</span>)</span>';

  $color = "#f5f5f5"; // default
  $level = $_SESSION['loginData']['level'] ?? 0;
  if ($r['status'] == 'In Progress') {
    if ($level == 1 || $level == 2) {
      $color = ($r['last_response_by'] == 'PIC') ? "#ffdcdc" : "#dcedff";
    } else {
      $color = ($r['last_response_by'] == 'Admin') ? "#ffdcdc" : "#dcedff";
    }
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

  $data[] = [
    'no' => $count++,
    'id' => $r['id'],
    'ticket_no' => $ticketWithIcon,
    'platform' => $platform,
    'subject' => $r['subject'],
    'created_date' => date('d-m-Y H:i A', strtotime($r['created_date'])),
    'row_color' => $color,
    'request_close' => $r['request_close']
  ];
}

// Return JSON
echo json_encode([
  "draw" => intval($_GET['draw']),
  "recordsTotal" => $totalRecords,
  "recordsFiltered" => $filteredRecords,
  "data" => $data
]);
?>
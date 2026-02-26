<?php
require('../application_top.php');
include_once "../includes/functions/general.php";
tep_db_connect();

//Start Login Process
if(isset($_POST['func']) && $_POST['func'] == 'getData')
{
    $about = $_POST['about'];
    $country = $_POST['country'];

    //Total tickets in progress, completed and rating average.
    $sql  = "SELECT SUM(CASE WHEN a.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress, ";
    $sql .= "SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed, ";
    $sql .= "SUM(a.rating) as rating ";
    $sql .= "FROM ticket a ";
    $sql .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
    $sql .= "WHERE 1=1 ";
    if($about != "")
        $sql .= " AND a.about = '".$about."'";
    if($country != "")
    {
        $sql .= " AND (b.fk_countries = '".$country."' ";
        $sql .= " OR a.country_pic = '".$country."') ";
    }

    $data = tep_db_single_row($sql);

    $total_in_progress = $data['in_progress'] != "" ? $data['in_progress'] : 0;
    $total_completed = $data['completed'] != "" ? $data['completed'] : 0;

    $count = $data['completed'] * 5;

    if($count != 0)
        $total = $data['rating'] / $count;
    else
        $total = 0;
    
    $totalRating = $total * 5;

    $average_rating = number_format($totalRating, 2);
    //END

    //Ticket Analysis
    $sql2  = " SELECT DATE_FORMAT(a.created_date, '%b') as month, a.status, COUNT(*) as count ";
    $sql2 .= " FROM ticket a ";
    $sql2 .= " INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
    $sql2 .= " WHERE 1=1 ";
    if($about != "")
        $sql2 .= " AND a.about = '".$about."'";
    if($country != "")
    {
        $sql2 .= " AND (b.fk_countries = '".$country."' ";
        $sql2 .= " OR a.country_pic = '".$country."') ";
    }
        

    $sql2 .= " GROUP BY MONTH(a.created_date), a.status ";
    $sql2 .= " ORDER BY month";

    $query = tep_db_query($sql2);
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
    //END

    //Today Task
    $sql3  = "SELECT SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed, ";
    $sql3 .= "SUM(CASE WHEN a.status = 'To Do' THEN 1 ELSE 0 END) as to_do ";
    $sql3 .= "FROM checklist a ";
    $sql3 .= "INNER JOIN admin_user b ON b.id = a.pic ";
    $sql3 .= "WHERE 1=1 ";
    if($country != "")
        $sql3 .= " AND b.fk_countries = '".$country."' ";
    
    $data3 = tep_db_single_row($sql3);

    $total_task_completed = $data3['completed'];
    $total_task_todo = $data3['to_do'];

    $response = [
        'months' => $months,
        'inProgressData' => $inProgressData,
        'completedData' => $completedData,
        'total_in_progress' => $total_in_progress,
        'total_completed' => $total_completed,
        'average_rating' => $average_rating,
        'task_completed' => $total_task_completed != 0 ? $total_task_completed : 0,
        'task_todo' => $total_task_todo != 0 ? $total_task_todo : 0
    ];

    echo json_encode(['status' => '1', 'data' => $response]);
    exit;
}
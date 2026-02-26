<?php 
require('application_top.php');
include_once "includes/functions/general.php";
tep_db_connect();

//$to = "foo_cheewah@dxn2u.com";
$to = "zalikha_osman@dxn2u.com";
$from = "noreply@dxnnwn.com";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: noreply@dxnnwn.com" . "\r\n";

$template = file_get_contents('email/ticket_notification.htm');
$subject = "DXN00049 - ASP DISCOUNT & PROMOTIONS";
$response = "Good Morning DXN. Hi everyone";
$history_content = generateTicketingResponseContent(28);

$emailContent = str_replace(
    ['{AssignName}','{FromName}','{Date}','{Request}','{Content}'],
    ["Mr Laszlo",'Izad', '28-11-2024 17:55:00', $response, $history_content],
    $template
);

test_tep_email($to, $from, $subject, $emailContent, $headers, $attachment);

function generateTicketingResponseContent($val)
{
    $sql  = "SELECT a.created_date, a.response, b.name FROM ticket_response a ";
    $sql .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
    $sql .= "WHERE a.fk_ticket = '".$val."' ORDER BY a.created_date DESC ";
    $query = tep_db_query($sql);
    $html  = "";
    $html .= "<p style=\"background-color: #f3f3f3; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px;\">";
    $html .= "Ticketing History Response";
    $html .= "<br/><br/>";
    $html .= "<font color=\"#999999\">";
    if(tep_db_num_rows($query)> 0)
    {
        while( $r = tep_db_fetch_assoc($query) )
        {
            $newDate = date("d-m-Y H:i:s", strtotime($r['created_date']));
            $html .= "From: ".$r['name']." (".$newDate.")<br/>";
            $html .= $r['response']."<br/><br/>";
        }
    }
    $html .= "</font>";
    $html .= "</p>";

    return $html;
}
?>
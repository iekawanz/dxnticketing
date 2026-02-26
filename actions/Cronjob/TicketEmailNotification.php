<?php 
require('../../application_top.php');
include_once "../../includes/functions/general.php";
tep_db_connect();

$sql = "SELECT * FROM ticket WHERE status = 'In Progress'";
$query = tep_db_query($sql);
if(tep_db_num_rows($query) > 0)
{
    while( $r = tep_db_fetch_assoc($query) )
    {
        $email = "";

        $sql2  = "SELECT id, fk_ticket, MAX(response_date) as response_date ";
        $sql2 .= "FROM ticket_response ";
        $sql2 .= "WHERE fk_ticket = '".$r['id']."'";
        if($r2 = tep_db_single_row($sql2))
        {
            if($r2['id'] != "")
            {
                $id = $r2['id'];

                // Example of a given date (in 'Y-m-d' format)
                $givenDate = $r2['response_date'];
    
                // Convert the given date to a timestamp using strtotime
                $givenDateTimestamp = strtotime($givenDate);
    
                // Get the current date, subtract 2 days, and convert it to a timestamp
                $currentDateTimestamp = strtotime('-2 days');
    
                // Compare the dates
                if ($givenDateTimestamp < $currentDateTimestamp)
                {
                    $sql3 = "SELECT * FROM admin_user WHERE id = '".$r['assign_pic']."'";
                    if($r3 = tep_db_single_row($sql3))
                        $email = $r3['email'];
    
                    $to = $email;
                    $from = "noreply@dxnnwn.com";
                    $subject = "Ticket No (".$r['ticket_no'].") - require your response.";
                    /*$emailContent  = "Just a quick note to let you know there will be a ticket 2-day delay. Please follow up as soon as possible <br><br>.";
                    $emailContent .= "Thanks for your understanding!";
                    $emailContent .= "<br><br><br><br><br><br>";
                    $emailContent .= "Please login in to <a href=\"https://www.dxnnwn.com/ticketing/index.php\" target=\"_blank\">DXN Ticketing</a> to view response.<br><br>";
                    $emailContent .= "Thank you.<br><br>";
                    $emailContent .= "This is system generated email, do not reply to this email.";*/
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: noreply@dxnnwn.com" . "\r\n";

                    $template = file_get_contents('../../email/auto_notification.htm'); // Load the template file

                    // Replace placeholders with actual values
                    $emailContent = str_replace($template);
            
                    // Send the email
                    tep_email($to, $from, $subject, $emailContent, $headers, "");
            
                    $data = array(
                        'response_date' => date('Y-m-d H:i:s')
                    );
                
                    tep_insert_n_update($data, "ticket_response", 'UPDATE', array('id' => $id), false, true);
                }
            }
            else
            {
                // Example of a given date (in 'Y-m-d' format)
                $givenDate = $r['response_date'];

                // Convert the given date to a timestamp using strtotime
                $givenDateTimestamp = strtotime($givenDate);

                // Get the current date, subtract 2 days, and convert it to a timestamp
                $currentDateTimestamp = strtotime('-2 days');

                // Compare the dates
                if ($givenDateTimestamp < $currentDateTimestamp)
                {
                    if($r['last_response_by'] == 'Admin')
                    {
                        $sql3 = "SELECT * FROM admin_user WHERE id = '".$r['assign_pic']."'";
                        if($r3 = tep_db_single_row($sql3))
                            $email = $r3['email'];
                    }
                    else
                    {
                        //Email to all Super Admin and Admin.
                        //Send email to all super admin & admin.
                        $sql3 = "SELECT email FROM admin_user WHERE level IN (1,2) AND status = 'Active'";
                        $query3 = tep_db_query($sql3);
                        if(tep_db_num_rows($query3)> 0)
                        {
                            while( $r4 = tep_db_fetch_assoc($query3) )
                            {
                                if($email === '')
                                    $email .= $r4['email'];
                                else
                                    $email .= ','.$r4['email'];
                            }
                        }
                    }

                    $to = $email;
                    $from = "noreply@dxnnwn.com";
                    $subject = "Ticket No (".$r['ticket_no'].") - require your response.";
                    /*$emailContent  = "Just a quick note to let you know there will be a ticket 2-day delay. Please follow up as soon as possible <br><br>.";
                    $emailContent .= "Thanks for your understanding!";
                    $emailContent .= "<br><br><br><br><br><br>";
                    $emailContent .= "Please login in to <a href=\"https://www.dxnnwn.com/ticketing/index.php\" target=\"_blank\">DXN Ticketing</a> to view response.<br><br>";
                    $emailContent .= "Thank you.<br><br>";
                    $emailContent .= "This is system generated email, do not reply to this email.";
                    $headers = "MIME-Version: 1.0" . "\r\n";*/
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: noreply@dxnnwn.com" . "\r\n";

                    $template = file_get_contents('../../email/auto_notification.htm'); // Load the template file

                    // Replace placeholders with actual values
                    $emailContent = str_replace(
                        [],
                        [],
                        $template
                    );

                    // Send the email
                    tep_email($to, $from, $subject, $emailContent, $headers, "");

                    //Update response date
                    $data = array(
                        'response_date' => date('Y-m-d H:i:s')
                    );
            
                    tep_insert_n_update($data, "ticket", 'UPDATE', array('id' => $r['id']), false, true);
                }
            }
        }
    }
}

echo "done";
exit;
?>
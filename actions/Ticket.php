<?php
require('../application_top.php');
include_once "../includes/functions/general.php";
tep_db_connect();

//Add New Ticket
if (isset($_POST['func']) && $_POST['func'] == 'addNewTicket') {
    if (isset($_POST['type']) && $_POST['type'] == "") {
        echo json_encode(['status' => '0', 'msg' => "Please select option.", 'type' => 'Type']);
        exit;
    } else if (isset($_POST['about']) && $_POST['about'] == "") {
        echo json_encode(['status' => '0', 'msg' => "Please select about.", 'type' => 'About']);
        exit;
    } else if (isset($_POST['subject']) && $_POST['subject'] == "") {
        echo json_encode(['status' => '0', 'msg' => "Please key in subject.", 'type' => 'Subject']);
        exit;
    } else if (isset($_POST['detail']) && $_POST['detail'] == "") {
        echo json_encode(['status' => '0', 'msg' => "Please key in detail.", 'type' => 'Detail']);
        exit;
    } else if (isset($_POST['country_pic']) && $_POST['country_pic'] == "" && $_SESSION['loginData']['level'] != 3) {
        echo json_encode(['status' => '0', 'msg' => "Please select Country PIC.", 'type' => 'CountryPIC']);
        exit;
    } else {
        $data = array();
        $country_id = "";
        $assign_to = "";
        $dest_path = "";
        $uploadedFilesStr = "";
        $validFiles = [];


        $sql = "SELECT * FROM admin_user WHERE id = '" . $_SESSION['loginData']['id'] . "'";
        if ($r = tep_db_single_row($sql)) {
            if (isset($_FILES['file']) && !empty($_FILES['file']['name'][0])) {
                $maxFileSize = 25 * 1024 * 1024; // 25MB
                $uploadFileDir = '../file_upload/';
                $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt', 'zip'];

                // Step 1: Validate all files first
                foreach ($_FILES['file']['name'] as $key => $fileName) {
                    $fileTmpPath = $_FILES['file']['tmp_name'][$key];
                    $fileSize = $_FILES['file']['size'][$key];
                    $fileType = $_FILES['file']['type'][$key];

                    // Extract file extension
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));

                    // Generate a unique file name
                    $newFileName = date('YmdHis') . "_" . uniqid() . "." . $fileExtension;

                    // Validate file size
                    if ($fileSize > $maxFileSize) {
                        echo json_encode(['status' => '0', 'msg' => "File is too large: $fileName (Max 25MB).", 'type' => 'File']);
                        exit;
                    }

                    // Validate file extension
                    if (!in_array($fileExtension, $allowedExts)) {
                        echo json_encode(['status' => '0', 'msg' => "Invalid file type: $fileName", 'type' => 'File']);
                        exit;
                    }

                    // If valid, store file info in array
                    $validFiles[] = [
                        'tmpPath' => $fileTmpPath,
                        'destPath' => $uploadFileDir . $newFileName,
                        'fileName' => $fileName,
                        'newFileName' => $newFileName,
                    ];
                }

                // Step 2: Move all validated files to the server
                foreach ($validFiles as $file) {
                    if (move_uploaded_file($file['tmpPath'], $file['destPath'])) {
                        $uploadedFiles[] = $file['newFileName'];
                    } else {
                        echo json_encode(['status' => '0', 'msg' => "Failed to upload: " . $file['fileName']]);
                        exit;
                    }
                }
            }

            if (!empty($uploadedFiles)) {
                // Convert array to comma-separated string
                $uploadedFilesStr = implode("|", $uploadedFiles);
            }

            /*if (isset($_FILES['file']) && $_FILES['file'] != "")
            {
                $maxFileSize = 2 * 1024 * 1024; //(2MB)
                $fileTmpPath = $_FILES['file']['tmp_name'];
                $fileName = $_FILES['file']['name'];
                $fileSize = $_FILES['file']['size'];
                $fileType = $_FILES['file']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $fileName = date('YmdHis');

                $allowedExts = array('jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt');

                if ($fileSize > $maxFileSize) 
                {
                    echo json_encode(['status' => '0', 'msg' => 'File is too large. Maximum file size is 2 MB.', 'type'=>'File']);
                    exit;
                }
                else
                {
                    if (in_array($fileExtension, $allowedExts)) 
                    {
                        $uploadFileDir = '../file_upload/';
                        $dest_path = $uploadFileDir . $fileName . ".".$fileExtension;

                        move_uploaded_file($fileTmpPath, $dest_path);
                    }
                    else
                    {
                        echo json_encode(['status' => '0', 'msg' => 'File is too large. Maximum file size is 2 MB.', 'type'=>'File']);
                        exit;
                    }
                }
            }*/

            if (isset($_SESSION['loginData']['level']) && ($_SESSION['loginData']['level'] == 1 || $_SESSION['loginData']['level'] == 2)) {
                $arr = explode("|", $_POST['country_pic']);
                if ($arr) {
                    $country_id = $arr[0];
                    $assign_pic = $arr[1];
                }
            }

            if ($_SESSION['loginData']['level'] == 1 || $_SESSION['loginData']['level'] == 2) {
                $data = array(
                    'type' => $_POST['type'],
                    'about' => $_POST['about'],
                    'subject' => addslashes(urldecode($_POST['subject'])),
                    'detail' => addslashes($_POST['detail']),
                    'file' => $uploadedFilesStr,
                    'fk_admin_user' => $r['id'],
                    'created_date' => date('Y-m-d H:i:s'),
                    'modify_date' => date('Y-m-d H:i:s'),
                    'country_pic' => $country_id,
                    'assign_pic' => $assign_pic,
                    'assign_to' => $r['id'],
                    'last_response_by' => 'Admin'
                );
            } else {
                $data = array(
                    'type' => $_POST['type'],
                    'about' => $_POST['about'],
                    'subject' => addslashes(urldecode($_POST['subject'])),
                    'detail' => addslashes($_POST['detail']),
                    'file' => $uploadedFilesStr,
                    'fk_admin_user' => $r['id'],
                    'country_pic' => $_SESSION['loginData']['country'],
                    'created_date' => date('Y-m-d H:i:s'),
                    'modify_date' => date('Y-m-d H:i:s'),
                    'assign_pic' => $r['id'],
                    'last_response_by' => 'PIC'
                );
            }

            $result = tep_insert_n_update($data, "ticket", 'INSERT', '', false, true);
            $new_id = tep_db_insert_id();

            //Generate Ticket No
            generateTicketNo($new_id);


            if ($result) {
                //Send email to country pic.
                if ($_SESSION['loginData']['level'] == 1 || $_SESSION['loginData']['level'] == 2) {
                    $sql = "SELECT email FROM admin_user WHERE id = '" . $assign_pic . "'";
                    if ($r = tep_db_single_row($sql))
                        $email = $r['email'];
                } else {
                    //Send email to all super admin & admin.
                    $sql = "SELECT email FROM admin_user WHERE level IN (1,2) AND status = 'Active'";
                    $query = tep_db_query($sql);
                    if (tep_db_num_rows($query) > 0) {
                        $email = "";
                        while ($r = tep_db_fetch_assoc($query)) {
                            if ($email === '')
                                $email .= $r['email'];
                            else
                                $email .= ',' . $r['email'];
                        }
                    }
                }

                //Generate Email
                generateEmail($email, $new_id, $validFiles, 1);

                echo json_encode(['status' => '1', 'msg' => 'New ticket has been created successfully!']);
                exit;
            } else {
                echo json_encode(['status' => '0', 'msg' => 'Database Connection Error.']);
                exit;
            }
        } else {
            echo json_encode(['status' => '0', 'msg' => 'Record not found.']);
            exit;
        }
    }
}

//Generate HTML Message Box
if (isset($_POST['func']) && $_POST['func'] == 'getResponse') {
    $data = array();

    $id = $_POST['id'];
    $html = "";
    $showClosedButton = 0;

    $sql = " SELECT a.id as ticket_id, a.type, a.about, a.subject, a.detail, a.fk_admin_user, ";
    $sql .= " a.created_date, a.closed_date, a.file, a.status, a.request_close, b.name, b.level, c.name as country_name, ";
    $sql .= " CASE WHEN b.level = 1 THEN 'Super Admin' WHEN b.level = 2 THEN 'Admin' ELSE 'Country Manager' END AS title ";
    $sql .= " FROM ticket a  ";
    $sql .= " INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
    $sql .= " LEFT JOIN countries c ON b.fk_countries = c.id ";
    $sql .= " WHERE a.id = '" . $id . "' ";

    if ($r = tep_db_single_row($sql)) {
        if (isset($_SESSION['loginData']['id']) && $_SESSION['loginData']['id'] == $r['fk_admin_user'])
            $showClosedButton = 1;

        $data = array(
            'id' => $id,
            'name' => "ID: " . $r['name'],
            'level' => "Level: " . $r['title'] . " ( " . $r['country_name'] . " )",
            'category' => "Categoy: " . $r['type'],
            'subject' => $r['subject'],
            'ticket_status' => $r['status'],
            'accepted_date' => "Accepted Date: " . date('d-m-Y H:i:s A', strtotime($r['created_date'])),
            'closed_date' => $r['closed_date'] != '' && $r['closed_date'] != null ? "Closed Date: " . date('d-m-Y H:i:s A', strtotime($r['closed_date'])) : "Closed Date: -",
            'btn_closed_ticket' => $showClosedButton,
            'request_close' => $r['request_close']
        );

        $startDate = date('d-m-Y H:i:s A', strtotime($r['created_date']));

        $sql2 = "SELECT a.id, a.response, a.created_date, a.file, b.name, b.level FROM ticket_response a ";
        $sql2 .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
        $sql2 .= "WHERE a.fk_ticket = '" . $id . "' ";
        $sql2 .= "ORDER BY a.created_date DESC ";
        $query = tep_db_query($sql2);
        if (tep_db_num_rows($query) > 0) {
            while ($r2 = tep_db_fetch_assoc($query)) {
                $responseDate = date('d-m-Y H:i:s A', strtotime($r2['created_date']));
                $responseFile = "file_upload/" . $r2['file'];

                if ($r2['level'] == 3)
                    $html .= "<div class=\"msg mymsg\">";
                else
                    $html .= "<div class=\"msg\">";

                $html .= "<div class=\"d-flex justify-content-between align-items-center\">";
                $html .= "<b>" . $r2['name'] . "</b>";
                $html .= "<b class=\"grey\">" . $responseDate . "</b>";
                $html .= "</div>";
                //$html .= "<p>".$r2['response']."</p>";
                $html .= nl2br(urldecode($r2['response']));

                if ($r2['file'] != "") {
                    $ticket2 = explode("|", $r2['file']);
                    //$count2 = count($ticket2);

                    //$html .= "<div class=\"pt-2\">";
                    //$html .= "<a class=\"d-flex align-items-center text-decoration-none fs-5\" data-bs-toggle=\"collapse\" href=\"#collapseAttach_B".$r2['id']."\" role=\"button\" aria-expanded=\"false\" aria-controls=\"collapseAttach_B".$r2['id']."\">";
                    //$html .= "<span class=\"material-symbols-outlined w-auto h-auto fs-4 lightblue\">attach_file</span> Attachment (".$count2.")";
                    //$html .= "</a>";
                    //$html .= "<div class=\"collapse\" id=\"collapseAttach_B".$r2['id']."\">";

                    $html .= "<div class=\"d-flex justify-content-start flex-wrap flex-row\">";
                    foreach ($ticket2 as $val) {
                        $file = "file_upload/" . $val;
                        $html .= "<a class=\"d-flex align-items-center text-decoration-none fs-5 me-2 mt-2\" href=\"javascript:void(0);\" onclick=\"window.open('$file', '_blank', 'width=800,height=600');\">";
                        $html .= "<span class=\"material-symbols-outlined w-auto h-auto fs-4 lightblue\">attach_file</span> " . $val;
                        $html .= "</a>";
                    }
                    $html .= "</div>";

                    //$html .= "</div>";
                    //$html .= "</div>";
                    //$html .= "<div class=\"icon-attach pe-1 pb-1\">";
                    //$html .= "<a href=\"javascript:void(0);\" onclick=\"window.open('$responseFile', '_blank', 'width=800,height=600');\" class=\"d-flex align-items-center text-decoration-none\"><span class=\"material-symbols-outlined w-auto h-auto fs-4 lightblue\">attach_file</span> Attachment</a>";
                    //$html .= "</div>";
                }

                //$html .= "</div>";
                $html .= "</div>";
            }
        }

        if ($r['level'] == 3)
            $html .= "<div class=\"msg mymsg\">";
        else
            $html .= "<div class=\"msg\">";

        $html .= "<div class=\"d-flex justify-content-between align-items-center\">";
        $html .= "<b>" . $r['name'] . "</b>";
        $html .= "<b class=\"grey\">" . $startDate . "</b>";
        $html .= "</div>";
        //$html .= "<p>".$r['detail']."</p>";
        $html .= "<p>" . nl2br(urldecode($r['detail'])) . "</p>";

        if ($r['file'] != "") {
            $ticket1 = explode("|", $r['file']);
            //$count1 = count($ticket1);

            //$html .= "<div class=\"pt-2\">";
            //$html .= "<a class=\"d-flex align-items-center text-decoration-none fs-5\" data-bs-toggle=\"collapse\" href=\"#collapseAttach_A".$r['ticket_id']."\" role=\"button\" aria-expanded=\"false\" aria-controls=\"collapseAttach_A".$r['ticket_id']."\">";
            //$html .= "<span class=\"material-symbols-outlined w-auto h-auto fs-4 lightblue\">attach_file</span> Attachment (".$count1.")";
            //$html .= "</a>";
            //$html .= "<div class=\"collapse\" id=\"collapseAttach_A".$r['ticket_id']."\">";

            $html .= "<div class=\"d-flex justify-content-start flex-wrap flex-row\">";
            foreach ($ticket1 as $val) {
                $file = "file_upload/" . $val;
                $html .= "<a class=\"d-flex align-items-center text-decoration-none fs-5 me-2 mt-2\" href=\"javascript:void(0);\" onclick=\"window.open('$file', '_blank', 'width=800,height=600');\">";
                $html .= "<span class=\"material-symbols-outlined w-auto h-auto fs-4 lightblue\">attach_file</span> " . $val;
                $html .= "</a>";
            }
            $html .= "</div>";

            //$html .= "</div>";
            //$html .= "</div>";
            //$file = "file_upload/".$r['file'];

            /*$html .= "<div class=\"icon-attach pe-1 pb-1\">";
            $html .= "<a href=\"javascript:void(0);\" onclick=\"window.open('$file', '_blank', 'width=800,height=600');\" class=\"d-flex align-items-center text-decoration-none\"><span class=\"material-symbols-outlined w-auto h-auto fs-4 lightblue\">attach_file</span> Attachment</a>";
            $html .= "</div>";*/
        }

        $html .= "</div>";
    }

    echo json_encode(['status' => '1', 'html' => $html, 'data' => $data]);
    exit;

}

if (isset($_POST['func']) && $_POST['func'] == 'getResponse1') {
    $data = array();

    $id = $_POST['id'];
    $html = "";
    $showClosedButton = 0;

    $sql = " SELECT a.id as ticket_id, a.type, a.about, a.subject, a.detail, a.fk_admin_user, ";
    $sql .= " a.created_date, a.closed_date, a.file, a.status, a.request_close, b.name, b.level, c.name as country_name, ";
    $sql .= " CASE WHEN b.level = 1 THEN 'Super Admin' WHEN b.level = 2 THEN 'Admin' ELSE 'Country Manager' END AS title ";
    $sql .= " FROM ticket a  ";
    $sql .= " INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
    $sql .= " LEFT JOIN countries c ON b.fk_countries = c.id ";
    $sql .= " WHERE a.id = '" . $id . "' ";

    if ($r = tep_db_single_row($sql)) {
        if (isset($_SESSION['loginData']['id']) && $_SESSION['loginData']['id'] == $r['fk_admin_user'])
            $showClosedButton = 1;

        $data = array(
            'id' => $id,
            'name' => "ID: " . $r['name'],
            'level' => "Level: " . $r['title'] . " ( " . $r['country_name'] . " )",
            'category' => "Categoy: " . $r['type'],
            'subject' => $r['subject'],
            'ticket_status' => $r['status'],
            'accepted_date' => "Accepted Date: " . date('d-m-Y H:i:s A', strtotime($r['created_date'])),
            'closed_date' => $r['closed_date'] != '' && $r['closed_date'] != null ? "Closed Date: " . date('d-m-Y H:i:s A', strtotime($r['closed_date'])) : "Closed Date: -",
            'btn_closed_ticket' => $showClosedButton,
            'request_close' => $r['request_close']
        );

        $startDate = date('d-m-Y H:i:s A', strtotime($r['created_date']));

        $sql2 = "SELECT a.id, a.response, a.created_date, a.file, b.name, b.level FROM ticket_response a ";
        $sql2 .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
        $sql2 .= "WHERE a.fk_ticket = '" . $id . "' ";
        $sql2 .= "ORDER BY a.created_date DESC ";
        $query = tep_db_query($sql2);
        if (tep_db_num_rows($query) > 0) {
            while ($r2 = tep_db_fetch_assoc($query)) {
                $responseDate = date('d-m-Y H:i:s A', strtotime($r2['created_date']));
                $responseFile = "file_upload/" . $r2['file'];

                if ($r2['level'] == 3)
                    $html .= "<div class=\"msg mymsg\">";
                else
                    $html .= "<div class=\"msg\">";

                $html .= "<div class=\"d-flex justify-content-between align-items-center\">";
                $html .= "<b>" . $r2['name'] . "</b>";
                $html .= "<b class=\"grey\">" . $responseDate . "</b>";
                $html .= "</div>";
                $html .= nl2br(urldecode($r2['response']));

                if ($r2['file'] != "") {
                    $ticket2 = explode("|", $r2['file']);
                    $html .= "<div class=\"d-flex justify-content-start flex-wrap flex-row\">";
                    foreach ($ticket2 as $val) {
                        $file = "file_upload/" . $val;
                        $html .= "<a class=\"d-flex align-items-center text-decoration-none fs-5 me-2 mt-2\" href=\"javascript:void(0);\" onclick=\"window.open('$file', '_blank', 'width=800,height=600');\">";
                        $html .= "<span class=\"material-symbols-outlined w-auto h-auto fs-4 lightblue\">attach_file</span> " . $val;
                        $html .= "</a>";
                    }
                    $html .= "</div>";
                }
                $html .= "</div>";
            }
        }

        if ($r['level'] == 3)
            $html .= "<div class=\"msg mymsg\">";
        else
            $html .= "<div class=\"msg\">";

        $html .= "<div class=\"d-flex justify-content-between align-items-center\">";
        $html .= "<b>" . $r['name'] . "</b>";
        $html .= "<b class=\"grey\">" . $startDate . "</b>";
        $html .= "</div>";
        $html .= "<p>" . nl2br(urldecode($r['detail'])) . "</p>";

        if ($r['file'] != "") {
            $ticket1 = explode("|", $r['file']);

            $html .= "<div class=\"d-flex justify-content-start flex-wrap flex-row\">";
            foreach ($ticket1 as $val) {
                $file = "file_upload/" . $val;
                $html .= "<a class=\"d-flex align-items-center text-decoration-none fs-5 me-2 mt-2\" href=\"javascript:void(0);\" onclick=\"window.open('$file', '_blank', 'width=800,height=600');\">";
                $html .= "<span class=\"material-symbols-outlined w-auto h-auto fs-4 lightblue\">attach_file</span> " . $val;
                $html .= "</a>";
            }
            $html .= "</div>";
        }

        $html .= "</div>";
    }

    echo json_encode(['status' => '1', 'html' => $html, 'data' => $data]);
    exit;
    /*$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    // Example DB connection using $db from application_top.php
    $sql = "SELECT * FROM ticket WHERE id = $id";
    if ($ticket = tep_db_single_row($sql)) 
    {
        $accepted_date = date('d-m-Y H:i A', strtotime($ticket['created_date']));
        $closed_date = $ticket['closed_date'] ? date('d-m-Y H:i A', strtotime($ticket['closed_date'])) : '-';

        // ✅ Build the HTML to insert inside the child row
        $html = '
        <div class="ticket-details p-3 bg-light border rounded">
            <div><strong>Subject:</strong> ' . htmlspecialchars($ticket['subject']) . '</div>
            <div><strong>Category:</strong> ' . htmlspecialchars($ticket['type']) . '</div>
            <div><strong>Status:</strong> ' . htmlspecialchars($ticket['status']) . '</div>
            <div><strong>Created:</strong> ' . $accepted_date . '</div>
            <div><strong>Closed:</strong> ' . $closed_date . '</div>
            <div class="mt-2"><strong>Description:</strong><br>' . nl2br(urldecode($ticket['detail'])) . '</div>
        </div>';

        $response = [
            'status' => '1',
            'data' => $ticket,
            'html' => $html
        ];
    }
    echo json_encode($response);*/

}

//Add New Response.
if (isset($_POST['func']) && $_POST['func'] == 'addNewResponse') {
    $id = $_POST['id'];
    $dest_path = "";
    $uploadedFilesStr = "";
    $validFiles = [];

    if (isset($_POST['response']) && $_POST['response'] == "") {
        echo json_encode(['status' => '0', 'msg' => "Please key in response.", 'type' => 'Response']);
        exit;
    } else {
        $data = array();
        $sql = "SELECT * FROM admin_user WHERE id = '" . $_SESSION['loginData']['id'] . "'";
        if ($r = tep_db_single_row($sql)) {
            /*if (isset($_FILES['file']) && $_FILES['file'] != "")
            {
                $maxFileSize = 2 * 1024 * 1024; //(2MB)
                $fileTmpPath = $_FILES['file']['tmp_name'];
                $fileName = $_FILES['file']['name'];
                $fileSize = $_FILES['file']['size'];
                $fileType = $_FILES['file']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $fileName = date('YmdHis');

                $allowedExts = array('jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt');

                if ($fileSize > $maxFileSize) 
                {
                    echo json_encode(['status' => '0', 'msg' => 'File is too large. Maximum file size is 2 MB.', 'type'=>'File']);
                    exit;
                }
                else
                {
                    if (in_array($fileExtension, $allowedExts)) 
                    {
                        $uploadFileDir = '../file_upload/';
                        $dest_path = $uploadFileDir . $fileName . ".".$fileExtension;

                        move_uploaded_file($fileTmpPath, $dest_path);
                    }
                    else
                    {
                        echo json_encode(['status' => '0', 'msg' => 'File is too large. Maximum file size is 2 MB.', 'type'=>'File']);
                        exit;
                    }
                }
            }*/

            if (isset($_FILES['file']) && !empty($_FILES['file']['name'][0])) {
                $maxFileSize = 25 * 1024 * 1024; // 25MB
                $uploadFileDir = '../file_upload/';
                $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt', 'zip'];

                // Step 1: Validate all files first
                foreach ($_FILES['file']['name'] as $key => $fileName) {
                    $fileTmpPath = $_FILES['file']['tmp_name'][$key];
                    $fileSize = $_FILES['file']['size'][$key];
                    $fileType = $_FILES['file']['type'][$key];

                    // Extract file extension
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));

                    // Generate a unique file name
                    $newFileName = date('YmdHis') . "_" . uniqid() . "." . $fileExtension;

                    // Validate file size
                    if ($fileSize > $maxFileSize) {
                        echo json_encode(['status' => '0', 'msg' => "File is too large: $fileName (Max 25MB).", 'type' => 'File']);
                        exit;
                    }

                    // Validate file extension
                    if (!in_array($fileExtension, $allowedExts)) {
                        echo json_encode(['status' => '0', 'msg' => "Invalid file type: $fileName", 'type' => 'File']);
                        exit;
                    }

                    // If valid, store file info in array
                    $validFiles[] = [
                        'tmpPath' => $fileTmpPath,
                        'destPath' => $uploadFileDir . $newFileName,
                        'fileName' => $fileName,
                        'newFileName' => $newFileName,
                    ];
                }

                // Step 2: Move all validated files to the server
                foreach ($validFiles as $file) {
                    if (move_uploaded_file($file['tmpPath'], $file['destPath'])) {
                        $uploadedFiles[] = $file['newFileName'];
                    } else {
                        echo json_encode(['status' => '0', 'msg' => "Failed to upload: " . $file['fileName']]);
                        exit;
                    }
                }
            }

            if (!empty($uploadedFiles)) {
                // Convert array to comma-separated string
                $uploadedFilesStr = implode("|", $uploadedFiles);
            }

            $data = array(
                'response' => addslashes($_POST['response']),
                //'file' => isset($dest_path) && $dest_path != "" ? $fileName . ".".$fileExtension : "",
                'file' => $uploadedFilesStr,
                'fk_ticket' => $id,
                'fk_admin_user' => $r['id'],
                'created_date' => date('Y-m-d H:i:s')
            );

            $result = tep_insert_n_update($data, "ticket_response", 'INSERT', '', false, true);
            $new_id = tep_db_insert_id();


            if ($result) {
                $data = array();
                if ($_SESSION['loginData']['level'] == 1 || $_SESSION['loginData']['level'] == 2) {
                    $sql = "SELECT b.id, b.assign_to, b.assign_pic FROM ticket_response a ";
                    $sql .= "INNER JOIN ticket b ON b.id = a.fk_ticket ";
                    $sql .= "WHERE a.id = '" . $new_id . "' ";
                    if ($r = tep_db_single_row($sql)) {
                        //Update Super Admin and Admin ID
                        $data = array(
                            'assign_to' => $_SESSION['loginData']['id'],
                            'last_response_by' => 'Admin'
                        );
                        tep_insert_n_update($data, "ticket", 'UPDATE', array("id" => $r['id']), false, true);
                        //End Update

                        //Send Email to PIC
                        $sql2 = "SELECT * FROM admin_user WHERE id = '" . $r['assign_pic'] . "'";
                        if ($r2 = tep_db_single_row($sql2))
                            $email = $r2['email'];
                        //End Send Email
                    }
                } else {
                    $sql = "SELECT b.id, b.assign_to, b.assign_pic FROM ticket_response a ";
                    $sql .= "INNER JOIN ticket b ON b.id = a.fk_ticket ";
                    $sql .= "WHERE a.id = '" . $new_id . "' ";

                    if ($r = tep_db_single_row($sql)) {
                        //Update PIC
                        $data = array(
                            'assign_pic' => $_SESSION['loginData']['id'],
                            'last_response_by' => 'PIC'
                        );
                        tep_insert_n_update($data, "ticket", 'UPDATE', array("id" => $r['id']), false, true);
                        //End Update

                        if ($r['assign_to'] == 0) {
                            //Send email to all super admin & admin.
                            $sql2 = "SELECT * FROM admin_user WHERE level IN (1,2) AND status = 'Active'";
                            $query = tep_db_query($sql2);
                            if (tep_db_num_rows($query) > 0) {
                                $email = "";
                                while ($r2 = tep_db_fetch_assoc($query)) {
                                    if ($email === '')
                                        $email .= $r2['email'];
                                    else
                                        $email .= ',' . $r2['email'];
                                }
                            }
                            //End Send Email
                        } else {
                            $sql = "SELECT * FROM admin_user WHERE id = '" . $r['assign_to'] . "'";
                            if ($r = tep_db_single_row($sql))
                                $email = $r['email'];
                        }
                    }
                }

                //Generate Email
                generateEmail($email, $new_id, $validFiles, 2);

                echo json_encode(['status' => '1', 'msg' => 'Response has been submit!', 'id' => $id]);
                exit;
            } else {
                echo json_encode(['status' => '0', 'msg' => 'Database Connection Error.']);
                exit;
            }
        } else {
            echo json_encode(['status' => '0', 'msg' => 'Record not found.']);
            exit;
        }
    }
}

//Rate Ticket
if (isset($_POST['func']) && $_POST['func'] == 'rateTicket') {
    $id = $_POST['id'];
    $rating = 1;

    if ($_POST['rating'] != '') {
        if ($_POST['rating'] >= 1 && $_POST['rating'] <= 5) {
            $rating = $_POST['rating'];
        }
    }

    $data = array();
    $sql = "SELECT * FROM admin_user WHERE id = '" . $_SESSION['loginData']['id'] . "'";
    if ($r = tep_db_single_row($sql)) {

        $sql2 = " SELECT *  FROM ticket WHERE id  ='" . $id . "'";
        if ($r2 = tep_db_single_row($sql2)) {
            $data = array(
                'rating' => $rating,
                'status' => 'Completed',
                'closed_date' => date('Y-m-d H:i:s')
            );
        }

        $result = tep_insert_n_update($data, "ticket", 'UPDATE', array('id' => $id), false, true);

        if ($result) {
            echo json_encode(['status' => '1', 'msg' => 'Rating has been submit!', 'id' => $id]);
            exit;
        } else {
            echo json_encode(['status' => '0', 'msg' => 'Database Connection Error.']);
            exit;
        }
    }
}

//Reopen Ticket
if (isset($_POST['func']) && $_POST['func'] == 'updateTicketStatus') {
    $data = array();
    $id = $_POST['id'];

    $sql = "SELECT * FROM admin_user WHERE id = '" . $_SESSION['loginData']['id'] . "'";
    if ($r = tep_db_single_row($sql)) {
        $sql2 = " SELECT *  FROM ticket WHERE id  ='" . $id . "' AND fk_admin_user = '" . $_SESSION['loginData']['id'] . "'";
        if ($r2 = tep_db_single_row($sql2)) {
            $data = array(
                'rating' => 0,
                'status' => 'In Progress',
                'modify_date' => date('Y-m-d H:i:s')
            );

            $result = tep_insert_n_update($data, "ticket", 'UPDATE', array('id' => $id), false, true);

            if ($result) {
                echo json_encode(['status' => '1', 'msg' => 'Ticket has been re-open!', 'id' => $id]);
                exit;
            } else {
                echo json_encode(['status' => '0', 'msg' => 'Database Connection Error.']);
                exit;
            }
        }
    }
}

//
if (isset($_POST['func']) && $_POST['func'] == 'requestCloseTicket') {
    $id = $_POST['id'];
    $request = $_POST['request'];

    $sql = "SELECT * FROM admin_user WHERE id = '" . $_SESSION['loginData']['id'] . "'";
    if ($r = tep_db_single_row($sql)) {
        $data = array(
            'request_close' => $request
        );

        $result = tep_insert_n_update($data, "ticket", 'UPDATE', array('id' => $id), false, true);

        if ($result) {
            echo json_encode(['status' => '1']);
            exit;
        } else {
            echo json_encode(['status' => '0']);
            exit;
        }
    }
}

function generateEmail($email, $id, $attachment, $type)
{
    //New Ticket Email
    if ($type == 1) {
        $sql = "SELECT a.id, a.ticket_no, a.subject, a.detail as response, b.name FROM ticket a ";
        $sql .= "INNER JOIN admin_user b ON a.assign_pic = b.id ";
        $sql .= "WHERE a.id = '" . $id . "'";
        $data = tep_db_single_row($sql);

        /*if($_SESSION['loginData']['level'] != 3)
            $subject = "DXN Ticketing System New Ticket - ".$_SESSION['loginData']['name']." (Admin) ";
        else
            $subject = "DXN Ticketing System New Ticket - ".$_SESSION['loginData']['name']." (".$_SESSION['loginData']['country_name'].") ";*/
    } else {
        //Response Email
        $sql = "SELECT a.id, a.ticket_no, a.subject, b.response, c.name FROM ticket a ";
        $sql .= "INNER JOIN ticket_response b ON b.fk_ticket = a.id ";
        $sql .= "INNER JOIN admin_user c ON a.assign_pic = c.id ";
        $sql .= "WHERE b.id = '" . $id . "' ";
        $sql .= "ORDER BY b.id DESC ";
        $sql .= "LIMIT 1";
        $data = tep_db_single_row($sql);

        /*if($_SESSION['loginData']['level'] != 3)
            $subject = "DXN Ticketing System Responded - ".$_SESSION['loginData']['name']." (Admin) ";
        else
            $subject = "DXN Ticketing System Responded - ".$_SESSION['loginData']['name']." (".$_SESSION['loginData']['country_name'].") ";*/
    }
    $history_content = "";
    $subject = $data['ticket_no'] . " - " . $data['subject'];
    $to = $email;
    $from = "noreply@dxnnwn.com";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@dxnnwn.com" . "\r\n";
    $template = file_get_contents('../email/ticket_notification.htm'); // Load the template file
    if ($type == 2)
        $history_content = generateTicketingResponseContent($data['id']);
    $todayDate = date("d-m-Y H:i:s");
    $emailContent = str_replace(
        ['{AssignName}', '{FromName}', '{Date}', '{Subject}', '{Request}', '{Content}'],
        [$data['name'], $_SESSION['loginData']['name'], $todayDate, $data['subject'], urldecode($data['response']), $history_content],
        $template
    );
    tep_email($to, $from, $subject, $emailContent, $headers, $attachment);
}

function generateTicketNo($id)
{
    $ticket_no = "DXN" . str_pad($id, 5, '0', STR_PAD_LEFT);

    $data = array(
        'ticket_no' => $ticket_no
    );

    tep_insert_n_update($data, "ticket", 'UPDATE', array('id' => $id), false, true);
}

function generateTicketingResponseContent($id)
{
    $html = "";
    $sql = "SELECT * ";
    $sql .= "FROM ( ";
    $sql .= "SELECT a.created_date, a.response, b.name, ";
    $sql .= "ROW_NUMBER() OVER (ORDER BY a.created_date DESC) AS row_num ";
    $sql .= "FROM ticket_response a ";
    $sql .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
    $sql .= "WHERE a.fk_ticket = '" . $id . "' ORDER BY a.created_date DESC ";
    $sql .= ") AS numbered_rows ";
    $sql .= "WHERE row_num > 1 ";
    $query = tep_db_query($sql);


    $html .= "<div style=\"background-color: #f3f3f3; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px;\">";
    $html .= "Ticketing History Response";
    $html .= "<br/><br/>";
    $html .= "<font color=\"#999999\">";
    if (tep_db_num_rows($query) > 0) {
        while ($r = tep_db_fetch_assoc($query)) {
            $newDate = date("d-m-Y H:i:s", strtotime($r['created_date']));
            $html .= "From: " . $r['name'] . " (" . $newDate . ")<br/>";
            $html .= urldecode($r['response']) . "<br/><br/>";
        }
    }
    $sql2 = "SELECT a.created_date, a.detail, b.name FROM ticket a ";
    $sql2 .= "INNER JOIN admin_user b ON a.fk_admin_user = b.id ";
    $sql2 .= "WHERE a.id = '" . $id . "' ";
    if ($r2 = tep_db_single_row($sql2)) {
        $newDate2 = date("d-m-Y H:i:s", strtotime($r2['created_date']));
        $html .= "From: " . $r2['name'] . " (" . $newDate2 . ")<br/>";
        $html .= urldecode($r2['detail']) . "<br/><br/>";
    }
    $html .= "</font>";
    $html .= "</div>";
    return $html;
}
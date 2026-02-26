<?php
require('../application_top.php');
include_once "../includes/functions/general.php";
tep_db_connect();

if(isset($_POST['func']) && $_POST['func'] == 'addNewChecklist')
{
    if(isset($_POST['project_type']) && $_POST['project_type'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please select project type.", 'type'=>'Project']);
        exit;
    }
    else if(isset($_POST['name']) && $_POST['name'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please key in item.", 'type'=>'Item']);
        exit;
    }
    else if(isset($_POST['pic']) && $_POST['pic'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please select country pic.", 'type'=>'PIC']);
        exit;
    }
    else if(isset($_POST['comment']) && $_POST['comment'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please key in comment.", 'type'=>'Comment']);
        exit;
    }
    else
    {
        $data = array();

        $sql = "SELECT * FROM admin_user WHERE id = '".$_SESSION['loginData']['id']."'";
        if($r = tep_db_single_row($sql))
        {
            $pic = $_POST['pic'];
            $country_pic = getCountryID($pic);

            if (isset($_FILES['file']) && $_FILES['file'] != "")
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
            }

            $data = array(
                'name' => urldecode($_POST['name']),
                'pic' => $pic,
                'country_pic' => $country_pic,
                'comment' => urldecode($_POST['comment']),
                'checklist_type' => $_POST['checklist_type'],
                'project_type' => $_POST['project_type'],
                'file' => isset($dest_path) && $dest_path != "" ? $fileName . ".".$fileExtension : "",
                'fk_admin_user' => $r['id'],
                'created_by' => $_SESSION['loginData']['name'],
                'created_date' => date('Y-m-d H:i:s'),
                'status' => 'To Do'
            );

            $result = tep_insert_n_update($data, "checklist", 'INSERT', '', false, true);
           

            if($result)
            {
                echo json_encode(['status' => '1', 'msg' => 'Deployment checklist has been created successfully!']);
                exit;
            }
            else
            {
                echo json_encode(['status' => '0', 'msg' => 'Failed to add or update function.', 'type'=>'Error']);
                exit;
            }
        }
        else
        {
            echo json_encode(['status' => '0', 'msg' => 'Record not found.', 'type'=>'Error']);
            exit;
        }
    }
}

if(isset($_POST['func']) && $_POST['func'] == 'editChecklist')
{
    if(isset($_POST['project_type']) && $_POST['project_type'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please select project type.", 'type'=>'Project']);
        exit;
    }
    else if(isset($_POST['name']) && $_POST['name'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please key in item.", 'type'=>'Item']);
        exit;
    }
    else if(isset($_POST['pic']) && $_POST['pic'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please select country pic.", 'type'=>'PIC']);
        exit;
    }
    else if(isset($_POST['comment']) && $_POST['comment'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please key in comment.", 'type'=>'Comment']);
        exit;
    }
    else
    {
        $data = array();

        $sql = "SELECT * FROM admin_user WHERE id = '".$_SESSION['loginData']['id']."'";
        if($r = tep_db_single_row($sql))
        {
            $pic = $_POST['pic'];
            $country_pic = getCountryID($pic);

            if (isset($_FILES['file']) && $_FILES['file'] != "")
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

                        //Remove previous file attachment.
                        $sql2 = "SELECT * FROM checklist WHERE id = '".$_POST['id']."'";
                        if($r2 = tep_db_single_row($sql2))
                        {
                            if($r2['file']!= '')
                                $file = '../file_upload/'.$r2['file'];
                        
                            //Delete the file if exists.
                            if (file_exists($file))
                                unlink($file);
                        }
                    }
                    else
                    {
                        echo json_encode(['status' => '0', 'msg' => 'File is too large. Maximum file size is 2 MB.', 'type'=>'File']);
                        exit;
                    }
                }

                $data = array(
                    'name' => urldecode($_POST['name']),
                    'pic' => $pic,
                    'country_pic' => $country_pic,
                    'comment' => urldecode($_POST['comment']),
                    'checklist_type' => $_POST['checklist_type'],
                    'project_type' => $_POST['project_type'],
                    'file' => isset($dest_path) && $dest_path != "" ? $fileName . ".".$fileExtension : "",
                    'fk_admin_user' => $r['id'],
                    'modify_by' => $_SESSION['loginData']['name'],
                    'modify_date' => date('Y-m-d H:i:s'),
                    'status' => 'To Do'
                );
            }
            else
            {
                $data = array(
                    'name' => urldecode($_POST['name']),
                    'pic' => $pic,
                    'country_pic' => $country_pic,
                    'comment' => urldecode($_POST['comment']),
                    'checklist_type' => $_POST['checklist_type'],
                    'project_type' => $_POST['project_type'],
                    'fk_admin_user' => $r['id'],
                    'modify_by' => $_SESSION['loginData']['name'],
                    'modify_date' => date('Y-m-d H:i:s'),
                    'status' => 'To Do'
                );
            }
        }
        
        $result = tep_insert_n_update($data, "checklist", 'UPDATE', array("id"=> $_POST['id']), false, true);           

        if($result)
        {
            echo json_encode(['status' => '1', 'msg' => 'Deployment checklist has been updated successfully!']);
            exit;
        }
        else
        {
            echo json_encode(['status' => '0', 'msg' => 'Failed to update function.', 'type'=>'Error']);
            exit;
        }
    }
}

//Get checklist info to display in edit form
if(isset($_POST['func']) && $_POST['func'] == 'getChecklistInfo')
{
    $data = array();

    $sql = "SELECT * FROM checklist WHERE id = '".$_POST['id']."'";
    if($r = tep_db_single_row($sql))
    {
        $data = array(
            'id' => $r['id'],
            'name' => $r['name'],
            'pic' => $r['pic'],
            'project' => $r['project_type'],
            'checklist_type' => $r['checklist_type'],
            'comment' => $r['comment']
        );

        echo json_encode(['status' => '1', 'data' => $data]);
        exit;
    }
    else
    {
        echo json_encode(['status' => '0', 'msg' => 'Record not found.', 'type'=>'Error']);
        exit;
    }
}

//Delete Checklist
if(isset($_POST['func']) && $_POST['func'] == 'deleteChecklist')
{
    $file = "";
    $sql = "SELECT * FROM admin_user WHERE id = '".$_SESSION['loginData']['id']."'";
    if($r = tep_db_single_row($sql))
    {
        $sql2 = "SELECT * FROM checklist WHERE id = '".$_POST['id']."'";
        if($r2 = tep_db_single_row($sql2))
        {
            if($r2['file'] != '')
            {
                $file = '../file_upload/'.$r2['file'];
                
                //Delete the file if exists.
                if (file_exists($file)) 
                    unlink($file);
            }

            //Delete the record
            $sql2 = "DELETE FROM checklist WHERE id ='".$r2['id']."'";
            $query2 = tep_db_query($sql2);

            echo json_encode(['status' => '1', 'msg' => 'Deployment checklist has been removed successfully.']);
            exit;
        }
        else
        {
            echo json_encode(['status' => '0', 'msg' => 'Failed to delete deployment checklist.', 'type'=>'Error']);
            exit;
        }
    }
    else
    {
        echo json_encode(['status' => '0', 'msg' => 'Record not found.', 'type'=>'Error']);
        exit;
    }
}

//Update checklist status
if(isset($_POST['func']) && $_POST['func'] == 'updateChecklistStatus')
{
    $sql = "SELECT * FROM admin_user WHERE id = '".$_SESSION['loginData']['id']."'";
    if($r = tep_db_single_row($sql))
    {
        $sql2 = "SELECT * FROM checklist WHERE id = '".$_POST['id']."'";
        if($r2 = tep_db_single_row($sql2))
        {

            if($_POST['status'] == 'Completed')
            {
                $data = array(
                    'status' => 'Completed',
                    'modify_by' => $_SESSION['loginData']['name'],
                    'modify_date' => date('Y-m-d H:i:s'),
                    'resolved_date' => date('Y-m-d H:i:s')
                );
            }
            else
            {
                $data = array(
                    'status' => 'To Do',
                    'modify_by' => $_SESSION['loginData']['name'],
                    'modify_date' => date('Y-m-d H:i:s')
                );
            }
            

            $result = tep_insert_n_update($data, "checklist", 'UPDATE', array("id"=> $_POST['id']), false, true);

            if($result)
            {
                echo json_encode(['status' => '1', 'msg' => 'Deployment checklist has been updated successfully.']);
                exit;
            }
            else
            {
                echo json_encode(['status' => '0', 'msg' => 'Database Error.']);
                exit;
            }
        }
        else
        {
            echo json_encode(['status' => '0', 'msg' => 'Record not found.']);
            exit;
        }
    }
    else
    {
        echo json_encode(['status' => '0', 'msg' => 'Record not found.']);
        exit;
    }
}

//Save Session
if(isset($_POST['func']) && $_POST['func'] == 'searchChecklist')
{
    $_SESSION['checklist'] = array(
        "project_type" => $_POST['about'],
        "country" => $_POST['country']
    );

    echo json_encode(['status' => '1']);
    exit;
}

function getCountryID($val)
{
    $sql = "SELECT * FROM admin_user WHERE id = '".$val."'";
    $data = tep_db_single_row($sql);

    return $data['fk_countries'];

}
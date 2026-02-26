<?php
require('../application_top.php');
include_once "../includes/functions/general.php";
tep_db_connect();

//Start Login Process
if(isset($_POST['func']) && $_POST['func'] == 'login')
{
    $sql = "SELECT * FROM admin_user WHERE email = '".tep_real_escape_string($_POST['email'])."' AND status = 'Active' ";
    if($r = tep_db_single_row($sql))
    {
        $_POST['password'] = md5($r['salt'].$_POST['password']);

        if( $_POST['password'] != $r['password'] )
        {
            echo json_encode(['status' => '0', 'msg' => 'Invalid username or password. Please try again.']);
            exit;
        }
        else
        {
            //Country PIC
            if($r['level'] == 3)
            {
                $sql2 = "SELECT name FROM countries WHERE id = '".tep_real_escape_string($r['fk_countries'])."' ";
                $data = tep_db_single_row($sql2);

                $_SESSION['loginData'] = array(
                    "id" => $r['id'],
                    "name" => $r['name'],
                    "email" => $r['email'],
                    "level" => $r['level'],
                    "country" => $r['fk_countries'],
                    "country_name" => $data['name'],
                    "allow_close" => $r['allow_close']
                );
            }
            else
            {
                //Super Admin & Admin
                $_SESSION['loginData'] = array(
                    "id" => $r['id'],
                    "name" => $r['name'],
                    "email" => $r['email'],
                    "level" => $r['level'],
                    "allow_close" => $r['allow_close']
                );
            }

            //update login history
            $now = date('Y-m-d H:i:s'); 
            $sql = "update admin_user set login_history ='".$now."' where id='".$_SESSION['loginData']['id']."'";
            tep_db_query($sql);

            echo json_encode(['status' => '1', 'redirect' => 'index.php']);
            exit;
        }
    }
    else
    {
        echo json_encode(['status' => '0', 'msg' => 'Invalid username or password. Please try again.']);
        exit;
    }
}
//End

//Change Password
if(isset($_POST['func']) && $_POST['func'] == 'changePassword')
{
    //Check password validation 2nd time.
    $result = validatePassword($_POST['password']);

    if ($result === true) 
    {
        $id = $_SESSION['loginData']['id'];
        $sql = "SELECT * from admin_user where id =".$id." LIMIT 1";
        $row = tep_db_single_row($sql);

        if(!tep_db_exist($sql))
        {
            echo json_encode(['status' => '0', 'msg' => 'Update password failed.']);
            exit;
        }
        else
        {
            $salt = $row['salt'];
            
            if( md5($salt.$_POST['currentpassword']) == $row['password'] )
            {
                $data = $_POST;
                $newsalt = generateRandomCode(8);
                $data['salt'] = $newsalt;
                $data['password'] = md5($newsalt.$data['password']);

                unset($data['currentpassword'],$data['confirmpassword'],$data['func']);

                $result = tep_insert_n_update($data, "admin_user", 'UPDATE', array('id' => $id), false, true);
                
                if( $result )
                {
                    echo json_encode(['status' => '1', 'msg' => 'Password has been changed successfully!']);
                    exit;
                }
                else
                {
                    echo json_encode(['status' => '0', 'msg' => 'Database Connection Error.']);
                    exit;
                }
            }
            else
            {
                echo json_encode(['status' => '0', 'msg' => 'Incorrect current password.', 'type'=>'CurrentPassword']);
                exit;
            }
        }
    } 
    else 
    {
        echo json_encode(['status' => '0', 'msg' => $result, 'type'=>'NewPassword']);
        exit;
    }
}
//End

//Add New Admin
if(isset($_POST['func']) && $_POST['func'] == 'addOrUpdateAdmin')
{
    if(isset($_POST['name']) && $_POST['name'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please key in admin name.", 'type'=>'name']);
        exit;
    }
    else if(isset($_POST['email']) && $_POST['email'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please key in email address.", 'type'=>'email']);
        exit;
    }
    else if (!validateEmail($_POST['email']))
    {
        echo json_encode(['status' => '0', 'msg' => "Invalid email address.", 'type'=>'email']);
        exit;
    }
    else if(isset($_POST['status']) && $_POST['status'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please select status.", 'type'=>'status']);
        exit;
    }
    else if(isset($_POST['level']) && $_POST['level'] == "")
    {
        echo json_encode(['status' => '0', 'msg' => "Please select admin level.", 'type'=>'level']);
        exit;
    }
    else
    {
        $_POST['fk_countries'] = ($_POST['level'] == 3) ? $_POST['fk_countries'] : 103; //Super Admin & Admin default Malaysia.

        if(isset($_POST['type']) && $_POST['type'] == "Add")
        {
            //Check to make sure no duplicated email address
            $sql = "SELECT * FROM admin_user WHERE email ='".tep_real_escape_string($_POST['email'])."'";
            if(tep_db_exist($sql))
            {
                echo json_encode(['status' => '0', 'msg' => "Email address already exists.", 'type'=>'email']);
                exit;
            }
            else
            {
                //Generate new password and email address.
                $data = $_POST;
                $newsalt = generateRandomCode(8);
                $generatedPassword = generateRandomPassword();
                $data['salt'] = $newsalt;
                $data['password'] = md5($newsalt.$generatedPassword);
                $data['created_date'] = date('Y-m-d H:i:s');
                $data['realpassword'] = $newsalt.$generatedPassword;

                unset($data['func'], $data['type']);

                $result = tep_insert_n_update($data, "admin_user", 'INSERT', '', false, true);
                $id = tep_db_insert_id();
    
                if( $result )
                {
                    $sql = "SELECT * FROM admin_user WHERE id = '".$id."'";
                    if($r = tep_db_single_row($sql))
                    {
                        //Email to inform user.
                        // Load HTML email template
                        $template = file_get_contents('../email/registration.htm'); // Load the template file

                        // Replace placeholders with actual values
                        $emailContent = str_replace(
                            ['{Name}', '{Email}', '{Password}'],
                            [$r['name'], $r['email'], $generatedPassword],
                            $template
                        );

                        // Email settings
                        //$to = "foo_cheewah@dxn2u.com";
                        $to = $data['email'];
                        $from = "noreply@dxnnwn.com";
                        $subject = "Registration";
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= "From: noreply@dxnnwn.com" . "\r\n";

                        // Send the email
                        tep_reg_email($to, $from, $subject, $emailContent, $headers);

                        //Send email to cc
                        //$cc = ["laszlo@kocso.com", "krisztian@dxnshop.com"];
                        $cc = ["zalikha_osman@dxn2u.com"];
                        tep_reg_email($cc, $from, $subject, $emailContent, $headers);
                    }

                    echo json_encode(['status' => '1', 'msg' => 'Admin user has been created successfully!']);
                    exit;
                }
                else
                {
                    echo json_encode(['status' => '0', 'msg' => 'Database Connection Error.']);
                    exit;
                }
            }
        }
        else
        {
            $data = array();

            //Check to make sure no duplicated email address
            if($_POST['prev_email'] != $_POST['email'])
            {
                $sql = "SELECT * FROM admin_user WHERE email ='".tep_real_escape_string($_POST['email'])."'";
                if(tep_db_exist($sql))
                {
                    echo json_encode(['status' => '0', 'msg' => "Email address already exists.", 'type'=>'email']);
                    exit;
                }
            }

            $data = array(
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'level' => $_POST['level'],
                'fk_countries' => $_POST['fk_countries'],
                'status' => $_POST['status'],
                'modify_date' => date('Y-m-d H:i:s')
            );
            
            $result = tep_insert_n_update($data, "admin_user", 'UPDATE', array('id' => $_POST['id']), false, true);

            if( $result )
            {
                echo json_encode(['status' => '1', 'msg' => 'Admin record has been successfully updated.']);
                exit;
            }
            else
            {
                echo json_encode(['status' => '0', 'msg' => 'Database Connection Error.']);
                exit;
            }
        }
        
    }
}

//Delete Admin update stauts
if(isset($_POST['func']) && $_POST['func'] == 'deleteAdmin')
{
    $sql = "SELECT * FROM admin_user WHERE id ='".tep_real_escape_string($_POST['id'])."'";
    if(tep_db_exist($sql))
    {
        $sql = "UPDATE admin_user SET status='Inactive' WHERE id = '".tep_real_escape_string($_POST['id'])."' ";
        $result = tep_db_query($sql);

        if($result)
        {
            echo json_encode(['status' => '1', 'msg' => 'Admin user has been deleted successfully!']);
            exit;
        }
        else
        {
            echo json_encode(['status' => '0', 'msg' => 'Database Connection Error.']);
            exit;
        }
    }
    else
    {
        echo json_encode(['status' => '0', 'msg' => 'Record not found.']);
        exit;
    }
}


//Start Logout Process
if(isset($_POST['func']) && $_POST['func'] == 'logout')
{
    unset($_SESSION['loginData']);

    $url = HTTP_SERVER.'ticketing/login.php';
    echo json_encode(['status' => '1', 'redirect' => $url]);
    exit;
}
//End

function generateRandomPassword($length = 10) {
    // Define character sets
    $chars = [
        'abcdefghijklmnopqrstuvwxyz', // lowercase letters
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ', // uppercase letters
        '0123456789'/*, // numbers
        '!@#$%^&*()_+-=[]{}|;:,.<>?',*/ // special characters
    ];

    // Initialize password variable
    $password = '';

    // Combine all characters into one string
    $charSet = implode('', $chars);

    // Generate random password
    for ($i = 0; $i < $length; $i++) {
        $randomChar = $charSet[rand(0, strlen($charSet) - 1)];
        $password .= $randomChar;
    }

    return $password;
}

function generateRandomCode($len, $possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV01234567890")
{
    rand(0,time());
    $str = "";
    while(strlen($str)<$len)
    {
        $str.=substr($possible,(rand()%(strlen($possible))),1);
    }

    return $str;
}

function validatePassword($password) {
    // Regular expressions for different criteria
    $hasLower = '/[a-z]/';
    $hasUpper = '/[A-Z]/';
    $hasDigit = '/\d/';
    $hasSpecial = '/[\W_]/';
    $minLength = 8; // Minimum length for the password

    // Check for minimum length
    if (strlen($password) < $minLength) {
        return 'Password must be at least ' . $minLength . ' characters long.';
    }

    // Check for at least one lowercase letter
    if (!preg_match($hasLower, $password)) {
        return 'Password must contain at least one lowercase letter.';
    }

    // Check for at least one uppercase letter
    if (!preg_match($hasUpper, $password)) {
        return 'Password must contain at least one uppercase letter.';
    }

    // Check for at least one digit
    if (!preg_match($hasDigit, $password)) {
        return 'Password must contain at least one digit.';
    }

    // Check for at least one special character
    /*if (!preg_match($hasSpecial, $password)) {
        return 'Password must contain at least one special character.';
    }*/

    return true; // Password is valid
}

function validateEmail($email) {
    // Remove all illegal characters from email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    // Validate e-mail
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true; // Valid email address
    } else {
        return false; // Invalid email address
    }
}
?>
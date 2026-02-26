<?php
date_default_timezone_set("Asia/Kuala_Lumpur");

if (!defined('SUB_FOLDER'))
    require('includes/declare.php');

if (!is_callable('tep_db_connect')) {
    require 'database/database_mysqli.php';
    tep_db_connect() or die("Could Not connect to database");
}

if(isset($_GET['obj']) && isset($_GET['fn']))
{
    include 'action.php';
}
else
{
    header('location:'.HTTP_SERVER.'csts/dashboard.php');
    exit;
}
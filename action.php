<?php

if (!isset($_GET['obj']))
  die("Object is not defined");

if (!defined('SUB_FOLDER'))
    require('includes/declare.php');

if (!function_exists('getBaseURL')) 
{
    require('includes/common.php');
    initBaseURL();
}

date_default_timezone_set('Asia/Kuala_Lumpur');
if (!is_callable('tep_db_connect'))
{
    require 'database/database_mysqli.php';
    tep_db_connect() or die("Could Not connect to database");
    
}

$objName = $_GET['obj'];
$objName = str_replace('.', '/', $objName);
$fn = "run";
if (isset($_GET['fn']))
  $fn = $_GET['fn'];

if (!class_exists($objName,false))
    require('actions/'.$objName.'.php');
$obj = new $objName;
$obj->$fn();
?>
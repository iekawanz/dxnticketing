<?php
define('SUB_FOLDER','');
define('DB_NAME', 'dxnnwnco_dxnticketing');
define('DB_PASSWORD', 'pVNKb#5s#0*h');
//define('DB_HOST', 'localhost');
define('DB_HOST', 'db');
define('DB_USER', 'dxnnwnco_dxnticketinguser');

if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' )
    define('HTTP_PROTOCOL','https');
else
    define('HTTP_PROTOCOL','http');

define('HTTP_SERVER',HTTP_PROTOCOL.'://'.$_SERVER["HTTP_HOST"]."/".SUB_FOLDER);
<?php
if(array_key_exists('pparam', $_GET) && substr($_GET['pparam'],0,2) == 'a/')
{
	$temp = explode('/', $_GET['pparam']);
	if( count($temp) < 3 )
		header("HTTP/1.0 404 Not Found");

	$_GET['obj'] = $temp[1];
	$_GET['fn'] = $temp[2];
	require('action.php');
}
else
{   
	if( isset($_GET['pparam']) || $_GET['pparam'] != '' )
    {
        $temp = explode('/', $_GET['pparam']);
		
        $_GET['tpl'] = $temp[0];
        $_GET['menu'] = 'default';

        if( count($temp )> 1 )
        {
            if( $temp[0] == 'v')
            {
                $_GET['tpl'] = $temp[1];
            }
            else
            {
                $_GET['info'] = $temp[1];
            }
        }
    }
    else
	{
		$_GET['tpl'] = 'home';
		$_GET['menu'] = 'default';
	}
	require('render.php');
}

?>
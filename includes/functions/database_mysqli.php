<?php

function tep_db_connect() {
    global $mysqli;

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
    }  
    
    return  $mysqli;
}

function tep_db_query($db_query) {
    global $mysqli;

    $result= $mysqli->query($db_query);

    return $result;
}

function tep_db_fetch_assoc($db_query) {

    $result = mysqli_fetch_assoc($db_query);

    return $result;
}

function tep_db_single_row( $sql, $assoc = false )
{    
    $query = tep_db_query($sql);
    if( $assoc )
    {
        if( $r = tep_db_fetch_assoc($query))
        {
            return $r; 
        }
        else
        {
            return false;
        }
    }
    else
    {
        if( $r = tep_db_fetch_array($query))
        {
            return $r; 
        }
        else
        {
            return false;
        }			
    } 
}

function tep_db_fetch_array($db_query) {

    $result = mysqli_fetch_array($db_query);

    return $result;
}

function tep_real_escape_string($str)
{
    global $mysqli;
    return $mysqli->real_escape_string( $str );
}

function tep_db_exist($db_query)
{
    $query = tep_db_query($db_query);
    if(tep_db_num_rows($query)) 
        return true;
    else 
        return false;
}

function tep_db_num_rows($db_query) 
{
    $result = mysqli_num_rows($db_query);
    return $result;
}

function tep_insert_n_update($fields,$table, $action, $wheres="",$debug=FALSE, $index_field_name=FALSE)
{
    if($action=='UPDATE')
    {
        $sql = "Update `$table` set ";
        $dxn=0;
        if(!$index_field_name)
        {
            foreach($fields as $f)
            {
                if($dxn!=0) $sql .= ', ';
                if( is_null($f[1]) )
                {
                        $sql .= "`".$f[0]."`=null ";
                }
                else
                {
                        $sql .= "`".$f[0]."`='".$f[1]."'";
                }
                $dxn++;
            }
        }
        else
        {
            foreach($fields as $k=>$v)
            {
                if($dxn!=0) $sql .= ', ';
                
                if(  is_null($v) )
                {
                        $sql .= "`".$k."`=null ";
                }
                else
                {
                        $sql .= "`".$k."`='".$v."'";
                }
                $dxn++;
            }
        }    

        if(is_array($wheres))
        {
            $sql .= " WHERE ";
            $dxn =0;
            if(!$index_field_name)
            {
                foreach($wheres as $w)
                {
                    if($dxn!=0) $sql .= ' and ';
                    
                    if(is_null($w[1]) )
                    {
                            $sql .= "`".$w[0]."`=null ";
                    }
                    else
                    {
                            $sql .= "`".$w[0]."`='".$w[1]."'";
                    }
                
                    $dxn++;            
                }
            }
            else
            {
                foreach($wheres as $k=>$v)
                {
                    if($dxn!=0) $sql .= ' and ';
                    if( is_null($v))
                    {
                        $sql .= "`".$k."`=null ";
                    }
                    else
                    {
                        $sql .= "`".$k."`='".$v."'";
                    }
                    $dxn++;            
                }

            }
        } 
    }
    elseif($action=='INSERT')
    {
        $fieldname_list = "";
        $fieldval_list = "";
        $dxn=0;
        if(!$index_field_name)
        {
                
            foreach($fields as $f)
            {
                if($dxn!=0) 
                {
                    $fieldname_list .= ', ';
                    $fieldval_list .= ', ';
                }
                $fieldname_list .= '`'.$f[0].'`';

                if( is_null($f[1]) )
                {
                        $fieldval_list .= "null";
                }
                else
                {
                        $fieldval_list .= "'".$f[1]."'";
                }
                $dxn++;
            }
        }
        else
        {
            foreach($fields as $k=>$v)
            {
                if($dxn!=0) 
                {
                    $fieldname_list .= ', ';
                    $fieldval_list .= ', ';
                }
                $fieldname_list .= '`'.$k.'`';
                if( is_null($v) )
                {
                        $fieldval_list .= "null";
                }
                else
                {
                        $fieldval_list .= "'".$v."'";
                }
                $dxn++;
            }
        }

        $sql = "Insert into `$table`(".$fieldname_list.") VALUES(".$fieldval_list.")";
    }
    
    if($debug)
    {   
        print $sql.'<br>';
        exit;
    }
    else
        $result = tep_db_query($sql);

    return $result;
}

function tep_db_insert_id() {
	global $mysqli;
    return mysqli_insert_id($mysqli);
}
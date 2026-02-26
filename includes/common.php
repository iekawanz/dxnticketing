<?php

function initBaseURL() {
    global $baseURL;
    
    if ( isset($baseURL) )
        return $baseURL;

    $baseURL = HTTP_SERVER;
    return $baseURL;
}
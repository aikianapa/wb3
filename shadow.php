<?php
/*
host = hostname 
port = port
uri
scheme
headers = headers (cookie + post data)
*/

foreach(@$argv as $arg) {
    $pos = strpos($arg,'=');
    if ($pos) {
        $fld = substr($arg,0,$pos);
        $val = substr($arg,$pos+1);
        $$fld = $val;
    }
}
$headers = json_decode(base64_decode($headers),true);
$_POST = $headers['post'];
$_GET = $headers['get'];
$_COOKIE = $headers['cook'];
wbAuthPostContents("{$scheme}://{$host}{$uri}", $_POST);
?>
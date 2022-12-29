<?php
/*
host = hostname 
port = port
uri
scheme
headers = headers (cookie + post data)
*/
include __DIR__.'/functions.php';
foreach(@$argv as $arg) {
    $pos = strpos($arg,'=');
    if ($pos) {
        $fld = substr($arg,0,$pos);
        $val = substr($arg,$pos+1);
        $$fld = $val;
    }
}
$headers = json_decode(base64_decode($headers),true);
$srvip = exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
if (!isset($headers['srvip']) OR $headers['srvip'] !== $srvip) {
    exit;
}
$_POST = isset($headers['post']) ? $headers['post'] : [];
$_POST = isset($headers['get']) ? $headers['get'] : [];
$_COOKIE = isset($headers['cook']) ? $headers['cook'] : [];
wbAuthPostContents("{$scheme}://{$host}{$uri}", $_POST);
?>
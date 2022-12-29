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
if (!isset($argv)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}
$headers = json_decode(base64_decode($headers),true);
$_POST = isset($headers['post']) ? $headers['post'] : [];
$_GET = isset($headers['get']) ? $headers['get'] : [];
$_COOKIE = isset($headers['cook']) ? $headers['cook'] : [];
wbAuthPostContents("{$scheme}://{$host}{$uri}", $_POST);
?>
<?php
/*
host = hostname 
port = port
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
$fp = fsockopen($host, $port, $errno, $errstr, 30);
$headers = base64_decode($headers);
fwrite($fp, $headers);
fclose($fp);
?>
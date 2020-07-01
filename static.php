<?php
$root = $_SERVER["DOCUMENT_ROOT"];
$file = $_SERVER["REQUEST_URI"];
$path = $root.$file;

if (is_file($path)) {
    $info = new SplFileInfo($path);
    $ext  = $info->getExtension();
    if (!in_array($ext,["less","php","html"])) {
        $mime = wbMime($path);
        header('Content-Type: '.$mime);
        echo file_get_contents($path);
        die;
    }

}
?>

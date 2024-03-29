<?php
$root = $_SERVER['DOCUMENT_ROOT'];

if (isset($_SERVER['REQUEST_URI'])) {
    $file = urldecode($_SERVER['REQUEST_URI']);
    $path = $root.$file;
} else {
    $file = $path = null;
}


if ($path && is_file($path)) {
    // http://work2.loc/tpl/fonts/fontawesome-webfont.woff2?v=4.7.0.html
    $tmp = explode('?', $path); // обработка таких вот случаев
    $info = new SplFileInfo($tmp[0]);
    $ext  = $info->getExtension();
    if (!in_array($ext,['less','scss','php','html'])) {
        $mime = wbMime($tmp[0]);
        header('Content-Type: '.$mime);
        if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
            parse_str($_SERVER['HTTP_CACHE_CONTROL'], $cc);
            isset($cc['no-cache']) ? header('Content-Type: '.$mime) : null;
            header('Cache-control: public');
            header('Pragma: cache');
            header('Cache-Control: max-age=31536000');
        }
        echo file_get_contents($path);
        exit;
    }

}
?>

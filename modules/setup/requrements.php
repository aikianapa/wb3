<?php
$err = '';
$path = $_SERVER['DOCUMENT_ROOT'];

if (!class_exists("DomDocument")) {
    $err .= "<li>Error! Extension not found: php-xml</li>";
}
if (!extension_loaded('mbstring')) {
    $err .= "<li>Error! Extension not loaded: php-mbstring</li>";
}
// проверить composer
// проверить GD или Imagick

if ($err > '') {
    echo "<ol>{$err}</ol>";
    die;
} else if (!is_file($path.'/database/_settings.json')) {
    @mkdir($path.'/database', 0766);
    @mkdir($path.'/forms', 0766);
    @mkdir($path.'/modules', 0766);
    @mkdir($path.'/tpl', 0766);
    copy($path.'/engine/database/_settings.json', $path.'/database/__setup.json');
    !is_file($path.'/.htaccess') ? @copy($path.'/engine/.htaccess', $path.'/.htaccess') : null;
    !is_file($path.'/index.php') ? @copy($path.'/engine/index.php', $path.'/index.php') : null;
    !is_file($path.'/database/users.json') ? @copy($path.'/engine/database/users.json', $path.'/database/users.json') : null;
}
?>
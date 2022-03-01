<?php
$err = '';
$path = $_SERVER['DOCUMENT_ROOT'];

$required = ['mbstring','dom','xml','gd','imagick','json','zip'];
$exts = get_loaded_extensions();

foreach($required as $req) {
    if (!in_array($req,$exts)) {
        $err .= "<li>Error! Extension not found: {$req}.<br>Use: sudo apt -y install php-{$req} </li>";
    }
}

// проверить composer
// проверить GD или Imagick

if ($err > '') {
    echo "<ol>{$err}</ol>";
    die;
} else if (!is_file($path.'/database/_settings.json')) {
    @mkdir($path.'/database', 0777);
    @mkdir($path.'/forms', 0777);
    @mkdir($path.'/modules', 0777);
    @mkdir($path.'/tpl', 0777);
    copy($path.'/engine/database/_settings.json', $path.'/database/__setup.json');
    !is_file($path.'/.htaccess') ? @copy($path.'/engine/.htaccess', $path.'/.htaccess') : null;
    !is_file($path.'/index.php') ? @copy($path.'/engine/index.php', $path.'/index.php') : null;
    !is_file($path.'/database/users.json') ? @copy($path.'/engine/database/users.json', $path.'/database/users.json') : null;
}
?>
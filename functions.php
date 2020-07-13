<?php
require_once __DIR__."/static.php";
require_once __DIR__.'/lib/vendor/autoload.php';
require_once __DIR__."/lib/weprocessor/weprocessor.php";
require_once __DIR__."/lib/weprocessor/weparser.class";
require_once __DIR__.'/wbrouter.php';
require_once __DIR__.'/wbapp.php';

use Adbar\Dot;
use Nahid\JsonQ\Jsonq;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//use soundintheory\PHPSQL;
// Rct567\DomQuery\DomQuery;

function wbInit()
{
    error_reporting(error_reporting() & ~E_NOTICE);
    date_default_timezone_set('Europe/Moscow');
    wbTrigger('func', __FUNCTION__, 'before');
    /*
        if (!isset($_ENV['settings'])) {
            wbErrorList();
            wbInitEnviroment($app);
            wbLoadDriver('default');
            wbInitSettings($app);
            wbInitDatabase();
            wbCheckWorkspace();
            wbLoadDriver($_ENV["driver"]);
            wbInitFunctions($app);
            //wbTableList();

            wbCacheEnvState();
            if (is_callable("wbAfterInit")) wbAfterInit();
        }
        if ($app->vars("_sess.user.lang")) $_SESSION["lang"]=$_ENV["lang"]=$app->vars("_sess.user.lang");
        */
}

function wbInitEnviroment()
{
    wbTrigger('func', __FUNCTION__, 'before');
    $_ENV["wbattr"]="data-wb";
    if (!isset($_SESSION['order_id']) or '' == $_SESSION['order_id']) {
        $_SESSION['order_id'] = wbNewId();
        $new = true;
    } else {
        $new = false;
    }

    $dir=explode("/", __DIR__);
    array_pop($dir);
    $dir=implode("/", $dir);
    if (!isset($_ENV["driver"])) {
        $_ENV["driver"] = "json";
    }
    if (!isset($_ENV["base"])) {
        $_ENV['base'] = "/tpl/";
    }
    $_ENV['path_app'] = ($_SERVER['DOCUMENT_ROOT']>"") ? $_SERVER['DOCUMENT_ROOT'] : $dir ;
    $_ENV['path_engine'] = $_ENV['path_app'].'/engine';
    $_ENV['dir_engine'] = __DIR__;
    $_ENV['dir_app'] = dirname(__FILE__);
    $_ENV['path_tpl'] = $_ENV['path_app'].$_ENV['base'];
    $_ENV['dbe'] = $_ENV['path_engine'].'/database'; 			// Engine data
    $_ENV['dba'] = $_ENV['path_app'].'/database';	// App data
    $_ENV['dbec'] = $_ENV['path_engine'].'/database/_cache'; 			// Engine data
    $_ENV['dbac'] = $_ENV['path_app'].'/database/_cache';	// App data
    $_ENV['drve'] = $_ENV['path_engine'].'/drivers'; 			// Engine data
    $_ENV['drva'] = $_ENV['path_app'].'/drivers';	// App data

    $_ENV['error'] = array();
    $_ENV['last_error'] = null;
    $_ENV['env_id'] = $_ENV['new_id'] = wbNewId();
    $_ENV['datetime'] = date('Y-m-d H:i:s');
    $_ENV['thumb_width'] = 200;
    $_ENV['thumb_height'] = 160;
    $_ENV['intext_width'] = 320;
    $_ENV['intext_height'] = 240;
    $_ENV['page_size'] = 12;
    $_ENV['data'] = new stdClass(); // for store some data

    $_ENV['forms'] = wbListForms(false);
    $_ENV['editors'] = [];
    wbListModules($app);

    //$_ENV['drivers'] = wbListDrivers();
    $_ENV['settings']['driver'] = 'json';
    // Load tags
    //$_ENV['tags'] = wbListTags();
    $_ENV['stop_func'] = explode(",", "exec,system,passthru,readfile,shell_exec,escapeshellarg,escapeshellcmd,proc_close,proc_open,ini_alter,dl,popen,parse_ini_file,show_source,curl_exec,file_get_contents,file_put_contents,file,eval,chmod,chown");
}


function wbInitSettings(&$app)
{
    if (!$app->vars("_sess.events")) {
        $app->vars("_sess.events", []);
    } // массив для передачи событий в браузер во время вызова wbapp.alive()
    if (isset($_COOKIE['user'])) {
        $_SESSION['user'] = $app->ItemRead("users", $_COOKIE['user']);
    }
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = null;
    } elseif ($_SESSION['user'] !== null) {
        $_ENV["user"] = $_SESSION['user'];
        $app->user = (object)$_ENV["user"];
        unset($_COOKIE['user']);
        isset($app->user->id) ? $cookuser = $app->user->id : $cookuser = "";
        setcookie("user", $cookuser, time()+3600, "/"); // срок действия час
    }
    $variables = [];
    $settings = $app->ItemRead('_settings', 'settings');
    if (!$settings) {
        $settings = [];
    } else {
        if (!isset($settings['variables'])) $settings['variables'] = [];
        foreach ((array)$settings['variables'] as $v) {
            $variables[$v['var']] = $v['value'];
        }
    }
    $_ENV['variables'] = array_merge((array)$_ENV['variables'], $variables);
    $settings = array_merge($settings, $variables);
    $_ENV['settings'] = &$settings;
    if (isset($_ENV['settings']['driver'])) $app->settings->driver = $_ENV['settings']['driver'];

    if ($_SERVER["REQUEST_URI"]=="/engine/") {
        unset($_ENV["lang"]);
    } else {
        isset($settings["lang"]) ? $_ENV["lang"] = $settings["lang"] : $_ENV["lang"] = "ru";
    }
    //    $_ENV["locales"]=wbListLocales($app);
    $_ENV["settings"]["locale"]=substr($_ENV["lang"], 0, 2);

    if (isset($_ENV['settings']['path_tpl']) and $_ENV['settings']['path_tpl'] > '') {
        $_ENV['base']=$_ENV['settings']['path_tpl'];
        $_ENV['path_tpl'] = $_ENV['path_app'].$_ENV['base'];
    }
    if (isset($_ENV['settings']['thumb_width']) and $_ENV['settings']['thumb_width'] > '0') {
        $_ENV['thumb_width'] = $_ENV['settings']['thumb_width'];
    }
    if (isset($_ENV['settings']['thumb_height']) and $_ENV['settings']['thumb_height'] > '0') {
        $_ENV['thumb_height'] = $_ENV['settings']['thumb_height'];
    }
    if (isset($_ENV['settings']['intext_width']) and $_ENV['settings']['intext_width'] > '0') {
        $_ENV['intext_width'] = $_ENV['settings']['intext_width'];
    }
    if (isset($_ENV['settings']['intext_height']) and $_ENV['settings']['intext_height'] > '0') {
        $_ENV['intext_height'] = $_ENV['settings']['intext_height'];
    }
    if (isset($_ENV['settings']['page_size']) and is_numeric($_ENV['settings']['page_size'])) {
        $_ENV['page_size'] = $_ENV['settings']['page_size'];
    }
    if (isset($_ENV['settings']['base']) and $_ENV['settings']['base'] > "") {
        $_ENV['base'] = $_ENV['settings']['base'];
        $_ENV['path_tpl'] = str_replace("//", "/", $_ENV['path_app']."/".$_ENV['base']);
    }
    if ($app->vars("_env.settings.editor") == "") {
        $_ENV['settings']['editor'] = 'jodit';
    }
    $_ENV['settings']['max_upload_size'] = wbMaxUplSize();
    $_ENV['sysmsg'] = wbGetSysMsg();
    $_ENV['settings']['sysmsg'] = &$_ENV['sysmsg']; // для доступа из JS
    $_ENV['settings']['user'] = $_SESSION["user"];
    if (isset($_ENV['settings']['user']['password'])) unset($_ENV['settings']['user']['password']);
    $app->vars("_sett", $_ENV["settings"]);
    if (in_array($app->vars("_route.controller"),["thumbnails","file"])) {
          if ($app->vars("_sett.user")) {
              $app->vars("_sett.user.group", wbItemRead("users", $app->vars("_sett.user.role")));
          }
          if (!$app->vars("_cookie.events")) {
              setcookie("events", base64_encode(json_encode([])), time()+3600, "/");
          } // срок действия час
    }
}

function wbMaxUplSize()
{
    $qty = (int)(ini_get('upload_max_filesize'));
    $unit = strtolower(preg_replace("/[0-9]/", "", ini_get('upload_max_filesize')));
    switch ($unit) {
          case 'k':
              $qty *= 1024;
              break;
          case 'm':
              $qty *= 1048576;
              break;
          case 'g':
              $qty *= 1073741824;
              break;
      }
    return $qty;
}


function wbGetSysMsg()
{
    $locale=array();
    if (is_file($_ENV["path_app"]."/forms/common/system_messages.ini")) {
        $locale=parse_ini_file($_ENV["path_app"]."/forms/common/system_messages.ini", true);
    } elseif (is_file($_ENV["path_engine"]."/forms/common/system_messages.ini")) {
        $locale=parse_ini_file($_ENV["path_engine"]."/forms/common/system_messages.ini", true);
    }
    if (isset($locale[$_ENV["lang"]])) {
        $locale=$locale[$_ENV["lang"]];
    }
    return $locale;
}


    function wbLoadDriver($drv = null)
    {
        // Load default DB driver //
        if ($drv !== null and is_file($_ENV['drva']."/{$drv}/{$drv}.php")) {
            require_once $_ENV['drva']."/{$drv}/{$drv}.php";
        } elseif ($drv !== null and is_file($_ENV['drve']."/{$drv}/{$drv}.php")) {
            require_once $_ENV['drve']."/{$drv}/{$drv}.php";
        } else {
            $drv = "default";
            if (is_file($_ENV['drve']."/{$drv}/{$drv}.php")) {
                require_once $_ENV['drve']."/{$drv}/{$drv}.php";
            }
        }
        $_ENV['driver'] = $drv;
    }

function wbFormClass($form = null) {
  require_once($_ENV["path_engine"]."/modules/cms/cms_formsclass.php");
  $app = $_ENV["app"];
  if ($form == null) $form = $app->vars("_route.form");
  if (is_file($app->vars("_env.path_app")."/forms/{$form}/_class.php")) {
      require_once($app->vars("_env.path_app")."/forms/{$form}/_class.php");
  } else if (is_file($app->vars("_env.path_engine")."/forms/{$form}/_class.php")) {
      require_once($app->vars("_env.path_engine")."/forms/{$form}/_class.php");
  } else if (is_file($app->vars("_env.path_engine")."/modules/cms/forms/{$form}/_class.php")) {
      require_once($app->vars("_env.path_engine")."/modules/cms/forms/{$form}/_class.php");
  }
  $class = $form."Class";
  if (class_exists($class)) {
      $form = new $class($app);
  } else {
      $form = new cmsFormsClass($app);
  }
  return $form;
}

function wbCorrelation($form,$id,$fld) {
    $app = $_ENV["app"];
    $item = wbItemRead($form,$id);
    $item = wbTrigger('form', __FUNCTION__, 'beforeItemShow', func_get_args(), $item);
    if ($item) {
        $data = new Dot($item);
        return $data->get($fld);
    } else {
        return null;
    }


}


function wbMime($path) {
    $mime = "text/plain";
    if (is_file($path)) {
        $info = new SplFileInfo($path);
        $ext  = $info->getExtension();
    } else {
        $ext = explode("?",$path);
        $ext = explode(".",$ext[0]);
        $ext = array_pop($ext);
    }
    $types = [
        "mid" => "audio/midi",
        "midi" => "audio/midi",
        "kar" => "audio/midi",
        "aac" => "audio/mp4",
        "f4a" => "audio/mp4",
        "f4b" => "audio/mp4",
        "m4a" => "audio/mp4",
        "mp3" => "audio/mpeg",
        "oga" => "audio/ogg",
        "ogg" => "audio/ogg",
        "ra" => "audio/x-realaudio",
        "wav" => "audio/x-wav",
        "bmp" => "image/bmp",
        "gif" => "image/gif",
        "jpeg" => "image/jpeg",
        "jpg" => "image/jpeg",
        "png" => "image/png",
        "tif" => "image/tiff",
        "tiff" => "image/tiff",
        "wbmp" => "image/vnd.wap.wbmp",
        "webp" => "image/webp",
        "ico" => "image/x-icon",
        "cur" => "image/x-icon",
        "jng" => "image/x-jng",
        "js" => "application/javascript",
        "json" => "application/json",
        "webapp" => "application/x-web-app-manifest+json",
        "manifest" => "text/cache-manifest",
        "appcache" => "text/cache-manifest",
        "doc" => "application/msword",
        "xls" => "application/vnd.ms-excel",
        "ppt" => "application/vnd.ms-powerpoint",
        "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "3gpp" => "video/3gpp",
        "3gp" => "video/3gpp",
        "mp4" => "video/mp4",
        "m4v" => "video/mp4",
        "f4v" => "video/mp4",
        "f4p" => "video/mp4",
        "mpeg" => "video/mpeg",
        "mpg" => "video/mpeg",
        "ogv" => "video/ogg",
        "mov" => "video/quicktime",
        "webm" => "video/webm",
        "flv" => "video/x-flv",
        "mng" => "video/x-mng",
        "asx" => "video/x-ms-asf",
        "asf" => "video/x-ms-asf",
        "wmv" => "video/x-ms-wmv",
        "avi" => "video/x-msvideo",
        "atom" => "application/xml",
        "rdf" => "application/xml",
        "rss" => "application/xml",
        "xml" => "application/xml",
        "woff" => "application/font-woff",
        "woff2" => "application/font-woff2",
        "eot" => "application/vnd.ms-fontobject",
        "ttc" => "application/x-font-ttf",
        "ttf" => "application/x-font-ttf",
        "otf" => "font/opentype",
        "svg" => "image/svg+xml",
        "svgz" => "image/svg+xml",
        "jar" => "application/java-archive",
        "war" => "application/java-archive",
        "ear" => "application/java-archive",
        "hqx" => "application/mac-binhex40",
        "pdf" => "application/pdf",
        "ps" => "application/postscript",
        "eps" => "application/postscript",
        "ai" => "application/postscript",
        "rtf" => "application/rtf",
        "wmlc" => "application/vnd.wap.wmlc",
        "xhtml" => "application/xhtml+xml",
        "kml" => "application/vnd.google-earth.kml+xml",
        "kmz" => "application/vnd.google-earth.kmz",
        "7z" => "application/x-7z-compressed",
        "crx" => "application/x-chrome-extension",
        "oex" => "application/x-opera-extension",
        "xpi" => "application/x-xpinstall",
        "cco" => "application/x-cocoa",
        "jardiff" => "application/x-java-archive-diff",
        "jnlp" => "application/x-java-jnlp-file",
        "run" => "application/x-makeself",
        "pl" => "application/x-perl",
        "pm" => "application/x-perl",
        "prc" => "application/x-pilot",
        "pdb" => "application/x-pilot",
        "rar" => "application/x-rar-compressed",
        "rpm" => "application/x-redhat-package-manager",
        "sea" => "application/x-sea",
        "swf" => "application/x-shockwave-flash",
        "sit" => "application/x-stuffit",
        "tcl" => "application/x-tcl",
        "tk" => "application/x-tcl",
        "der" => "application/x-x509-ca-cert",
        "pem" => "application/x-x509-ca-cert",
        "crt" => "application/x-x509-ca-cert",
        "torrent" => "application/x-bittorrent",
        "zip" => "application/zip",
        "bin" => "application/octet-stream",
        "exe" => "application/octet-stream",
        "dll" => "application/octet-stream",
        "deb" => "application/octet-stream",
        "dmg" => "application/octet-stream",
        "iso" => "application/octet-stream",
        "img" => "application/octet-stream",
        "msi" => "application/octet-stream",
        "msp" => "application/octet-stream",
        "msm" => "application/octet-stream",
        "safariextz" => "application/octet-stream",
        "css" => "text/css",
        "html" => "text/html",
        "htm" => "text/html",
        "shtml" => "text/html",
        "mml" => "text/mathml",
        "txt" => "text/plain",
        "jad" => "text/vnd.sun.j2me.app-descriptor",
        "wml" => "text/vnd.wap.wml",
        "vtt" => "text/vtt",
        "htc" => "text/x-component",
        "vcf" => "text/x-vcard",
        "map" => 'application/json'
      ];
    if (isset($types[$ext])) $mime = $types[$ext];
    return $mime;
}


function wbMail(
    $from = null,
    $sent = null,
    $subject = null,
    $message = null,
    $attach = null
    ) {
    $app = $_ENV['app'];
    if ($from == null) {
        $from=$_ENV["settings"]["email"].";".$_ENV["settings"]["header"];
    } elseif (!is_array($from)) {
        if (strpos($from, ";")) {
            $from=explode(";", $from);
        } else {
            $from=array($from,strip_tags($_ENV['settings']['header']));
        }
    }
    if (!is_array($sent) and is_string($sent) and strpos($sent, ";")) {
        $sent=array(explode(";", $sent));
    } elseif (!is_array($sent)) {
        $sent=array(array($sent,$sent));
    } elseif (is_array($sent) and !is_array($sent[0]) and strpos($sent[0], ";")) {
        foreach ($sent as $k => $s) {
            if (!is_array($s)) {
                $sent[$k]=explode(";", $s);
            }
        }
    } elseif (is_array($sent) and !is_array($sent[0]) and !strpos($sent[0], ";")) {
        $sent=array($sent);
    }

        require_once __DIR__.'/modules/phpmailer/phpmailer/PHPMailerAutoload.php';
        //$sett=$_ENV["settings"]["phmail"];
        $sett = ['smtp'=>'','host'=>$app->vars('_route.hostname')];
        $mail = new PHPMailer;
        try {
        /*
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'user@example.com';                 // SMTP username
            $mail->Password = 'secret';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
        */
        if ($sett["smtp"]=="on") {
            $mail->isSMTP();
            $mail->Host = $sett["host"];
            $sett["smtp"]=="on" ? $mail->SMTPAuth = true : $mail->SMTPAuth = false;
            $mail->Username = $sett["username"];
            $mail->Password = $sett["password"];
            $mail->SMTPSecure = $sett["secure"];
            intval($sett["port"]) > 0 ? $mail->Port = intval($sett["port"]) : $mail->Port = 587;
        } else {
            $mail->isSendmail();
        }
        $mail->setFrom($from[0], $from[1]);
        $mail->addReplyTo($from[0], $from[1]);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        foreach ($sent as $s) {
            $mail->addAddress($s[0], $s[1]);
        }
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML($message, dirname(__FILE__));
        $mail->CharSet = 'utf-8';
        //Replace the plain text body with one created manually
        $mail->AltBody = strip_tags($message);
        //Attach an image file
        if (!is_array($attach) and is_string($attach)) {
            $attach=array($attach);
        }
        if (is_array($attach)) {
            foreach ($attach as $a) {
                if (is_string($a) and substr($a, 0, 5) == "data:") {
                    preg_match('/^data:(.*);base64,/', substr($a, 0, 50), $matches, PREG_OFFSET_CAPTURE);
                    $mime = $matches[1][0];
                    $ext = explode("/", $mime);
                    $file = wbNewId().".".$ext[1];
                    $base64 = substr($a, strlen("data:{$mime};base64,"));
                    $mail->AddStringAttachment(base64_decode($base64), $file, 'base64', $mime);
                } else {
                    $mail->addAttachment($a);
                }
            }
        }
        //send the message, check for errors
            $mail->send();
            return ['error'=>false];
        } catch (Exception $e) {
            return ['error'=>true,'msg'=>$mail->ErrorInfo];
        }
}

function wbCheckWorkspace()
{
    if (!is_readable($_ENV['path_app']) or !is_writable($_ENV['path_app'])) {
        @chmod($_ENV['path_app'], 0777);
        if (!is_readable($_ENV['path_app']) or !is_writable($_ENV['path_app'])) {
            $out = wbGetTpl('setup.htm');
            $error = $out->find('#errors #rights');
            $out->find('#error.alert-warning')->html($error);
            $out->find('#wizard')->remove();
            $out->wbSetData();
            echo $out;
            die;
        }
    }
}

function wbPagination($c, $m, $dots = '...')
{
    $current = $c;
    $last = $m;
    $delta = 4;
    $left = $current - $delta;
    $right = $current + $delta + 1;
    $range = array();
    $rangeWithDots = array();
    $l = -1;

    for ($i = 1; $i <= $last; $i++) {
        if ($i == 1 || $i == $last || $i >= $left && $i < $right) {
            array_push($range, $i);
        }
    }

    for ($i = 0; $i<count($range); $i++) {
        if ($l != -1) {
            if ($range[$i] - $l === 2) {
                array_push($rangeWithDots, $l + 1);
            } elseif ($range[$i] - $l !== 1) {
                array_push($rangeWithDots, $dots);
            }
        }

        array_push($rangeWithDots, $range[$i]);
        $l = $range[$i];
    }
    $range = [];
    $flag = false;
    $i=0;
    if ($c == 1) {
        $range[$i] = ["label"=>"prev","page"=>""];
    } else {
        $range[$i] = ["label"=>"prev","page"=>$c-1];
    }
    foreach ($rangeWithDots as $key => $page) {
        $i++;
        if ($page == $dots and $flag) {
            $idx = ceil($rangeWithDots[$key-1] + ($rangeWithDots[$key+1] - $rangeWithDots[$key-1]) / 2);
            $range[$i] = ["label"=>$dots,"page"=>$idx];
        } elseif ($page == $dots and !$flag) {
            $idx = ceil($rangeWithDots[$key+1] / 2);
            $range[$i] = ["label"=>$dots,"page"=>$idx];
            $flag = true;
        } elseif ($page !== $dots) {
            $range[$i] = ["label"=>$page,"page"=>$page];
        }
    }
    $i++;
    $c == $m ? $range[$i] = ["label"=>"next","page"=>""] : $range[$i] = ["label"=>"next","page"=>$c+1];
    return $range;
}

function wbFormUploadPath()
{
    $path = '/uploads';
    if ('form' == $_ENV['route']['controller']) {
        if (isset($_ENV['route']['form']) and $_ENV['route']['form'] > '') {
            $path .= '/'.$_ENV['route']['form'];
        } else {
            $path .= '/undefined';
        }
        if (isset($_ENV['route']['item']) and $_ENV['route']['item'] > '') {
            $path .= '/'.$_ENV['route']['item'];
        } else {
            $path .= '/undefined';
        }
    } elseif ('ajax' == $_ENV['route']['controller'] and 'buildfields' == $_ENV['route']['mode'] and isset($_POST['data'])) {
        if (isset($_POST['data']['_form']) and $_POST['data']['_form'] > '') {
            $path .= '/'.$_POST['data']['_form'];
        } else {
            $path .= '/undefined';
        }
        if (isset($_POST['data']['_id']) and $_POST['data']['_id'] > '') {
            $path .= '/'.$_POST['data']['_id'];
        } else {
            $path .= '/undefined';
        }
    } else {
        $path .= '/undefined';
    }

    return $path;
}

function wbInitFunctions(&$app)
{
    wbTrigger('func', __FUNCTION__, 'before');
    if (is_file($_ENV['path_app'].'/functions.php')) {
        require_once $_ENV['path_app'].'/functions.php';
    }
    foreach ($_ENV['forms'] as $form) {
        $inc = array(
                   "{$_ENV['path_engine']}/forms/{$form}.php", "{$_ENV['path_engine']}/forms/{$form}/{$form}.php",
                   "{$_ENV['path_app']}/forms/{$form}.php", "{$_ENV['path_app']}/forms/{$form}/{$form}.php",
               );
        foreach ($inc as $k => $file) {
            if (is_file("{$file}")) {
                require_once "{$file}";
            }
        }
    }

    $excmod = ["cms"];

    foreach ($_ENV['modules'] as $module) {
        if (in_array($module['name'], $excmod) or $app->vars("_sett.modcheck") !== "on" or
            ($app->vars("_sett.modcheck") == "on"
            and $app->vars("_sett.modules.{$module['name']}.active") == "on"
            )
          ) {
            require_once $module["path"];
        }
    }
}

function wbGetUserUi($details=false)
{
    $prop=wbGetUserUiConfig();
    if ($prop==null) {
        $conf=wbObjToArray($_SESSION["user"]->group);
        if (!$_ENV["last_error"] and isset($conf["roleprop"]) and $conf["roleprop"] !== "") {
            if ($details) {
                $prop=array();
                $prop["roleprop"]=wbItemToArray($conf["roleprop"]);
                $prop["_roleprop__dict_"]=wbItemToArray($conf["_roleprop__dict_"]);
            } else {
                $prop=wbItemToArray($conf["roleprop"]);
            }
        }
    }
    if ($prop==null) {
        $conf=wbItemRead("users:engine", "admin");
        if (!$_ENV["last_error"]) {
            if ($details) {
                $prop=array();
                $prop["roleprop"]=wbItemToArray($conf["roleprop"]);
                $prop["_roleprop__dict_"]=wbItemToArray($conf["_roleprop__dict_"]);
            } else {
                $prop=wbItemToArray($conf["roleprop"]);
            }
        } else {
            if ($details) {
                $prop=array();
                $prop["roleprop"]=array();
                $prop["_roleprop__dict_"]=array();
            } else {
                $prop=array();
            }
        }
    }
    return $prop;
}

function wbGetUserUiConfig($prop=null)
{
    if ($prop==null) {
        $prop=wbTreeRead("_config");
        $prop=wbItemToArray($prop["tree"]);
    }
    if (is_array($prop)) {
        foreach ($prop as $key => $item) {
            $item=wbItemToArray($item);
            if (is_array($item)) {
                $item=wbGetUserUiConfig($item);
                if (!isset($item["data"])) {
                    $item["data"]=array();
                }
                $item["data"]["visible"]="on";
                $prop[$key]=$item;
            }
        };
    }
    return $prop;
}

function wbProfData(&$xhprof_data)
{
    $tmp=$xhprof_data;
    $last=array_pop($tmp);
    $sec=$last["wt"];
    foreach ($xhprof_data as $key=> &$item) {
        $item["func"]=explode("==>", $key);
        $item["func"]=$item["func"][1];
    }
    $xhprof_data = wbArraySort($xhprof_data, "wt:d");
    $tpl=wbFromString('
	<script data-wb-src="jquery"></script>
	<script data-wb-src="bootstrap3"></script>
	<table class="table table-striped">
	<thead><tr>
	<th>Name</th><th>Calls</th><th>Time</th><th>MemUse</th><th>PeakMemUse</th><th>CPU</th>
	</tr></thead>
	<tbody data-wb-role="foreach" data-wb-from="data">
	<tr>
	<td class="text-right">{{func}}</td>
	<td class="text-center">{{ct}}</td>
	<td class="text-right">{{wt->round(@ / 1000000,4)}}</td>
	<td class="text-right">{{mu->number_format()}}</td>
	<td class="text-right">{{pmu->number_format()}}</td>
	<td class="text-center">{{cpu}}</td>
	</tr>
	</tbody>
	</table>');
    $tpl->wbSetData(array("data"=>$xhprof_data));
    return $tpl->outerHtml();
}

function wbPostToArray(&$array = [], $replace = false)
{
    foreach ($_POST as $key => $val) {
        if ($replace == true or !in_array($key, array_keys($array))) {
            $array[$key] = htmlspecialchars($val, ENT_QUOTES);
        }
    }
    return $array;
}

function wbObjToArray($obj)
{
    return json_decode(json_encode($obj), true);
}

function wbArrayToObj($arr)
{
    return json_decode(wbJsonEncode($arr));
}

function wbAttrToValue($atval)
{
    $prms = "";
    if (substr(trim($atval), 0, 1) == "{" && substr(trim($atval), -1, 1) == "}") {
        $atval = str_replace("'", '"', $atval);
        $prms = json_decode($atval, true);
        if (!$prms) {
            $prms = [];
        }
    } else {
        $prms = $atval;
        if (strpos($atval, "=")) {
            parse_str($atval, $prms);
        }
    }
    return $prms;
}

function wbItemBeforeShow(&$Item)
{
    if (!((array)$Item === $Item)) {
        return;
    }
    if (!isset($Item["_form"])) {
        return;
    }
    $form = $Item["_form"];
    $ecall = "_{$form}BeforeItemShow";
    $acall = "{$form}BeforeItemShow";
    if (is_callable($ecall)) {
        $Item = @$ecall($Item);
    }
    if (is_callable($acall)) {
        $Item = @$acall($Item);
    }
}


function wbItemToArray(&$Item = array(), $convid = true)
{
    if (is_object($Item)) {
        $Item=wbObjToArray($Item);
    }
    if (isset($Item["_table"]) && $Item["_table"]=="admin" && $Item["id"]=="settings") {
        $convid=false;
    }
    if ((array)$Item === $Item) {
        $tmpItem=array();
        foreach ($Item as $i => $item) {
            if (substr($i, 0, 1) !== "%" and $i !== "_parent") {
                if (!((array)$item === $item)) {
                    $tmp = json_decode($item, true);
                    if ((array)$tmp === $tmp) {
                        $item = wbItemToArray($tmp, $convid);
                    }
                }
                $item = wbItemToArray($item, $convid);
            }
            if ($convid == true and (array)$item === $item and isset($item['id'])) {
                $tmpItem[$item['id']] = $item;
            } else {
                $tmpItem[$i] = $item;
            }
        }
        $Item=$tmpItem;
    } elseif (!(array($Item) === $Item)) {
        $tmp = json_decode($Item, true);
        if ((array)$tmp === $tmp) {
            $Item = wbItemToArray($tmp, $convid);
        }
    }
    return $Item;
}

function wbGetDataWbFrom($Item, $str)
{
    $str = trim($str);
    $str_1=json_decode(wbSetValuesStr("{{".$str."}}", $Item), true);
    if (is_array($str_1)) {
        return $str_1;
    }

    if (substr($str, 0, 1)=="_" and $str !== $str_1) {
        // если в атрибуте data-wb-from указанна общая переменная (типа _ENV, _SESS)
        $tmp=json_encode($str_1, true);
        if (is_array($tmp)) {
            return $tmp;
        } else {
            return $str_1;
        }
    }
    if (strpos($str, "}}")) {
        $str = wbSetValuesStr($str, $Item);
    }

    $pos = strpos($str, '[');
    if ($pos) {
        $fld = '['.substr($str, 0, $pos).']';
        $suf = substr($str, $pos);
        $fld .= $suf;
        $fld = str_replace('[', '["', $fld);
        $fld = str_replace(']', '"]', $fld);
        $fld = str_replace('""', '"', $fld);
        if (eval('return isset($Item'.$fld.');')) {
            eval('$res=$Item'.$fld.';');
        } else {
            $res="";
        }

        return $res;
    }
    if (isset($Item[$str])) {
        return $Item[$str];
    } else {
        return null;
    }
}

function wbMerchantList($type = 'both')
{
    $res = array();
    if ('both' == $type) {
        $res_e = wbMerchantList('engine');
        $res_a = wbMerchantList('app');

        return array_merge($res_e, $res_a);
    }
    $dir = $_ENV["path_{$type}"].'/modules';
    if (is_dir($dir)) {
        exec("find {$dir} -maxdepth 2 -name '*.php'", $list);
        foreach ($list as $val) {
            $file = $val;
            if (is_file($file) and !strpos($file, "_")) {
                $php = strtolower(trim(file_get_contents($file)));
                $form = array_pop(explode('/', $file));
                $form = explode('.php', $form);
                $form = $form[0];
                if ((strpos($php, "function {$form}_checkout") and strpos($php, "function {$form}_success"))
                        or (strpos($php, "function {$form}__checkout") and strpos($php, "function {$form}__success"))) {
                    $arr = array();
                    $arr['id'] = $form;
                    $arr['name'] = $form;
                    $arr['dir'] = $dir;
                    $arr['type'] = $type;
                    $res[] = $arr;
                }
            }
        }
    }
    unset($dir,$list,$val,$form,$php,$file,$arr);
    return $res;
}

function wbFieldBuild($dict, $data = array(), $locale=array())
{
    $param = wbItemToArray($dict);
    $dict = json_decode(json_encode($param));
    $app = new wbApp();
    $set = $app->getForm('common', 'tree_fldset');
    $tpl = $app->getForm('snippets', $param["type"]);

    $label=$param["label"];
    if (isset($lang["labels"]) and isset($lang["labels"][$_ENV["lang"]]) and $lang["labels"][$_ENV["lang"]]["name"]>"") {
        $label=$param['label']=$lang["labels"][$_ENV["lang"]]["name"];
    }
    if ($dict->prop == "") {
        $dict->prop = (object)[];
    }
    switch ($param["type"]) {
        case 'number':
            if (isset($opt['min'])) {
                $tpl->find('input')->attr('min', $opt['min']);
            }
            if (isset($opt['max'])) {
                $tpl->find('input')->attr('max', $opt['max']);
            }
            if (isset($opt['step'])) {
                $tpl->find('input')->attr('step', $opt['step']);
            }
            if (isset($opt['datalist'])) {
                $param['listid'] = wbNewId();
                $tpl->find('input')->attr('list', $param['listid']);
                $tpl->find('datalist')->attr('data-wb-from', wbJsonEncode($opt['datalist']));
                $tpl->find('datalist')->attr('data-wb-role', 'foreach');
            } else {
                $tpl->find('datalist')->remove();
            }
        break;
        case 'enum':
            $tpl->setAttributes($param);
            $arr=array();
            if (isset($param["prop"]["enum"])) {
                $arr=explode(",", $param["prop"]["enum"]);
            }
            $param['enum']=[];
            foreach ($arr as $i => $line) {
                $param['enum'][] = array('id' => $line, 'name' => $line);
            }
            $tpl->fetch($param);
            $tpl->addClass("wb-done");
        break;
         case 'multiinput':
            $tpl->setValues($param);
            $wrp = $app->fromFile($_ENV['path_app'].'/engine/tags/multiinput/multiinput_wrapper.php');
            $field = $dict->name;
            $flds = '';

             if ($dict->prop->multiflds) {
                 foreach ($dict->prop->multiflds as $i => $multi) {
                     $name = $field;
                     if ($multi->name > "") {
                         $name=$multi->name;
                     }
                     if (!isset($multi->style)) {
                         $multi->style = "";
                     }
                     if (!isset($multi->class)) {
                         $multi->class = "";
                     }
                     $line = $app->fromString($wrp->outerHtml());
                     $snip = $app->getForm('snippets', $multi->type);

                     $snip->find(":first")->addClass($multi->class);
                     $snip->find(":first")->attr("placeholder", $multi->label);
                     $snip->find(":first")->attr("style", $multi->style);
                     if ($snip->find(":first")->attr("style")=="") {
                         $snip->find(":first")->removeAttr("style");
                     }
                     $snip->setAttributes(json_decode(json_encode($multi), true));
                     $snip->find('[name]')->attr('name', $name);
                     $snip->find(".wb-value")->removeClass("wb-value");
                     $snip->find(".wb-attrs")->removeClass("wb-attrs");
                     $snip->find(".wb-done")->removeClass("wb-done");

                     $line->append($snip);

                     $flds .= $line->outerHtml();
                 }
             } else {
                 $snip = $app->getForm('snippets', "string");
                 $snip->attr('name', $field);
                 $snip->attr("placeholder", $dict->label);
                 $snip->attr("style", $dict->style);
                 $snip->removeClass("wb-value");
                 $snip->removeClass("wb-attrs");

                 $line = $app->fromString($wrp->outerHtml());
                 //return $snip->outerHtml();
                 $line->append($snip);
                 $flds .= $line->outerHtml();
             }


             $tpl->html($flds)
                 ->removeClass("wb-done")
                 ->fetch($data["data"])
                 ->clearValues();

        break;
     }

    $set->find('.form-group > label')->html($label);
    $set->find('.form-group > div')->html($tpl->outerHtml());
    $set->fetch($param);
    $set->setValues($data["data"])->clearValues();
    $out = $set->outerHtml();

    return $out;
}


function wbFieldBuild1($param, $data = array(), $locale=array())
{
    $param=wbItemToArray($param);
    $set = wbGetForm('common', 'tree_fldset');
    $tpl = wbGetForm('snippets', $param['type']);
    $opt=$param["prop"];
    $lang=$param["lang"];
    $options = '';
    if (isset($opt['required']) and true == $opt['required']) {
        $options .= ' required ';
    }
    if (isset($opt['readonly']) and true == $opt['readonly']) {
        $options .= ' readonly ';
    }
    if (isset($opt['disabled']) and true == $opt['disabled']) {
        $options .= ' disabled ';
    }
    $param['options'] = trim($options);
    $label=$param['label'];
    if (isset($lang["labels"]) and isset($lang["labels"][$_ENV["lang"]]) and $lang["labels"][$_ENV["lang"]]["name"]>"") {
        $label=$param['label']=$lang["labels"][$_ENV["lang"]]["name"];
    }

    if ($param["name"]=="" and $paran["label"]=="") {
        return ;
    }
    switch ($param['type']) {
    case 'number':
        if (isset($opt['min'])) {
            $tpl->find('input')->attr('min', $opt['min']);
        }
        if (isset($opt['max'])) {
            $tpl->find('input')->attr('max', $opt['max']);
        }
        if (isset($opt['step'])) {
            $tpl->find('input')->attr('step', $opt['step']);
        }
        if (isset($opt['datalist'])) {
            $param['listid'] = wbNewId();
            $tpl->find('input')->attr('list', $param['listid']);
            $tpl->find('datalist')->attr('data-wb-from', wbJsonEncode($opt['datalist']));
            $tpl->find('datalist')->attr('data-wb-role', 'foreach');
        } else {
            $tpl->find('datalist')->remove();
        }
        break;
    case 'enum':
    $arr=array();
    if ($param['value'] > '' and strpos(";", $param['value'])) {
        $param['enum'] = array();
        $arr = explode(';', $param['value']);
    }
    $param=wbItemToArray($param);
    if (isset($param["prop"]["enum"])) {
        $arr=explode(",", $param["prop"]["enum"]);
    }
        foreach ($arr as $i => $line) {
            $line=ltrim(rtrim($line));
            $param['enum'][$line] = array('id' => $line, 'name' => $line);
        }
        $tpl->wbSetData($param);
    if (isset($data["type"])) {
        // если имя поля и одна из пропертей совпадает, то нужно фиксить
        $tpl->find("[name=type]")->attr("value", $data["type"]);
    }
        break;
    case 'image':
        if (isset($_POST['data-id']) and $_POST['_form']=="tree") {
            $data["path"]="/uploads/{$_POST['_form']}/{$_POST['_item']}/{$_POST['data-id']}/";
        } else {
            $data["path"]="/uploads/{$data['_form']}/{$data['_item']}/";
        }
        $tpl->find('[data-wb-role=uploader]')->attr('data-wb-path', $data["path"]);
        $tpl->wbSetValues($param);
        $tpl->wbSetData($data);
        break;
    case 'gallery':
        if (isset($_POST['data-id']) and $_POST['_form']=="tree") {
            $data["path"]="/uploads/{$_POST['_form']}/{$_POST['_item']}/{$_POST['data-id']}/";
        } else {
            $data["path"]="/uploads/{$data['_form']}/{$data['_item']}/";
        }
        $tpl->find('[data-wb-role=uploader]')->attr('data-wb-path', $data["path"]);
        $tpl->wbSetValues($param);
        $tpl->wbSetData($data);
        break;
    case 'forms':
        $env=$_ENV;
        $get=$_GET;
        $par=$param;
        $param=wbItemToArray($param);
        $form=$param["prop"]["form"];
        $mode=$param["prop"]["mode"];
        $_ENV["route"]["form"]=$param["_form"]=$_GET["form"]=$form;
        $_ENV["route"]["mode"]=$param["_mode"]=$_GET["mode"]=$mode;
        $tpl=wbGetForm($form, $mode);
        if ($param["prop"]["selector"]>"") {
            $tpl=$tpl->find($param["prop"]["selector"], 0)->clone();
        }
        $tpl->wbSetValues($param);
        $tpl->wbSetData($data);
        $tpl->find(".nav-tabs .nav-item:first-child")->addClass("active");
        $_ENV=$env;
        $_GET=$get;
        $param=$par;
        unset($env,$get,$par);
        break;
    case 'module':
        if (!is_array($opt)) {
            break;
        }
        foreach ($opt as $key => $val) {
            $tpl->find("[data-wb-role]:first")->attr($key, $val);
        }
        $tpl->wbSetValues($param);
        $tpl->wbSetData($data);
        break;
    case 'multiinput':
        $tpl->wbSetValues($param);
       $wrp=wbGetForm('common', 'multiinput_wrapper');
        $field=$param["name"];
        $flds = wbFromString('');
        if (isset($param["prop"]["multiflds"])) {
            $arr=$param["prop"]["multiflds"];
            foreach ($arr as $i => $multi) {
                if (!isset($multi["style"])) {
                    $multi["style"]="";
                }
                if (!isset($multi["class"])) {
                    $multi["class"]="";
                }
                $name = 'data'.$i;
                if ($multi["name"] > "") {
                    $name=$multi["name"];
                }
                $line = $wrp->clone();
                $snip = wbGetForm('snippets', $multi['type']);
                $line->find(":first")->attr("class", $multi["class"]);
                $line->find(":first")->attr("style", $multi["style"]);
                $line->find(":first")->append($snip);
                $line->find('[name]')->attr('name', $name);
                $line->wbSetValues($multi);
                $line->find(".wb-value")->removeAttr("value");
                $line->find(".wb-value")->removeClass("wb-value");
                $line->find(".wb-attrs")->removeClass("wb-attrs");
                $flds->append($line);
            }
        }
        $tpl->find('[data-wb-role=multiinput]')->html($flds);
    $tpl->wbSetData($data);
        unset($flds);
        break;
    }
    if (isset($param["style"]) and $param["style"]>"") {
        // old
        $style=$tpl->attr("style");
        $tpl->find(":first")->attr("style", $style.$param["style"]);
    }

    if (isset($param["prop"]["class"])) {
        $tpl->find(":first")->addClass($param["prop"]["class"]);
    }
    if (isset($param["prop"]["style"])) {
        $tpl->find(":first")->attr("style", $param["prop"]["style"]);
    }

    $set->find('.form-group > label')->html($label);
    $set->find('.form-group > div')->html($tpl->outerHtml());
    $set->wbSetData($param);
    $set->wbSetValues($data);
    $out = $set->outerHtml();
    /*
    if ($param['type']=="forms") {
        $out=wbFromString($out);
        $inp=$out->find("[name]");
        foreach($inp as $i) {
            $name=$i->attr("name");
            $spos=strpos($name,"[");
            if ($spos) {
                $name=$param['name']."[".substr($name,0,$spos)."]".substr($name,strpos($name,"]")+1);
            } else {
                $name=$param['name']."[".$name."]";
            }
            $i->attr("name",$name);
        }
        $out = $out->outerHtml();
    }
    */
    return $out;
}

function wbInitDatabase()
{
    wbTrigger('func', __FUNCTION__, 'before');
    if (!is_dir($_ENV['dbe'])) {
        @mkdir($_ENV['dbe'], 0766);
    }
    if (!is_dir($_ENV['dba'])) {
        @mkdir($_ENV['dba'], 0766);
    }
    if (!is_dir($_ENV['dbec'])) {
        @mkdir($_ENV['dbec'], 0766);
    }
    if (!is_dir($_ENV['dbac'])) {
        @mkdir($_ENV['dbac'], 0766);
    }
    $_ENV['tables'] = wbTableList();
}

function wbTreeRead($name)
{
    wbTrigger('form', __FUNCTION__, 'BeforeTreeRead', func_get_args(), array());
    $tree = wbItemRead('tree', $name);
    $tree = wbTrigger('form', __FUNCTION__, 'AfterTreeRead', func_get_args(), $tree);
    return $tree;
}

function wbTreeToArray($tree)
{
    $assoc=array();
    if (!is_array($tree)) {
        return $assoc;
    }
    foreach ($tree as $i => $item) {
        if (isset($item["children"])  and is_array($item["children"]) and count($item["children"])) {
            $item["children"]=wbTreeToArray($item["children"]);
        }
        if (isset($item["id"])) {
            $key=$item["id"];
        } else {
            $key=$i;
        }
        if (!is_array($item["children"]) or !count($item["children"])) {
            $item["children"]="";
        }
        if (!is_array($item["data"]) or !count($item["data"])) {
            $item["data"]="";
        }
        $assoc[$key]=$item;
    }
    return $assoc;
}

function wbTreeFindBranchById($Item, $id)
{
    //$Item=wbItemToArray($Item);
    $res = false;
    if (is_array($Item)) {
        foreach ($Item as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
            if (isset($item['children']) AND (array)$item['children'] === $item['children']) {
                $res = wbTreeFindBranchById($item['children'], $id);
                if ($res) {
                    return $res;
                }
            }
        }
    }
    return $res;
}

function wbTreeFindBranch($tree, $branch = '', $parent = 'true', $childrens = 'true')
{
    //$tree=wbItemToArray($tree);
    if (trim($branch) == '') {
        return $tree;
    }
    $branch = html_entity_decode($branch);
    $br = explode('->', $branch);
    foreach ($br as $b) {
        $tree = array(wbTreeFindBranchById($tree, rtrim(ltrim($b))));
    }

    if ('false' == $childrens) {
        unset($tree['children']);
    }
    if ('false' == $parent) {
        $tree = $tree[0]['children'];
    }

    return $tree;
}

function wbTreeWhere($tree, $id, $field, $inc = true)
{
    if (!is_array($tree)) {
        $tree = wbTreeRead($tree);
        $tree_id = $tree['id'];
        $tree = $tree['tree'];
    } else {
        $tree_id = $tree['id'];
    }
    if (strpos($id, '->')) {
        $tree = wbTreeFindBranch($tree, $id);
        $tree = $tree[0];
    } else {
        $tree = wbTreeFindBranchById($tree, $id);
    }
    $cache_id = md5($tree_id.$id.$field.$inc.$_ENV["lang"].$_ENV["lang"]);
    if (isset($_ENV['cache'][__FUNCTION__][$cache_id])) {
        return $_ENV['cache'][__FUNCTION__][$cache_id];
    }
    $list = wbTreeIdList($tree);
    $where = '';
    foreach ($list as $key => $val) {
        if (0 == $key) {
            $where .= '"'.$val.'"';
        } else {
            $where .= ',"'.$val.'"';
        }
    }
    $where = "in_array({$field},array({$where}))";
    $_ENV['cache'][__FUNCTION__][$cache_id] = $where;

    return $_ENV['cache'][__FUNCTION__][$cache_id];
}

function wbTreeIdList($tree, $list = array())
{
    if (isset($tree['id'])) {
        $list[] = $tree['id'];
    }
    //$tree = wbItemToArray($tree);
    if (isset($tree['children']) and is_array($tree['children'])) {
        foreach ($tree['children'] as $key => $child) {
            $list = wbTreeIdList($child, $list);
        }
    }

    return $list;
}

function wbWhereLike($ref, $val)
{
    if (is_array($ref)) {
        $ref = implode('|', $ref);
    } else {
        $val = trim($val);
        $val = str_replace(' ', '|', $val);
    }
    $res = preg_match("/{$val}/ui", $ref);

    return $res;
}

function wbWhereNotLike($ref, $val)
{
    if (is_array($ref)) {
        $ref = implode('|', $ref);
    } else {
        $val = trim($val);
        $val = str_replace(' ', '|', $val);
    }
    $res = preg_match("/{$val}/ui", $ref);
    if (1 == $res) {
        $res = 0;
    } else {
        $res = 1;
    }

    return $res;
}

function wb_json_encode($Item=[])
{
    return wbJsonEncode($Item);
}

function wbJsonEncode($Item = [])
{
    if (version_compare(phpversion(), "5.6")<0) {
        return stripcslashes(wbJsonEncodeAlt($Item));
    } else {
        return json_encode($Item, JSON_UNESCAPED_UNICODE | JSON_HEX_AMP | JSON_HEX_TAG | JSON_HEX_APOS | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_HEX_QUOT);
    }
}

function wbJsonEncodeAlt($a=false)
{
    if (is_null($a)) {
        return 'null';
    }
    if ($a === false) {
        return 'false';
    }
    if ($a === true) {
        return 'true';
    }
    if (is_scalar($a)) {
        if (is_float($a)) {
            // Always use "." for floats.
            $a = str_replace(",", ".", strval($a));
        }

        // All scalars are converted to strings to avoid indeterminism.
        // PHP's "1" and 1 are equal for all PHP operators, but
        // JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
        // we should get the same result in the JS frontend (string).
        // Character replacements for JSON.
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
    array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
        if (key($a) !== $i) {
            $isList = false;
            break;
        }
    }
    $result = array();
    if ($isList) {
        foreach ($a as $v) {
            $result[] = wbJsonEncodeAlt($v);
        }
        return '[ ' . join(', ', $result) . ' ]';
    } else {
        foreach ($a as $k => $v) {
            $result[] = wbJsonEncodeAlt($k).': '.wbJsonEncodeAlt($v);
        }
        return '{ ' . join(', ', $result) . ' }';
    }
}

function wbCallDriver($func, $args=array())
{
    if (isset($_ENV["driver"]) and $_ENV["driver"]!=='default') {
        $func=str_replace("wb", $_ENV["driver"], $func);
        if (!is_callable($func)) {
            return false;
        }
        $result=call_user_func_array($func, $args);
        return [
            "func"=>$func,
            "result"=>$result
        ];
    } else {
        return false;
    }
}



function wbCacheCheck()
{
    exec("find {$_ENV["dbac"]} -type f -mmin +120 -exec rm -rf {} \; &"); // clean old chaches
    $cache = array("check"=>false,"id"=>false,"path"=>false,"data"=>false,"save"=>false);
    if (in_array($_ENV["cache_state"], ["update","false"])) {
        $save = true;
        if ($_ENV["cache_state"]=="false") {
            $save = false;
        }
        $cacheId = wbGetCacheId();
        $cacheFile = wbGetCacheId(true);
        $cache = array("check"=>null,"id"=>$cacheId,"path"=>$cacheFile,"data"=>false,"save"=>$save);
        return $cache;
    }
    if (isset($_ENV["settings"]["cache"]) and is_array($_ENV["settings"]["cache"])) {
        foreach ($_ENV["settings"]["cache"] as $line) {
            $c=wbAttrToArray($line["controller"]);
            $f=wbAttrToArray($line["form"]);
            $m=wbAttrToArray($line["mode"]);
            if (
                (in_array($_ENV["route"]["controller"], $c) or $c==array("*"))
                and     (in_array($_ENV["route"]["form"], $f) or $f==array("*") or ($f==array() and !in_array("form", $c)))
                and     (in_array($_ENV["route"]["mode"], $m) or $m==array("*") or ($m==array() and !in_array("form", $c)))
                and     $line["active"] == "on"
            ) {
                $cacheId = wbGetCacheId();
                $cacheFile = wbGetCacheId(true);
                if (!is_file($cacheFile)) {
                    $cache = array("check"=>null,"id"=>$cacheId,"path"=>$cacheFile,"data"=>false,"save"=>true);
                } else {
                    $lastmod = filemtime($cacheFile);
                    $expired = $lastmod + $line["lifetime"]*1;
                    if (time() > $expired) {
                        $cache = array("check"=>null,"id"=>$cacheId,"path"=>$cacheFile,"data"=>false,"save"=>true);
                    } else {
                        $data = file_get_contents($cacheFile);
                        $cache = array("check"=>true,"id"=>$cacheId,"path"=>$cacheFile,"data"=>$data,"save"=>false);
                    }
                }
            }
        }
    }
    return $cache;
}

function wbCacheEnvState()
{
    $_ENV["cache_state"]=null;
    if (isset($_ENV["route"]["params"]) and $_ENV["route"]["params"]["wbcache"] and in_array($_ENV["route"]["params"]["wbcache"], ["update","false"])) {
        if (isset($_ENV["route"]["params"]) and isset($_ENV["route"]["params"]["wbcache"])) {
            $_ENV["cache_state"]=$_ENV["route"]["params"]["wbcache"];
            if (strpos($_ENV["route"]["uri"], "?wbcache=".$_ENV["route"]["params"]["wbcache"]."&")) {
                $_ENV["route"]["uri"]=str_replace("wbcache=".$_ENV["route"]["params"]["wbcache"]."&", "", $_ENV["route"]["uri"]);
            } elseif (strpos($_ENV["route"]["uri"], "?wbcache=".$_ENV["route"]["params"]["wbcache"])) {
                $_ENV["route"]["uri"]=str_replace("?wbcache=".$_ENV["route"]["params"]["wbcache"], "", $_ENV["route"]["uri"]);
            } elseif (strpos($_ENV["route"]["uri"], "&wbcache=".$_ENV["route"]["params"]["wbcache"])) {
                $_ENV["route"]["uri"]=str_replace("&wbcache=".$_ENV["route"]["params"]["wbcache"], "", $_ENV["route"]["uri"]);
            }
            unset($_ENV["route"]["params"]["wbcache"]);
            if (isset($_ENV["route"]["params"]) and !count($_ENV["route"]["params"])) {
                unset($_ENV["route"]["params"]);
            }
        }
    }
}

function wbGetCacheId($file=false)
{
    // return Cache ID or Cache Filename
    $cacheId = md5(json_encode($_ENV["route"]).$_ENV["lang"]);
    if ($file==true) {
        $cacheDir = substr(md5($cacheId), 0, 4);
        $cacheFile = "{$_ENV["dbac"]}/{$cacheDir}/{$cacheId}.htm";
        return $cacheFile;
    } else {
        return $cacheId;
    }
}

function wbCacheName($table, $id = null)
{
    $tmp = explode($_ENV['dbe'], $table);
    if (2 == count($tmp)) {
        $dbc = $_ENV['dbec'];
        $db = $_ENV['dbe'];
    } else {
        $dbc = $_ENV['dbac'];
        $db = $_ENV['dba'];
    }
    $tname = str_replace($db.'/', '', $table);
    if (!is_dir($db)) {
        mkdir($db, 0766);
    }
    if (!is_dir($dbc)) {
        mkdir($dbc, 0766);
    }
    if (!is_dir($dbc.'/'.$tname)) {
        mkdir($dbc.'/'.$tname, 0766);
    }
    if (null == $id) {
        $cache = $cache = $dbc.'/'.$tname;
    } else {
        $cache = $dbc.'/'.$tname.'/'.$id;
    }

    return $cache;
}







function wbSetChmod($ext = '.json')
{
    foreach ($_ENV['tables'] as $table) {
        if (is_file($_ENV['dba'].'/'.$table.$ext)) {
            @chmod($_ENV['dba'].'/'.$table.$ext, 0766);
        }
    }
}

function wbItemInit($table, $item = null)
{
    $app = &$_ENV["app"];
    if (!isset($item['_id']) && isset($item['id'])) {
        $item['_id'] = $item['id'];
    }
    if (!isset($item['_id']) or '_new' == $item['_id'] or $item['_id'] == "") {
        $item['_id'] = wbNewId();
    }
    $item['id'] = $item['_id'];

    $item['_table'] = $item['_form'] = $table;

    $tmp = wbItemRead($item["_form"], $item["_id"]);
    if ((!$tmp or !isset($tmp['_created']) or '' == $tmp['_created']) and !isset($item["_created"])) {
        $item['_created'] = date('Y-m-d H:i:s');
    }
    if ((!$tmp or !isset($tmp['_creator']) or '' == $tmp['_creator']) and !isset($item["_creator"])) {
        $item['_creator'] = $app->vars("_sess.user.id");
    }

    $item['_lastdate'] = date('Y-m-d H:i:s');
    $item['_lastuser'] = $app->vars("_sess.user.id");
    return $item;
}

function wb_file_get_contents($file)
{
    $fp = fopen($file, 'rb');
    flock($fp, LOCK_SH);
    $contents = file_get_contents($file);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $contents;
}

function wbTrigger($type, $name, $trigger, $args = [], $data = null)
{

    //$env_error = $_ENV['error'];
    if (!isset($env_error) or (array)$env_error !== $env_error) {
        $_ENV['error'] = array();
    }
    if (!isset($env_error[$type])) {
        $_ENV['error'][$type] = array();
    }
    switch ($type) {
    case 'form':
        $form = $args[0];
        $class = wbFormClass($form);
        if ($class && method_exists($class,$trigger)) {
            if ($trigger == 'beforeItemRemove') {
                $data = wbItemRead($args[0],$args[1]);
            }
            $class->$trigger($data);
        }
        return $data;
        break;
    case 'func':
        $call = $name.'_'.$trigger;
        if (is_callable($call)) {
            $data = $call($data, $args);
        } else {
            wbError($type, $name, null);
        }

        return $data;
        break;
    default:
        break;
    }

    return $data;
}

function wbFurlPut($item, $string, $flag = 'update')
{
    $res = false;
    $table = $item['_table'];
    $id = $item['id'];
    $furl = wbFurlGenerate($string);
    if (!in_array('furl_index', $_ENV['tables'], true)) {
        wbTableCreate('furl_index');
    }
    $item = wbItemRead('furl_index', $table);
    if (!isset($item)) {
        $item = array('id' => $table, 'furl' => array());
    }
    switch ($flag) {
    case 'update':
        foreach ($item['furl'] as $f => $fid) {
            if ($id == $fid) {
                unset($item['furl'][$f]);
            }
        }
        $item['furl'][$furl] = $id;
        $res = $furl;
        break;
    case 'remove':
        foreach ($item['furl'] as $f => $fid) {
            if ($id == $fid) {
                unset($item['furl'][$f]);
            }
        }
        break;
    }
    $res = wbItemSave('furl_index', $item);
    if ($res) {
        $res = $furl;
    }
    unset($item,$table,$id,$furl);

    return $res;
}

function wbFurlGet($table, $furl)
{
    $res = false;
    if (!in_array('furl_index', $_ENV['tables'], true)) {
        wbTableCreate('furl_index');
    }
    $item = wbItemRead('furl_index', $table);
    if (isset($item['furl'][$furl])) {
        return $item['furl'][$furl];
    }

    return $res;
}

function wbFurlGenerate($str)
{
    $str = mb_strtolower(wbTranslit($str));
    $str = mb_ereg_replace('[^A-Za-z0-9 ]', ' ', $str);
    $str = str_replace(' ', '-', trim($str));
    $str = str_replace('--', '', trim($str));

    return $str;
}

function wbError($type, $name, $error = '__return__error__', $args = null)
{
    if (null == $error) {
        if (isset($_ENV['error'][$type][$name])) {
            unset($_ENV['error'][$type][$name]);
        }
        $_ENV["last_error"]=null;
    } else {
        if (is_array($args) and isset($_ENV['errors'][$error])) {
            foreach ($args as $key => $arg) {
                if (is_array($arg)) {
                    $arg = implode(',', $arg);
                }
                $_ENV['errors'][$error] = str_replace('{{'.$key.'}}', $arg, $_ENV['errors'][$error]);
            }
        }

        if ('__return__error__' == $error) {
            $error = $_ENV['error'][$type][$name];
        } else {
            if (isset($_ENV['errors'])) {
                $_ENV['error'][$type][$name] = array('errno' => $error, 'error' => $_ENV['errors'][$error]);
            } else {
                $_ENV['error'][$type][$name] = array('errno' => $error, 'error' => 'unknown error');
            }
        }
        $_ENV["last_error"]=$error;
    }
    return $error;
}

function wbErrorOut($error, $ret = false)
{
    if ($ret == false) {
        echo $_ENV['errors'][$error];
    } else {
        return $_ENV['errors'][$error];
    }
}


function wbOconv($value, $oconv)
{
    $oconv = htmlspecialchars_decode($oconv, ENT_QUOTES);
    $value = htmlspecialchars_decode($value, ENT_QUOTES);
    if (is_callable($oconv)) {
        $oconv = '$result = '.$oconv.'("'.$value.'");';
        eval($oconv);
        return $result;
    }
}

function wbEnvData($index, $value="__wb__null__data__")
{
    $loop=explode("->", $index);
    $index='$_ENV["data"]';
    $count=count($loop);
    $i=0;
    $res = false;
    foreach ($loop as $key) {
        $i++;
        if ($key=="") {
            $key="undefined";
        } else {
            $key=preg_replace('/[^ a-zа-яё\d]/ui', '_', $key);
        }
        $index.="->".$key;
        if (!eval('return isset( '.$index.' );')) {
            eval($index.' = new stdClass();');
        }
        if ($i==$count) {
            if ($value=="__wb__null__data__") {
                $res = eval('return '.$index.';');
            } else {
                $res = eval('return '.$index.' = $value;');
            }
        }
    }
    return $res;
}

function wbPage404(&$app=null)
{
    header("HTTP/1.0 404 Not Found");
    if ($app == null) {
        $app = new wbApp();
    }
    $_ENV["route"]["error"]="404";
    $dom = $app->getTpl("404.php");
    if (is_object($dom)) {
        $dom->fetch();
    }
    wbLog("func", __FUNCTION__, 404, $_ENV["route"]);
    return $dom;
}


function wbErrorList()
{
    $_ENV['errors'] = array(
                          100 => 'Login succeessful {{l}}',
                          101 => 'Login incorrect {{l}}',
                          404 => 'Page not found',
                          1001 => 'Table {{0}} not exists',
                          1002 => 'Table {{0}} already exixts',
                          1003 => 'Do not remove {{0}}',
                          1004 => 'Failed to remove file {{0}}',
                          1005 => 'Failed to remove table {{0}}',
                          1006 => 'Item {{1}} in table {{0}} not exists',
                          1007 => 'Failed to save record to table {{0}}',
                          1008 => 'Delete item {{1}} in table {{0}}',
                          1009 => 'Flush data from cache table {{0}}',
                          1010 => 'Failed to create table {{0}}',
                          1010 => 'Create a table {{0}}',
                          1011 => 'Template not found {{0}}',
                          1012 => 'Form not found {{0}}',
                          1013 => 'PHP code not valid'
                      );
}

function wbLog($type, $name, $error, $args)
{
    if (isset($_ENV['errors'][$error])) {
        $error = array('errno' => $error, 'error' => $_ENV['errors'][$error]);
    } else {
        $error = wbError($type, $name);
    }
    if (is_array($args)) {
        foreach ($args as $key => $arg) {
            if (is_array($arg)) {
                $arg = implode(',', $arg);
            }
            $error['error'] = str_replace('{{'.$key.'}}', $arg, $error['error']);
        }
    }
    if (isset($_ENV["settings"]["log"]) and $_ENV["settings"]["log"]=="on") {
        error_log("{$type} {$name} [{$error['errno']}]: {$error['error']} [{$_SERVER['REQUEST_URI']}]");
    }
}

function wbNewId($separator = '', $prefix = '')
{
    $mt = explode(' ', microtime());
    $md = substr(str_repeat('0', 2).dechex(ceil($mt[0] * 10000)), -4);
    $id = dechex(time() + rand(100, 999));
    if ($prefix > '') {
        $id = $prefix.$separator.$id.$md;
    } else {
        $id = "id".$id.$separator.$md;
    }
    return $id;
}

function wbGetItemImg($Item = null, $idx = 0, $noimg = '', $imgfld = 'images', $visible = true)
{
    $res = false;
    $count = 0;

    if (!is_file("{$_ENV['path_app']}/{$noimg}")) {
        if (is_file("{$_ENV['path_engine']}/uploads/__system/{$noimg}")) {
            $noimg = "/engine/uploads/__system/{$noimg}";
        } else {
            $noimg = '/engine/uploads/__system/image.jpg';
        }
    }
    $image = $noimg;

    if (isset($Item[$imgfld])) {
        if (!is_array($Item[$imgfld])) {
            $Item[$imgfld] = json_decode($Item[$imgfld], true);
        }
        if (!is_array($Item[$imgfld])) {
            $Item[$imgfld] = array();
        }
        foreach ($Item[$imgfld] as $key => $img) {
            if (!isset($img['visible'])) {
                $img['visible'] = 1;
            }

            if (false == $res and ((true == $visible and 1 == $img['visible']) or false == $visible) and is_file("{$_ENV['path_app']}/uploads/{$Item['_table']}/{$Item['id']}/{$img['img']}")) {
                if ($idx == $count) {
                    $image = "/uploads/{$Item['_table']}/{$Item['id']}/{$img['img']}";
                    $res = true;
                }
                ++$count;
            }
        }
        unset($img);
    }

    return urldecode($image);
}

function wbImagesToText($Item, $fld = 'text', $imgs = 'images')
{
    if (isset($Item[$imgs])) {
        $image = wbGetItemImg($Item, 0, 0, $imgs);
        $image = substr($image, strlen($_ENV['path_app']));
        $Item['_image'] = $image;
        if (!isset($Item[$fld])) {
            $Item[$fld]="";
        }
        if (isset($Item['intext_position']) and $Item['intext_position']['pos'] > '') {
            if ('' == $Item['intext_position']['width']) {
                $width = $_ENV['intext_width'];
            } else {
                $width = $Item['intext_position']['width'];
            }
            if ('' == $Item['intext_position']['height']) {
                $height = $_ENV['intext_height'];
            } else {
                $height = $Item['intext_position']['height'];
            }
            $img = "
                   <a href='{$image}' data-fancybox='gallery' class='wb-intext'>
                                                        <img data-wb-role='thumbnail' data-wb-size='{$width};{$height};src' src='{$image}' style='float:{$Item['intext_position']['pos']};' data-wb-hide='wb'>
                                                                </a>
                                                                ";
            $Item[$fld] = $img.$Item[$fld];
        }
        if (isset($Item['images_position']) and isset($Item['images_position']['pos']) and $Item['images_position']['pos'] > '') {
            $gal = wbGetForm('common', 'gallery');
            $gal->wbSetData($Item);
            if ($image > '' and $Item['intext_position']['pos'] > '') {
                if ($gal->find("a[href='{$image}'][idx]")->length) {
                    $gal->find("a[href='{$image}']")->remove();
                } else {
                    $gal->find("a[href='{$image}']")->parents('[idx]')->remove();
                }
            }
            if (!$gal->find('a')->length) {
                $gal->find('.wb-gallery')->remove();
            }
            if ('top' == $Item['images_position']['pos']) {
                $Item[$fld] = $gal->outerHtml().$Item[$fld];
            } elseif ('bottom' == $Item['images_position']['pos']) {
                $Item[$fld] = $Item[$fld].$gal->outerHtml();
            }
            unset($gal);
        }
    }

    return $Item;
}


function wbListFiles($dir)
{
    $list = array();
    if (is_dir($dir) and $dircont = scandir($dir)) {
        $i = 0;
        $idx = 0;
        while (isset($dircont[$i])) {
            if ('.' !== $dircont[$i] && '..' !== $dircont[$i]) {
                $current_file = "{$dir}/{$dircont[$i]}";
                if (is_file($current_file)) {
                    $list[] = "{$dircont[$i]}";
                }
            }
            ++$i;
        }
    }

    return $list;
}

function wbFileRemove($file)
{
    $res = false;
    if (is_file($file) and wbRole('admin')) {
        unlink($file);
        if (is_file($file)) {
            $res = false;
        } else {
            $res = true;
        }
    }

    return $res;
}

function wbPutContents($dir, $contents)
{
    $parts = explode('/', $dir);
    $file = array_pop($parts);
    $dir = '';
    $u=umask();
    foreach ($parts as $part) {
        if (!is_dir($dir .= "/$part")) {
            mkdir($dir);
        }
    }
    umask($u);

    return file_put_contents("$dir/$file", $contents);
}

function wbRecurseDelete($src)
{
    $dir = opendir($src);
    if (is_resource($dir)) {
        while (false !== ($file = readdir($dir))) {
            if (('.' !== $file) && ('..' !== $file)) {
                if (is_dir($src.'/'.$file)) {
                    wbRecurseDelete($src.'/'.$file);
                } else {
                    unlink($src.'/'.$file);
                }
            }
        }
        closedir($dir);
        if (is_dir($src)) {
            rmdir($src);
        }
    }
}

function wbRecurseCopy($src, $dst)
{
    $mask=umask();
    if (is_file($src)) {
        copy($src, $dst);
    } else {
        $dir = opendir($src);
        if (is_resource($dir)) {
            if (!is_dir($dst)) {
                mkdir($dst);
            }
            while (false !== ($file = readdir($dir))) {
                if (('.' !== $file) && ('..' !== $file)) {
                    if (is_dir($src.'/'.$file)) {
                        wbRecurseCopy($src.'/'.$file, $dst.'/'.$file);
                        @chmod($dst.'/'.$file, 0777);
                    } else {
                        copy($src.'/'.$file, $dst.'/'.$file);
                        @chmod($dst.'/'.$file, 0766);
                    }
                }
            }
            closedir($dir);
        }
    }
    umask($mask);
}

function wbQuery($sql)
{
    require_once $_ENV['path_engine'].'/lib/sql/PHPSQLParser.php';

    $parser = new PHPSQLParser();
    $p = $parser->parse($sql);
    $sid = md5($sql.$_ENV["lang"].$_ENV["lang"]);

    foreach ($p as $r => $a) {
        foreach ($a as $e) {
            wbt_route($sid, $e, $r);
        }
    }

    $table = array();
    $join = array();
    foreach ($_ENV['sql'][$sid]['FROM'] as $key => $t) {
        if (!isset($t['join'])) {
            // join пока не работает
            $table['name'] = $key;
            $table['table'] = $t['name'];
            $table['data'] = wbItemList(wbTable($t['name']));
        } else {
            $join[$key]['name'] = $key;
            $join[$key]['data'] = wbItemList(wbTable($t['name']));
            $join[$key]['join'] = $t['join'];
        }
    }
    if (isset($_ENV['sql'][$sid]['WHERE'])) {
        $where = wbt_where($table['name'], $_ENV['sql'][$sid]['WHERE']);
    }

    $object = new ArrayObject($table['data']);
    foreach ($object as $key => $item) {
        $call = $table['table'].'AfterReadItem';
        if (is_callable($call)) {
            $item = $call($item);
        }
        $object[$key] = $item;
    }
    $iterator = new wbt_filter($object->getIterator(), 'where', array('where' => $where, 'table' => $table['name'], 'join' => $join));
    $table['data'] = iterator_to_array($iterator);
    $iterator->rewind();

    unset($_ENV['sql'][$sid]);

    return $table['data'];
}

function wbItemFilter($item, $filter)
{
    $fields = new Dot();
    $fields->setReference($item);
    $result = true;
    foreach ((array)$filter as $fld => $expr) {
        if ((array)$expr !== $expr) {
            if ($fields->get($fld) !== $expr) {
                $result = false;
            }
        } else {
            if ($fld == '$or') {
                $result = false;
                foreach($expr as $orFilter) {
                    if (wbItemFilter($item, $orFilter) == true) $result = true;
                }
            } else if ($fld == '$and') {
                $result = true;
                foreach($expr as $andFilter) {
                    if (wbItemFilter($item, $andFilter) == false) {
                      $result = false;
                      break;
                    }
                }
            } else {
                foreach ($expr as $cond => $val) {
                    switch($cond) {
                        case '$ne':
                            if ($fields->get($fld) == $val) $result = false;
                            break;
                        case '$like':
                            if (!preg_match('/'.$val.'/ui', $fields->get($fld))) $result = false;
                            break;
                        case '$gte':
                            if (!($val >= $fields->get($fld))) $result = false;
                            break;
                        case '$lte':
                            if (!($val <= $fields->get($fld))) $result = false;
                            break;
                        case '$gt':
                            if (!($val > $fields->get($fld))) $result = false;
                            break;
                        case '$lt':
                            if (!($val < $fields->get($fld))) $result = false;
                            break;
                    }

                }

            }
        }
        if ($result == false) {
            break;
        }
    }
    return $result;
}


function wbWhereItem($item, $where = null)
{
    if (null == $where) {
        return true;
    }
    $where = htmlspecialchars_decode($where);
    $res = true;
    if (strpos($where, "}}")) {
        $where = wbSetValuesStr($where, $item);
    }

    if ('%' == $where[0]) {
        $phpif = substr($where, 1);
    } else {
        $phpif = wbWherePhp($where, $item);
    }
    if ($phpif > '') {
        //echo $where."<br>";
        //echo $phpif."<br>";
        eval('return $res = ( '.$phpif.' );');
    }

    return $res;
}

function wbWherePhp($str = '', $item = array())
{
    if (strpos($str, "}}")) {
        $str = wbSetValuesStr($str, $item);
        //$str=preg_replace("~\{\{([^(}})]*)\}\}~","",$str);
        $str=preg_replace("~\{\{(.*)\}\}~", "", $str);
    }
    $cache=md5($str);
    if (!isset($_ENV["cache"][__FUNCTION__])) {
        $_ENV["cache"][__FUNCTION__]=array();
    }
    if (isset($_ENV["cache"][__FUNCTION__][$cache])) {
        return $_ENV["cache"][__FUNCTION__][$cache];
    }
    $exclude = array(
        'AND'	=> 0,
        'OR'	=> 0,
        'ARRAY'	=> 0,
        'LIKE'		=>array("func2"=>"wbWhereLike"),
        'IN_ARRAY'	=>array("func1"=>"in_array"),
        'IN'		=>array("func"=>"in_array"),
        'NOT_LIKE'	=>array("func2"=>"!wbWhereNotLike"),
        'NOT_IN_ARRAY'	=>array("arr"=>"!in_array"),
        'NOT_IN'	=>array("arr"=>"!in_array"),
    );
    $cond=array('<','>','=','==','>=','<=','!=','!==','#',"(",")","<>",",");
    $re = '/"(?:[^"\\\\]|\\\\.)*"|\'(?:[^"\\\\]|\\\\.)*\'|\{\{([^(}})]*)\}\}|\w+(?!\")\b|\[(.*?)\]|[=!#<>\,]+|[\(\)]/ium';
    preg_match_all($re, $str, $arr, PREG_SET_ORDER);
    $str="";
    $len=0;
    $flag=0;
    foreach ($arr as $index => $fld) {
        $fld=$fld[0];
        $sup=strtoupper($fld);
        $exc=isset($exclude[$sup]);
        $con=in_array($fld, $cond);
        if ($flag==1) {
            $flag=2;
        }
        if ((isset($item[$fld]) and ((array)$item[$fld] === $item[$fld])) or isset($tmpfld)) {
            if (isset($arr[$index+1]) and substr($arr[$index+1][0], 0, 1) == "[") {
                if (!isset($tmpfld)) {
                    $tmpfld = str_replace("{$fld}", ' $item["'.$fld.'"]', $fld);
                    $flag=3;
                } else {
                    $tmpfld.= $fld;
                }
            }
        }

        if ($flag!==3) {
            if ((substr($fld, 0, 1)!=='"' or substr($fld, 0, 1)!=="'") and !$exc and !$con) {
                if (isset($item[$fld])) {
                    if (is_array($item[$fld])) {
                        $fld="'".wbJsonEncode($item[$fld])."'";
                        //$fld='wbJsonEncode($item["'.$fld.'"])';
                        if ($fld=="null") {
                            $fld=' "[]" ';
                        } else {
                            $fld=htmlentities($fld);
                        }
                    } else {
                        $fld = str_replace("{$fld}", ' $item["'.$fld.'"] ', $fld);
                    }
                } elseif ((substr($fld, 0, 1)=='"' or substr($fld, 0, 1)=="'") or $fld=="''" or $fld=='""') {
                    // строки в кавычках
                } elseif ($fld > "" and $fld!=="''" and $fld!=='""') {
                    if (!is_numeric($fld) and is_string($fld)) {
                        $fld = str_replace("{$fld}", ' $item["'.$fld.'"] ', $fld);
                    }
                }
            } elseif ($exc and $flag==0) {
                $prev=substr($str, -$len);
                if (isset($exclude[$sup]) and isset($exclude[$sup]["func1"])) {
                    $str=substr($str, 0, -$len);
                    if ($str>"") {
                        $str.="(";
                    }
                    $str.=$exclude[$sup]["func1"];
                    $fld="";
                } elseif (isset($exclude[$sup]) and isset($exclude[$sup]["func2"])) {
                    $str=substr($str, 0, -$len);
                    $str.=$exclude[$sup]["func2"]."(".$prev;
                    $flag = 1;
                    $fld="";
                } elseif (isset($exclude[$sup]) and isset($exclude[$sup]["arr"])) {
                    $str=substr($str, 0, -$len);
                    $str.=$exclude[$sup]["arr"]."(".$prev.", array";
                    $flag = 4;
                    $fld="";
                } elseif (isset($exclude[$sup]) and isset($exclude[$sup]["func1"])) {
                    $fld=$exclude[$sup]["func1"];
                }
            } elseif ($exc and $flag==4) {
                $str.=")";
                $flag=0;
            } elseif ($con) {
                $fld = strtr($fld, array(
                    '>' => '>',
                    '<' => ' < ',
                    '>=' => ' >= ',
                    '<=' => ' <= ',
                    '<>' => ' !== ',
                    '!=' => ' !== ',
                    '!==' => ' !== ',
                    '#' => ' !== ',
                    '==' => ' == ',
                    '=' => ' == ',
                ));
                if ($str=="") {
                    $str='""';
                }
            }
        }
        if ($flag==3 and (!isset($arr[$index+1]) or substr($arr[$index+1][0], 0, 1) !== "[")) {
            $fld=wbSetQuotes($tmpfld.$fld);
            eval('$arr=is_array('.$fld.');');
            if ($arr) {
                eval('$fld=wbJsonEncode('.$fld.');');
            }
            $str.=" ".$fld;
            $flag=0;
        } elseif ($flag==2) {
            $str.=", ".$fld." ) ";
            $flag=0;
        } elseif ($flag!==2 and $flag!==3) {
            $len=strlen($fld);
            $str.=" ".$fld;
        }
    }
    if ($flag==4) {
        $str.=")";
    }
    //$str=preg_replace("~\{\{([^(}})]*)\}\}~","",$str);
    $_ENV["cache"][__FUNCTION__][$cache]=$str;
    return $str;
}

function wbRouterAdd($route = null, $destination = null)
{
    if (null == $route) { // Роутинг по-умолчанию
        $route = wbRouterRead();
    }
    wbRouter::addRoute($route, $destination);
}

function wbRouterRead($file = null)
{
    if (null == $file) {
        $file = $_ENV['path_engine'].'/router.ini';
        $route = wbRouterRead($file);
        if (is_file($_ENV['path_app'].'/router.ini')) {
            $route = array_merge(wbRouterRead($_ENV['path_app'].'/router.ini'), $route);
        }
    } else {
        if (is_file($file)) {
            $route = array();
            $router = new ArrayIterator(file($file));
            foreach ($router as $key => $r) {
                $r = explode('=>', $r);
                if (2 == count($r)) {
                    $route[trim($r[0])] = trim($r[1]);
                }
            }
        }
    }

    return $route;
}


function wbAuthGetContents($url, $get=null, $username=null, $password=null)
{
    if (func_num_args()==3) {
        $password=$username;
        $username=$get;
        $get=array();
    }
    if (!is_array($get)) {
        $get=(array)$get;
    }
    $cred = sprintf('Authorization: Basic %s', base64_encode("$username:$password"));
    $opts = array(
                'http'=>array(
                    'method'=>'GET',
                    'header'=>$cred,
                    'content'=>$get
                ),
                 "ssl"=>array(
                     "verify_peer"=>false,
                     "verify_peer_name"=>false,
                 )
            );
    $context = stream_context_create($opts);
    session_write_close();
    $result = file_get_contents($url, false, $context);
    return $result;
}

function wbAuthPostContents($url, $post=null, $username=null, $password=null)
{
    if (func_num_args()==3) {
        $password=$username;
        $username=$get;
        $post=array();
    }
    if (!is_array($post)) {
        $post=(array)$post;
    }

    $cred = sprintf('Authorization: Basic %s', base64_encode("$username:$password"));
    $post=http_build_query($post);
    $opts = array(
                'http'=>array(
                    'method'=>'POST',
                    'header'=>$cred."\r\nCookie: ".session_name()."=".session_id()."\r\n",
                    'content'=>$post
                ),
                 "ssl"=>array(
                     "verify_peer"=>false,
                     "verify_peer_name"=>false,
                 )
            );
    $context = stream_context_create($opts);
    session_write_close();
    $result = file_get_contents($url, false, $context);
    return $result;
}

function wbPasswordCheck($str, $pass)
{
    $res=false;
    if (wbPasswordMake($str) == $pass) {
        $res=true;
    }
    return $res;
}

function wbPasswordMake($str)
{
    return md5($str);
}

function wbRouterGet($requestedUrl = null)
{
    return wbRouter::getRoute($requestedUrl);
}


function wbAttrToArray($attr)
{
    return wbArrayAttr($attr);
}

function wbAttrAddData($data, $Item, $mode = false)
{
    $data = stripcslashes(html_entity_decode($data));
    $data = json_decode($data, true);
    if (!is_array($Item)) {
        $Item = array($Item);
    }
    if (false == $mode) {
        $Item = array_merge($data, $Item);
    }
    if (true == $mode) {
        $Item = array_merge($Item, $data);
    }

    return $Item;
}

function wbGetWords($str, $w = 100)
{
    $res = '';
    $str = html_entity_decode(strip_tags(trim($str)));
    $arr = explode(' ', trim($str));
    for ($i = 0; $i <= $w; ++$i) {
        if (isset($arr[$i])) {
            $res = $res.' '.$arr[$i];
        }
    }
    if (count($arr) > $w) {
        $res = $res.'...';
    }
    $res = trim($res);

    return $res;
}

function wbPhoneFormat($phoneNumber) {
    $phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);

    if(strlen($phoneNumber) > 10) {
        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
        $areaCode = substr($phoneNumber, -10, 3);
        $nextThree = substr($phoneNumber, -7, 3);
        $lastFour = substr($phoneNumber, -4, 4);

        $phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
    }
    else if(strlen($phoneNumber) == 10) {
        $areaCode = substr($phoneNumber, 0, 3);
        $nextThree = substr($phoneNumber, 3, 3);
        $lastFour = substr($phoneNumber, 6, 4);

        $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
    }
    else if(strlen($phoneNumber) == 7) {
        $nextThree = substr($phoneNumber, 0, 3);
        $lastFour = substr($phoneNumber, 3, 4);

        $phoneNumber = $nextThree.'-'.$lastFour;
    }

    return $phoneNumber;
}

function wbSetValuesStr($tag = "", $Item = array(), $limit = 2, $vars = null)
{
    if ((object)$tag === $tag) $tag = $tag->outer();
    if (!strpos($tag, "}}")) return $tag;

    $processor = new WEProcessor($Item);
    $tag = $processor->substitute($tag);
    return $tag;
}



// добавление кавычек к нечисловым индексам
function wbSetQuotes($In)
{
    $err = false;
    $mask = '`\[(%*[\w\d]+)\]`u';
    $nBrackets = preg_match_all($mask, $In, $res, PREG_OFFSET_CAPTURE);				// найти индексы без кавычек
    if ($nBrackets === false) {
        echo 'Ошибка в шаблоне индексов. Обратитесь к разработчику.' . '<br>';
        $err = true;
    } else {
        if ($nBrackets == 0) {
            if (substr($In, 0, 2) != '["') {
                if (!is_numeric($In)) {
                    $In = '"' . $In . '"';
                }
                $In = '[' . $In . ']';
            }
        } else {
            for ($i = 0; $i < $nBrackets; $i++) {
                if (!is_numeric($res[1][$i][0])) {
                    $In = str_replace('['.$res[1][$i][0].']', '["'.$res[1][$i][0].'"]', $In);
                }
            }
        }
    }
    return $In;
}

// заменяем &quot на "
function wbChangeQuot($Tag)
{
    $mask = '`&quot[^;]`u';
    $nQuot = preg_match_all($mask, $Tag, $res, PREG_OFFSET_CAPTURE);				// найти &quot без последеующего ;
    if ($nQuot === false) {
        echo 'Ошибка в шаблоне &quot. Обратитесь к разработчику.' . '<br>';
        $err = true;
        $In = $tag;
    } else {
        if ($nQuot == 0) {
            $In = $Tag;
        } else {
            $In = '';
            $startIn = 0;		// начальная позиция текста за предыдущей заменой
            for ($i = 0; $i < $nQuot; $i++) {
                $beforSize = $res[0][$i][1] - $startIn;
                $In .= substr($Tag, $startIn, $beforSize) . '"';		// исходный текст между предыдущей и текущей &quot
                $startIn += $beforSize + 5;
                if ($i+1 == $nQuot) {		// это была последняя &quot
                    $In .= substr($Tag, $startIn, strlen($Tag) - $startIn);
                }
            }
        }
    }
    return $In;
}

function wbRole($role, $userId = null)
{
    $res = false;
    if (!is_array($role)) {
        $role = wbAttrToArray($role);
    }
    if (null == $userId) {
        $res = in_array($_SESSION['user_role'], $role, true);
    } else {
        $user = wbReadItem('users', $userId);
        $res = in_array($user['role'], $role, true);
    }

    return $res;
}

function wbControls($set = '')
{
    $res = '*';
    $controls = '[data-wb-role],[data-wb]';
    $allow = '[data-wb-allow], [data-wb-disallow], [data-wb-disabled], [data-wb-enabled], [data-wb-readonly], [data-wb-writable]';
    $target = '[data-wb-prepend], [data-wb-append], [data-wb-remove], [data-wb-before], [data-wb-after], [data-wb-html], [data-wb-replace], [data-wb-selector], [data-wb-addclass], [data-wb-removeclass], [data-wb-prependto], [data-wb-appendto], [data-wb-htmlto], [data-wb-removeattr], [data-wb-attr], [data-wb-src], [data-wb-clear]';
    $tags = array('dict', 'tree', 'gallery', 'imageloader', 'thumbnail', 'uploader','multiinput', 'where');
    foreach (array_keys($_ENV["tags"]) as $tag) {
        if (!in_array($tag, $tags)) {
            $tags[]=$tag;
        }
    }

    if ('' !== $set) {
        $res = $$set;
    } else {
        $res = "{$controls},{$allow},{$target}";
    }
    unset($controls,$allow,$target);

    return $res;
}

function wbListForms($exclude = true)
{
    if (isset($_ENV["list_forms"])) {
        return $_ENV["list_forms"];
    }
    if (true == $exclude) {
        $exclude = array('forms/common', 'forms/admin', 'forms/source', 'forms/snippets');
    } elseif (!is_array($exclude)) {
        $exclude = array('forms/snippets');
    }
    $list = array();
    $eList = wbListFilesRecursive($_ENV['path_engine'].'/forms', true);
    $aList = wbListFilesRecursive($_ENV['path_app'].'/forms', true);
    $jList = wbListFilesRecursive($_ENV['path_app'].'/database', true);
    $arr = $eList;
    foreach ($aList as $a) {
        $arr[] = $a;
    }
    foreach ($jList as $a) {
        $arr[] = $a;
    }
    unset($eList,$aList);
    foreach ($arr as $i => $data) {
        $name = $data['file'];
        $path = $data['path'];
        $path = str_replace(array($_ENV['path_engine'], $_ENV['path_app']), array('.', '.'), $path);
        $inc = strpos($name, '.inc');
        $ext = explode('.', $name);
        $ext = $ext[count($ext) - 1];
        $name = substr($name, 0, -(strlen($ext) + 1));
        $name = explode('_', $name);
        $name = $name[0];
        foreach ($exclude as $exc) {
            if (!strpos($path, $exc)) {
                $flag = true;
            } else {
                $flag = false;
            }
        }
        if (('php' == $ext or 'htm' == $ext or 'json' == $ext) && !$inc && true == $flag && $name > '' && $name !== "admin" && !in_array($name, $list, true)) {
            $list[] = $name;
        }
    }
    unset($arr);
    //$merchE=wbCheckoutForms(true);
    //$merchA=wbCheckoutForms();
    //foreach($merchE as $m) {if (in_array($m["name"],$list)) {unset($list[array_search($m["name"],$list)]);}}
    //foreach($merchA as $m) {if (in_array($m["name"],$list)) {unset($list[array_search($m["name"],$list)]);}}
    if (in_array('form', $list, true)) {
        unset($list[array_search('form', $list, true)]);
    }
    $_ENV["list_forms"] = $list;
    return $list;
}

function wbListDrivers()
{
    $arr = [];
    $p=[$_ENV['path_engine'].'/drivers',$_ENV['path_app'].'/drivers'];
    foreach ($p as $d) {
        if (is_dir($d)) {
            $list = scandir($d);
        }
        foreach ($list as $e) {
            if (!in_array($e, [".",".."]) and substr($e, 1)!=="_" and !in_array($e, $arr) and is_dir($d.'/'.$e) and is_file($d.'/'.$e.'/'.$e.".php")) {
                $arr[$e] = $d.'/'.$e.'/'.$e.".php";
            }
        }
    }
    return $arr;
}


function wbListModules(&$app = null)
{
    $arr = [];
    $p=[$_ENV['path_engine'].'/modules',$_ENV['path_app'].'/modules'];
    foreach ($p as $d) {
        if (is_dir($d)) {
            $list = scandir($d);
        }
        foreach ($list as $e) {
            if (!in_array($e, [".",".."]) and substr($e, 1)!=="_" and !in_array($e, $arr) and is_dir($d.'/'.$e) and is_file($d.'/'.$e.'/'.$e.".php")) {
                $arr[$e] = $d.'/'.$e.'/'.$e.".php";
                if ($app !== null) {
                    $app->addModule($e, $arr[$e]);
                }
            }
        }
    }
    return $arr;
}

function wbListTags()
{
    if (isset($_ENV["list_tags"])) {
        return $_ENV["list_tags"];
    }
    $list = array();
    $eList = wbListFilesRecursive($_ENV['path_engine'].'/tags', true);
    $aList = wbListFilesRecursive($_ENV['path_app'].'/tags', true);
    $arr = $eList;
    foreach ($aList as $a) {
        $arr[] = $a;
    }
    unset($eList,$aList);
    foreach ($arr as $i => $data) {
        $name = $data['file'];
        $path = $filepath = $data['path'];
        $path = str_replace(array($_ENV['path_engine'], $_ENV['path_app']), array('.', '.'), $path);
        $inc = strpos($name, '.inc');
        $ext = explode('.', $name);
        $ext = $ext[count($ext) - 1];
        $name = substr($name, 0, -(strlen($ext) + 1));
        $name = explode('_', $name);
        $name = $name[0];
        if (('php' == $ext) && !$inc && $name > '' && !in_array($name, $list, true)) {
            $list[strtolower($name)] = $filepath."/{$name}.{$ext}";
        }
    }
    unset($arr);
    foreach (array_keys($list) as $name) {
        require_once $list[$name];
    }
    $_ENV["list_tags"] = $list;
    return $list;
}

function wbListLocales(&$app = null)
{
    if (!$app) {
        $app = new wbApp();
    }
    $out = $app->getForm("admin", "common.ini", true);
    $out->setLocale(parse_ini_string(trim($out->text()), true));
    return $out->locale;
}

function wbListFormsFull()
{
    $list = array();
    $types = array('engine', 'app');
    foreach ($types as $type) {
        $list[$type] = array();
        $fList = wbListFilesRecursive($_ENV['path_'.$type].'/forms');
        foreach ($fList as $fname) {
            $inc = strpos($fname, '.inc');
            $ext = explode('.', $fname);
            $ext = $ext[count($ext) - 1];
            $name = substr($fname, 0, -(strlen($ext) + 1));
            $tmp = explode('_', $name);
            $form = $tmp[0];
            unset($tmp[0]);
            $mode = implode('_', $tmp);
            //$uri_path=str_replace($_SESSION["root_path"],"",$_ENV["path_".$type]);
            $uri_path = '';
            $data = array(
                        'type' => $type,
                        'path' => $_ENV['path_'.$type]."/forms/{$form}/".$name.".{$ext}",
                        'dir' => "/forms/{$form}",
                        'uri' => $uri_path."/forms/{$form}/".$fname,
                        'form' => $form,
                        'file' => $fname,
                        'ext' => $ext,
                        'name' => $name,
                        'mode' => $mode,
                    );
            $list[$type][] = $data;
        }
    }

    return $list;
}

function wbArraySortMulti($array = array(), $args = array('votes' => 'd'))
{
    return wbArraySort($array, $args);
}
function wbArraySort($array = array(), $args = array('votes' => 'd'))
{
    // если передан атрибут, то предварительно готовим массив параметров
    if (is_string($args) && $args > '') {
        $args = wbArrayAttr($args);
        $param = array();
        foreach ($args as $ds) {
            $tmp = explode(':', $ds);
            if (!isset($tmp[1])) {
                $tmp[1] = 'a';
            }
            $param[$tmp[0]] = $tmp[1];
        }
        $args = $param;
        unset($param,$tmp,$ds);
    }
    // сортировка массива по нескольким полям
    uasort($array, function ($a, $b) use ($args) {
        $res = 0;
        $a = (object) $a;
        $b = (object) $b;
        foreach ($args as $k => $v) {
            if (isset($a->$k)) {
                $a->$k=mb_strtolower($a->$k);
            }
            if (isset($b->$k)) {
                $b->$k=mb_strtolower($b->$k);
            }
            if (isset($a->$k) && isset($b->$k)) {
                if ($a->$k == $b->$k) {
                    continue;
                }
                $res = ($a->$k < $b->$k) ? -1 : 1;
                if ('d' == $v) {
                    $res = -$res;
                }
                break;
            }
        }

        return $res;
    });

    return $array;
}

function wbArrayAttr($attr)
{
    $attr = str_replace(',', ' ', $attr);
    $attr = str_replace(';', ' ', $attr);
    $attr = explode(" ", trim($attr));
    foreach($attr as $k => $v) {
        if ($v == "") unset($attr[$k]);
    }
    return $attr;
}

function wbNormalizePath($path)
{
    return realpath($path);
}

function wbClearValues($out, $rep='')
{
    $out = preg_replace('/\{\{([^\}]+?)\}\}+|<script.*text\/template.*?>.*?<\/script>(*SKIP)(*F)|<template.*?>.*?<\/template>(*SKIP)(*F)/isumx', $rep, $out);
    return $out;
}

function wbListTpl()
{
    if (isset($_ENV["list_tpl"])) {
        return $_ENV["list_tpl"];
    }
    $dir=$_ENV['path_tpl'];
    $list = [];
    $result = [];
    if (is_dir($dir)) {
        $list=wbListFilesRecursive($dir, true);
        foreach ($list as $l=> $val) {
            if (('.php' == substr($val['file'], -4) or '.htm' == substr($val['file'], -4) or '.tpl' == substr($val['file'], -4)) and !strpos('.inc.', $val['file'])) {
                $path = str_replace($dir, '', $val['path']);
                $res = substr($path.'/'.$val['file'], 1);
                $result[] = $res;
            }
        }
    }
    sort($result);
    $_ENV["list_tpl"] = $result;
    return $result;
}

    function wbListFilesRecursive($dir, $path = false)
    {
        $list = array();
        if (is_dir($dir)) {
            $stack[] = $dir;
        } else {
            $stack=array();
        }
        while ($stack) {
            $thisdir = array_pop($stack);
            if (is_dir($thisdir) and $dircont = scandir($thisdir)) {
                $i = 0;
                $idx = 0;
                while (isset($dircont[$i])) {
                    if ('.' !== $dircont[$i] && '..' !== $dircont[$i]) {
                        $current_file = "{$thisdir}/{$dircont[$i]}";
                        if (is_file($current_file)) {
                            if (true == $path) {
                                $list[] = array(
    'file' => "{$dircont[$i]}",
    'path' => "{$thisdir}",
    );
                            } else {
                                $list[] = "{$dircont[$i]}";
                            }
                            ++$idx;
                        } elseif (is_dir($current_file)) {
                            $stack[] = $current_file;
                        }
                    }
                    ++$i;
                }
            }
        }

        return $list;
    }

    function wbArrayWhere($arr, $where)
    {
        $res = array();
        $where=wbSetValuesStr($where);
        foreach ($arr as $key => $val) {
            if (wbWhereItem($val, $where)) {
                $res[]=$arr[$key];
            }
            unset($arr[$key]);
        }
        unset($arr);
        return $res;
    }

    function wbEval($code)
    {
        $res = eval('return '.$code.';');
        return $res;
    }

    function wbCallFunc($func)
    {
        $exclude = $_ENV['stop_func'];
        $func=ltrim($func);
        foreach ($exclude as $f) {
            $l = strlen($f);
            if (substr($func, 0, $l) == $f) {
                echo "Error!!! PHP function <b>{$func}</b> is disabled !";
                die;
            }
        }
        if (strpos($func, "(")) {
            $res = eval('return '.$func.';');
        } else {
            $res = eval('return '.$func.'();');
        }

        return $res;
    }


    function wbCallFormFunc($name, $Item, $form = null, $mode = null)
    {
        if (!isset($_GET['mode'])) {
            $_GET['mode'] = '';
        }
        if (!isset($_GET['form'])) {
            $_GET['form'] = '';
        }
        if (null == $mode) {
            $mode = $_GET['mode'];
        }
        if ('' == $mode) {
            $mode = 'list';
        }
        if (null == $form) {
            if (isset($Item['form']) && $Item['form'] > '') {
                $form = $Item['form'];
            } else {
                $form = $_GET['form'];
            }
        }
        $sf = $_GET['form'];
        $_GET['form'] = $form;
        // formCurrentInclude($form);
        $func = $form.$name;
        $_func = '_'.$func;
        //$Item=wbItemToArray($Item);
        if (is_callable($func)) {
            $Item = $func($Item, $mode);
        } else {
            if (is_callable($_func)) {
                $Item = $_func($Item, $mode);
            }
        }
        $_GET['form'] = $sf;

        return $Item;
    }

    function wbTranslit($textcyr = null, $textlat = null)
    {
        $cyr = array(
    'ё', 'ж', 'ч', 'щ', 'ш', 'ю', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ы', 'ь', 'э', 'я',
    'Ё', 'Ж', 'Ч', 'Щ', 'Ш', 'Ю', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ы', 'Ь', 'Э', 'Я', );
        $lat = array(
    'e', 'j', 'ch', 'sch', 'sh', 'u', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', '`', 'y', '', 'e', 'ya',
    'E', 'j', 'Ch', 'Sch', 'Sh', 'U', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', '`', 'Y', '', 'E', 'ya', );
        if ($textcyr) {
            return str_replace($cyr, $lat, $textcyr);
        } elseif ($textlat) {
            return str_replace($lat, $cyr, $textlat);
        } else {
            return null;
        }
    }

    function is_email($email)
    {
        $res=true;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $res=false;
        }
        return $res;
    }

    function wbBr2nl($str)
    {
        $str = preg_replace('/(rn|n|r)/', '', $str);
        return preg_replace('=<br */?>=i', 'n', $str);
    }

    function wbCheckPhpCode($code)
    {
        $file=$_ENV["path_app"]."/uploads/".wbNewId().".php";
        $umask=umask(0);
        file_put_contents($file, $code);
        umask($umask);
        exec("php -l ".$file, $error, $code);
        wbFileRemove($file);
        // ошибок нет
        if ($code == 0) {
            return true;
        }
        // ошибки есть
        return false;
    }

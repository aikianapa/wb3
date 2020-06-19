<?php
$sessdir = "/tmp";
if (!is_dir($sessdir)) mkdir($sessdir,0766,true);
ini_set('display_errors', 1);
session_start([
    "save_path" => $sessdir,
    "gc_probability" => 5,
    "gc_divisor" => 80,
    "gc_maxlifetime" => 84600,
    "cookie_lifetime" => 0
]);
if (!isset($_SESSION["lang"])) $_SESSION["lang"] = "ru";
require_once __DIR__."/functions.php";
$app = new wbApp();
die;

if (!$app->cache["check"]) {
    $app->data = array();
    $exclude  = in_array($app->vars->get("_route.controller"),array("module","ajax","thumbnails"));
		$app->dom = $app->LoadController(); //depricated!
	  if (!is_object($app->dom)) {$app->dom = $app->fromString($app->dom,true);}


  $app->dom->find("[append]","[prepend]","[after]","[before]","[html]","[text]")->fetchTargets();

    if (is_callable("wbBeforeOutput") AND !$exclude)  {
        $html = wbBeforeOutput();
        if (is_object($html)) $html = $html->outerHtml();
    } else {
        $html = $app->dom->outerHtml();
    }
	if ($cache["save"]==true) wbPutContents($cache["path"],$html);

} else if ($cache["check"]===true) {
	$html=$cache["data"];
}
session_write_close();
echo $html;
ob_flush();
?>

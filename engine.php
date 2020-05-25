<?php
$sessdir = "/tmp";
if (!is_dir($sessdir)) mkdir($sessdir,0766,true);
ini_set('display_errors', 1);
session_start([
    "save_path" => $sessdir,
    "gc_probability" => 5,
    "gc_divisor" => 80,
    "gc_maxlifetime" => 100,
    "cookie_lifetime" => 100
]);
if (!isset($_SESSION["lang"])) $_SESSION["lang"] = "en";
ob_start();
require_once __DIR__."/functions.php";

$app = new wbApp([
  "driver" => "json"
]);

$dom = $app->fromString("<b>test <i class='fa'>kjasdf</i><br><i class='fa'>4444</i></b>");
$dom->find(".fa")->each(function($n){
  $n->html("===");
});

$dom->find(".fa:first")->each(function($n){
  $n->html("<hr>");
});

$dom->find(".fa")->addClass("test");

echo "<pre>";
echo $dom->outer();
echo "</pre>";



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

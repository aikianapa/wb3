<?php
header('Cache-Control: max-age=31536000');

@session_start([
    "gc_probability" => 5,
    "gc_divisor" => 80,
    "gc_maxlifetime" => 84600,
    "cookie_lifetime" => 0
]);
ini_set('display_errors', 0);

if (!isset($_SESSION["lang"])) $_SESSION["lang"] = "ru";

require_once __DIR__.'/wbrouter.php';
require_once __DIR__."/modules/setup/requrements.php";
require_once __DIR__."/functions.php";

if (!isset($app) OR ( isset($app) && !($app === false))) $app = new wbApp();
?>

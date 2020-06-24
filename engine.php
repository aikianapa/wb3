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
session_write_close();
die;
?>

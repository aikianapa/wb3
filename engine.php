<?php
@session_start([
    "gc_probability" => 5,
    "gc_divisor" => 80,
    "gc_maxlifetime" => 84600,
    "cookie_lifetime" => 0
]);
ini_set('display_errors', 1);
if (!isset($_SESSION["lang"])) $_SESSION["lang"] = "ru";
require_once __DIR__."/modules/setup/requrements.php";
require_once __DIR__."/functions.php";

$app = new wbApp();
session_write_close();
?>

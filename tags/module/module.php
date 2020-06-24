<?php
class tagModule {

  public function __construct($dom) {
      return $this->module($dom);
  }

  public function module($dom) {
    if (!$dom->params("module")) return;
    $module = $dom->params->module;
    if ((array)$module === $module) {
        foreach($module as $name => $param) $dom->params->$name = $param;
        $module = $module["module"];
    }
    if ($dom->app->vars("_sett.modcheck") == "on" && $dom->app->vars("_sett.modules.{$module}.active") !== "on") {
        $dom->attr("data-error","Module disabled");
        return;
    }

    $e=$_ENV["path_engine"]."/modules/{$module}/{$module}.php";
    $a=$_ENV["path_app"]."/modules/{$module}/{$module}.php";
    if (is_file($a)) require_once($a);
    else if (is_file($e)) require_once($e);

    $class = "mod".ucfirst($module);
    if (class_exists($class)) {
      new $class($dom);
    } else {
      $dom->html("<div class='alert alert-secondary'><i class='ri-error-warning-line'></i> Module &laquo;{$module}&raquo; not found</div>");
    }
    $dom->unwrap("wb-module");
  }
}
?>

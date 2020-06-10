<?php
class modCms {
  function __construct($app) {
      $app->router->addRouteFile(__DIR__."/router.ini");
      $mode = $app->vars("_route.mode");
      $this->app = $app;
      if (method_exists($this,$mode)) {
        echo $this->$mode();
      } else {
        header( "HTTP/1.1 404 Not Found" );
      }
      die;
  }

  function init() {
    $app = $this->app;
    $cms = $app->fromFile(__DIR__."/tpl/cms_ui.php",true);
    $cms->fetch();
    return $cms;
  }

  function ajax() {
      $app = $this->app;
      if ($app->vars("_route.form") > "") {
          require_once(__DIR__."/cms_formsclass.php");
          require_once(__DIR__."/forms/{$app->vars("_route.form")}/_class.php");
          $class = $app->vars("_route.form")."Class";
          if (class_exists($class)) {
              $form = new $class($app);
          } else {
              $form = new cmsFormsClass($app);
          }
          $action = $app->vars("_route.action");
          $form->$action();
      }
  }

  function login() {
      unset($_SESSION["user"]);
      $app = $this->app;
      $out = $app->fromFile(__DIR__ . "/tpl/cms_login.php");
      $out->fetch();
      echo $out;
      die;
  }

  function settings() {
      $app = $this->app;
      $out = $app->fromFile(__DIR__."/forms/_settings/{$app->vars("_route.form")}.php");
      $out->fetch();
      echo $out;
      die;
  }
}
/*

$app->addModule("cms",__FILE__,"WebBasic CMS");

function cms__init(&$app)
{
    $app->path = __DIR__;
    if ($app->vars->get("_route.action")) {
        $call = "cms__".$app->vars("_route.action");
        if (is_callable($call)) return $call($app);
    } else {
        $cms = $app->fromFile(__DIR__."/tpl/cms_ui.php",true);
        return $cms->fetch();
    }
}

function cms__tpl(&$app) {
  $tpl = $app->vars("_route.params.0");
  $out = $app->fromFile(__DIR__."/tpl/cms_{$tpl}.php",true);
  $out->fetch();
  if ($out->find("[data-prop]")->length) return $out->find("[data-prop]")[0]->outerHtml();
  return $out->outerHtml();
}

function cms__modprop(&$app) {
  $module = $app->vars("_route.params.0");
  $path = $app->vars("_env.modules.{$module}.dir");
  $out = $app->fromString("",true);
  $out->html($app->fromFile(__DIR__."/tpl/cms_modules.php",true)->find("[data-prop='modal']"));
  $out->data("module",$module);
  $out->fetch();
  $sett = $app->vars("_env.path_app").$path."/{$module}_sett.php";
  if (is_file($sett)) {
      $set = $app->fromFile($sett,true);

      $out->find(".modal-body form")->append($set);
  }
  $out->find(".modal-title")->text($app->vars("_env.modules.{$module}.label"));
  echo $out->fetch();
  die;
}


*/
?>

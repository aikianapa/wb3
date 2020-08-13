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
		if (is_callable('modCmsBeforeShow')) modCmsBeforeShow($cms);
    return $cms;
  }

  function ajax() {
      $app = $this->app;
      if ($app->vars("_route.form") > "") {
          $form = $app->formClass($app->vars("_route.form"));
          $action = $app->vars("_route.action");
          $form->$action();
      }
  }

  function login() {
      unset($_SESSION["user"]);
      setcookie("user", null, time() - 3600, "/");
      $app = $this->app;
      $out = $app->fromFile(__DIR__ . "/tpl/cms_login.php");
      $out->fetch();
      echo $out;
      die;
  }

  function logout() {
      if (isset($_SESSION["user"])) {
          $role = $this->app->itemRead("users",$_SESSION["user"]["role"]);
          if ($role && $role["url_logout"] > "") {
            header('Content-Type: charset=utf-8');
            header('Content-Type: application/json');
            unset($_SESSION["user"]);
            setcookie("user", null, time() - 3600, "/");
            return json_encode(["error"=>false,"callback"=>"document.location.href = '{$role["url_logout"]}';"]);
          }
      }
      die;
  }

  function settings() {
      $app = $this->app;
      $out = $app->fromFile(__DIR__."/forms/_settings/{$app->vars("_route.form")}.php");
      if ($out !== null) {
          $out->fetch();
      } else {
          $out = "Error: /forms/_settings/{$app->vars("_route.form")}.php not found!";
      }
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

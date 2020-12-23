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
          if ($role) {
            if (!isset($role["url_logout"]) OR $role["url_logout"] == '') $role["url_logout"] = '/';  
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
      $out = $app->getForm('_settings',$app->vars("_route.form"));
      if ($out !== null) {
          $out->fetch();
      } else {
          $out = "Error: /forms/_settings/{$app->vars("_route.form")}.php not found!";
      }
      echo $out;
      die;
  }
}
?>

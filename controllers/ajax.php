<?php
class ctrlAjax {
  function __construct($app) {
      include_once($_ENV["path_engine"]."/attrs/save/ajax.php");
      include_once($_ENV["path_engine"]."/attrs/tree/ajax.php");
      if (is_file($_ENV["path_app"]."/ajax.php")) {
          include_once($_ENV["path_app"]."/ajax.php");
          $this->ajax = new wbAjax($app);
      }
      $this->app = $app;
      $this->route = $app->route;
      $mode = $this->route->mode;
      echo $this->$mode();
      die;
  }

  function __call($mode, $params)  {
      if (in_array($mode,["save","tree"])) {
          require_once($_ENV["path_engine"]."/attrs/".strtolower($mode)."/ajax.php");
          $class = "wbAjax".ucfirst($mode);
          $this->ajax = new $class($this->app);
      }

      if ($this->ajax) {
          $this->ajax->$mode();
      } else if (is_callable(@$this->$mode)) {
          @$this->$mode();
      } else {
          echo json_encode([null]);
      }
      die;
  }

  public function alive() {
    if (isset($_SESSION["user"])) echo json_encode(["result" => true]);
    else echo json_encode(["result" => false]);
  }


  public function auth() {
      unset($_SESSION["user"]);
      $app = $this->app;
      $post = (object)$app->vars("_post");
      $fld = $app->vars("_route.params.0");
      $url = "/";
      if ($fld == "logout") {
          if (@isset($_SESSION["user"]["userole"]["url_login"]) && $_SESSION["user"]["userole"]["url_login"] > "") {
              $url = $_SESSION["user"]["userole"]["url_login"];
          }
          return json_encode(["login"=>false,"error"=>false,"redirect"=>$url,"user"=>[],"role"=>[]]);
      }
      if (!in_array($fld,["email","phone","login"])) return json_encode(["login"=>false,"error"=>"Unknown"]);

      $user = $app->itemList("users",["filter"=> [$fld => $post->login ], "limit"=>1 ]);
      if (intval($user["count"]) > 0) $user = array_shift($user["list"]);
          else return json_encode(["login"=>false,"error"=>"Unknown"]);
      $user = (object)$user;
      if ($user->password == md5($post->password)) {
          $role = (object)$app->itemRead("users",$user->role);
          if ($role->active !== "on" OR $user->active !== "on") return json_encode(["login"=>false,"error"=>"Account is not active"]);
          if ($role->url_login > "") $url = $role->url_login;
          $_SESSION["user"] = (array)$user;
          $_SESSION["userole"] = (array)$role;
          return json_encode(["login"=>true,"error"=>false,"redirect"=>$url,"user"=>$user,"role"=>$role]);
      } else {
          return json_encode(["login"=>false,"error"=>"Wrong password"]);
      }
  }

  function form() {
      // передача вызова в контроллер form
      require_once(__DIR__."/form.php");
      $this->app->vars("_route.mode","ajax");
      $this->app->route = (object)$this->app->vars("_route");
      $ctrl = new ctrlForm($this->app);
      //$ctrl->ajax->
  }

  function rmitem()
  {
      $app = $this->app;
      $form = $app->vars("_route.form");
      $item = $app->vars("_route.item");
      if (!isset($_REQUEST["_confirm"])) {
          $dom = $app->getForm("snippets", "remove_confirm");
          $dom->item = ["_form"=>$form,"_item"=>$item];
          $ajax = $dom->find("[data-ajax]")[0];
          $params = wbAttrToValue($ajax->attr("data-ajax"));
          $append = json_encode($app->vars("_post"));
          $append = wbAttrToValue($append);
          $params = array_merge((array)$append,(array)$params);
          $ajax->attr("data-ajax",json_encode($params));
          $dom->fetch();
          header('Content-Type: text/html; charset=utf-8');
          echo $dom;
          die;
      } else {
          echo json_encode($app->itemRemove($form, $item));
          die;
      }
  }

  function getsess() {
    echo json_encode($_SESSION);
  }

}
?>

<?php
class ctrlAjax {
  function __construct($app) {
      include_once($_ENV["path_engine"]."/attrs/save/ajax.php");
      if (is_file($_ENV["path_app"]."/ajax.php")) {
          include_once($_ENV["path_app"]."/ajax.php");
          $this->ajax = new wbAjax($app);
      }
      $this->app = $app;
      $this->route = $app->route;
      $mode = $this->route->mode;
      $this->$mode();
      die;
  }

  function __call($mode, $params)  {
      switch($mode) {
        case "save":
              require_once($_ENV["path_engine"]."/attrs/save/ajax.php");
              $this->ajax = new wbAjaxSave($this->app);
              break;
      }
      if ($this->ajax) {
          $this->ajax->$mode();
      } else if (is_callable(@$this->$mode)) {
          @$this->$mode();
      } else {
          echo json_encode([null]);
      }
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

}
?>

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
          @$this->ajax->$mode();
      } else if (is_callable(@$this->$mode)) {
          @$this->$mode();
      } else {
          echo json_encode([null]);
      }
  }
}
?>

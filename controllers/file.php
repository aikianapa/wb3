<?php
// replaced with static.php
class ctrlFile {
  function __construct($app) {
      $this->app = $app;
      $this->route = $app->route;
      $mode = $this->route->mode;
      $this->$mode();
  }

  function __call($mode, $params)  {
      if (!is_callable(@$this->$mode)) {
          $tpl = $this->app->getTpl('404.php');
          header( "HTTP/1.1 404 Not Found" );
          $tpl = $this->app->getTpl('404.php');
          if ($tpl) {
            echo $tpl->fetch();
          } else {
            echo "Error 404";
          }
      }
  }

  public function output() {
      if (isset($this->route->file)) {
        $mime = wbMime($this->route->file);
        header("Content-type: {$mime}");
        echo file_get_contents($this->route->file);
      } else {
        header( "HTTP/1.1 404 Not Found" );
        $tpl = $this->app->getTpl('404.php');
        if ($tpl) {
          echo $tpl->fetch();
        } else {
          echo "Error 404";
        }
      }
  }

}

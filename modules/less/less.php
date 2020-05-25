<?php
class modLess {
  function __construct($app) {
      $this->app = $app;
  }

  function compile() {
    require __DIR__ . "/vendor/leafo/lessphp/lessc.inc.php";
    $less = $this->app->route->path_app.$this->app->route->uri;
    if (is_file($less)) {
            $lessc = new lessc;
            header("Content-type: text/css");
            echo $lessc->compileFile($less);

    } else  {
      header( "HTTP/1.1 404 Not Found" );
      echo "Error 404";
    }
    exit();
  }
}
?>

<?php
class ctrlFile {
  function __construct($app) {
      $this->app = $app;
      $this->route = $app->route;
      $mode = $this->route->mode;
      $this->$mode();
  }

  function __call($mode, $params)  {
      if (!is_callable(@$this->$mode)) {
          header( "HTTP/1.1 404 Not Found" );
          echo "Error 404";
      }
  }

  public function output() {
      if (isset($this->route->file)) {
        $info = wbArrayToObj(pathinfo($this->route->file));
        $mime = mime_content_type($this->route->file);
        if ($mime == "image/svg") $mime = "image/svg+xml";
        if (in_array($info->extension,["ttf","woff","woff2","eot","otf"])) $mime = "application/font-".$info->extension;
        header("Content-type: {$mime}");
        echo file_get_contents($this->route->file);
      } else {
        header( "HTTP/1.1 404 Not Found" );
        echo "Error 404";
      }
  }

}

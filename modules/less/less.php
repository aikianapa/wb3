<?php
class modLess {
  function __construct($app) {
    require __DIR__ . "/vendor/leafo/lessphp/lessc.inc.php";
    $this->minify = false;
    $this->app = $app;
    $this->file = $this->app->route->path_app.$this->app->route->uri;
    $this->path();
    $this->name = explode('/', substr($this->file, 0, -5));
    $this->name = array_pop($this->name);
    if (substr($this->file, -9) == '.min.less') {
        $this->name = explode('/', substr($this->file, 0, -9));
        $this->name = array_pop($this->name);
        $this->file = substr($this->file, 0, -9).'.less';
        $this->minify = true;
    }
    $this->compiler = new lessc;
  }

  function path() {
        $cfile = md5($this->file);
        $cdir = $_ENV['dbac'] . '/_css';
        is_dir($cdir) ? null : mkdir($cdir, 0755, true);
        $this->path = dirname($this->file);
        $this->cpath = $cdir;
        $this->cfile = $cfile.'.css';
        $this->cmfile = $cfile.'.min.css';
  }

  function compile() {
    if (is_file($this->file)) {
        $cache = true;
        if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
            parse_str($_SERVER['HTTP_CACHE_CONTROL'], $cc);
            isset($cc['no-cache']) ? $cache = false : null;
        }

        $cssfile = $this->cpath.'/'.$this->cfile;
        $cssminfile = $this->cpath.'/'.$this->cmfile;
        if ($cache) {
            if ($this->minify && is_file($cssminfile)) {
                $css = file_get_contents($cssminfile);
            } elseif (!$this->minify && is_file($cssfile)) {
                $css = file_get_contents($cssfile);
            } else {
                $cache = false;
            }
        }

        if ($cache == false) {
            $css = $this->compiler->compileFile($this->file);
            if ($this->minify) {
                $css = $this->app->minifyCss($css);
                $this->app->putContents($cssminfile, $css, LOCK_EX);
            } else {
                $this->app->putContents($cssfile, $css, LOCK_EX);
            }
        }
        header("Content-type: text/css");
        header("Cache-control: public");
        header("Pragma: cache");
        header("Expires: " . gmdate("D, d M Y H:i:s", time()+$this->app->vars('_sett.cache')) . " GMT");
        header("Cache-Control: max-age=".$this->app->vars('_sett.cache'));
        echo $css;
    } else  {
      header( "HTTP/1.1 404 Not Found" );
      echo "Error 404";
    }
    exit();
  }
}
?>

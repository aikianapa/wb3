<?php
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;
use ScssPhp\Server\Server;

//https://scssphp.github.io/scssphp/
class modScss {
  function __construct($app) {
      require_once __DIR__.'/scssphp/scss.inc.php';
      $this->minify = false;
      $this->app = $app;
      $this->file = $this->app->route->path_app.$this->app->route->uri;
      $this->path();
      $this->name = explode('/',substr($this->file, 0, -5));
      $this->name = array_pop($this->name);
      if (substr($this->file,-9) == '.min.scss') {
          $this->name = explode('/', substr($this->file, 0, -9));
          $this->name = array_pop($this->name);
          $this->file = substr($this->file, 0, -9).'.scss';
          $this->minify = true;
      }
      $this->compiler = new Compiler();
      $this->compiler->setImportPaths($this->path);
  }


  function path() {
    $cfile = md5($this->file);
    $cdir = $_ENV['dbac'] . '/' . substr($cfile,0,4);
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
            } else if (!$this->minify && is_file($cssfile)) {
                $css = file_get_contents($cssfile);
            } else {
              $cache = false;
            }
        }

        if ($cache == false) {
          $mapurl = realpath($this->path.'/'.$this->name.'.map');
          $appdir = realpath($this->app->vars("_env.path_app"));
          $mapurl = explode('/', $this->app->route->uri);
          array_pop($mapurl);
          $mappath = implode('/', $mapurl);
          $mapurl = $mappath . '/' . $this->name .'.map';

            $this->compiler->setSourceMapOptions([
                'sourceMapWriteTo'  => $this->path.'/'.$this->name.'.map',
                // relative or full url to the above .map file
                'sourceMapURL'      => $mapurl,
                // partial path (server root) removed (normalized) to create a relative url
                'sourceMapBasepath' => $this->path,
            ]);

            $this->src = file_get_contents($this->file);
            if ($this->minify) {
                $this->compiler->setFormatter('ScssPhp\ScssPhp\Formatter\Crunched');
                $css = $this->compiler->compile($this->src);
                $this->app->putContents($cssminfile, $css, LOCK_EX);
            } else {
              $this->compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
              $css = $this->compiler->compile($this->src);
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
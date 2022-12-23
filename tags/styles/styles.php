<?php

use MatthiasMullie\Minify;

class tagStyles
{
    public function __construct(&$dom)
    {
        $this->dom = &$dom;
        $this->app = &$dom->app;
        $this->inner = $dom->text();
        $this->home = $dom->app->vars('_env.path_app');
        $this->path = '/assets/compress/css';
        $this->filename = $dom->attr('src') ? $dom->attr('src') : md5($this->inner).'.css';
        $this->src = substr($this->filename, 0, 1) == '/' ? $this->filename : $this->path.'/'.$this->filename;
        $this->dir = substr($this->filename, 0, 1) == '/' ? $this->home : $this->home.$this->path;
        strtolower(substr($this->filename, -4)) !== '.css' ? $this->filename.='.css' : null;
        $this->file = wbNormalizePath($this->dir.'/'.$this->filename);
        $info = (object)pathinfo($this->file);
        $this->dir = $info->dirname;
        $this->filename = $info->basename;
        is_dir($this->dir) ? null : mkdir($this->dir, 0777, true);
        $this->load($dom);
        $dom->remove();
    }

    public function load(&$dom)
    {
        $inner = wbSetValuesStr($this->inner, $dom->item);
        $arr = json_decode($inner, true);
        $styles = new Minify\CSS();
        foreach ($arr as $i => $src) {
            $this->info = (object)pathinfo($src);
            $ext = strtolower($this->info->extension);
            $opts = [];
            if (stream_is_local($src) && in_array($ext, ['less','scss'])) {
                $src = $this->app->route->host.$src;
            }
            if (stream_is_local($src)) {
                $styles->addFile($this->home.$src);
            } else {
                $src = wbAuthGetContents($src);
                $styles->add($src);
            }
        }
        $styles->minify($this->file);
        $this->dom->after('<script type="wbapp" remove >wbapp.loadStyles(["'.$this->src.'"])</script>'.PHP_EOL);
    }
}

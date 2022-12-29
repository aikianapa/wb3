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
        $this->dir = substr($this->filename, 0, 1) == '/' ? $this->home : $this->home.$this->path;
        strtolower(substr($this->filename, -4)) !== '.css' ? $this->filename.='.css' : null;
        $this->src = substr($this->filename, 0, 1) == '/' ? $this->filename : $this->path.'/'.$this->filename;

        $this->file = wbNormalizePath($this->dir.'/'.$this->filename);
        $info = (object)pathinfo($this->file);
        $this->dir = $info->dirname;
        $this->filename = $info->basename;
        is_dir($this->dir) ? null : mkdir($this->dir, 0777, true);
        $this->dom->attr('type')=='link' ? $this->load_link($dom) : $this->load($dom);
        $dom->remove();
    }

    public function load_link(&$dom) {
        $inner = wbSetValuesStr($this->inner, $dom->item);
        $arr = json_decode($inner, true);
        $css = "";
        foreach ($arr as $i => $src) {
            $css.='<link rel="stylesheet" href="'.$src.'">'.PHP_EOL;
        }
        $this->dom->after($css);
    }

    public function load(&$dom)
    {
        $inner = wbSetValuesStr($this->inner, $dom->item);
        $arr = json_decode($inner, true);
        $styles = new Minify\CSS();
        foreach ($arr as $i => $src) {
            $this->info = (object)pathinfo($src);
            $ext = strtolower($this->info->extension);
            in_array($ext, ['less','scss']) ? $src = $this->app->route->host.$src : null;
            $local = stream_is_local($src);
            if ($local) {
                $styles->addFile($this->home.$src);
            } else {
                $src = wbAuthGetContents($src);
                $styles->add($src);
            }
        }
        $styles->minify($this->file);
        $this->dom->after('<link rel="stylesheet" href="'.$this->src.'">'.PHP_EOL);
    }
}

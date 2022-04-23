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
        $info = pathinfo($this->filename);
        $this->filename = $info['dirname'].'/_'.$info['filename'].'.'.$info['extension'];

        $this->src = substr($this->filename, 0, 1) == '/' ? $this->filename : $this->path.'/'.$this->filename;
        $this->dir = substr($this->filename, 0, 1) == '/' ? $this->home : $this->home.$this->path;
        $this->file = wbNormalizePath($this->dir.'/'.$this->filename);
        $this->load($dom);
        $dom->remove();
    }

    public function load(&$dom)
    {
        $inner = wbSetValuesStr($this->inner, $dom->item);
        $arr = json_decode($inner, true);
        $styles = '';
        foreach ($arr as $i => $src) {
            $this->info = (object)pathinfo($src);
            $ext = strtolower($this->info->extension);
            $opts = [];
            if (stream_is_local($src) && in_array($ext, ['less','scss'])) {
                $src = $this->app->route->host.$src;
                $opts = ['http' => [
                    'header' => "Cache-Control: no-cache\r\n" .
                    "Pragma: no-cache\r\n"
                    ]
                ];
            }
            $src = stream_is_local($src) ? file_get_contents($this->home.$src) : file_get_contents($src, false, stream_context_create($opts));
            $styles = $i == 0 ? new Minify\CSS($src) : $styles->add($src);
        }
        $styles = $styles->minify();
        file_put_contents($this->file,$styles,LOCK_EX);
        $this->dom->after('<link rel="stylesheet" href="'.$this->src.'" >'.PHP_EOL);
    }
}

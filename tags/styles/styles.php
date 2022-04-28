<?php
require_once __DIR__ . "/vendor/autoload.php";

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
        $styles = new Minify\CSS();
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
            if (stream_is_local($src)) {
                $styles->addFile($this->home.$src);
            } else {
                $src = file_get_contents($src, false, stream_context_create($opts));
                $styles->add($src);
            }
        }
        $styles->minify($this->file);
        $this->dom->after('<link rel="stylesheet" href="'.$this->src.'" >'.PHP_EOL);
    }
}

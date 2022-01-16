<?php

require_once('jsmin.php');

class tagScripts
{
    public function __construct(&$dom)
    {   
        $this->dom = &$dom;
        $this->app = &$dom->app;
        $this->home = $dom->app->vars('_env.path_app');
        $this->path = '/assets/compress/js';
        $this->dir = $this->home.$this->path;
        $this->filename = $dom->attr('result');
        $this->file =  $this->dir.'/'.$this->filename;
        $this->access();
        $this->load($dom);
        $dom->remove();
    }

    public function load(&$dom)
    {
        $arr = json_decode($dom->text(),true);
        $script = '';
        foreach($arr as $src) {
            $src = stream_is_local($src) ? $this->home.$src : $src;
            $tmp = file_get_contents($src);

            try {
                $tmp = JSMin::minify($tmp);
            } catch (\Throwable $th) {
                echo "Ошибка минификации скрипта: ".$src;
                die;
            }
            $script .= $tmp.';'.PHP_EOL;
        }
        $this->dom->attr('trigger') > '' ? $script.='$(document).trigger("'.$this->dom->attr('trigger').'");'.PHP_EOL : null;
        
        $script = gzencode($script, 9);
        $this->app->putContents($this->file, $script);
        $this->dom->after('<script wb-app src="'.$this->path.'/'.$this->filename.'"></script>\n');
    }

    public function access() {
        $this->hta = $this->dir.'/.htaccess';
        if (!is_file($this->hta)) {

            $htaccess='Options All -Indexes'.PHP_EOL;
            $htaccess.='AddType text/css cssgz'.PHP_EOL;
            $htaccess.='AddType text/javascript jsgz'.PHP_EOL;
            $htaccess.='AddEncoding x-gzip .cssgz .jsgz'.PHP_EOL;

            $this->app->putContents($this->hta, $htaccess);
        }
    }
}
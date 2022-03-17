<?php

require_once('jsmin.php');

class tagScripts
{
    public function __construct(&$dom)
    {   
        $this->dom = &$dom;
        $this->app = &$dom->app;
        $this->inner = $dom->text();
        $this->home = $dom->app->vars('_env.path_app');
        $this->path = '/assets/compress/js';
        $this->dir = $this->home.$this->path;
        $this->filename = $this->dom->attr('src') ? $this->dom->attr('src') : md5($this->inner).'.jsgz';
        strtolower(substr($this->filename, -5)) == '.jsgz' ? null : $this->filename.='.jsgz';
        $this->file =  wbNormalizePath($this->dir.'/'.$this->filename);
        $this->access();
        $this->load();
        $dom->remove();
    }

    public function load()
    {
        $inner = wbSetValuesStr($this->inner, $this->dom->item);
        $arr = json_decode($inner,true);
        $script = '';
        $wbapp = false;

        if ($this->app->vars('_env.tmp.modScripts') == '') {
            $this->loaded = [];
        } else {$this->app->vars('_env.tmp.modScripts');}

        $loaded = 'if (!document.loadedScripts) document.loadedScripts =[];';

        foreach((array)$arr as $src) {
            if (!in_array($this->loaded, (array)$src)) {
                $loaded.="document.loadedScripts.push('{$src}');";
                $this->loaded[] = $src;
                $this->app->vars('_env.tmp.modScripts', $this->loaded);
                $src = stream_is_local($src) ? $this->home.$src : $src;
                $tmp = file_get_contents($src);
                try {
                    //$tmp = wbMinifyJs($tmp);
                    $tmp = JSMin::minify($tmp);
                } catch (\Throwable $th) {
                    //$tmp = wbMinifyJs($tmp);
                }
                $script .= $tmp.';'.PHP_EOL;
            }
        }
        $this->app->vars('_env.tmp.modScripts',$this->loaded);
        if ($script == '') return;
        $this->dom->attr('trigger') > '' ? $script.='$(document).trigger("'.$this->dom->attr('trigger').'");'.PHP_EOL : null;
        $script = $loaded.$script.PHP_EOL;
        $script = gzencode($script, 9);
        $this->app->putContents($this->file, $script);
        $type = strtolower(trim($this->dom->attr('src')) == 'wbapp') ? '' : ' type="wbapp" ';
        $this->dom->after('<script '.$type.' src="'.$this->path.'/'.$this->filename.'"></script>'.PHP_EOL);
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
?>
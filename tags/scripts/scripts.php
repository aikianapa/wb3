<?php
class tagScripts
{
    public function __construct(&$dom)
    {   
        $this->dom = &$dom;
        $this->app = &$dom->app;
        $this->inner = $dom->text();
        $this->home = $dom->app->vars('_env.path_app');
        $this->path = '/assets/compress/js';
        $this->ext = $dom->attr('compress') == 'true' ? 'jsgz' : 'js';
        $this->filename = $this->dom->attr('src') ? $this->dom->attr('src') : md5($this->inner).'.'.$this->ext;
        strtolower(substr($this->filename, -strlen($this->ext))) == $this->ext ? null : $this->filename.='.'.$this->ext;
        $this->filename = str_replace('.jsgz.js', '.js', $this->filename);
        $this->file =  wbNormalizePath($this->home.$this->path.'/'.$this->filename);
        $info = (object)pathinfo($this->file);
        $this->dir = $info->dirname;
        is_dir($this->dir) ? null : mkdir($this->dir, 0777, true);
        $this->load();
        $dom->remove();
    }

    public function load()
    {
        $inner = wbSetValuesStr($this->inner, $this->dom->item);
        $arr = json_decode($inner,true);
        $script = '';
        $wbapp = false;
        isset($this->loaded) ? null : $this->loaded = [];

        if ($this->app->vars('_env.tmp.modScripts') == '') {
            $this->loaded = [];
        } else {$this->app->vars('_env.tmp.modScripts');}

        $loaded = 'if (!document.loadedScripts) document.loadedScripts =[];';


        foreach((array)$arr as $src) {
            if (!in_array($src, $this->loaded)) {
                $loaded.="document.loadedScripts.push('{$src}');";
                $this->loaded[] = $src;
                $this->app->vars('_env.tmp.modScripts', $this->loaded);
                $src = stream_is_local($src) ? $this->home.$src : $src;
                $tmp = file_get_contents($src);
                $script .= PHP_EOL.';'.PHP_EOL.$tmp;
            }
        }
        $this->app->vars('_env.tmp.modScripts',$this->loaded);
        if ($script == '') return;
        $this->dom->attr('trigger') > '' ? $script.= PHP_EOL.'$(document).trigger("'.$this->dom->attr('trigger').'");'.PHP_EOL : null;
        

        if (strtolower(trim($this->dom->attr('src')) == 'wbapp')) {
            $type = ' type="text/javascript" ';
        } else if ($this->dom->attr('type')>'') {
            $type = $this->dom->attr('type');
        } else {
            $type = ' type="text/wbapp" ';
        }
        $script = $loaded.PHP_EOL.';'.PHP_EOL.$script;
        if ($this->ext == 'jsgz') $script = gzencode($script, 9);
        $this->app->putContents($this->file, $script);
        $this->dom->after('<script '.$type.' src="'.$this->path.'/'.$this->filename.'"></script>'.PHP_EOL);
    }
}
?>
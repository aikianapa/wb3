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
        $this->filename = $dom->attr('src') ? $dom->attr('src') : md5($this->inner).'.cssgz';
        strtolower(substr($this->filename,0,6)) !== '.cssgz' ? $this->filename.='.cssgz' : null;

        $this->src = substr($this->filename, 0, 1) == '/' ? $this->filename : $this->path.'/'.$this->filename;
        $this->dir = substr($this->filename, 0, 1) == '/' ? $this->home : $this->home.$this->path;
        $this->file = wbNormalizePath($this->dir.'/'.$this->filename);
        $info = (object)pathinfo($this->file);
        $this->dir = $info->dirname;
        $this->filename = $info->basename;
        $this->access();
        $this->load($dom);
        $dom->remove();
    }

    public function load(&$dom)
    {
        $inner = wbSetValuesStr($this->inner, $dom->item);
        $arr = json_decode($inner,true);
        foreach($arr as $i => $src) {
            $this->info = (object)pathinfo($src);
            $ext = strtolower($this->info->extension);

            if (stream_is_local($src) && in_array($ext, ['less','scss'])) {
                $src = $this->app->route->host.$src;
            }
            $src = stream_is_local($src) ? $this->home.$src : file_get_contents($src);

            if ($i == 0) {
                $style = new Minify\CSS($src);
            } else {
                $style->add($src);
            }
        }
        if (!isset($style)) return;
        $style->gzip($this->file,9);
        $this->dom->after('<script type="wbapp" remove >wbapp.loadStyles(["'.$this->src.'"])</script>'.PHP_EOL);
    }

    public function access() {
        $this->hta = $this->dir.'/.htaccess';
        if (!is_file($this->hta)) {
            $htaccess='Options All -Indexes'.PHP_EOL;
            $htaccess.='AddType text/css cssgz'.PHP_EOL;
            $htaccess.='AddEncoding x-gzip .cssgz'.PHP_EOL;
            $this->app->putContents($this->hta, $htaccess);
        }
    }
}
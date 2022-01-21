<?php

// https://github.com/momentum81/php-remove-unused-css

require __DIR__ .'/vendor/autoload.php';

class modCompress
{
    public function __construct(&$obj)
    {
        if (strtolower(get_class($obj)) == 'wbapp') {
            $this->app = &$obj;
            $this->dom = $this->app->fromSrting('');
        } elseif (strtolower(get_class($obj)) == 'wbdom') {
            $this->app = &$obj->app;
            $this->dom = &$obj;
        }
        $this->css = new \Momentum81\PhpRemoveUnusedCss\RemoveUnusedCssBasic();
        $this->init();
        return $this->dom;
    }

    public function init()
    {
        if ($this->app->vars('_sett.modules.compress.active') !== 'on') {
            return $this->dom;
        }
        $css = '';
        $files = [];
        $list = $this->dom->parents('html')->find('link[href*=".css"],link[href*=".less"],link[href*=".scss"]');
        foreach ($list as $link) {
            if ($link->attr('rel') == 'stylesheet') {
                $query = parse_url($link->href);
                if (!isset($query['host'])) {
                    $files[] = $query['path'];
                    $css .= file_get_contents($this->app->route->host.'/'.$link->href);
                    $link->remove();
                } else {
                    $files[] = $query['host'].'/'.$query['path'];
                    $css .= file_get_contents($link->href);
                }
            }
        }
        if (count($files)) {
            $name = md5(implode(',', $files));
            $filename = $this->app->route->path_app . '/assets/css/compress/'.$name;

            if ($this->app->vars('_sett.modules.compress.unused') !== 'on') {
                    $css = wbMinifyCss($css);
                    wbPutContents($filename.'.min.css', $css);
            } else {

                $cssname = $filename.'.css';
                $htmname = $filename.'.htm';
                
                wbPutContents($cssname, $css);
                wbPutContents($htmname, $this->dom->outer());

                $this->dom->append('<link rel="stylesheet" href="/assets/css/compress/'.$name.'.min.css">');

                $this->css->whitelist('html','body',':before',':after',':hover')
                    ->styleSheets($cssname)
                    ->htmlFiles($htmname)
                    ->setFilenameSuffix('.min')
                    ->minify()
                    ->refactor()
                    ->saveFiles();
                unlink($htmname);
                unlink($cssname);
            }
                if ($this->dom->find('head')->length) {
                    $this->dom->find('head')->append('<link rel="stylesheet" href="/assets/css/compress/'.$name.'.min.css">');
                } else {
                    $this->dom->append('<link rel="stylesheet" href="/assets/css/compress/'.$name.'.min.css">');
                }

        }
        return $this->dom;
    }
}

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
        } else if (strtolower(get_class($obj)) == 'wbdom') {
            $this->app = &$obj->app;
            $this->dom = &$obj;
        }
        $this->css = new \Momentum81\PhpRemoveUnusedCss\RemoveUnusedCssBasic();
        return $this->init();
    }
    public function init()
    {
        if ($this->app->vars('_sett.modules.compress.active') !== 'on') {
            return $this->dom;
        }
        $css = '';
        $files = [];
        $list = $this->dom->parents('html')->find('link[href*=".css"],link[href*=".less"],link[href*=".scss"]');
        foreach($list as $link) {
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
            $css = wbMinifyCss($css);
            wbPutContents($filename.'.min.css', $css);
            $this->dom->append('<link rel="stylesheet" href="/assets/css/compress/'.$name.'.min.css">');

/*
    // Идея удалить неиспользуемый css провалилась - не все нужные стили сохраняются.
            $cssname = $filename.'.css';
            $htmname = $filename.'.htm';

            wbPutContents($cssname, $css);
            wbPutContents($htmname, $this->dom->outer());
            
            $this->dom->append('<link rel="stylesheet" href="/assets/css/compress/'.$name.'.min.css">');

            $this->css->styleSheets($cssname)
                ->htmlFiles($htmname)
                ->setFilenameSuffix('.min')
                ->saveFiles();
            unlink($htmname);
            unlink($cssname);
*/
        }
        return $this->dom;
    }
}
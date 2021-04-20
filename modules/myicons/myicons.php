<?php
class modMyicons
{
    public function __construct($obj)
    {
        $this->size = 24;
        $this->path = __DIR__ . '/icons/';
        if (get_class($obj) == 'wbApp') {
            $this->app = &$obj;
            $this->dom = $this->app->fromString('');
            $this->dom->params = (object)[
                'icon' => $this->app->route->mode
            ];
            $this->app->route->mode == 'init' ? $this->init() : null;
            isset($this->app->route->query->size) ? $this->size = $this->app->route->query->size : $this->size = null;
            isset($this->app->route->query->stroke) ? $this->stroke = $this->app->route->query->stroke : $this->stroke = null;
            isset($this->app->route->query->fill) ? $this->fill = $this->app->route->query->fill : $this->fill = null;
            header('Content-Type: image/svg+xml');
            echo $this->icon();
            die;
        } else {
            $this->app = &$obj->app;
            $this->dom = &$obj;
            $this->dom->attr('stroke') > '' ? $this->stroke = $this->dom->attr('stroke') : $this->stroke = null;
            $this->dom->attr('fill') > '' ? $this->fill = $this->dom->attr('fill') : $this->fill = null;
            $icon = $this->icon();
            if (!$icon) {$icon = '<err>[mi]</err>';}
            $obj->after($icon);
            $obj->remove();
        } 
    }

    public function init() {
        $list = scandir($this->path, 0);
        $i=0;
        ob_start();
        $html = '<html>';
        $html .= '<head>';
        $html .= '<script src="/engine/js/wbapp.js"></script><script type="wbapp">wbapp.lazyload()</script>';
        $html .= '<link rel="stylesheet" href="/engine/modules/cms/tpl/assets/css/dashforge.css">';
        $html .= '</head><body><div class="container"><div class="row">';
        echo $html; ob_flush();
        $letter = '';
        foreach($list as $svg) {
            if (substr($svg,-4) == '.svg') {
                if (substr($svg,0,1) !== $letter) {
                    $letter = substr($svg,0,1);
                    if ($letter > '') echo '<div class="col-12 divider-text">'.$letter.'</div>';
                }
                $i++;
                $html = "<div class='col-2 text-center'><img data-src='/module/myicons/{$svg}?size=50&stroke=000000' ><br>{$svg}</div>";
                echo $html; ob_flush();
            }

        }
        $html = '</div></div></body></html>';
        echo $html; ob_flush();
        ob_clean();
        die;
    }

    public function icon() {
        $app = &$this->app;
        $params = $this->dom->params;
        $icon = $this->name();
        $file = $this->path.$icon;
        $sprite = $app->fromFile($file);
        if (!$sprite) return false;
        substr($this->stroke,0,1) !== '#' ? $this->stroke = '#'.$this->stroke : null;
        $id = $sprite->attr('id');
/*
        if ($id == '') {
            $id = 'Layer';
            $sprite->attr('id', $id);
            file_put_contents($file,$sprite->outer());
        }
*/
        $svg = $app->fromFile(__DIR__.'/myicon_ui.php');
        $svg->find('[viewBox]')->removeAttr('viewBox');
        $svg->find('use')->attr('href', $file.'#'.$id);
        $svg->find('use')->after($sprite->inner());
        $svg->find('use')->remove();
        $svg->attr('class', $this->dom->attr('class'));
        if ($this->size) {
            $svg->attr('width',$this->size);
            $svg->attr('height',$this->size);
            $svg->attr('viewBox',"0 0 24 24");
        }
        $this->stroke ? $svg->find('[stroke]')->attr('stroke',$this->stroke) : null;
        $this->fill ? $svg->find('[fill]')->attr('fill',$this->fill) : null;
        return $svg->outer();
    }

    public function name() {
        isset($this->dom->params->icon) ? $name = $this->dom->params->icon : $name = null;
        if (!$name) {
            $class = $this->dom->attr('class');
            $class = $this->app->arrayAttr($class);
            foreach($class as $mi) {
                if (substr($mi,0,3) == 'mi-') {
                    $name = substr($mi, 3);
                } else if (substr($mi,0,5) == 'size-'){
                    $this->size = substr($mi, 5);
                }
            }
        }
        if (substr($name,-4) !== '.svg') $name .= '.svg';
        return $name;
    }

}
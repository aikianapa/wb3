<?php
class modMyicons
{
    public function __construct($obj)
    {
        $this->size = 24;
        $this->path = __DIR__ . '/icons/';
        $this->stroke = null;
        $this->fill = null;

        if (get_class($obj) == 'wbApp') {
            $this->app = &$obj;
            $this->dom = $this->app->fromString('');
            $this->dom->params = (object)[
                'icon' => $this->app->vars('_route.mode')
            ];
            $this->app->vars('_route.mode') == 'init' ? $this->init() : null;
            $this->parseURL();
            header('Content-Type: image/svg+xml');

            echo $this->icon();
            die;
        } else {
            $this->app = &$obj->app;
            $this->dom = &$obj;
            $this->dom->attr('size') > '' ? $this->size = $this->dom->attr('size') : null;
            $this->dom->attr('stroke') > '' ? $this->stroke = $this->dom->attr('stroke') : null;
            $this->dom->attr('fill') > '' ? $this->fill = $this->dom->attr('fill') : null;
            $icon = $this->icon();
            $this->icon = 'ico-icon-sqaure.svg';
            !$icon ? $icon = $this->icon() : null;
            !$icon ? $icon = '<err>[mi]</err>' : null;
            $obj->after($icon);
            $obj->remove();
        } 
    }


    public function parseURL() {
        if (isset($this->app->route->params) && is_array($this->app->route->params) && count($this->app->route->params) == 2) {
            $this->size = $this->app->route->mode;
            isset($this->app->route->params[0]) ? $this->stroke = $this->app->route->params[0] : null;
            isset($this->app->route->params[1]) ? $this->icon = $this->app->route->params[1] : null;
        } else {
            isset($this->app->route->query->size) ? $this->size = $this->app->route->query->size : $this->size = null;
            isset($this->app->route->query->stroke) ? $this->stroke = $this->app->route->query->stroke : $this->stroke = null;
            isset($this->app->route->query->fill) ? $this->fill = $this->app->route->query->fill : $this->fill = null;

            isset($this->app->route->icon) ? $this->icon = $this->app->route->icon : null;
            isset($this->app->route->size) ? $this->size = $this->app->route->size : null;
            isset($this->app->route->stroke) ? $this->stroke = $this->app->route->stroke : null;
            isset($this->app->route->fill) ? $this->fill = $this->app->route->fill : null;
        }
    }

    public function init() {
        $list = scandir($this->path, 0);
        $i=0;
        ob_start();
        $html = '<html>';
        $html .= '<head>';
        $html .= '<script src="/engine/js/wbapp.js"></script>';
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
                $html = "<div class='col-2 text-center'><img loading='lazy' src='/module/myicons/{$svg}?size=50&stroke=000000' ><br>{$svg}</div>";
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
        isset($this->icon) ? $icon = $this->icon : $icon = $this->name();
        $file = $this->path.$icon;
        substr($this->stroke,0,1) !== '#' ? $this->stroke = '#'.$this->stroke : null;
        substr($this->fill,0,1) !== '#' ? $this->fill = '#'.$this->fill : null;

        

            $sprite = $app->fromFile($file);
            if (!$sprite) {
                return false;
            }
            $id = $sprite->attr('id');
            /*
                    if ($id == '') {
                        $id = 'Layer';
                        $sprite->attr('id', $id);
                        file_put_contents($file,$sprite->outer());
                    }
            */


            $this->stroke > '#' ? $sprite->find('[stroke]')->attr('stroke', $this->stroke) : null;
            $this->fill > '#' ? $sprite->find('[fill]')->attr('fill', $this->fill) : null;

            $styled = $sprite->find('[style*="stroke:"],[style*="fill:"]');
            foreach ($styled as $tag) {
                $style = ' '.$tag->attr('style');
                $stroke = strpos($style, 'stroke:');
                $fill = strpos($style, 'fill:');
                if ($stroke) {
                    substr($style, $stroke, 11) == 'stroke:none' ? $stroke = 'stroke:none' : $stroke = substr($style, $stroke, 14);
                    $style = str_replace($stroke, 'stroke:'.$this->stroke, $style);
                }
                if ($fill) {
                    substr($style, $fill, 9) == 'fill:none' ? $fill = 'fill:none' : $fill = substr($style, $fill, 12);
                    $style = str_replace($fill, 'fill:'.$this->fill, $style);
                }
                $tag->attr('style', $style);
            }


            $svg = $app->fromString('<svg version="1.1" xmlns="http://www.w3.org/2000/svg"><use href=""></use></svg>');
            $svg->find('[viewBox]')->removeAttr('viewBox');
            $svg->find('use')->attr('href', $file.'#'.$id);
            $svg->find('use')->after($sprite->inner());
            $svg->find('use')->remove();
            $svg->attr('class', $this->dom->attr('class'));
            if ($this->size) {
                $svg->attr('width', $this->size);
                $svg->attr('height', $this->size);
                $svg->attr('viewBox', "0 0 24 24");
            }

            //file_put_contents($destination, $svg->outer());
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
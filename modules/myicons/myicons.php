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
            $this->mode = $this->app->vars('_route.mode');
            if (method_exists($this, $this->mode)) {
                $mode = $this->mode;
                $this->$mode();
            } else {
                $this->dom = $this->app->fromString('');
                $this->dom->params = (object)[
                    'icon' => $this->mode
                ];
                $this->mode == 'init' ? $this->init() : null;
                $this->parseURL();
                header('Content-Type: image/svg+xml');
                echo $this->icon();
                exit;
            }
        } else {
            $this->app = &$obj->app;
            $this->dom = &$obj;
            if ($this->dom->is('input')) {
                $this->finder();
            } else {
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
    }


    public function parseURL()
    {
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

    public function init()
    {
        $html = $this->app->fromFile(__DIR__.'/myicons_ui.php');
        $html->fetch();
        echo $html->outer();
        exit;
    }

    public function getlist()
    {
        $list = scandir($this->path, 0);
        header("Content-type:application/json");
        $res = [];
        foreach($list as $file) {
            $name = substr($file, 0, -4);
            if (substr($file,-4) == '.svg' && strpos(' '.$name,$this->app->vars('_req.find'))) {
                $svg = $this->app->fromString('<html><svg class="mi mi-'.$name.' " size="50" wb-module="myicons"></svg></html>');
                $svg->fetch();
                $res[$name] = ['svg'=>$svg->html()];
            }
        }
        echo json_encode($res);
        exit;
    }


public function icon1() {
        $app = &$this->app;
        $params = $this->dom->params;
        isset($this->icon) ? $icon = $this->icon : $icon = $this->name();
        $file = $this->path.$icon;
        substr($this->stroke, 0, 1) !== '#' ? $this->stroke = '#'.$this->stroke : null;
        substr($this->fill, 0, 1) !== '#' ? $this->fill = '#'.$this->fill : null;
        if (!is_file($file)) {
            return false;
        }
        $id = wbNewId();
        $start = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="'.$id.'" width="'.$this->size.'" height="'.$this->size.'" viewBox="0 0 24 24" xml:space="preserve">';

        $sprite = file_get_contents($file);
        $sprite = $start.substr(mb_strpos('>', $sprite),$sprite);
        echo $sprite; exit;
        if (!$sprite) {
            return false;
        }
        echo $sprite;

}

    public function icon()
    {
        $app = &$this->app;
        $params = $this->dom->params;
        isset($this->icon) ? $icon = $this->icon : $icon = $this->name();
        $file = $this->path.$icon;
        substr($this->stroke, 0, 1) !== '#' ? $this->stroke = '#'.$this->stroke : null;
        substr($this->fill, 0, 1) !== '#' ? $this->fill = '#'.$this->fill : null;
        if (!is_file($file)) {
            return false;
        }
        $sprite = $app->fromFile($file);
        if (!$sprite) {
            return false;
        }
        $path = $sprite->find('[d]');
        foreach($path as $d) {
            substr($d->attr('d'), 0, 3)== 'M0,' ? $d->remove() : null;
        }
        
        if ($this->size) {
            $sprite->attr('width', $this->size);
            $sprite->attr('height', $this->size);
            $sprite->attr('viewBox', "0 0 24 24");
            $sprite->attr('style', "width:{$this->size}px;height:{$this->size}px;");
        }
        $inner = $sprite->inner();
        $this->stroke > '#' ? $inner = str_replace('#323232', $this->stroke, $inner) : null;
        $sprite->inner($inner);
        $sprite->find('rect')->remove();

        $attrs = $this->dom->attributes;
        if (gettype($attrs) == 'object' && $attrs->length) {
            foreach ($attrs as $attr) {
                if (!in_array($attr->nodeName, ['stroke','fill','size'])) {
                    $sprite->attr($attr->nodeName, $attr->nodeValue);
                }
            }
        }

        return $sprite->outer();
    }

    public function name()
    {
        isset($this->dom->params->icon) ? $name = $this->dom->params->icon : $name = null;
        if (!$name) {
            $class = $this->dom->attr('class');
            $class = $this->app->arrayAttr($class);
            foreach ($class as $mi) {
                if (substr($mi, 0, 3) == 'mi-') {
                    $name = substr($mi, 3);
                } elseif (substr($mi, 0, 5) == 'size-') {
                    $this->size = substr($mi, 5);
                }
            }
        }
        if (substr($name, -4) !== '.svg') {
            $name .= '.svg';
        }
        return $name;
    }

    public function finder() {
        $html = $this->app->fromFile(__DIR__.'/myicons_finder_ui.php');
        $attrs = $this->dom->attributes;
        $inp = $html->find('input');
        foreach ($attrs as $at) {
            $at->name == 'class' ? $inp->addClass($at->value) : $inp->attr($at->name, $at->value);
        }
        $html->copy($this->dom);
        $html->fetch();
        $this->dom->after($html->outer());
        $this->dom->remove();
    }
}

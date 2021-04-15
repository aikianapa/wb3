<?php
class modMyicons
{
    public function __construct($obj)
    {
        if (get_class($obj) == 'wbApp') {
            $this->app = &$obj;
            $this->dom = $this->app->fromString('');
            $this->dom->params = (object)[
                'icon' => $this->app->route->mode
            ];
            header('Content-Type: image/svg+xml');
            echo $this->icon();
            die;
        } else {
            $this->app = &$obj->app;
            $this->dom = &$obj;
            $icon = $this->icon();
            if (!$icon) {$icon = '<err>[mi]</err>';}
            $obj->after($icon);
            $obj->remove();
        } 
    }

    public function icon() {
        $app = &$this->app;
        $params = $this->dom->params;
        $this->size = null;
        $icon = $this->name();
        $path = __DIR__ . '/icons/';
        //$path = str_replace($app->route->path_app, '', $path);
        $file = $path.$icon;
        $sprite = $app->fromFile($file);
        if (!$sprite) return false;
        $id = $sprite->attr('id');
        if ($id == '') {
            $id = 'Layer';
            $sprite->attr('id', $id);
            file_put_contents($file,$sprite->outer());
        }
        $svg = $app->fromFile(__DIR__.'/myicon_ui.php');
        $svg->find('[viewBox]')->removeAttr('viewBox');
        $svg->find('use')->attr('href', $file.'#'.$id);
        $svg->find('use')->after($sprite->inner());
        $svg->find('use')->remove();
        $svg->attr('class', $this->dom->attr('class'));
        if ($this->size) {
            $svg->attr('width',24);
            $svg->attr('height',24);
            $svg->attr('style','zoom:'.round($this->size / 24 , 4));
        }
        if ($this->dom->attr('stroke') > '') {
            $stroke = $this->dom->attr('stroke');
            $svg->find('[stroke]')->attr('stroke',$stroke);
        }
        if ($this->dom->attr('fill') > '') {
            $stroke = $this->dom->attr('fill');
            $svg->find('[fill]')->attr('fill',$stroke);
        }
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
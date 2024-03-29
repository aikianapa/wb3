<?php
use Adbar\Dot;

class tagVar
{
    public function __construct(&$dom)
    {
        return $this->var($dom);
    }

    public function var(&$dom)
    {
        $parent = &$dom->parent;
        if (!isset($parent->variables)) {
            $parent->variables = [];
        }
        $attrs = $dom->attributes();
        $name = '';
        if (isset($dom->params->api)) {
            $api = wbAuthGetContents($dom->app->route->host.$dom->params->api);
        } 
        foreach ($attrs as $atname => $atval) {
            $wb = substr($atname, 0, 3);
            if (!in_array($wb, ["wb","wb-"]) && $atname !== 'else') {
                $name = $atname;
                if (!(strpos($atval, ">") and strpos(" ".$atval, "<"))) {
                    $atval = wbAttrToValue($atval);
                }
                if (isset($dom->params->if) && $dom->params->if !== true) {
                    if (isset($attrs['else'])) {
                        $atval = $attrs['else'];
                    } else if (isset($attrs['wb-else'])) {
                        $atval = $attrs['wb-else'];
                    } else {
                        $atval = null;
                    }
                }
                isset($api) ? $atval=$api : null;
                $dom->app->isJson($atval) ? $atval=json_decode($atval,true) : null;
                $dom->app->vars("_var.{$atname}", $atval);
                $parent->variables[$atname] = $atval;
                break;
            }
        }
        $dom->remove();
    }
}

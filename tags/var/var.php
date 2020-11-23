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
        foreach ($attrs as $atname => $atval) {
            $wb = substr($atname, 0, 3);
            if (!in_array($wb, ["wb","wb-"])) {
                $name = $atname;
                if (!(strpos($atval, ">") and strpos(" ".$atval, "<"))) {
                    $atval = wbAttrToValue($atval);
                }
                $dom->app->vars("_var.{$atname}", $atval);
                $parent->variables[$atname] = $atval;
                break;
            }
        }
        if (isset($dom->params->where) AND !wbEval($dom->params->where)) {
            $dom->app->vars("_var.{$name}", $dom->attr('wb-else'));
            $parent->variables[$name] = $dom->attr('wb-else');
        }
        $dom->remove();
    }
}

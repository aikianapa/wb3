<?php
use Adbar\Dot;
class modCodemirror
{
    public function __construct($dom)
    {
        $this->init($dom);
    }
    public function init($dom)
    {

	
        if (isset($_ENV["route"]["params"][0]) AND isset($dom->app->route->module) AND $dom->app->route->module == 'module' ) {
            $mode=$_ENV["route"]["params"][0];
            $call="codemirror__{$mode}";
            is_callable($call) ? $out=@$call() : null;
            die;
        } else {
            $out = $dom->app->fromFile(__DIR__ ."/codemirror-ui.php");
            $textarea = $out->find(".codemirror");
            isset($dom->params->oconv) ? $textarea->attr('wb-oconv',$dom->params->oconv) : null;
            isset($dom->params->name) ? $name = $dom->params->name : $name = $dom->attr("name");
            $textarea->attr('name', $name);
            $dom->attr('id') > '' ? $out->attr('id', $dom->attr('id')) : $out->attr('id', 'cm_'.wbNewId());
            $item = new Dot();
            $item->setReference($dom->item);
            $text = $item->get($name);
			$dom->after($out);
			$dom->remove();
        }
    }
}
?>
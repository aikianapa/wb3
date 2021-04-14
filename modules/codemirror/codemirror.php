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
        if ($dom->is("[done]")) {
            return $dom;
		}
		
        if (isset($_ENV["route"]["params"][0]) AND isset($dom->app->route->module) AND $dom->app->route->module == 'module' ) {
            $mode=$_ENV["route"]["params"][0];
            $call="codemirror__{$mode}";
            if (is_callable($call)) {
                $out=@$call();
            }
            die;
        } else {
            $out = $dom->app->fromFile(__DIR__ ."/codemirror-ui.php");
            $textarea = $out->find(".codemirror");
			if (isset($dom->params->name)) {
                $textarea->attr("name", $dom->params->name);
			} else {
				$textarea->attr("name", $dom->attr("name"));
			}
            $name = $textarea->attr("name");
            if ($dom->attr('id')>'') {
                $out->attr('id', $dom->attr('id'));
			} else {
                $out->attr('id', 'cm_'.wbNewId());
			}
            $item = new Dot();
            $item->setReference($dom->item);
            $text = $item->get($name);
            $textarea->text($text);

            $out->prop("done",true);
			$dom->after($out);
			$dom->remove();
        }
    }
}
?>
<?php
class modYamap
{
    public function __construct($dom)
    {
        $this->init($dom);
    }


    public function init(&$dom)
    {
        $out = $dom->app->fromFile(__DIR__ ."/yamap_ui.php");
        $field = $dom->attr("name");
        if ($dom->tag() !== "input") {
            $out->find(".yamap_editor")->remove();
            if ($field > "") {
                $out->find(".yamap_canvas")->inner(json_encode($dom->item[$field]));
            } else {
                $dom->setValues();
                $out->find(".yamap_canvas")->inner($dom->html());
            }
        } else {
            if ($field > "") {
                $out->find("div.yamap .yamap_editor")->attr("name", $field);
            }
        }
        foreach ($dom->attributes() as $at => $val) {
            if (substr($at, 0, 2) !== "wb" and substr($at, 0, 5) !== "class") {
                $out->find(".yamap")->attr($at, $val);
            }
		}
        $out->fetch($dom->item);
		$dom->after($out)->remove();
    }
}
?>

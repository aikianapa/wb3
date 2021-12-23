<?php

use Adbar\Dot;

//$app->addEditor("tinymce", __FILE__, "TinyMCE editor");

class modTinymce
{
    public function __construct($dom)
    {
        $this->init($dom);
    }
    public function init($dom)
    {
        $out = $dom->app->fromFile(__DIR__ ."/tinymce_ui.php", true);
        $dom->attr('id') > '' ? $id = $dom->attr('id') : $id = "tm_".$dom->app->newId();
        $textarea = $out->find("textarea");
        $textarea->attr("id", $id);
        $out->copy($dom);
        if ($dom->attr("name") > "") {
            $dom->params->name = $dom->attr("name");
        }
        if ($dom->params("name")) {
            $item = new Dot();
            $item->setReference($dom->item);
            $textarea->attr("name", $dom->params->name);
            $text = $item->get($dom->params->name);
        } else {
            $text = $dom->html();
        }
        $text = html_entity_decode($text);

        $text = $dom->app->fromString($text);
        $code = $text->find('code,pre');
        foreach ($code as $c) {
            $c->inner(htmlentities($code->html()));
        }
        $out->children('.wb-content-editor')->html($text);
        $dom->after($out->outer());
        $dom->remove();
    }
}

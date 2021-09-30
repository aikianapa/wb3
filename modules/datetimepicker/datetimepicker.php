<?php
class modDatetimepicker
{
    public function __construct($dom)
    {
        $this->init($dom);
    }
    public function init($dom)
    {
        $out=$dom->app->fromFile(__DIR__ ."/datetimepicker_ui.php");
        $inph = $out->children("input[type=hidden]");
        $inpv = $out->children("input.form-control");
        $attributes = $dom->attributes();
        $inpv->attr("autocomplete","off");
        if (isset($dom->params->type)) {
            $dom->params->type = $dom->params->type;
        } else if ($dom->attr("type") > "") {
            $dom->params->type = $dom->attr("type");
        } else {
            $dom->params->type = "datetimepicker";
        }
        $inpv->attr("type", $dom->params->type);

        isset($dom->app->lang) AND !isset($dom->params->lang) ? $dom->params->lang = $dom->app->lang : null; 
        foreach ($attributes as $attr => $val) {
            $attr = mb_strtolower($attr);
            if (substr($attr, 0, 9) !== "wb-module") {
                if ($attr == "class") {
                    $inpv->addClass($val);
                } elseif ($attr == "style") {
                    $inpv->attr("style", trim($inpv->attr("style")." ".$val));
                } else {
                    $inpv->attr($attr, $val);
                    if (in_array($attr, ["name","value"])) {
                        $inph->attr($attr, $val);
                    }
                }
            }
        }
        $inph->attr("wb-params", json_encode($dom->params));
        $inpv->removeAttr('wb');

        if ($inpv->attr("data-date-start")>"") $inpv->attr("data-date-start", date('Y-m-d H:i:s',strtotime($inpv->attr("data-date-start"))));
        if ($inpv->attr("data-date-end")>"") $inpv->attr("data-date-end", date('Y-m-d H:i:s',strtotime($inpv->attr("data-date-end"))));

        $out->copy($dom);
        $dom->after($out);
        $dom->remove();
    }
}

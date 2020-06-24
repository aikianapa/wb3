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
        $inpv = $inph->next("input");
        $attributes = $dom->attributes();
        $inpv->attr("autocomplete","off");
        if (isset($dom->params->type)) {
            $inpv->attr("type",$dom->params->type);
        } else if ($dom->attr("type") > "") {
            $inpv->attr("type",$dom->attr("type"));
        } else {
            $dom->params->type = "datetimepicker";
            $inpv->attr("type","datetimepicker");
        }
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
        $out->copy($dom);
        $out->attr("wb-params",json_encode($dom->params));
        $dom->after($out);
        $dom->remove();
    }
}

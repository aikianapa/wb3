<?php
class attrSave extends wbDom
{
    public function __construct(&$dom)
    {
        $this->attrSave($dom);
        unset($dom->funca);
    }

    public function attrSave(&$dom)
    {
        $params = wbArrayToObj($dom->params->save);
        unset($params->role);
        if (!isset($params->method)) {
            $params->method = "ajax";
        } else {
            $dom->closest("form")->attr("method", $params->method);
        }
        if (!$dom->attr("id")) {
            $id = "sv-".$dom->app->newId();
        }
        $dom->attr("id", $id);
        if (!isset($params->url)) {
            if (!isset($params->table) OR $params->table == "") $params->table = $dom->app->vars("_route.table");
            $params->url = "/ajax/save/{$params->table}";
        }
        $callback = "wbapp.save($(this),".json_encode($params).");";

        if (!$dom->is("[contenteditable]") && !$dom->is("input,select.textarea")) {
            if ($params->method == "ajax") {
                $callback .= "return false;";
            }
            $dom->attr("onClick", $callback);
        } elseif ($dom->is(":input")) {
            $callback = "wbapp.save($(this),".json_encode($params).");";
            $dom->attr("onChange", $callback);
        } else {
            $dom->addClass("contenteditable");
            $params->editor_id = $id;
            $callback = "wbapp.save($(this),".json_encode($params).");";
            if ($dom->is(":input") || $params->editor>"") {
                $dom->attr("onChange", $callback);
            } else {
                $dom->attr("onBlur", $callback);
            }
            if ($params->editor) {
                $dom->addClass($params->editor)->removeAttr("contenteditable");
                $scripts = '["/engine/modules/'.$params->editor.'/'.$params->editor.'.js"]';
                $dom->append('<script type="wbapp">wbapp.loadScripts('.$scripts.',"'.$params->editor.'-js");</script>');
            }
        }
        $dom->removeAttr("wb-save");
    }
}

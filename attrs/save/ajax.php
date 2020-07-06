<?php
class wbAjaxSave
{
    public function __construct(&$app)
    {
        header('Content-Type: charset=utf-8');
        header('Content-Type: application/json');
        $this->app = $app;
    }

    function save() {
        $table = $this->app->vars("_route.params.0");
        if ($this->app->vars("_route.table") > "") $table = $this->app->vars("_route.table");
        $item = $this->app->vars("_post");
        if (!isset($item["_id"]) && isset($item["id"]) && $item["id"] > "") $item["_id"] = $item["id"];
        if (!isset($item["_id"]) && $this->app->vars("_route.item") > "") $item["_id"] = $this->app->vars("_route.item");
        if (count($item)) $res = $this->app->itemSave($table,$item,true);
        if ($res) $res = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$table], $res);
        echo json_encode($res);
    }
}
?>

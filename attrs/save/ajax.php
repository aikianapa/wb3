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
        $item = $this->app->vars("_post");
        if (count($item)) $res = $this->app->itemSave($table,$item,true);
        echo json_encode($res);
    }

}
?>

<?php
class wbAjaxTree
{
    public function __construct(&$app)
    {
        header('Content-Type: charset=utf-8');
        header('Content-Type: application/json');
        $this->app = $app;
    }

    function tree() {
        $app = $this->app;
        $mode = $app->vars("_route.params.0");
        if (method_exists($this,$mode)) $this->$mode();
        die;
    }

    function update() {
        $app = $this->app;
        $tpl=$app->fromFile(__DIR__ ."/tree_ui.php",false);
        $tpl->fetch($_POST);
        echo wb_json_encode(["content"=>$tpl->html()]);
    }

    function form() {
        $app = $this->app;
        if ($app->vars->get("_route.params.0") == "prop") return tagTreeProp();
        if ($app->vars->get("_route.params.0") == "lang") return tagTreeProp("lang");

        if ($app->vars("_route.params.1") == "dict") {
            $dict = $app->fromFile(__DIR__ . "/tree_dict.php");
            $dict->fetch($_POST);
            echo wb_json_encode(["content"=>$dict->outer(),"post"=>$_POST]);
            die;
        }

        $data = $this->tagTreeForm($app->vars("_post.dict"),$app->vars("_post.data"));
        $data = $app->fromString($data);
        $data->fetch($app->vars("_post.data"));
        if ($app->vars->get("_route.params.1") == "data") {
          echo wb_json_encode(["content"=>$data->outer(),"post"=>$_POST]);
          die;
        }
        $out = $app->fromFile(__DIR__ . "/tree_edit.php");
        $out->fetch($app->vars("_post.data"));
        $out->find(".treeData > form")->html($data);
        echo wb_json_encode(["content"=>$out->outer(),"post"=>$_POST]);
        die;
    }

    function tagTreeForm($dict=[],$data=[]) {
        $app = $this->app;
        $fldset = $app->fromFile(__DIR__ . "/tree_fldset.php");
        $out = "";
        if (!isset($data["data"])) $data["data"] = [];
        if ((array)$dict === $dict) {
            foreach($dict as $fld) {
              $set = $fldset->clone();
              $set->fetch($fld);
              $set->find("label")->inner($fld["label"]);
              $set->find("div.col-12")->append($app->fieldBuild($fld,$data));
              $out .= $set->outer()."\n";
              //$out .= $app->FieldBuild($fld,$data)."\n";
            }
        }
        return $out;
    }

}

?>

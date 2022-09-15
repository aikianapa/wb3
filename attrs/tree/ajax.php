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
        $app = &$this->app;
        $mode = $app->vars("_route.params.0");
        if (method_exists($this,$mode)) $this->$mode();
        die;
    }

    function update() {
        unset($_POST['__token']);
        $app = &$this->app;
        $tpl=$app->fromFile(__DIR__ ."/tree_ui.php",false);
        $tpl->fetch($_POST);
        echo wb_json_encode(["content"=>$tpl->html()]);
    }

    function form() {
      unset($_POST['__token']);
        $app = $this->app;
        if ($app->vars->get("_route.params.1") == "prop") return $this->tagTreeProp();
        if ($app->vars->get("_route.params.1") == "lang") return $this->tagTreeProp("lang");

        if ($app->vars("_route.params.1") == "dict") {
            $dict = $app->fromFile(__DIR__ . "/tree_dict.php");
            $dict->fetch($_POST);
            echo wb_json_encode(["content"=>$dict->outer(),"post"=>$_POST]);
            die;
        }
        $data = $this->tagTreeForm($app->vars("_post.dict"),$app->vars("_post.data"));
        $data = $app->fromString($data);
        $data->fetch($app->vars("_post.data.data"));
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

		function tagTreeProp( $type = null ) {
		    $app = $this->app;
		    $out = $app->fromFile( __DIR__ . '/tree_prop.php' );
				$lang = $out->find('wb-lang');
				$lang->copy($out);
				$lang->fetchNode();
				isset($_POST['dict']) ? $dict = $_POST['dict'] : $dict = [];

		    if ( $type == null ) {
		        $com = $app->fromString( $out->find( '[type=common]' )->html(), true );
		        $com->fetch($dict);
		    }
				isset($_POST['type']) ? $type = $_POST['type'] : $type = null;
		    if ($out->find( "template[type='{$type}']")->length ) {
		        $outtype = $app->fromString($out->find( "[type={$type}]" )->inner());
						$outtype->copy($out);
						$outtype->fetch($dict);
						$out = $outtype;
		        if ( isset( $com ) ) $out->find( 'form' )->append( $com->find( 'form' )->html() );
		    } else {
		        $out = $com;
		    }
		    $out->fetch( $dict );
		    echo wb_json_encode( ['content'=>$out->outer()] );
				die;
		}


    function tagTreeForm($dict=[],$data=[]) {
        $app = $this->app;
        $fldset = $app->fromFile(__DIR__ . "/tree_fldset.php");
        $out = "";
        $env = $app->vars('_env.locale');
        if (!isset($data["data"])) $data["data"] = [];
        if ((array)$dict === $dict) {
            foreach($dict as $fld) {
              $set = $fldset->clone();
              isset($fld["label"]) ? $label = $fld["label"] : $label='';
              $app->vars('_env.locale', $label);
              isset($label[$_SESSION['lang']]) ? $label = $label[$_SESSION['lang']] : null;
              $fld['label'] = $label;
              $set->fetch($fld);
              //if ($fld['name'] == 'test') echo $app->fieldBuild($fld, $data);
              $field = $app->fieldBuild($fld, $data);
              $set->find("div.col-12")->append($field);
              $out .= $set->outer()."\n";
            }
        }
        $app->vars('_env.locale',$env);
        return $out;
    }
}

?>

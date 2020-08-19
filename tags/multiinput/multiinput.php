<?php
use Adbar\Dot;
class tagMultiinput {

  public function __construct($dom) {
			$this->app = $dom->app;
      if (!$dom->is('[done]')) $this->multiinput($dom);
  }

  public function multiinput($dom) {
        if (!$dom->app) $dom->app = new wbApp();
				$this->dom = &$dom;
        $wrp = $dom->app->fromFile(__DIR__ ."/multiinput_wrapper.php");
        $field = "multifld";
        if ($dom->attr("name") > "") $field = $dom->attr("name");
        if ($dom->params("name") > "") $field = $dom->params("name");
        $dom->attr("name",$field);
        $dom->params->name = $field;
        if (isset($dom->item[$field])) $dom->item = [$field => $dom->item[$field],"_parent"=>$dom->item];
				if (isset($dom->dict->prop->multiflds)) {
						$inner = $this->buildInner();
				} else {
						$inner = $dom->inner();
				}

        if ($inner == "") $inner = "<input type='text' name='{$field}' class='form-control' />";
        $wrp = $dom->app->fromString(str_replace("{{inner}}",$inner,$wrp));
        $dom->attr("id") > "" ? $tplId = $dom->attr("id") : $tplId='mi_'.wbNewId();
        $dom->attr("id",$tplId);
        $textarea = $dom->app->fromString("<textarea name='{$field}' type='json' class='wb-multiinput-data' style='display:none;'></textarea>");
        $textarea->copy($dom);
        $textarea->attr("data-tpl",$tplId);
        $dom->tpl = $wrp->outer();
        $fields = new Dot();
        $fields->setReference($textarea->item);
        $wrp->fetch($fields->get());

				$values = $fields->get($field);
				if ((array)$values === $values) {$avlues;} else {
						$values = json_decode($values,true);
				}

        $this->setData($dom,$values);
        $dom->append($textarea)
            ->append("\n<template id='{$tplId}'>{$wrp}</template>\n")
            ->append('<script type="wbapp">wbapp.loadScripts(["/engine/js/php.js","/engine/js/jquery-ui.min.js","/engine/tags/multiinput/multiinput.js"],"multiinput-js");</script>'."\n\r");
        $dom->attr('done',"");
    }

		function buildInner() {
				$app = &$this->app;
				$dom = &$this->dom;
				$out = '';
				$col = $dom->app->fromFile(__DIR__ ."/multiinput_col.php");
				foreach($dom->dict->prop->multiflds as $i => $fld) {
						$col->inner($app->fieldBuild($fld));
						$col->find("[done]")->removeAttr("done");
						$out .= $col->outer()."\n";
				}
				return $out;
		}

    function setData(&$dom, $data=[[]]) {
        $name = $dom->params("name");
        $str = "";
        $_idx = 0;
        if ((array)$data === $data) {
            foreach($data as $i => $item) {
                $line = $dom->app->fromString($dom->tpl);
                if ((array)$item === $item) {
                    $item['_idx'] = $_idx;
                    $line->item = $item;
                    $line->fetch();
                } else {
                    $line->find("[name='{$name}']")->attr("value",$item)->attr("done",true);
                }
								$_idx++;
                $str .= $line;
            }
        }
        if ($str > "") $dom->html($str);
        else $dom->html($dom->tpl);
    }

}
?>

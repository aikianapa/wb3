<?php
use Adbar\Dot;
class tagMultiinput {
  public $app;
  public $dom;
  public $col;
  
  public function __construct($dom) {
			$this->app = $dom->app;
      if (!$dom->is('[done]')) $this->multiinput($dom);
  }

  public function multiinput($dom) {
        if (!$dom->app) $dom->app = new wbApp();
				$this->dom = &$dom;
        $this->col = $dom->app->fromFile(__DIR__ ."/multiinput_col.php");
        $wrp = $dom->app->fromFile(__DIR__ ."/multiinput_wrapper.php");
        $field = "multifld";
        if ($dom->attr("name") > "") $field = $dom->attr("name");
        if ($dom->params("name") > "") $field = $dom->params("name");
        $dom->attr("name",$field);
        $dom->params->name = $field;
        if (isset($dom->item[$field])) $dom->item = [$field => &$dom->item[$field],"_parent"=>&$dom->item];
				if (isset($dom->dict->prop->multiflds)) {
						$inner = $this->buildInner();
				} else {
						$inner = $dom->inner();
				}
        if (in_array(trim($inner),['',' '])) $inner = "<input type='text' name='{$field}' value='' class='form-control' />";
        $wrp = $dom->app->fromString(str_replace("{{inner}}",$inner,$wrp));
        $dom->attr("id") > "" ? $tplId = $dom->attr("id") : $tplId='mi_'.wbNewId();
        $dom->attr("id",$tplId);
        $textarea = $dom->app->fromString("<textarea data-tpl='{$tplId}' name='{$field}' type='json' class='wb-multiinput-data' style='display:none;'></textarea>");
        $dom->tpl = $wrp->outer();
        $tpl = $dom->app->fromString($wrp->outer());
        $tpl->fetch();
        $fields = $dom->app->dot($dom->item);
        $dom->attr('value')>'' ? $fields->set($field, $dom->attr('value')) : null;
        //$wrp->fetch($fields->get());
				$values = $fields->get($field);
				((array)$values === $values) ? null : $values = json_decode($values,true);
        $this->setData($values);
        $inner = $textarea;
        $inner .= "\n<template id='{$tplId}'>{$tpl}</template>\n";
        $inner .= '<script wb-app remove>wbapp.loadScripts(["/engine/js/php.js","/engine/js/jquery-ui.min.js","/engine/tags/multiinput/multiinput.js"],"multiinput-js");</script>'."\n\r";
        $dom->attr('done', '');
        $dom->append($inner);
    }

		function buildInner() {
        gc_enable();
				$app = &$this->app;
				$dom = &$this->dom;
				$col = $dom->app->fromString($this->col->outer());
        $out = '';
				foreach($dom->dict->prop->multiflds as $i => $fld) {
						$col->inner($app->fieldBuild($fld));
						$col->find("[done]")->removeAttr("done");
						$out .= $col->outer()."\n";
            gc_collect_cycles();
				}
        gc_disable();
				return $out;
		}

    function setData($data=[[]]) {
        $dom = &$this->dom;
        $name = $dom->params("name");
        $str = "";
        $_idx = 0;
        if ((array)$data === $data) {
            gc_enable();
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
                gc_collect_cycles();
            }
            gc_disable();
        }
        $str > "" ? $dom->html($str) : $dom->html($dom->tpl);
    }

}
?>

<?php
use Adbar\Dot;
use Nahid\JsonQ\Jsonq;
class tagForeach {
  public function __construct($dom) {
      return $this->foreach($dom);
  }

  public function foreach($dom) {
        $app = &$dom->app;
        if ($dom->is(":root")) $dom->rootError();
        $idx = 0;
        $ndx = 1;
        $page = $pages = 1;
        $list = [];
        if (!isset($dom->role)) return $dom;
        if (!$dom->app) $dom->app = new wbApp();
        $dom->attr("id") > "" ? $tid = $dom->attr("id") : $tid = "fe_".$dom->app->newId();
        $list = $dom->item;
        $options = [];
        $dom->params("table") > "" ? $table = $dom->params->table : $table = "";
        if ($dom->params("orm") > "") $options["orm"] = $dom->params->orm;
        if ($dom->params("item") > "") $options["item"] = $dom->params->item;
        if ($dom->params("filter") > "") $options["filter"] = $dom->params->filter;
        if ($dom->params("where") > "") $options["where"] = $dom->params->where;
        if ($dom->params("render") == "client" && $dom->params("table") > "") {
            $dom->attr("data-ajax",'{"url":"/ajax/'.$dom->params("table").'/list/"}');
            unset($table);
        }
        if ($dom->params("return") > "") {
            $options["return"] = $app->attrToArray($dom->params("return"));
        }
        if ($dom->params("sort") > "") {
            $options["sort"] = $app->attrToArray($dom->params("sort"));
        }
        $dom->options = $options;
        if ($table > "" AND $dom->params("call") == "") {
          $res = wbItemList($table,$options);
          $list = $res["list"];
          $count = $res["count"];
        } else if ($table > "" AND $dom->params("call") > "") {
          $list = [];
          $formClass = $app->formClass($table);
          $method = $dom->params("call");
          if (method_exists($formClass,$method)) $list = $formClass->$method($dom);
          $count = count($list);
        } else if ($table == "" AND $dom->params("call") > "") {
            $list = (array)wbEval($dom->params("call"));
        }

        if ($dom->params("from")) {
            if (isset($list[$dom->params->from])) {$list = $list[$dom->params->from];} else {$list = [];}
            if (isset($options["sort"]) AND (array)$options["sort"] === $options["sort"]) {
                foreach((array)$options["sort"] as $key=> $fld) {
                    if (!((array)$fld === $fld)) {
                        $fld = explode(":",$fld);
                        if (!isset($fld[1])) {
                            $fld[1] = 1;
                        } else if (in_array(strtolower($fld[1]),['a','asc','1'])) {
                            $fld[1] = '';
                        } else if (in_array(strtolower($fld[1]),['d','desc','-1'])) {
                            $fld[1] = 'desc';
                        }
                        $params['sort'][$fld[0]] = $fld[1];
                    } else {
                        $params['sort'][$key] = $fld;
                    }

                }
                $json = new Jsonq();
                $json = $json->collect($list);
                if (count($params['sort'])) {
                    foreach($params['sort'] as $fld => $order) $json->sortBy($fld,$order);
                }
                $list = $json->get();
            }
        }

        if ($dom->params("size") > "") {
            $dom->params("page") ? $page = $dom->params->page : $page = 1;
            $list = array_chunk($list,$dom->params->size);
            $pages = ceil($count / $dom->params->size);
            if ($page > $pages OR $page<=0) $list = [];
            if ($pages >= 1 && isset($list[$page -1])) $list = $list[$page -1];
            $ndx = ($page -1) * $dom->params("size") +1;
        }
        if ($dom->params("count") > "") {
            $item = $list;
            $list = [];
            $count = intval($dom->params->count);
            if (count($item)) {
                for ( $i=0; $i<$count; $i++ ) {
                    foreach($item as $line) $list[] = $line;
                }
            } else {
                for ( $i=0; $i<$count; $i++ ) {
                    $list[] = ["_id"=>$i];
                }
            }
        }
        if ($dom->params("rand") == "true") shuffle($list);
        $empty = $dom->find("wb-empty")[0];
        $dom->find("wb-empty")->remove();
        $tpl = $dom->html();
        $dom->html("");
        $dom->attr("data-ajax") == "" ? $render = false : $render = true;
        if (!$render) $tpl = "<wb>{$tpl}</wb>";

        foreach((array)$list as $key => $val) {
            $value = $val;
            $val = (object)$val;
            $val->_idx = $idx;
            $val->_ndx = $ndx;
            $val->_page = $page;
            $val->_pages = $pages;
            $val->_val = $value;
            if (!isset($val->_id)) isset($val->id) ? $val->_id = $val->id : $val->_id = $idx;
            if ($table > "") $val = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$table], (array)$val);
            if ($render > "") {
                $list[$key] = (array)$val;
            } else {
                $line = $dom->app->fromString($tpl);
                $line->copy($dom);
                $line->item = (array)$val;
                $line->fetch();
                $dom->append($line->inner());
            }
            $idx++;
            $ndx++;
        }
        if ($render) {
            $dom->append("<template id = \"{$tid}\" data-ajax=\"".$dom->attr("data-ajax")."\">\n{{#each result}}\n".$tpl."\n{{/each}}</template>\n");
            $dom->find("template[id=\"{$tid}\"] .pagination")->attr("data-tpl",$tid);
        }
        if (!count((array)$list) OR $dom->html() == "") $dom->inner($empty->inner());
        if ($dom->tagName == "wb-foreach") $dom->unwrap("wb-foreach");
        return $dom;
  }
}
?>

<?php
use Adbar\Dot;
class tagForeach {

  public function __construct($dom) {
      return $this->foreach($dom);
  }

  public function foreach($dom) {
        if ($dom->is(":root")) $dom->rootError();
        $idx = 0;
        $ndx = 1;
        $page = $pages = 1;
        if (!isset($dom->role)) return $dom;
        if (!$dom->app) $dom->app = new wbApp();
        $list = $dom->item;
        $dom->params("table") > "" ? $table = $dom->params->table : $table = "";
        $dom->params("orm") > "" ? $options = ["orm"=>$dom->params->orm] : $options = [];

        $list = wbItemList($table,$options);
        if ($dom->params("size") > "") {
            $dom->params("page") ? $page = $dom->params->page : $page = 1;
            $list = array_chunk($list,$dom->params->size);
            $pages = count($list);
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
        $tpl = "<wb>".$dom->html()."</wb>";
        $dom->html("");
        foreach($list as $val) {
            $val = (object)$val;
            $val->_idx = $idx;
            $val->_ndx = $ndx;
            $val->_page = $page;
            $val->_pages = $pages;
            if (!isset($val->_id)) isset($val->id) ? $val->_id = $val->id : $val->_id = $idx;
            $line = $dom->app->fromString($tpl);
            $line->copy($dom);
            $line->item = (array)$val;
            $line->fetch();
            $dom->append($line->children("wb")->inner());
            $idx++;
            $ndx++;
        }
        if (!count($list) OR $dom->html() == "") $dom->inner($empty->inner());
        if ($dom->tagName == "wb-foreach") $dom->unwrap("wb-foreach");
        return $dom;
  }
}
?>

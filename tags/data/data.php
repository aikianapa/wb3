<?php
use Adbar\Dot;
class tagData {
  public function __construct(&$dom) {
      return $this->data($dom);
  }

  public function data(&$dom) {
    $save = $dom->item;
    $data = new Dot();
    if ($dom->is(":root")) $dom->rootError();

    if ($dom->params("table") AND $dom->params("item")) {
        $dom->item = wbItemRead($dom->params->table, $dom->params->item);
    } else if ($dom->params("json")) {
        $dom->item = $dom->params("json");
    } else if ($dom->params("table") AND $dom->params("filter")) {
       $dom->item = $dom->app->itemList($dom->params->table,['filter'=>$dom->params->filter,'limit'=>1]);
       if (!count($dom->item["list"])) {
          $dom->item = [];
       } else {
          $dom->item = array_shift($dom->item["list"]);
       }
    } else if (!$dom->params("field")) {
        $dom->item = [];
    }
    $dom->item == null ? $dom->item = [] : null;

    if ($dom->params('ajax')) {
        $ajax = $dom->params('ajax');
        if ($dom->params("size") == '') {
            $dom->params->size = 999999999;
        }
        $url = parse_url($ajax);
        if (!isset($url['scheme'])) {
            if ($dom->app->vars('_sett.api_key_query') == 'on' and !isset($url['__token'])) {
                $post = ['__token'=>$dom->app->vars('_sess.token')];
            } else {
                $post = null;
            }
            
        }
        $ajax = $dom->app->vars('_route.host').$ajax;
        $list = json_decode(str_replace("'", '"', wbAuthPostContents($ajax, $post)), true);
        $list === (array)$list ? null : $list = (array)$list;
        $dom->item = &$list;
    }



    if (isset($dom->item["_table"])) $dom->item = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$dom->item["_table"]], $dom->item);
    if ($dom->params("field")) {
        $data = new Dot();
        $data->setReference($dom->item);
        $dom->item = $data->get($dom->params("field"));
        if (!((array)$dom->item === $dom->item)) $dom->item = [$dom->params("field") => $dom->item];
    }

    if ($dom->find("wb-empty")->length) {
        $empty = $dom->find("wb-empty")[0];
        $dom->find("wb-empty")->remove();
        if (!count($dom->item)) $dom->inner($empty->inner());
    }
    if ($dom->params("parent") !== "false") $dom->item['_parent'] = $save;
    $dom->fetch();
    $dom->unwrap();
//    $dom->before($dom->inner());
//    $dom->remove();
    return $dom;
  }
}
?>

<?php
class modCart {
  public function __construct(&$dom) {
      $this->dom = &$dom;
      $this->init($dom);
  }

  public function init(&$dom) {
      $app = &$dom->app;
      $dom->after('<script type="wbapp" removable>wbapp.loadScripts(["/engine/modules/cart/cart.js"],"cart-mod-js");</script>');
      if (!$dom->params) $dom->params = ['list'=>'list'];
      if (isset($dom->params->list)) {
            $dom->params->list = 'list';
            $this->list();    
      } else if (isset($dom->params->remove)) {
          $dom->addClass('mod-cart-remove');
      } else if (isset($dom->params->add)) {
          if ($dom->params("ajax") > "") {
              $dom->attr("data-ajax",$dom->params("ajax"));
          }
          if ($dom->params("data") > "") {
              $dom->attr("mod-cart-data",json_encode($dom->params("data")));
          }
          $dom->addClass('mod-cart-add');
      }
      //$dom->remove();
  }
    
  public function list() {
      $app = &$this->dom->app;
      $dom = &$this->dom;
      $tpl = $dom->inner();
      if ($dom->tagName == 'wb-module') {
        $cid = $dom->parent()->attr('id'); 
      } else {
        $cid = $dom->attr('id');
      }
      if ($cid == '') {
          $cid = wbNewId('_','cartlist');
          if ($dom->tagName == 'wb-module') {
              $dom->parent()->attr('id', $cid);
          } else {
              $dom->attr('id', $cid);
          }
      }
      $inner = $app->fromString($dom->inner());
      $inner->addClass('mod-cart-item');
      $dom->after('<template data-target="'.$cid.'">{{#'.$dom->params->list.': index, key}}'."\n".$inner->outer()."\n".'{{/'.$dom->params->list.'}}</template>');
      if ($dom->tagName == 'wb-module') $dom->remove();
  }
  
}
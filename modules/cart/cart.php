<?php
class modCart {
  public function __construct(&$dom) {
      $this->dom = &$dom;
      $this->init($dom);
  }

  public function init(&$dom) {
      $app = &$dom->app;
      if (!$dom->params) $dom->params = ['list'=>'list'];
      if (isset($dom->params->list)) {
            $dom->params->list > '' ? null : $dom->params->list = 'list';
            $this->list();    
      } else if (isset($dom->params->remove)) {
          $dom->addClass('mod-cart-remove');
      } else if (isset($dom->params->add)) {
        $dom->params("ajax") > "" ? $dom->attr("data-ajax",$dom->params("ajax")) : null;
        $dom->params("data") > "" ? $dom->attr("mod-cart-data",json_encode($dom->params("data"))) : null;
        $dom->addClass('mod-cart-add');
      }
      $dom->after('<script type="wbapp" remove>wbapp.loadScripts(["/engine/modules/cart/cart.js"],"cart-mod-js");</script>');
      //$dom->remove();
  }
    
  public function list() {
      $app = &$this->dom->app;
      $dom = &$this->dom;

      if ($dom->tagName == 'wb-module') {
        $cid = $dom->parent()->attr('id'); 
      } else {
        $cid = $dom->attr('id');
      }
      if ($cid == '') {
          $cid = 'cartlist_'.$dom->params->list;
          if ($dom->tagName == 'wb-module') {
              $dom->parent()->attr('id', $cid);
          } else {
              $dom->attr('id', $cid);
          }
      }
      $sum = $dom->params->sum;
      $inner = $app->fromString($dom->inner());
      $inner->addClass('mod-cart-item');
      $tpl = $inner->outer();
      $dom->after('<template data-target="'.$cid.'">{{#list: index, key}}'."\n".$tpl."\n".'{{/list}}<meta name="sum" value="'.$sum.'"></template>');
      if ($dom->tagName == 'wb-module') $dom->remove();
  }
  
}
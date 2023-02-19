<?php
class attrIf extends wbDom {
  public function __construct(&$dom) {
      $this->if($dom);
      unset($dom->funca);
  }

  public function if(&$dom) {
      $res = false;
      $item = $dom->item;
      $dom->removeAttr("wb-if");


      $dom->params->if = explode("==>",$dom->params->if);
      if (isset($dom->params->if[1])) {
          $setattr = $dom->params->if[1];
      }
      $dom->params->if = $dom->params->if[0];

      $res = $dom->app->cond($dom->params->if, $item);
      $dom->params->if = $res;
      if (isset($setattr)) {
        if ($res) {
          $setattr = explode("=",$setattr);
          if (!isset($setattr[1])) $setattr[1] = true;
          $dom->attr(trim($setattr[0]),$setattr[1]);
        }
      } 
      if (!$res && !$dom->is('wb-var[else]') && !isset($setattr)) {
          $dom->done = true;
          $dom->remove();
      }
      
      return $dom;
  }
}
?>

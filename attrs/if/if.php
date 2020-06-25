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

      if (in_array(substr(trim($dom->params->if),0,1),['"',"'"] ) ) {
          $res = wbEval($dom->params->if );
      } else {
          $res = wbEval( '$item'.$dom->params->if );
      }

      if (isset($setattr)) {
        if ($res) {
          $setattr = explode("=",$setattr);
          if (!isset($setattr[1])) $setattr[1] = true;
          $dom->attr(trim($setattr[0]),$setattr[1]);
        }
      } else {
          if (!$res) $dom->remove();
      }
      return $dom;
  }
}
?>

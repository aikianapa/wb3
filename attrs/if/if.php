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

      if (in_array(substr(trim($dom->params->if),0,1),['"',"'"] ) ) {
          $res = wbEval($dom->params->if );
      } else {
          $res = wbEval( '$item'.$dom->params->if );
      }

      if (!$res) $dom->remove();
      return $dom;
  }
}
?>

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
      $res = wbEval( $dom->params->if );
      if (!$res) $dom->remove();
      return $dom;
  }
}
?>

<?php
class attrJq extends wbDom {
  public function __construct(&$dom) {
      $this->attrJq($dom);
      unset($dom->funca);
  }

  public function attrJq(&$that) {
          $jq = $that->params->jq;
          $jq = explode(";",$jq);
          $jq = $jq[0].";";

          if (substr($jq,0,6) == '$dom->') {
              $dom = $that;
              $ch = $dom->children();
              foreach($ch as $c) {
                  $c->copy($dom);
                  $c->fetchNode();
              }
              try {
                @eval($jq);
              }
              catch(Exception $err) {
                echo "Unknown result in the tag: ". $tag;
              }
              $ch = $dom->children();
              foreach($ch as $c) {
                  $c->copy($dom);
                  $c->fetch();
              }
            } else {
              echo 'Error: wb-jq command was start at $dom->';
              echo "\n<br/>". $jq;
            }
            $that->removeAttr("wb-jq");
  }
}
?>

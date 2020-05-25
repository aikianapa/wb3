<?php
use Adbar\Dot;
class tagJq {
  public function __construct(&$dom) {
      return $this->jq($dom);
  }

  public function jq(&$that) {
      if ($that->is(":root")) $that->rootError();
      if (!$that->params) {
            $that->remove();
            return;
      }

      $tag = htmlentities($that->outer());
      $tag = str_replace("&amp;gt;",">",$tag);
      $tag = str_replace("&gt;&lt;/wb-jq&gt;","/>",$tag);
      if ($tag == "") return;

      $jq = $that->params->wb;
      $jq = explode(";",$jq);
      $jq = $jq[0].";";
      if (substr($jq,0,6) == '$dom->') {
          $dom = $that->parent;
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
          echo "\n<br/>". $tag;
      }
      $that->remove();
  }
}
?>

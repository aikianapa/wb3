<?php
class attrJq extends wbDom {
  public function __construct(&$dom) {
      $this->attrJq($dom);
      unset($dom->funca);
  }

  public function attrJq(&$that) {
            $jqs = explode( ';', $that->params->jq);
            $dom = &$that;

            foreach ((array)$jqs as $i => $jq) {
                if (substr($jq, 0, 6) == '$dom->') {
                    $jq = ltrim($jq);
                    $ch = $dom->children();
                    foreach ($ch as $c) {
                        $c->copy($dom);
                        $c->fetchNode();
                    }
                    try {
                        @eval($jq.';');
                    } catch (Exception $err) {
                        echo "Unknown result in the tag: ". $tag;
                    }
                    $ch = $dom->children();
                    foreach ($ch as $c) {
                        $c->copy($dom);
                        try {
                            @$c->fetch();
                        } catch (\Throwable $th) {
                            null;
                        }
                    }
                } else if ($jq > '') {
                    echo 'Error: wb-jq command was start at $dom->';
                    echo "\n<br/>". $jq;
                }
            }
            $that->removeAttr("wb-jq");
  }
}
?>

<?php
use Adbar\Dot;
class tagVar {
  public function __construct(&$dom) {
      return $this->var($dom);
  }

  public function var(&$dom) {
      $parent = &$dom->parent;
      if (!isset($parent->variables)) $parent->variables = [];
      $attrs = $dom->attributes();
      foreach($attrs as $atname => $atval) {
          $wb = substr($atname,0,3);
          if (!in_array($wb,["wb","wb-"])) {
              $atval = wbAttrToValue($atval);
              $dom->app->vars("_var.{$atname}",$atval);
          }
      }
      $dom->remove();
  }

}
?>

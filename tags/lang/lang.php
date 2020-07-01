<?php
use Adbar\Dot;
class tagLang {
  public function __construct(&$dom) {
      return $this->lang($dom);
  }

  public function lang(&$dom) {
      $app = $dom->app;
      $parent = &$dom->parent;
      $parent->locale = parse_ini_string($dom->html(),true);
      $app->vars("_env.locale", $parent->locale);
      $dom->remove();
  }

}
?>

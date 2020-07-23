<?php
use Adbar\Dot;
class tagLang {
  public function __construct(&$dom) {
      return $this->lang($dom);
  }

  public function lang(&$dom) {
      $app = $dom->app;
      $parent = &$dom->parent;
      $lang = trim($dom->inner());
      $lang = str_replace('[',"\n[",$lang);
      $parent->locale = parse_ini_string($lang,true);
      $app->vars("_env.locale", $parent->locale);
      $dom->remove();
  }

}
?>

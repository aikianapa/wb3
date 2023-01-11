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
      try {
          $ini = parse_ini_string($lang, true);
          $locale = (array)$app->vars("_env.locale");
          $app->vars("_env.locale", array_merge($locale, $ini));
      } catch (\Throwable $th) {
        $ini = [];
      }
      $parent->locale = $app->vars("_env.locale");
      $dom->remove();
  }

}
?>

<?php
use Adbar\Dot;
class tagSnippet {
  public function __construct(&$dom) {
      return $this->snippet($dom);
  }

  public function snippet(&$dom) {
      $app = $dom->app;
      if (!$dom->params) {
            $dom->remove();
            return;
      }
      if ($dom->attr("src")>"") $dom->params->src = $dom->attr("src");
      if ($dom->attr("name")>"") $dom->params->name =  $dom->attr("name");
      if ($dom->params("name")) {
          $snip = $app->getForm("snippets", $dom->params->name);
      } else if ($dom->params("src")) {
          $snip = $app->fromFile($app->vars("_env.path_app").$dom->params->src);
      }
      $snip->fetch((array)$dom->params("data"));
      $snip->fetch($dom->item);
      $dom->inner($snip);
      $dom->unwrap("wb-snippet");
      $dom->fetch();
  }
}
?>

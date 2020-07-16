<?php
class tagInclude {
  public function __construct($dom) {
      $this->include($dom);
  }

  public function include($dom) {
    if ($dom->is(":root")) $dom->rootError();
    if ($dom->params("src")) {
        $inc = $dom->app->fromFile($_ENV["path_app"].$dom->params("src"));
    } else if ($dom->params("url")) {
        $inc = $dom->app->fromString(file_get_contents($dom->app->vars("_route.host").$dom->params("url")));
    } else if ($dom->params("tpl")) {
        $inc = $dom->app->getTpl($dom->params("tpl"));
    } else if ($dom->params("form")) {
      $form = $dom->params("form");
      $mode = $dom->params("mode");
      if (!$dom->params("mode")) {
          $mode = explode("_",$form);
          $form = array_shift($mode);
          $mode = implode("_",$mode);
      }
      $inc = $dom->app->getForm($form,$mode,$dom->params("engine"));
    }
    if (isset($inc)) {
        if (!isset($dom->item['header'])) {$dom->item['header'] = $dom->app->vars('_sett.header');}
        $inc->copy($dom);
        if ($dom->head()) {
            $dom->head($inc->outer());
        } else {
          $inner = $dom->app->fromString("<wb>".$dom->html().$inc->outer()."</wb>");
          $inner->copy($inc);
          $inner->fetch();
          $dom->before($inner->children("wb")->inner());
        }
    }
    $dom->remove();
  }
}
?>

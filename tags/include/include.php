<?php
class tagInclude {
  public function __construct($dom) {
      $this->include($dom);
  }

  public function include($dom) {
    if ($dom->is(":root")) $dom->rootError();
    if ($dom->params("src")) {
        $inc = $dom->app->fromFile($_ENV["path_app"].$dom->params("src"));
        $dom->before("\n<!-- Include src: ".$dom->params("src")." -->\n");
    } else if ($dom->params("url")) {
        $inc = $dom->app->fromString(file_get_contents($dom->app->vars("_route.host").$dom->params("url")));
        $dom->before("\n<!-- Include url: ".$dom->params("url")." -->\n");
    } else if ($dom->params("snippet")) {
        $snippet = $dom->params("snippet");
        strtolower(substr($snippet, -4)) == '.php' ? null : $snippet .= '.php';
        $inc = $dom->app->getForm('snippets', $snippet);
        $dom->before("\n<!-- Include snippet: ".$dom->params("tpl")." -->\n");
    } else if ($dom->params("tpl")) {
        $inc = $dom->app->getTpl($dom->params("tpl"));
        $dom->before("\n<!-- Include tpl: ".$dom->params("tpl")." -->\n");
    } else if ($dom->params("form")) {
      $form = $dom->params("form");
      $mode = $dom->params("mode");
      if (!$dom->params("mode")) {
          $mode = explode("_",$form);
          $form = array_shift($mode);
          $mode = implode("_",$mode);
      }
      $inc = $dom->app->getForm($form,$mode,$dom->params("engine"));
      $dom->before("\n<!-- Include form: {$form}->{$mode} -->\n");

    }
    if (isset($inc) AND is_object($inc)) {
        if (!isset($dom->item['header'])) {$dom->item['header'] = $dom->app->vars('_sett.header');}
        $inc->copy($dom);
        if ($dom->attr('class') > ' ') {
            if ($inc->is("html")) {
                $inc->find("html:first > :first-child")->addClass($dom->attr('class'));
            } else {
                $inc->children(":first-child")->addClass($dom->attr('class'));
            }
        }
        

        if ($dom->head()) {
            $dom->head($inc->outer());
        } else {
          $inner = $dom->app->fromString("<wb>".$dom->html().$inc->outer()."</wb>");
          $inner->copy($inc);
          if ($dom->params('render') !== 'client' && !$dom->params('nofetch')) $inner->fetch();
          $dom->before($inner->children("wb")->inner());
        }
    } else {
        if (isset($form) && isset($mode)) {
            $dom->before('<div>'.$inc." {$form}->{$mode} </div>");
        } else {
            $dom->before('<div>Include error: '.json_encode($dom->params).' </div>');
        }
        
    }
    $dom->remove();
  }
}
?>

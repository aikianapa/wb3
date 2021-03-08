<?php
use Adbar\Dot;

class modQuill {
  function __construct($dom) {
      $dom->app->addEditor("quill",__DIR__,"Quill editor");
      $this->init($dom);
  }
  public function init($dom) {
  		$out = $dom->app->fromFile(__DIR__ ."/quill_ui.php",true);
      $dom->attr('id') > '' ? $id = $dom->attr('id') : $id = "jd-".$dom->app->newId();
  		$textarea = $out->find("textarea");
  		$textarea->attr("id",$id);
      $out->copy($dom);
      if ($dom->attr("name") > "") $dom->params->name = $dom->attr("name");
      if ($dom->params("name")) {
					$item = new Dot();
					$item->setReference($dom->item);
          $textarea->attr("name",$dom->params->name);
          $text = $item->get($dom->params->name);
        } else {
          $text = $dom->html();
      }
//      $text = html_entity_decode(htmlspecialchars_decode($text));
      $dom->after($out);
      $dom->remove();
  }
}

?>

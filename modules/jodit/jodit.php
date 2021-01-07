<?php
use Adbar\Dot;

class modJodit {
  function __construct($dom) {
      $dom->app->addEditor("jodit",__DIR__,"Jodit editor");
      $this->init($dom);
  }
  public function init($dom) {
  		$out = $dom->app->fromFile(__DIR__ ."/jodit_ui.php",true);
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
      $textarea->inner($text);
      $dom->inner($out);
  }
}

?>

<?php

class tagDict {

  public function __construct($dom) {
      $this->dict($dom);
  }

  public function dict(&$dom) {
        $out = $dom->app->fromFile( __DIR__ ."/dict_ui.php");
        $out->item = $dom->item;
        $out->app = $dom->app;
        $name = $dom->attr("name");
        $out->find(".wb-dict")->attr("name",$name);
        $out->fetch();
        $dom->after($out->inner());
        $dom->remove();
    }
}
?>

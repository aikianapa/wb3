<?php
class modTagsinput {
  function __construct($dom) {
      $this->init($dom);
  }


    function init(&$dom) {
        $name = $dom->attr("name");
        $value = $dom->attr("value");
        if ($name == "" AND $dom->params('name') > "") $name = $dom->params('name');
        if ($value == "" AND $dom->params('value') > "") $value = $dom->params('value');
        $out = $dom->app->fromFile(__DIR__ ."/tagsinput_ui.php");
        $dom->attrsCopy($out);
        $out->attr("name",$name);
        $out->attr("value",$value);
        $dom->after($out);
        $dom->remove();
    }
}
?>

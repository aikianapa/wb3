<?php
use Adbar\Dot;
class tagMultiinput {

  public function __construct($dom) {
      $this->multiinput($dom);
  }

  public function multiinput($dom) {
        if (!$dom->app) $dom->app = new wbApp();
        $wrp = $dom->app->fromFile(__DIR__ ."/multiinput_wrapper.php");
        $field = "multifld";
        if ($dom->attr("name") > "") $field = $dom->attr("name");
        if ($dom->params("name") > "") $field = $dom->params("name");
        $dom->attr("name",$field);
        $dom->params->name = $field;
        $inner=$dom->inner();
        if ($inner == "") $inner = "<input type='text' name='{$field}' class='form-control' />";
        $inner = $dom->app->fromString($inner);
        $wrp = str_replace("{{inner}}",$inner,$wrp);

        $tplId=wbNewId();
        $textarea = $dom->app->fromString("<textarea name='{$field}' type='json' class='wb-multiinput-data' style='display:none;'></textarea>");
        $textarea->copy($dom);
        $dom->tpl = $wrp;
        $fields = new Dot();
        $fields->setReference($textarea->item);
        $this->setData($dom,$fields->get($field));
        $dom->append($textarea)
            ->append("<template id='{$tplId}'>{$wrp}</template>")
            ->append('<script type="wbapp">wbapp.loadScripts(["/engine/js/php.js","/engine/js/jquery-ui.min.js","/engine/tags/multiinput/multiinput.js"],"multiinput-js");</script>'."\n\r");
    }

    function setData(&$dom, $data=[[]]) {
        $name = $dom->params("name");
        $str = "";
        if ((array)$data === $data) {
            foreach($data as $i => $item) {
                $line = $dom->app->fromString($dom->tpl);
                if ((array)$item === $item) {
                    $line->item = $item;
                    $line->fetch();
                } else {
                    $line->find("[name='{$name}']")->attr("value",$item);
                }
                $str .= $line;
            }
        }
        if ($str > "") $dom->html($str);
        else $dom->html($dom->tpl);
    }

}
?>

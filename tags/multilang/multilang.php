<?php
use Adbar\Dot;
class tagMultilang {

  public function __construct($dom) {
      $this->multilang($dom);
  }

  public function multilang($dom) {
        if (!$dom->app) $dom->app = new wbApp();
        $wrp = $dom->app->fromFile(__DIR__ ."/multilang_wrapper_vertical.php");
        $field = "lang";
        $dom->attr("name") > "" ? $field = $dom->attr("name") : null;
        $dom->params("name") > "" ? $field = $dom->params("name") : null;
        $dom->attr("name",$field);
        $dom->params->name = $field;

        if ($dom->params("lang") > '') {
            $langs = wbArrayAttr($dom->params("lang"));
        } else if ($dom->params("langs") > '') {
            $langs = wbArrayAttr($dom->params("lang"));
        } else if (isset($_ENV['locale'])) {
            $langs = (array)$_ENV['locale'];
            $langs = array_keys($langs);
        } else {
            $langs = ['ru','en'];
        }
        $data = [];
        foreach((array)$langs as $l) {
            $data[$l] = [];
        }
        $dom->item['lang'] = array_merge($data, (array)$dom->getField($field));
        
        $wrp->find('.tab-content > wb-foreach > .tab-pane')->html($dom->inner());
        $wrp->copy($dom);
        $wrp->fetch();
        $wrp->find('.nav-tabs .nav-item:first-child .nav-link')->addClass('active');
        $wrp->find('.tab-content .tab-pane:first-child')->addClass('show active');
        $wrp->find('textarea.wb-multilang-data')[0]->attr('name',$field);

        $dom->attr("id") > "" ? $tplId = $dom->attr("id") : $tplId='ml_'.wbNewId();
        $dom->attr("id",$tplId);
        $dom->html($wrp->outer());
        $dom->append('<script type="wbapp" data-remove="multilang-js">wbapp.loadScripts(["/engine/js/php.js","/engine/tags/multilang/multilang.js"],"multilang-js");</script>'."\n\r");
    }


}
?>

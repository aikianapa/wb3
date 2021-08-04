<?php
use Adbar\Dot;
class tagMultilang {

  public function __construct($dom) {
      $this->multilang($dom);
  }

  public function multilang($dom) {
        if (!$dom->app) $dom->app = new wbApp();
        in_array($dom->params("vertical"), ['true','1','on']) ? $wrp = 'multilang_wrapper_vertical.php' : $wrp = 'multilang_wrapper.php';
        $wrp = $dom->app->fromFile(__DIR__ .'/'.$wrp);
        $field = "lang";
        $dom->attr("name") > "" ? $field = $dom->attr("name") : null;
        $dom->params("name") > "" ? $field = $dom->params("name") : null;
        $dom->attr("name",$field);
        $dom->params->name = $field;

        if ($dom->params("lang") > '') {
            $langs = wbArrayAttr($dom->params("lang"));
        } else if ($dom->params("langs") > '') {
            $langs = wbArrayAttr($dom->params("langs"));
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

        $dom->getField($field) ? $fld = (array)$dom->getField($field) : $fld = [];
        $dom->item['lang'] = array_merge($data, $fld);

        $wrp->find('.tab-content > wb-foreach > .tab-pane')->html($dom->inner());
        $wrp->copy($dom);
        $wrp->fetch();
        if ($dom->params("flags") == "false") $wrp->find(".nav-link img.mod-multilang-flag")->remove();
        $inputs = $wrp->find('input[name],select[name],textarea[name]');
        foreach($inputs as $inp) {
            $inp->attr('wb-name', $inp->attr('name'));
            $inp->removeAttr('name');
        }
        $wrp->find('.nav-tabs .nav-item:first-child .nav-link')->addClass('active');
        $wrp->find('.tab-content .tab-pane:first-child')->addClass('show active');
        $wrp->find('textarea.wb-multilang-data')[0]->attr('name',$field);
        $dom->attr("id") > "" ? $tplId = $dom->attr("id") : $tplId='ml_'.wbNewId();
        $dom->attr("id",$tplId);
        $outer = $wrp->outer();
        $dom->inner($outer);
        $dom->append('<script wb-app data-remove="multilang-js">wbapp.loadScripts(["/engine/js/php.js","/engine/tags/multilang/multilang.js"],"multilang-js");</script>'."\n\r");
        $dom->fetched = true;
    }


}
?>

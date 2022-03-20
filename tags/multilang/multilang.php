<?php
use Adbar\Dot;
class tagMultilang {

  public function __construct($dom) {
      $this->multilang($dom);
  }

  public function multilang($dom) {
        if ($dom->is('[done]')) return;
        if (!$dom->app) $dom->app = new wbApp();
        in_array($dom->params("vertical"), ['true','1','on']) ? $wrp = 'multilang_wrapper_vertical.php' : $wrp = 'multilang_wrapper.php';
        $wrp = $dom->app->fromFile(__DIR__ .'/'.$wrp);
        $field = "lang";
        $dom->attr("name") > "" ? $field = $dom->attr("name") : null;
        $dom->params("name") > "" ? $field = $dom->params("name") : null;
        $dom->attr("name",$field);
        $dom->params->name = $field;
        $keys = array_keys($dom->item);
        if (in_array('scalar',$keys) && in_array('_parent',$keys) && in_array('_key',$keys)) {$dom->item = $dom->item['_parent'];}
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
            $data[wbTranslit($l)] = ['_origkey'=>$l];
        }

        $tpl = '<wb>'.$dom->inner().'</wb>';

        $dom->getField($field) ? $fld = (array)$dom->getField($field) : $fld = [];
        $dom->item['lang'] = array_merge($data, $fld);

        $inners = [];
        foreach($dom->item['lang'] as $key => &$line) {
            if (isset($data[$key])) {
                $line['_origkey'] = $data[$key];
                $tabdata = isset($dom->item[$field][$key]) ? $dom->item[$field][$key] : [];
                $tabdom = $dom->app->fromString($tpl);
                $tabdom->fetch($tabdata);
                $inputs = $tabdom->find('input[name],select[name],textarea[name]');
                foreach ($inputs as $inp) {
                    $inp->attr('wb-name', $inp->attr('name'));
                    $inp->removeAttr('name');
                }
                $inners[$key] = $tabdom->inner();
            } else {
                unset($dom->item['lang'][$key]);
            }
        }

        $wrp->copy($dom);
        $wrp->fetch();
        $wrp->find('.nav-tabs .nav-item:first-child .nav-link')->addClass('active');
        $wrp->find('.tab-content .tab-pane:first-child')->addClass('show active');
        $wrp->find('textarea.wb-multilang-data')[0]->attr('name', $field);
        $tabs = $wrp->find('.tab-content .tab-pane');
        foreach($tabs as $tab) {
            $tab->inner($inners[$tab->attr('data-id')]);
        }
        if ($dom->params("flags") == "false") $wrp->find(".nav-link img.mod-multilang-flag")->remove();
        $dom->attr("id") > "" ? $tplId = $dom->attr("id") : $tplId='ml_'.wbNewId();
        $dom->attr("id",$tplId);
        $dom->inner($wrp->html());
        $dom->append("\n\r".'<script wb-app data-remove="multilang-js">wbapp.loadScripts(["/engine/js/php.js","/engine/tags/multilang/multilang.js"],"multilang-js");</script>'."\n\r");
        $dom->attr('done',true);
        $dom->fetched = true;
    }


}
?>

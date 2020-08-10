<?php
use Adbar\Dot;
class tagMultilang {

  public function __construct($dom) {
      $this->multilang($dom);
  }

  public function multilang($dom) {
        if (!$dom->app) $dom->app = new wbApp();
        $wrp = $dom->app->fromFile(__DIR__ ."/multilang_wrapper.php");
        $field = "multifld";
        if ($dom->attr("name") > "") $field = $dom->attr("name");
        if ($dom->params("name") > "") $field = $dom->params("name");
        $dom->attr("name",$field);
        $dom->params->name = $field;
        if (!$dom->params("lang")) $dom->params("lang",[$dom->app->vars('_sess.lang') => $dom->app->vars('_sess.lang')]);

        $langdata = $dom->getField($field);
        if (!is_array($langdata)) $langdata = [];
        foreach($dom->params("lang") as $key => $lang) {
            if (!isset($langdata[$key])) {
                $langdata[$key] = ['id' => $key, 'lang' => $lang, 'data' => ['test1'=>$key,'test2'=>$lang]];
            } 
        }
        $dom->item['lang'] = $langdata;
        $wrp->find('.tab-content > wb-foreach > .tab-pane > wb-data')->html($dom->inner());
        $wrp->copy($dom);
        $wrp->fetch();
      
        $wrp->find('.nav-tabs .nav-item:first-child .nav-link')->addClass('active');
        $wrp->find('.tab-content .tab-pane:first-child')->addClass('show active');
        $wrp->find('textarea.wb-multilang-data')[0]->attr('name',$field);

      
//        $dom->setField($field,$langdata);
      

        $dom->attr("id") > "" ? $tplId = $dom->attr("id") : $tplId='ml_'.wbNewId();
        $dom->attr("id",$tplId);
        $dom->html($wrp->outer());

        $dom
//            ->append("\n<template id='{$tplId}'>{$wrp}</template>\n")
            ->append('<script type="wbapp">wbapp.loadScripts(["/engine/js/php.js","/engine/tags/multilang/multilang.js"],"multilang-js");</script>'."\n\r");
    }

    function setData(&$dom, $data=[[]]) {
        $name = $dom->params("name");
        $str = "";
        $_idx = 0;
        if ((array)$data === $data) {
            foreach($data as $i => $item) {
                $line = $dom->app->fromString($dom->tpl);
                if ((array)$item === $item) {
                    $item['_idx'] = $_idx;
                    $line->item = $item;
                    $line->fetch();
                    $_idx++;
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

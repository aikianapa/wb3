<?php
class newsClass extends cmsFormsClass {

    function beforeItemShow(&$item) {
        isset($item['lang']) ? $lang = $item['lang'][$this->app->vars('_sess.lang')] : $lang = [];
        $item = (array)$lang + (array)$item;
        isset($item['header'])  AND isset($item['header'][$_SESSION['lang']]) ? $item['header'] = $item['header'][$_SESSION['lang']] : null;
        isset($item['date']) ? $item['date'] = date('d.m.Y H:i',strtotime($item['date'])) : null;
    }

    function afterItemSave($item) {
        if (!isset($item['_table']) OR  $item['_table'] == '') return;
        if (!isset($item['_id']) OR  $item['_id'] == '') return;
        $app = $this->app;
        $index = file_get_contents($app->vars('_env.dba').'/url.idx');
        $index = json_decode($index,true);
        $index ? null : $index = [];
        $index[$item['_table']] ? $idx = $index[$item['_table']] : $idx = [];
        foreach($idx as $u => $id) {
            if ($id == $item['_id']) unset($idx[$u]);
        }
        foreach($item['header'] as $l => $h) {
            if ($h > '') {
                $url = '/news/'.$item['_id'].'/'.$app->furlGenerate($h);
                break;
            }
        }
        if (!isset($url)) {
            $url = '/news/'.$item['_id'].'/'.date('Ymd_His');
        }
        $idx[$url] = $item['_id'];
        $index[$item['_table']] = $idx;
        $index = json_encode($index);
        file_put_contents($app->vars('_env.dba').'/url.idx',$index,  LOCK_EX);
    }
}
?>
<?php
class newsClass extends cmsFormsClass {

    function beforeItemShow(&$item) {
        isset($item['lang']) ? $lang = $item['lang'][$this->app->vars('_sess.lang')] : $lang = [];
        $item = (array)$lang + (array)$item;
        isset($item['header'])  AND isset($item['header'][$_SESSION['lang']]) ? $item['header'] = $item['header'][$_SESSION['lang']] : null;
        isset($item['date']) ? $item['date'] = date('d.m.Y H:i',strtotime($item['date'])) : null;
    }
}
?>
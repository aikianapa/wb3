<?php
// input with language variants
class modLanginp
{
    public function __construct($dom)
    {
        $this->init($dom);
    }
    public function init($dom)
    {
        if (!in_array($dom->tagName,['input','textarea']) OR isset($dom->done)) return;
        $dom->done = true;
        $ui = $dom->tagName == 'input' ? "/langinp_ui.php" : "/langtxt_ui.php";
        $out = $dom->app->fromFile(__DIR__ .$ui);
        $attrs = $dom->attributes;
        $inp = $dom->tagName == 'input' ? $out->find('input.mod-langinp') : $out->find('textarea.mod-langinp');
        $txt = $out->find('textarea.mod-langinp-data');
        foreach($attrs as $at) {
            $at->name == 'class' ? $inp->addClass($at->value) : $inp->attr($at->name, $at->value);
        }
        $name = $inp->attr('name');
        $txt->attr('name', $name);
        $out->copy($dom);
        $inp->removeAttr('name');
        $inp->removeAttr('wb');
        $inp->removeAttr('data-params');
        $l = wbListLocales($dom->app);
        if (count($l)) {
            $locales = [];
            foreach($l as $v) {
                $locales[$v] = $v;
            }
        } else {
            $locales = $dom->app->vars('_env.locale');
        }
        isset($out->item[$name]) ? $value = $out->item[$name] : $value = [];
        if ( isset($value) && is_string($value)) {
            $tmp = [];
            foreach($locales as $lang => $val) {
                $tmp[$lang] = $value;
            }
            $value = $tmp;
        }
        $dom->item[$name] = $value;
        $out->item['_locales'] = $locales;
        $out->fetch();
        if (isset($dom->item[$name])) {
            foreach ((array)$dom->item[$name] as $k => $v) {
                $tag = $out->find('[data-name="'.$k.'"]');
                if ($inp->tagName == 'textarea') {
                    $tag->inner($v);
                    $k == $dom->app->vars('_env.lang') ? $inp->inner($v) : null;
                } else {
                    $tag->attr('value', $v);
                    $k == $dom->app->vars('_env.lang') ? $inp->attr('value', $v) : null;
                }
            }
        }
        $out->find('textarea.mod-langinp-data')->text(json_encode($dom->item[$name]));
        $out->find('.dropdown-item input')->removeAttr('name');
        $out->find('.dropdown-item textarea')->removeAttr('name');
        $out->attr('data-mid', wbNewId());
        $dom->after($out->outer());
        $dom->remove();

    }
}
?>
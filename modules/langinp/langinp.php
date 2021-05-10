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
        if ($dom->tagName !== 'input' OR isset($dom->done)) return;
        $dom->done = true;
        $out = $dom->app->fromFile(__DIR__ ."/langinp_ui.php");
        $attrs = $dom->attributes;
        $inp = $out->find('input.mod-langinp');
        $txt = $out->find('textarea.mod-langinp');
        foreach($attrs as $at) {
            $at->name == 'class' ? $inp->addClass($at->value) : $inp->attr($at->name, $at->value);
        }
        $txt->attr('name', $inp->attr('name'));
        $out->copy($dom);
        $inp->removeAttr('name');
        $inp->removeAttr('wb');
        $out->fetch();
        $name = $txt->attr('name');
        if (isset($dom->item[$name])) {
            foreach ((array)$dom->item[$name] as $k => $v) {
                $out->find('input[data-name="'.$k.'"]')->attr('value', $v);
                $k == $dom->app->vars('_env.lang') ? $inp->attr('value', $v) : null;
            }
        }
        $out->find('.dropdown-item input')->removeAttr('name');
        $dom->after($out->outer());
        $dom->remove();

    }
}
?>
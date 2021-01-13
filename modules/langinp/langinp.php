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
        if ($dom->tagName !== 'input') return;
        $dom->prop('done', true);
        $out = $dom->app->fromFile(__DIR__ ."/langinp_ui.php");
        $attrs = $dom->attributes;
        $inp = $out->find('input.mod-langinp');
        $txt = $out->find('textarea.mod-langinp');
        foreach($attrs as $at) {
            $at->name == 'class' ? $inp->addClass($at->value) : $inp->attr($at->name, $at->value);
        }
        $txt->attr('name', $inp->attr('name'));
        $inp->removeAttr('name');
        $out->copy($dom);
        $out->fetch();
        $name = $txt->attr('name');
        if (isset($dom->item[$name])) {
            foreach ($dom->item[$name] as $k => $v) {
                $out->find('input[data-name="'.$k.'"]')->attr('value', $v);
                $k == $dom->app->vars('_env.lang') ? $inp->attr('value', $v) : null;
            }
        }
        $dom->after($out->inner());
        $dom->remove();

    }
}
?>
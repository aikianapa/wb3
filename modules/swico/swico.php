<?php
class modSwico
{
    public function __construct(&$dom)
    {
        return $this->swico($dom);
    }

    public function swico(&$dom)
    {
        $app = $dom->app;
        $switch = $app->fromFile(__DIR__ . '/swico_ui.php');
        $switch->copy($dom);
        $name = $dom->attr("name");
        $value = $dom->attr("value");
        $swid = $dom->attr("id");

        $dom->attr('data-size') == '' ? $swsize = 24 : $swsize = $dom->attr('data-size');
        $dom->attr('data-ico-on') == '' ? $swicon = 'power-turn-on-square.1' : $swicon = $dom->attr('data-ico-on');
        $dom->attr('data-ico-off') == '' ? $swicoff = 'power-turn-on-square' : $swicoff = $dom->attr('data-ico-off');
        $dom->attr('data-color-on') == '' ? $swcolon = '82C43C' : $swcolon = $dom->attr('data-color-on');
        $dom->attr('data-color-off') == '' ? $swcoloff = 'FC5A5A' : $swcoloff = $dom->attr('data-color-off');
        if ($dom->attr('data-color') > '') {
            $swcolon = $dom->attr('data-color');
            $swcoloff = $dom->attr('data-color');
        }
        $swicon = "/module/myicons/{$swsize}/{$swcolon}/{$swicon}.svg";
        $swicoff = "/module/myicons/{$swsize}/{$swcoloff}/{$swicoff}.svg";
        ($name == '' and $dom->params('name') > '') ? $name = $dom->params('name') : null;
        ($value == '' and $dom->params('value') > '') ? $value = $dom->params('value') : null;
        ($swid == '') ? $swid = 'swi_'.wbNewId() : null;

        $inp = $switch->find('.mod-swico-input');
        $dom->attrsCopy($inp);
        $inp->attr("name", $name);
        $inp->attr("value", $value);
        $switch->item['swico_id'] = $swid;
        $switch->item['swico_on'] = $swicon;
        $switch->item['swico_off'] = $swicoff;
        $switch->item['swico_size'] = $swsize;
        $switch->fetch();
        $dom->after($switch);
        $dom->remove();
    }
}
?>
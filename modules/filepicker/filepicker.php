<?php
class modFilepicker
{
    public function __construct($dom)
    {
        $this->init($dom);
    }
    public function init($dom)
    {
        $app = $dom->app;
        if ($dom->params("mode") == "" and $dom->tagName == 'button') {
            $dom->params("mode", 'button');
        }

        if ($dom->params("mode") == "single") {
            $out=$app->fromFile(__DIR__ ."/filepicker_ui_single.php");
        } else if ($dom->params("mode") == "button") {
            $out=$app->fromFile(__DIR__ ."/filepicker_ui_button.php");
        } else {
            $out=$app->fromFile(__DIR__ ."/filepicker_ui_multi.php");
        }
        $out->copy($dom);
        $out->addClass($dom->attr('class'));
        if ($dom->params("name")) {
            $out->find(".filepicker-data")
                ->attr("name", $dom->params->name);
        }
        if ($dom->attr("name") >"") {
            $out->find(".filepicker-data")
                ->attr("name", $dom->attr("name"))
                ->attr('data-params',json_encode($dom->params));
        }
        if ($dom->params("path")) {
            $out->item['upload_url'] = $dom->params->path;
        } else {
            $out->item['upload_url'] = '_auto_';
        }

        if ($dom->params("ext")) {
            $out->item['upload_ext'] = $dom->params->ext;
            $out->find("input[name=upload_url]")
                ->after("<input type='hidden' name='upload_ext'>");
        }
        if ($dom->params("original")) {
            $out->item['original'] = $dom->params->original;
            $out->find("input[name=upload_url]")
                ->after("<input type='hidden' name='original'>");
        }
        $out->fetch();
        if ($dom->tagName == 'input') {
            $dom->after($out);
            $dom->remove();
        } else if ($dom->tagName == 'button') {
            $inner = $out->find('.filepicker')->inner();
            $dom->append($inner);
            $dom->removeAttr("wb");
            $dom->addClass('filepicker fileinput');
        } else {
            $dom->html($out);
        }
    }
}

?>

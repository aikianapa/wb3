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
        if ($dom->params("mode") == "single") {
            $out=$app->fromFile(__DIR__ ."/filepicker_ui_single.php", true);
        } else {
            $out=$app->fromFile(__DIR__ ."/filepicker_ui_multi.php", true);
        }
        $out->copy($dom);
        if ($dom->params("name")) {
            $out
            ->find(".filepicker-data")
            ->attr("name", $dom->params->name);
        }
        if ($dom->attr("name") >"") {
            $out
            ->find(".filepicker-data")
            ->attr("name", $dom->attr("name"));
        }
        if ($dom->params("path")) {
            $out
            ->find("input[name=upload_url]")
            ->attr("value", $dom->params->path);
        }
        if ($dom->params("ext")) {
            $out
              ->find("input[name=upload_url]")
              ->after("<input type='hidden' name='upload_ext' value='{$dom->params->ext}'>");
        }
        $out->fetch();
        if ($dom->tagName == 'input' ) {
            $dom->after($out);
            $dom->remove();
        } else {
            $dom->html($out);
        }
    }
}

?>

<?php
use Adbar\Dot;


class modSummernote
{
    public function __construct($dom)
    {
        $dom->app->addEditor("summernote", __DIR__, "Summernote editor");
		$this->init($dom);
        $dom->removeAttr("wb");
    }
    public function init($dom)
    {
            $out = $dom->app->fromFile(__DIR__ ."/summernote-ui.php", true);
            $textarea = $out->find(".summernote");
            $ats = $dom->attributes();
            foreach ($ats as $at => $val) {
                if (!strpos(" ".$at, "data-wb")) {
                    $textarea->attr($at, $val);
                }
            }
			$name = $textarea->attr('name');
			if ($name > '') {
                echo $name;
				$item = $dom->app->dot($dom->item);
				$textarea->html($item->get($name));
            }
            $dom->after($out);
            $dom->remove();

    }
}
?>

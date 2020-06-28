<?php
class attrMask {
  public function __construct(&$dom) {
      $this->mask($dom);
  }

  public function mask(&$dom) {
      if ($dom->attr("data-mask") == "" && isset($dom->params->mask) && $dom->params->mask > "") {
          $dom->attr("data-mask",$dom->params->mask);
      }
      $dom->removeAttr("wb-mask")->addClass("wb-mask");
      $script = "
        <script type='wbapp'>
            wbapp.loadScripts(['/engine/lib/js/maskedinput/maskedinput.min.js'],'MaskedInput',function(){
                $(document).find('.wb-mask').each(function(){
                    let mask = $(this).attr('data-mask');
                    $(this).removeClass('wb-mask');
                    $(this).inputmask(mask);
                });
            });

        </script>
      ";
      $dom->after($script);
      return $dom;
  }
}
?>

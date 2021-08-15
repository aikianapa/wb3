<?php
class attrSelect2 {
  function __construct(&$dom) {
      $this->select2($dom);
  }


    function select2(&$dom) {
        $name = $dom->attr("name");
        if ($dom->params('options')>'') $dom->attr('wb-options', json_encode($dom->params('options')));
        $dom->addClass('select2');
        $script = "
        <script type='wbapp' remove>
            wbapp.loadStyles(['/engine/lib/js/select2/select2.min.css'],'select2-css');
            wbapp.loadScripts(['/engine/lib/js/select2/select2.min.js'],'select2-js',function(){
                $(document).find('select.select2').each(function(){
                    if (this.done) return;
                    this.done = true;
                    let that = this;
                    $(that).css('visibility','hidden');
                    setTimeout(function(){
                        let options;
                        $(that).css('visibility','visible');
                        $(that).attr('wb-options')>'' ? options = json_decode($(that).attr('wb-options')) : options = {};
                        if (options.placeholder == undefined) options.placeholder = $(that).attr('placeholder');
                        $(that).removeAttr('wb-options');
                        $(that).select2(options).on('select2:select', function (e) {
                            if (e.params.data.selected == true) {
                                $(e.params.data.element).attr('selected',true);
                            } else {
                                $(e.params.data.element).attr('selected',false);
                            }
                            $(this).trigger('change');
                        });
                        $(that).next('.select2').addClass('w-100');
                    },250);
                });
            });

        </script>
      ";
      $dom->after($script);
    }
}
?>
<?php
class attrSelect2 {
  function __construct(&$dom) {
      $this->select2($dom);
  }


    function select2(&$dom) {
        $name = $dom->attr("name");
        if ($dom->params('options')>'') $dom->attr('wb-options', json_encode($dom->params('options')));
        $dom->addClass('select2');
        $dom->attr('select2init',true);
        //$dom->fetch(); // - так не работает wb-tree после wb-select2
        $script = "
        <script type='wbapp' remove>
            wbapp.loadStyles(['/engine/lib/js/select2/select2.min.css'],'select2-css');
            wbapp.loadScripts(['/engine/lib/js/select2/select2.min.js'],'select2-js',function(){
                $(document).find('select[select2init]').each(function(){
                    $(this).removeAttr('select2init')
                    let id = $(this).attr('id')
                    substr(id, 0, 3) == 'fe_' ? $(this).removeAttr('id') : null;
                    let that = this;
                    $(that).css('visibility','hidden');
                    setTimeout(function(){
                        let options;
                        $(that).css('visibility','visible');
                        $(that).attr('wb-select2')>'' ? options = json_decode($(that).attr('wb-select2')) : options = {};
                        if (options.placeholder == undefined) options.placeholder = $(that).attr('placeholder');
                        //options.allowClear = true
                        $(that).removeAttr('wb-select2');
                        $(that).select2(options)
                        $(that).on('select2:select', function (e) {
                            if (e.params.data.selected == true) {
                                $(e.params.data.element).attr('selected',true);
                            } else {
                                $(e.params.data.element).attr('selected',false);
                            }
                            if ($(that).is('[multiple]')) {
                                //
                            } else {
                                $(that).val(e.params.data.element.value);
                            }
                            that.dispatchEvent(new Event('change'));
                        });
                        $(that).on('select2:open', function (e) {
                            if ($(that).parents('.modal[data-zidx]').length) {
                                $('.select2-container--open').css('z-index',$(that).parents('.modal[data-zidx]').attr('data-zidx'));
                            }
                        });
                        $(that).next('.select2').addClass('w-100');

                    },10);
                });
            });

        </script>

      ";
      $dom->after($script);
    }
}
?>

var mod_subform = function() {

    let watcher = function (sub) {
        if (sub.params.watch && $(sub).parents('form').find(sub.params.watch).length) {
            $(sub).parents('form').find(sub.params.watch).on('change', function () {
                let form = $(this).find('option:selected').attr('data-form');
                form = explode('/',form);

                let html = wbapp.getForm(form[0], form[1], sub.data);
                $(sub).find('.mod-subform-inner').html(html.result);
                console.log(html);
            })
        }
    }


    $('.mod-subform:not(done)').each(function(){
        $(this).addClass('done');
        this.data = json_decode(base64_decode($(this).attr('data')));
        this.params = json_decode(base64_decode($(this).attr('data-params')));
        $(this).removeAttr('data').removeAttr('data-params');
        if (this.params.watch) watcher(this);
        console.log(this.params);
    });
}
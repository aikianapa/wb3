var mod_subform = function() {
    
    let n2m = function(el) {
        $(el).find('[name]').each(function () {
            $(this).attr('wb-msf', $(this).attr('name')).removeAttr('name');
        })
    }

    let m2n = function(el){
        $(el).find('[wb-msf]').each(function () {
            $(this).attr('name', $(this).attr('wb-msf')).removeAttr('wb-msf');
        });
    }

    let getdata = function(el) {
        let form = $(el).clone();
        m2n($(form));
        $(form).wrap('<form></form>');
        form = json_encode($(form).parent().serializeJson());
        return form;
    }

    let listener = function (sub) {
        $(sub).children('.mod-subform-inner').delegate('[name],[wb-msf]','change', function () {
            let form = $(this).parents('.mod-subform-inner');
            let data = getdata(form);
            $(sub).children('textarea').val(data);
            sub.data = json_decode(data);
        });
    }

    let starter = function(sub) {
        wbapp.wbappScripts();
        wbapp.tplInit();
        wbapp.lazyload();
        wbapp.ajaxAuto();
        setTimeout(function () {
            n2m($(sub).children('.mod-subform-inner'));
        }, 50);
    }

    let watcher = function (sub) {
        if (sub.params.watch && $(sub).parents('form').find(sub.params.watch).length) {
            $(sub).parents('form').find(sub.params.watch).on('change', function () {
                let form = $(this).find('option:selected').attr('data-form');
                form = explode('/',form);
                let html = wbapp.getForm(form[0], form[1], {'data':sub.data});
                html = $(html.result);
                $(sub).children('.mod-subform-inner').html($(html));
                starter(sub);
                $(sub).children('textarea').val('');
                $(sub).children('.mod-subform-inner').find('[name],[wb-msf]').trigger('change');
            }).trigger('change');
        }
    }


    $('.mod-subform:not(done)').each(function(){
        var subform = this;
        $(this).addClass('done');
        this.data = json_decode(base64_decode($(this).attr('data')));
        this.params = json_decode(base64_decode($(this).attr('data-params')));
        if (this.params.name !== undefined && this.params.name > '') {
            eval('this.data = this.data.' + this.params.name+';');
        }

        $(this).removeAttr('data').removeAttr('data-params');
        starter(this);
        listener(this);
        if (this.params.watch) watcher(this);
    });
}
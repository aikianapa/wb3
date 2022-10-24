var modLangInp = function() {
    $('.mod-langinp[data-mid]').each(function() {
        let mod = this
        let id = $(mod).attr('id');
        if (id == undefined) {
            id = $(mod).attr('data-mid')
            $(mod).attr('id', id)
        }
        $(mod).removeAttr('data-mid')
        let ractive = new Ractive({
            el: '#' + id,
            template: $('#' + id).html(),
            data: {},
            on: {
                init() {
                    let data = $(mod).find('textarea.mod-langinp-data').html();
                    try {
                        this.set(json_decode(data))
                    } catch (error) {
                        let lang = $(mod).find('textarea.mod-langinp-data').next('[data-lang]').data('lang')
                        tmp = {}
                        tmp[lang] = data
                        this.set(tmp)
                    }
                },
                complete() {
                    $.each(this.get(), function(lng, val) {
                        $(mod).find(`[data-lang="${lng}"]:input`).val(val)
                    })
                },
                dropdown(ev) {
                    let form = $(mod).find('.dropdown-menu');
                    let width = $(ev.node).closest('.input-group').width();
                    $(form).width(width);
                },
                edit(ev) {
                    let lng = $(ev.node).data('lang')
                    ractive.set(lng, $(ev.node).val())
                    ractive.fire('complete')
                    $(mod).find('textarea.mod-langinp-data').html(json_encode(ractive.get())).trigger('change')
                }
            }
        })
    })
}
var modLangInp = function() {
    $('.mod-langinp-init').each(function() {
        let mod = this
        let id = $(mod).attr('id');
        if (id == undefined) {
            id = wbapp.newId()
            $(mod).attr('id', id)
        }
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
                    $(mod).removeClass('mod-langinp-init')
                },
                complete() {
                    $.each(this.get(), function(lng, val) {
                        $(mod).find(`[data-lang="${lng}"]:input`).val(val)
                    })
                    let mi = $(this.target).parents('wb-multiinput')
                    if (mi) {
                        $(mi).off('multiinput_after_add')
                        $(mi).on('multiinput_after_add', function() {
                            modLangInp()
                        })
                    }
                    setTimeout(function() {
                        let width = $(this.target).width();
                        $(mod).find('.dropdown-menu').width(width);
                    }, 100)
                },
                dropdown(ev) {
                    let width = $(ev.node).parent('.dropdown').width();
                    $(mod).find('.dropdown-menu').width(width);
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
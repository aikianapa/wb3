var modLangInp = function() {
    $('.mod-langinp-init').each(function() {
        let mod = this
        let id = $(mod).attr('id');
        if (id == undefined) {
            id = wbapp.newId()
            $(mod).attr('id', id)
        }
        let ticks;
        let timer = 300
        let time = 0
        let ractive = new Ractive({
            el: '#' + id,
            template: $('#' + id).html(),
            data: {
                langs: wbapp._settings.locales.split(','),
                lang: 'ru',
                curr: 0,
                data: {}
            },
            on: {
                init() {
                    let data = $(mod).find('textarea.mod-langinp-data').html();
                    try {
                        this.set('data',json_decode(data))
                    } catch (error) {
                        let lang = $(mod).find('textarea.mod-langinp-data').next('[data-lang]').data('lang')
                        tmp = {}
                        tmp[lang] = data
                        this.set(tmp)
                    }
                    $(mod).removeClass('mod-langinp-init')
                },
                complete() {
                    $(ractive.el).find('.switch').removeClass('text-transparent').addClass('text-white')
                    $.each(this.get('data'), function(lng, val) {
                        $(mod).find(`[data-lang="${lng}"]:input`).val(val)
                    })
                    let mi = $(this.target).parents('wb-multiinput')
                    if (mi) {
                        $(mi).off('multiinput_after_add')
                        $(mi).on('multiinput_after_add', function() {
                            modLangInp()
                        })
                    }
                },
                switch(ev) {
                    let langs = ractive.get('langs')
                    let curr = ractive.get('curr')
                    let data = ractive.get('data')
                    curr++
                    curr = curr >= langs.length ? 0 : curr
                    let lang = langs[curr]
                    ractive.set('curr', curr)
                    ractive.set('lang', lang)
                    let text = ''
                    try {
                        text = data[lang]    
                    } catch (error) {
                        text = ''
                    }
                    $(ractive.el).find(':input[data-lang]').data('lang',lang).val(text).focus()
                    
                },
                edit(ev) {
                    let lng = ractive.get('lang')
                    let data = ractive.get('data');
                    ractive.set('data.'+lng, $(ev.node).val())
                    //ractive.fire('complete')
                    $(mod).find('textarea.mod-langinp-data').html(json_encode(data)).trigger('change')
                },
                keyup(ev) {
                    time = 0
                    if (ticks !== undefined) clearInterval(ticks)
                    ticks = setInterval(function(){
                        time = time + timer / 10
                        if (time >= timer) {
                            ractive.fire('edit',ev)
                            clearInterval(ticks)
                        }
                    },timer /10)
                }
            }
        })
    })

}
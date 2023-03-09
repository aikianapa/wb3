"use strict"
import wbapp from "/engine/js/wbapp.mod.js"
const twlang = async function(id) {
    let $twlang = $(document).find('#'+id)
    if ($twlang.length == 0 ) return;
    if ($twlang[0].done) {return} else {$twlang[0].done = true}
    var lid = 0
    let loc = window.wbapp._settings.locales.split(',')
    var val
    loc = loc == [] ? ['ru'] : loc
    var $inp = $twlang.find(':input[data-lang]')
    var data
    var value = $inp.val()
    var name = $inp.attr('name')
    var wbname = $inp.attr('wb-name')
    if (wbapp.isJson(value)) {
        data = JSON.parse(value)
    } else {
        let tmp = value.replace('','')
        data = {}
        data[loc[lid]] = tmp
    }
    $inp.removeAttr('value')
    $inp.removeAttr('name').removeAttr('wb-name')
    var $store = $('<input type="hidden">')
    $store.data('value', data)
    name !== undefined ? $store.attr('name',name) : null
    wbname !== undefined ? $store.attr('wb-name', wbname) : null
    $inp.after($store)
    val = data[loc[lid]] !== undefined ? data[loc[lid]] : ''
    $inp.val(val).removeClass('text-transparent')
    $twlang.find('.switch').text(loc[lid])
    $twlang.undelegate('.switch', 'click');
    $twlang.delegate('.switch','click',function(){
        console.log(this, loc);
        lid++
        lid = lid > loc.length -1 ? 0 : lid
        $(this).text(loc[lid])
        let val = data[loc[lid]] == undefined ? '' : data[loc[lid]]
        $inp.val(val)
    })
    $twlang.undelegate(':input:visible', 'keyup')
    $twlang.delegate(':input:visible', 'keyup', function () {
            data[loc[lid]] = $(this).val()
            $store.data('value',data)
            $store.trigger('change')
    })
}

export default twlang
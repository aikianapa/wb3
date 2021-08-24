"use strict"


$(document).on('cart-mod-js',function(){
    
    var uid;
    if (!wbapp._session.user || !wbapp._session.user.id || wbapp._session.user.id < ' ') {
        uid = 'unknown';
    } else { 
        uid = wbapp._session.user.id;
    }
    
    var mod_cart = 'mod.cart';
    var mod_cart_bind = mod_cart+'.'+uid;
    var mod_cart_list = [];
    var mod_cart_sum = ("qty*price").split(/\s|\b/);
    var unk_cart = wbapp.storage(mod_cart + '.unknown');

    if (uid !== 'unknown' && unk_cart) {
        wbapp.storage(mod_cart_bind, unk_cart);
        wbapp.storage(mod_cart + '.unknown',null);
    }

    wbapp.tplInit();

    $("[id^='cartlist_']").each(function(i){
        let cid = $(this).attr('id');
        let tpl = wbapp.tpl('#'+cid).html;
        let sum = $("<wb>"+tpl+"</wb>").find("meta[name=sum]").attr("value");
        if (sum > "") mod_cart_sum = sum.split(/(\*|\/|\+|-)/);
        
        mod_cart_list[i] = new Ractive({
            'target' : '#'+cid,
            'template' : tpl,
            'oncomplete': function(){wbapp.lazyload();},
            'data' : () => {return wbapp.storage(mod_cart_bind)}
        });
    });

    var calcSum = function(cart) {
        let formula = 'cart.sum = ';
        $(mod_cart_sum).each(function(i,val){
            if (cart[val] !== undefined || strpos(val,']')) {
                if (strpos(val,']')) {
                    let tmp;
                    if (!strpos(val,"']")) {
                        tmp = 'cart.'+str_replace('[','[1*cart.',val);
                    } else {
                        tmp = 'cart.'+val;
                    }
                    try {
                        eval('tmp = '+tmp);
                        tmp !== undefined ? formula += tmp : formula += '1';
                    } catch (error) {
                        // null
                    }
                } else if (cart[val]) {
                    formula += 'cart.'+val+'*1';
                } else {
                    formula += '1';
                }
            } else {
                formula += val;
            }
        })
        eval(formula);
        return cart.sum;
    }


    var updateCart = function(cart = null) {
        var data = modCartGet();
        var total = {};
        var res = false;
        !res && cart !== null ? data.list[cart.id] = cart : null;

        $.each(data.list,function(id,item) {
            item.sum = calcSum(item);
            data.list[id] = item;
        });

        $.each(data.list,function(id,item){
            $.each(item,function(fld,val){
            if (is_numeric(val) && val+'') {
                undefined == total[fld] ? total[fld] = 0 : null;
                total[fld] += val*1;
            }
        })
        })

        data.total = total;
        wbapp.storage(mod_cart_bind,data);
        modCartTotals();
    }
 
    
    var modCartGet = function() {
        var cart = wbapp.storage(mod_cart_bind);
        var list = {};
        var total = {};
        if (cart == undefined) {
            cart = {'list':{},'total':{}};
        } else {
            if (cart.list !== undefined) list = cart.list;
            if (cart.total !== undefined) total = cart.total;
        }
        cart.list = list;
        cart.total = total;
        return cart;
    }

    var modCartTotals = function() {
        var cart = modCartGet();
        $(document).find(".mod-cart-count").text(cart.list.length);
        $(document).find('[class*="mod-cart-total-"]').text(0);
        Object.entries(cart.total).forEach(function(fld,i) {
            $(document).find('.mod-cart-total-'+fld[0]).text(fld[1]);
        });
    }
    
    wbapp.lazyload();
    modCartTotals();


$(document).delegate('.mod-cart-remove','tab click',function(e){
    let index = $(this).closest('.mod-cart-item').index();
    let data = wbapp.storage(mod_cart_bind);
    let id = array_keys(data.list)[index];
    wbapp.storage(mod_cart_bind+`.list.${id}`,null);
    updateCart();
    wbapp.trigger('mod-cart-remove',index);
    e.stopPropagation();
});
    
$(document).delegate('.mod-cart-item :input','change blur',function(){
    let name = $(this).attr('name');
    if (name == undefined || name == '') return;
    let index = $(this).closest('.mod-cart-item').index();
    let data = wbapp.storage(mod_cart_bind);
    let id = array_keys(data.list)[index];
    wbapp.storage(mod_cart_bind+`.list.${id}.${name}`,$(this).val()*1);
    updateCart();
});

$(document).delegate('.mod-cart-inc, .mod-cart-dec',wbapp.evClick,function(e){
    e.preventDefault();
    var $button = $(this);
    var $inp = $button.parent().find('input');
    var oldValue = $inp.val();
    if ($inp.attr('enum') !== undefined) {
        let vals = $inp.attr('enum').split(',');
        let indx = parseFloat(array_search($inp.val(),vals));
        var newVal = vals[indx];
        if ($button.hasClass('mod-cart-inc')) {
            if (indx < vals.length-1) newVal = vals[indx + 1];
        } else {
            if (indx > 0) newVal = vals[indx - 1];
        }
    } else {
        if ($button.hasClass('mod-cart-inc')) {
            var newVal = parseFloat(oldValue) + 1;
        } else {
            // Don't allow decrementing below zero
            if (oldValue > 1) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 1;
            }
        }
    }
    $inp.val(newVal);
    $inp.trigger('change');
});


$(document).delegate('.mod-cart-add','tab click', async function(e){
    e.preventDefault();
    
    let form = $(this).closest('form');
    let ajax = $(this).attr('data-ajax');
    let cart = [];
    let data = $(this).data();
    if (data.id !== undefined) {
        cart = array_merge(cart,data);
    } else if (!form.length && ajax == undefined) {
        wbapp.toast("Ошибка","Нет формы для добавления");
        return;
    } else if (form !== undefined) {
        cart = $(form).serializeJson();
        if (cart.id == undefined && ajax == undefined) {
            wbapp.toast("Ошибка","Требуется id записи");
            return;
        }
    }
    if (cart.qty == undefined) cart.qty = 1;
    if (ajax !== undefined) {
        let data = await wbapp.getSync(ajax);
        cart = array_merge(data,cart);
    }
    if ($(this).is('[mod-cart-data]')) {
        let data = wbapp.parseAttr($(this).attr('mod-cart-data'));
        let id = data.id;
        cart = array_merge(cart,{id:data});
    }
    updateCart(cart);
    wbapp.trigger('mod-cart-add',data);
});


$(document).on("bind", function (e, res) {
    if (res.key == mod_cart_bind || res.key.substr(0,14) == mod_cart_bind + '.') {
        let data = wbapp.storage(mod_cart_bind);
        $(mod_cart_list).each(function(i,cart){
            cart.set(data);
        });
        setTimeout(function(){wbapp.lazyload()},300);
    }
});


});
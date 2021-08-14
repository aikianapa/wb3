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
        if (sum > "") mod_cart_sum = sum.split(/\s|\b/);
        
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
            if (cart[val] !== undefined) {
                if ((cart[val])) val += '*1';
                formula += 'cart.'+val;
            } else {
                formula += val;
            }
        })
        eval(formula);
        return cart.sum;
    }


    var updateCart = function(cart = null) {
        var data = modCartGet();
        var list = data.list;
        var total = {};
        var res = false;
        list.forEach(function(item,index) {
            if (index == undefined) {
                list.splice(index, 1);
            } else if (cart !== null && item.id === cart.id) {
                    cart.sum = calcSum(cart);
                    list[index] = cart;
                    res = true;
            }
            Object.entries(list[index]).forEach(function(a,b) {
                if (is_numeric(a[1]) && a[1]+'') {
                    if (undefined == total[a[0]]) {
                        total[a[0]] = 0;
                    }
                    total[a[0]] += a[1]*1;
                }
            })
        });
        
        if (!res && cart !== null) {
            if (cart.price !== undefined && cart.qty !== undefined) {
                cart.sum = cart.sum = calcSum(cart);
            }
            Object.entries(cart).forEach(function(a,b) {
                if (is_numeric(a[1]) && a[1]+'') {
                    if (undefined == total[a[0]]) {
                        total[a[0]] = 0;
                    }
                    total[a[0]] += a[1]*1;
                }
            })
            list.push(cart);
        }
        data.list = list;
        data.total = total;
        wbapp.storage(mod_cart_bind,data);
        modCartTotals();
    }
 
    
    var modCartGet = function() {
        var cart = wbapp.storage(mod_cart_bind);
        var list = [];
        var total = {};
        if (cart == undefined) {
            cart = {'list':[],'total':{}};
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
        $.each(cart.total,function(fld,value) {
            $(document).find('.mod-cart-total-'+fld).text(value);
        });
    }
    
    
    wbapp.lazyload();
    modCartTotals();


$(document).delegate('.mod-cart-remove','tab click',function(){
    let index = $(this).closest('.mod-cart-item').index();
    let list = wbapp.storage(mod_cart_bind+'.list');
    let removed = list.splice(index, 1);
    wbapp.storage(mod_cart_bind+'.list',list);
    updateCart();
    modCartTotals();
    wbapp.trigger('mod-cart-remove',removed);
});
    
$(document).delegate('.mod-cart-item :input','change blur',function(){
    let index = $(this).closest('.mod-cart-item').index();
    let list = wbapp.storage(mod_cart_bind+'.list');
    updateCart(list[index]);
    modCartTotals();
    wbapp.trigger('mod-cart-change',list[index]);
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
        cart = array_merge(cart,data);
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
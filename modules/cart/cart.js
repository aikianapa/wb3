"use strict"
var mod_cart_bind = 'mod.cart';
var mod_cart_list = [];

$(document).on('cart-mod-js',function(){
    $("[id^='cartlist_']").each(function(i){
        let cid = $(this).attr('id');
        let tpl = wbapp.tpl('#'+cid).html;

        mod_cart_list[i] = new Ractive({
            'target' : '#'+cid,
            'template' : tpl,
            'oncomplete': function(){wbapp.lazyload();},
            'data' : () => {return wbapp.storage(mod_cart_bind)}
        });
    });
    wbapp.lazyload();
});

$(document).delegate('.mod-cart-remove','tab click',function(){
    let index = $(this).closest('.mod-cart-item').index();
    let list = wbapp.storage(mod_cart_bind+'.list');
    list.splice(index, 1);
    wbapp.storage(mod_cart_bind+'.list',list);
});

$(document).delegate('.mod-cart-add','tab click', async function(e){
    e.preventDefault();
    
    var updateCart = function(cart) {
        var data = wbapp.storage(mod_cart_bind);
        var list = [];
        var total = [];
        let res = false;
        if (data == undefined) {
            data = {'list':[],'total':[]};
        } else {
            if (data.list !== undefined) list = data.list;
            if (data.total !== undefined) total = data.total;
        }
        
        list.forEach(function(item,index) {
            if (index == undefined) {
                list.splice(index, 1);
            } else if (item.id == cart.id) {
                    if (cart.price !== undefined && cart.qty !== undefined) {
                        cart.sum = (cart.price *1) * (cart.qty *1);
                    }
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
        
        if (!res) {
            if (cart.price !== undefined && cart.qty !== undefined) {
                cart.sum = (cart.price *1) * (cart.qty *1);
            }
            list.push(cart);
        }
        data = {'list':list,'total':total};
                    console.log(data);
        wbapp.storage(mod_cart_bind,data);        
    }
    
    let form = $(this).closest('form');
    let ajax = $(this).attr('data-ajax');
    let cart = [];
        console.log(ajax);
    if (form == undefined && ajax == undefined) {
        wbapp.toast("Ошибка","Нет формы для добавления");
        return;
    } else if (form !== undefined) {
        cart = $(form).serializeJson();
        if (cart.id == undefined && ajax == undefined) {
            wbapp.toast("Ошибка","Требуется id записи");
            return;
        }
        
    }
    if (ajax !== undefined) {
        var data = await wbapp.getSync(ajax);
        cart = array_merge(data,cart);
    }
    updateCart(cart);
});


$(document).on("bind", function (e, res) {
    if (res.key == mod_cart_bind || res.key.substr(0,9) == mod_cart_bind + '.') {
        let data = wbapp.storage(mod_cart_bind);
        $(mod_cart_list).each(function(i,cart){
            cart.set(data);
        });
        setTimeout(function(){wbapp.lazyload()},300);
    }
});



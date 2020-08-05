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
            'data' : wbapp.storage(mod_cart_bind)
        });
    });
});

$(document).delegate('.mod-cart-remove','tab click',function(){
    let index = $(this).closest('.mod-cart-item').index();
    let list = wbapp.storage(mod_cart_bind+'.list');
    list.splice(index, 1);
    wbapp.storage(mod_cart_bind+'.list',list);
});

$(document).delegate('.mod-cart-add','tab click', async function(e){
    e.preventDefault();
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
    
    console.log(cart);
    
    var updateList = function(cart) {
        let list = wbapp.storage(mod_cart_bind+'.list');
        let res = false;
        if (list == undefined) list = [];
        list.forEach(function(item,index) {
                    console.log(item.id,cart.id);
            if (index == undefined) {
                //list.splice(index, 1);
            } else if (item.id == cart.id) {
                    list[index] = cart;
                    res = true;
            }
        });
        if (!res) {
            list.push(cart);
        }
        console.log(list);
        wbapp.storage(mod_cart_bind+'.list',list);        
    }
    
    

});


$(document).on("bind", function (e, res) {
    if (res.key == mod_cart_bind || res.key.substr(0,9) == mod_cart_bind + '.') {
        let data = wbapp.storage(mod_cart_bind);
        $(mod_cart_list).each(function(i,cart){
            cart.set(data);
        });
    }
});



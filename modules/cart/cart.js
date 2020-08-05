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

$(document).delegate('.mod-cart-add','tab click',function(e){
    e.preventDefault();
    let form = $(this).closest('form');
    if (form == undefined) {
        wbapp.toast("Ошибка","Нет формы для добавления");
        return;
    }
    let cart = $(form).serializeJson();
    if (cart.id == undefined) {
        wbapp.toast("Ошибка","Требуется id записи");
        return;
    }
    let list = wbapp.storage(mod_cart_bind+'.list');
    let res = false;
    list.forEach(function(key,index,item) {
        console.log(item);
                console.log(item.id,cart.id);
        if (item.id == undefined) {
            //list.splice(index, 1);
        } else if (item.id == cart.id) {
                list[key] = cart;
                res = true;
        }
    });
    if (!res) {
        list.push(cart);
    }
    console.log(list);
    wbapp.storage(mod_cart_bind+'.list',list);
});


$(document).on("bind", function (e, res) {
    if (res.key == mod_cart_bind || res.key.substr(0,9) == mod_cart_bind + '.') {
        let data = wbapp.storage(mod_cart_bind);
        $(mod_cart_list).each(function(i,cart){
            cart.set(data);
        });
    }
});



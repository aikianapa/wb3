"use strict"
wbapp.session();

$(document).delegate(".nav-link","click",function(){
    $(this).parents("ul,nav").find(".nav-link").removeClass("active");
    $(this).addClass("active");
})

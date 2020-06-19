"use strict"
wbapp.session();

$(document).delegate(".nav-link","tap click",function(){
    $(this).parents("ul,nav").find(".nav-link").removeClass("active");
    $(this).addClass("active");
})

$(document).delegate("aside .nav-link","tap click",function(){
    $(".content-header .content-search input").prop("disabled",true);
});

$(document).on("data-ajax",function(e,params){
  let spinner = '<div class="text-center pt-5"><div class="spinner-border text-primary" role="status"></div></div>';
  if (params.html) $(params.html).html(spinner);
  if (params._tid) $(params._tid).html(spinner);
  if (params.target) $(params.target).html(spinner);
})

$(document).on("ajax-done",function(e,params){
    $(document).find(".content-body [type=search][data-ajax].search-header").each(function(){
        $(".content-header .content-search [type=search]").attr("data-ajax",$(this).attr("data-ajax")).prop("disabled",false);
        $(this).remove();
    });
});

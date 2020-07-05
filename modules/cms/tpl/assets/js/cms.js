"use strict"

$(document).delegate(".nav-link","tap click",function(){
    $(this).parents("ul,nav").find(".nav-link").removeClass("active");
    $(this).addClass("active");
})

$(document).delegate("aside .nav-link","tap click",function(){
    $(".content-header .content-search input").prop("disabled",true);
    $("body").removeClass("show-aside");
    $("body").addClass("chat-content-show");
});

$(document).delegate(".chat-sidebar .nav-link","tap click",function(){
    $("body").addClass("chat-content-show");
});

$(document).on("data-ajax",function(e,params){
  let spinner = '<div class="text-center pt-5"><div class="spinner-border text-primary" role="status"></div></div>';
  if (params.html) $(params.html).html(spinner);
  if (params._tid) $(params._tid).html(spinner);
  if (params.target) $(params.target).html(spinner);
})

$(document).on("wb-save-start",function(e,params){
  if ($(e.target).is("button.cms") && !$(e.target).find(".spinner-border").length) {
      let spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ';
      $(e.target).find("i").addClass("d-none");
      $(e.target).prepend(spinner).prop("disabled",true);
  }
})

$(document).on("wb-save-done",function(e,params){
  if ($(e.target).is("button.cms") && $(e.target).find(".spinner-border").length) {
      $(e.target).find(".spinner-border").remove();
      $(e.target).find("i").removeClass("d-none");
      $(e.target).prop("disabled",false);
  }
  if (params.params.form !== undefined && params.params.form == "#userProfile") {
      wbapp.storage('cms.profile.user',params.data);
      wbapp._session.user = params.data;
      $("#userProfileMenu").data("ractive").set(params.data);
      wbapp.lazyload();
  }
})

$(document).on("ajax-done",function(e,params){
    $(document).find(".content-body [type=search][data-ajax].search-header").each(function(){
        $(".content-header .content-search [type=search]").attr("data-ajax",$(this).attr("data-ajax")).prop("disabled",false);
        $(this).remove();
    });
    if ($(document).find(".chat-sidebar").length == 0) $("body").removeClass("chat-content-show");
});

var sess = wbapp.session();
sess.then(function(){
    wbapp.storage('cms.profile.user',wbapp._session.user);
    var profileMenu = Ractive({
      target: "#userProfileMenu",
      template: wbapp.template["#userProfileMenu"].html,
      data: () => {return wbapp.storage('cms.profile.user');}
    });
    $("#userProfileMenu").data("ractive",profileMenu);
    wbapp.lazyload();
})

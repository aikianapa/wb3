"use strict"
var yonger = {};

$(document).delegate(".nav-link:not([data-toggle=tab])", "tap click",function() {
    $(this).parents("ul,nav").find(".nav-link").removeClass("active");
    $(this).addClass("active");
})

$(document).delegate("aside .nav-link", "tap click",function() {
    $(".content-header .content-search input").prop("disabled",true);
    $("body").removeClass("show-aside");
    $("body").addClass("chat-content-show");
});

$(document).delegate(".chat-sidebar .nav-link", "tap click",function() {
    $("body").addClass("chat-content-show");
});

$(document).on("wb-save-start",function(e,params) {
  if ($(e.target).is("button.cms") && !$(e.target).find(".spinner-border").length) {
      var spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ';
      $(e.target).find("i").addClass("d-none");
      $(e.target).prepend(spinner).prop("disabled",true);
  }
})

$(document).on('wb-verify-false',function(e,el,err){
    if (err !== undefined) {
        wbapp.toast(wbapp._settings.sysmsg.error,err,{target:'.content-toasts','bgcolor':'warning','txcolor':'white'});
    }
});

$(document).on("wb-save-error", function (e, params) {
    if ($(e.target).is("button.cms") && $(e.target).find(".spinner-border").length) {
        $(e.target).find(".spinner-border").remove();
        $(e.target).find("i").removeClass("d-none");
        $(e.target).prop("disabled", false);
    }
});

$(document).delegate(".modal", "hidden.bs.modal", function (event) {
    if ($('body').hasClass('app-chat') && $(document).find('.modal:visible').length) {
        $('body').addClass('modal-open');
    }
});

$(document).on("wb-save-done", function (e, params) {
    if ($(e.target).is("button.cms") && $(e.target).find(".spinner-border").length) {
        $(e.target).find(".spinner-border").remove();
        $(e.target).find("i").removeClass("d-none");
        $(e.target).prop("disabled", false);
    }
    if (params.params.form !== undefined && params.params.form == "#userProfile") {
        wbapp.storage('cms.profile.user', params.data);
        wbapp._session.user = params.data;
        $("#userProfileMenu").data("ractive").set(params.data);
        wbapp.lazyload();
    }
    if (params.params.silent == undefined || (params.params.silent !== "true" && params.params.silent !== true)) {
        wbapp.toast("Сохранение","Данные успешно сохранены",{target:'.content-toasts','bgcolor':'success','txcolor':'white'});
    }
})


$(document).on("data-ajax",function(e,params) {

});

$(document).on("wb-ajax-done",function(e,params) {
    $(document).find(".content-body [type=search][data-ajax].search-header").each(function() {
        $(".content-header .content-search [type=search]").attr("data-ajax",$(this).attr("data-ajax")).prop("disabled",false);
        $(this).remove();
    });
    if ($(document).find(".chat-sidebar").length == 0) $("body").removeClass("chat-content-show");
    yonger.plugins();
});

yonger.workspace = function() {
    if ($("#userProfileMenu").length) {
        wbapp.storage('cms.profile.user',wbapp._session.user);
        var profileMenu = Ractive({
        target: "#userProfileMenu",
        template: wbapp.template["#userProfileMenu"].html,
        data: () => {return wbapp.storage('cms.profile.user');}
        });
        $("#userProfileMenu").data("ractive",profileMenu);
    }
    wbapp.lazyload();
    yonger.plugins();
};

yonger.plugins = function(){
    setTimeout(function(){
        $(document).find('.modal-body').addClass('scroll-y');
        
        $('.scroll-x').each(function(){
            if (this.done) return;
            this.done = true;
            new PerfectScrollbar(this, {suppressScrollY: true});
        });
        $('.scroll-y').each(function(){
            if (this.done) return;
            this.done = true;
            new PerfectScrollbar(this, {suppressScrollX: true});
        });
        $('.scroll').each(function(){
            if (this.done) return;
            this.done = true;
            new PerfectScrollbar(this, {suppressScrollY: false,suppressScrollX: false});
        });
    },100)
}

yonger.siteCreator = function(){
    if ($('#yongerSiteCreator form').verify()) {
        let form = $('#yongerSiteCreator form').serializeJson();
        let domain = document.location.host.split('.');
        let scheme = document.location.protocol; 
        domain = array_slice(domain,-2).join('.');
        let data = wbapp.postSync(scheme+'//'+domain+'/module/yonger/createSite',form);
        //let data = wbapp.postSync('/module/yonger/createSite',form);
        if (data.error == true) {
            wbapp.toast('Ошибка',data.msg);
        } else {
            $('#yongerSiteCreator').modal('hide').on('hidden.bs.modal',function(){
                $('.content-header nav a[href="#sites"]').trigger('click');
            });
        }
    }
}

yonger.siteRemove = function(sid) {
    let confirm = window.confirm("Удалить сайт?");
    if (confirm) {
        let res = wbapp.postSync('/module/yonger/removeSite/'+sid);
        if (res.error == false) {
            $('#yongerListSites tr[data-id="'+sid+'"]').remove();
            if (res.self == true) {
                document.location.href = $('aside a.aside-logo').attr('href');
            }
        }
    }
}

yonger.siteWorkspace = function(sid) {
    let res = wbapp.postSync('/module/yonger/goto/'+sid);
    if (res.goto == undefined) return;
    let $form = $('<form class="d-none" method="post" action="'+res.goto+'" />');
    $form.append('<input name="token" value="'+res.token+'">');
    $form.append('<input name="login" value="'+wbapp._session.user.login+'">');
    $form.appendTo('body');
    $form.submit();
}

yonger.workspace();
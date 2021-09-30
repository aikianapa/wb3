$(document).one("pagination-js", function() {

  wbapp.loadStyles(['/engine/tags/pagination/pagination.css']);

  $(document).find(".pagination .page-more[data-trigger]").each(function(){
      var more = this;
      var selector = $(this).attr("data-trigger");

      $(document).find(selector).on(wbapp.evClick,function(){
          $(more).find(".page-link").trigger("click");
      });

  });

  $(document).undelegate(".pagination:not(.wb-wait) .page-link", wbapp.evClick);
  $(document).delegate(".pagination:not(.wb-wait) .page-link", wbapp.evClick, function (e) {
    if ($(this).is('[disabled]') || $(this).parents('[disabled]').length) return false;
    e.preventDefault();
    let paginator = $(this).closest(".pagination");
    let tid = $(paginator).data("tpl");
    let params = wbapp.template[tid].params;
    let page = $(this).attr("data-page");
    let that = this;
    let inner = $(that).html();
    let offset = 0;

    $(paginator).addClass('wb-wait');


    $(paginator).find('.page-link, .page-item').removeClass('active');
    $(that).html(wbapp.ui.spinner_sm);

    if (params._route) {
      params.url = params._route.url;
      params._params.page = page;
      if (params._params.offset !== undefined) offset = params._params.offset*1;
    } else {
      params.page = page;
    }
    params._tid = tid;
    wbapp.ajax(params, function () {
      var top = $(tid).offset().top;
      
      $([document.documentElement, document.body]).animate({
        scrollTop: top + offset
      }, 500);
      $(paginator).removeClass('wb-wait');
    });
  });
});

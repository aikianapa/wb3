$(document).one("pagination-js", function() {

    wbapp.loadStyles(['/engine/tags/pagination/pagination.css']);

    $(document).find(".pagination .page-more").each(function(ev) {
        var more = this;
        var selector = $(this).attr("data-trigger");
        let paginator = $(this).closest(".pagination");
        let tid = $(paginator).data("tpl");

        if (selector !== undefined) {
            $(document).find(selector).on(wbapp.evClick, function() {
                $(more).find(".page-link").trigger("click");
            });
        }
    });

    $(document).on('scroll', function(ev) {
        $(document).find(".pagination .page-more").each(function() {
            is_visible(this) ? $(this).find(".page-link").trigger("click") : null;
        })
    });

    $(document).undelegate(".pagination:not(.wb-wait) .page-link", wbapp.evClick);
    $(document).delegate(".pagination:not(.wb-wait) .page-link", wbapp.evClick, function(e) {
        if ($(this).is('[disabled]') || $(this).parents('[disabled]').length) return false;
        e.preventDefault();
        let paginator = $(this).closest(".pagination");
        let tid = $(paginator).data("tpl");
        let params = wbapp.template[tid].params;
        let page = $(this).attr("data-page");
        let that = this;
        let offset = 0;

        $(paginator).addClass('wb-wait');

        $(paginator).find('.page-link, .page-item').removeClass('active');
        $(that).html(wbapp.ui.spinner_sm);

        if (params._route) {
            params.url = params._route.url;
            params._params.page = page;
            if (params._params.offset !== undefined) offset = params._params.offset * 1;
        } else {
            params.page = page;
        }
        params._tid = tid;
        wbapp.ajax(params, function() {
            if (params._params !== undefined && params._params.more !== undefined) {

            } else {
                var top = $(tid).offset().top;
                $([document.documentElement, document.body]).animate({
                    scrollTop: top + offset
                }, 200);
            }
            $(paginator).removeClass('wb-wait');
        });
    });
});
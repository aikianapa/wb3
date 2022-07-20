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
        $(document).find(".pagination .page-more[data-trigger=auto]").each(function() {
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
        if ($(tid)[0].filter !== undefined) {
            params.filter = $(tid)[0].filter;
        }
        wbapp.ajax(params, function(ev, data) {
            /*
                        if (params._params !== undefined && params._params.more !== undefined) {

                        } else {
                            var top = $(tid).offset().top;
                            $([document.documentElement, document.body]).animate({
                                scrollTop: top + offset
                            }, 100);
                        }
            */

            wbapp.setPag(params.target, data);
            $(paginator).removeClass('wb-wait');
        });
    });
});
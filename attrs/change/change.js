$.fn.wbAttrChange = function (selector, cache) {
    $.post("/ajax/change_fld/", { 'cache': cache, 'value': $(this).val() }, function (data) {
        if (data.content) {
            $(selector).html($(data.content).html());
            if ($(selector).is('[onchange *= "wbAttrChange"]')) {
                $(selector).trigger('change');
            }
        }
    });
}

function wb_change() {
        $(document).find('[onchange*="wbAttrChange"]').each(function () {
            $(this).trigger('change');
        });
}

$(document).on("wb-change-js", function () {
    wb_change();
});

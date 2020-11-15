$.fn.wbAttrChange = function (selector, cache) {
    let data = {};
    if ($(this).is('select')) {
        let opt = $(this).find('option[value="'+$(this).val()+'"]');
        data = $(opt).data();
    }
    data.value = $(this).val();
    $.post("/ajax/change_fld/", { 'cache': cache, 'data': data }, function (data) {
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

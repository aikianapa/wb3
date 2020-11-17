$.fn.wbAttrChange = function (sl, fn) {
    let that = this;
    $.each(sl,function(i,selector){
        var cache = fn[i];
        var data = {};
        if ($(that).is('select')) {
            let opt = $(that).find('option[value="' + $(that).val() + '"]');
            data = $(opt).data();
        }
        data.value = $(that).val();
        $.post("/ajax/change_fld/", { 'cache': cache, 'data': data }, function (data) {
            if (data.content) {
                $(selector).html('');
                if ($(selector).is('select[placeholder]') && !$(data.content).find("option[value='']").length) {
                    $(selector).prepend('<option value="">' + $(selector).attr('placeholder') + '</option>');
                }
                $(selector).append($(data.content).html());
                if ($(selector).is('[onchange *= "wbAttrChange"]')) {
                    $(selector).trigger('change');
                }
            }
        });

    });
}

function wb_change() {
        $(document).find('[onchange*="wbAttrChange"]').each(function () {
            if (this.wb_change_init == undefined) {
                this.wb_change_init = true;
                $(this).trigger('change');
            }
        });
}

$(document).on("wb-change-js", function () {
    wb_change();
});

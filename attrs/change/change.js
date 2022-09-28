$.fn.wbAttrChange = function(sl, fn) {
    let that = this;
    let selector;
    $.each(sl, function(i, selector) {
        $(selector).trigger('wb-change-start');
        console.log('Trigger: wb-change-start ' + selector);
        var cache = fn[i];
        var data = {};
        let name = $(that).attr('name');
        name ? null : name = $(that).attr('data-name');
        if (name !== undefined) {
            if ($(that).is('select')) {
                let opt = $(that).find('option[value="' + $(that).val() + '"]');
                $(that).find('[selected]').removeAttr('selected');
                $(opt).prop('selected', true).attr('selected', true);
                data[name] = $(that).val();
            } else if (name > '') {
                data[name] = $(that).val();
            }
        }
        data.value = $(that).val();

        wbapp.post("/ajax/change_fld/", { 'cache': cache, 'data': data }, function(data) {
            if (data.content) {
                $(selector).html($(data.content).html());
                if ($(selector).is('[onchange *= "wbAttrChange"]')) {
                    $(selector).trigger('change');
                }
            }
            console.log('Trigger: wb-change-done ' + selector);
            $(selector).trigger('wb-change-done');
        });

    });
}

function wb_change() {
    $(document).find('[onchange*="wbAttrChange"]').each(function() {
        if (this.wb_change_init == undefined) {
            this.wb_change_init = true;
            $(this).trigger('change');
        }
    });
}

$(document).on("wb-change-js", function() {
    wb_change();
});
$(document).on("smartid-js", function() {

    let furl = function(str, slash = false) {
        if (slash) {
            str = str.replace(/[^\/а-яА-Яa-zA-Z0-9_-]{1,}/gm, "-");
        } else {
            str = str.replace(/[^а-яА-Яa-zA-Z0-9_-]{1,}/gm, "-");
        }
        str = str.replace(/[--]{1,}/gm, "-");
        str = wbapp.transilt(str);
        return str;
    }



    $(".wb-smartid").each(function() {
        if ($(this).data('smartid-js') !== undefined) return;
        $(this).data('smartid-js', true);
        let smartid = this;
        let params = {};
        let slash = false;
        if ($(smartid).attr('data-params') !== undefined) {
            params = json_decode($(smartid).attr('data-params'));
            $(smartid).removeAttr('data-params');
        }
        if (params.slash == 'true') slash = true;
        $(smartid).on("keypress", function(event) {
            let code = event.charCode;
            let self = this;
            if (code > 0) {
                let char = String.fromCharCode(event.which);
                let offset = furl(char, slash).length - 1;

                setTimeout(function() {
                    let val = $(self).val();
                    let pos = $(self).get(0).selectionStart + offset;
                    $(self).val(furl(val, slash));
                    $(self).get(0).setSelectionRange(pos, pos);
                }, 0);
            }

        });
        if ($(smartid).attr("data-furl") !== undefined) {
            let furl = $(smartid).attr("data-furl");
            if ($(document).find(furl).length) {
                $(this).parents("form").find(furl).on("change", function() {
                    let str = furl($(this).val(), slash);
                    $(smartid).val(str);
                });
            }
            $(smartid).removeAttr("data-furl");
        }
    });
});
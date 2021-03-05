$(document).on("smartid-js", function() {
    $(".wb-smartid").each(function(){
        if ($(this).data('smartid-js') !== undefined) return;
        $(this).data('smartid-js',true);
        let smartid = this;
        $(smartid).on("keypress",function(event){
            let code = event.charCode;
            let self = this;
            if (code > 0) {
                let char = String.fromCharCode(event.which);
                let offset = wbapp.furl(char).length - 1;

                setTimeout(function(){
                    let val = $(self).val();
                    let pos = $(self).get(0).selectionStart + offset;
                    $(self).val(wbapp.furl(val));
                    $(self).get(0).setSelectionRange(pos, pos);
                },0);
            }
            
        });
        if ($(smartid).attr("data-furl") !== undefined) {
            let furl = $(smartid).attr("data-furl");
            if ($(document).find(furl).length) {
                $(this).parents("form").find(furl).on("change",function(){
                    let str = wbapp.furl($(this).val());
                    $(smartid).val(str);
                });
            }
            $(smartid).removeAttr("data-furl");
        }
    });
});

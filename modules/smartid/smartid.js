$(document).on("smartid-js", function() {
    $(".wb-smartid").each(function(){
        let smartid = this;
        $(smartid).on("keypress",function(event){
            let char = String.fromCharCode(event.which);
            var val = $(this).val();
            $(this).val(wbapp.furl(val + char));
            event.preventDefault();
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

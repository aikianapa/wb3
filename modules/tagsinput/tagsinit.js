$(document).off("tagsinput-js");
$(document).on("tagsinput-js", function() {
    var wb_tagsinput = function() {
        $(".wb-tagsinput").each(function() {
            if (this.done == undefined) {
                let that = this;
                this.done = true;
                let placeholder = $(this).attr('placeholder');
                $(this).tagsInput({
                    minChars: 0,
                    maxChars: null,
                    limit: null,
                    placeholder: placeholder,
                    validationPattern: null,
                    unique: true,
                    onChange: function(ev, data, d1) {
                        $(that).attr("value", $(this).val());
                        $(that).trigger('change');
                    }
                });
                $(this).removeClass('wb-tagsinput');
                $(this).next(".tagsinput").addClass($(this).attr('class'));
            }
        });
    }
    setTimeout(function() {
        wb_tagsinput();
    }, 10);

});

$(document).off("tagsinit-js");
$(document).on("tagsinit-js", function() {
    wbapp.loadScripts(["/engine/modules/tagsinput/tagsinput.js"], "tagsinput-js");
    wbapp.loadStyles(["/engine/modules/tagsinput/tagsinput.css"]);
    $("[data-remove=tagsinit-js]").remove();
});
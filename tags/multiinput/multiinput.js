function wb_multiinput_init() {
    if ($(document).data("wb-multiinput-row") == undefined) {
        wbapp.loadStyles(["/engine/tags/multiinput/multiinput.less",
        "/engine/lib/fonts/materialicons/materialicons.css"]);


    $.fn.events = function () {
        var $multi = $(this);
        var name = $multi.attr("name");
        $multi.data("name",name);

        $multi.undelegate(".wb-multiinput-del", "click");
        $multi.delegate(".wb-multiinput-del", "click", function (e) {
            var line = $(this).parent(".wb-multiinput-row");
            console.log("Trigger: before_remove");
            $multi.trigger("before_remove", line);
            $(line).remove();
            if (!$multi.find(".wb-multiinput-row").length) {
                $multi.prepend($multi.data("wb-tpl"));
            }
            $multi.store();
            return false;
        });

        $multi.undelegate(".wb-multiinput-add", "click");
        $multi.delegate(".wb-multiinput-add", "click", function (e) {
            var line = $(this).parent(".wb-multiinput-row");
            $(line).after($multi.data("wb-tpl"));
            $multi.store();
            //wb_plugins();
            return false;
        });

        $multi.delegate(".wb-multiinput-row :input","change",function(){
            if ($(this).is("select")) {
                $(this).find("option:not(:selected)").prop("selected",false).removeAttr("selected");
                $(this).find("option:selected").prop("selected",true).attr("selected",true);
            }
            $multi.store();
        });

        $multi.delegate(".wb-multiinput-row","change",function(){
            $multi.store();
        });
    }

    $.fn.value = function(data = undefined) {
        if (data == undefined) {
            var data = $(this).children("textarea").html();
            if (data !== undefined) data = json_decode(data,true);
            return data;
        } else {
            $(this).children("textarea").html(json_encode(data));
        }
        return $(this);
    }

    $.fn.store = function() {
        var data = [];
        var name = $(this).attr("name");
        $(this).children(".wb-multiinput-row").each(function (i) {
            var multi = $(this).clone();
            var item = {};
            var mono = false;
            $(multi).find(".wb-multiinput").each(function(){
                var name = $(this).attr("name");
                var txtd = $(this).children(".wb-multiinput-data");
                $(txtd).attr("wb-name",name).removeAttr("name");
                $(this).after($(txtd));
                $(this).remove();
            });
            $(this).find(":input[name]").each(function(){
                var name = $(this).attr("name");
                $(this).attr("wb-name",name).removeAttr("name");
            });
            var inputs = $(multi).find("input,select,textarea");
            if ($(inputs).length == 1 && $(inputs).is("input")) {mono = true;}
            $(inputs).each(function () {
                var value = $(this).jsonVal();
                if ($(this).attr("name") !== undefined) {
                    var field = $(this).attr("name");
                    $(this).attr("wb-name",field).removeAttr("name");
                } else if ($(this).attr("wb-name") !== undefined) {
                    var field = $(this).attr("wb-name");
                }
                if (field !== undefined && mono == true && field == name) {
                        item = value;
                } else if (field !== undefined) {
                        item[field] = value;
                }
                $(this).attr("wb-name",name).removeAttr("name");
            });
            data[i] = item;
        });
        //console.log(data);
        $(this).value(data).trigger("change");
    }
    }
}

function wb_multiinput() {
$(document).find("wb-multiinput").each(function () {
    if ($(this).data("wb-tpl") == undefined) {
         $(this).sortable({
               update: function(e) {
                  $(e.target).store();
               }
         });
        $(this).data("wb-tpl",wbapp.tpl($(this).children("textarea").data("tpl")).html);
        $(this).events();
        $(this).store();
        $(this).find("input:visible:first").trigger("change"); // important
    }
});
}

$(document).on("multiinput-js", function () {
    wb_multiinput_init();
    wb_multiinput();
    $(document).find("[data-remove=multiinput-js]").remove();
});

$(document).on("dict-js",function(){
   $(".wb-dict").each(function () {
        if ($(this).data("wb-dict") == undefined) {
            $(this).undelegate(".wb-tree-dict-prop-btn");
            $(this).delegate(".wb-tree-dict-prop-btn","click", function(){
                var modal = $(wbapp.tpl('wb.modal').html);
                var title = $(this).next("input").val();
                var type = $(this).parents(".wb-multiinput-row").find("[wb-name=type]").val();
                var prop = $(this).parents(".wb-multiinput-row").find("[wb-name=prop]");

                var res = wbapp.postSync("/ajax/tree/form/prop/", {"dict":$(prop).jsonVal(),"type":type});
								$(modal).attr("id",wbapp.newId());
                $(modal).find(".modal-body").html(res.content);
                $(modal).find(".modal-dialog").addClass("modal-xl");
                if ($(modal).find(".modal-body form",0).attr("data-title") !== undefined) {
                    title = $(modal).find("form",0).attr("data-title") + title;
                }
                $(modal).find(".modal-title").html(title);
								$(modal).modal("show")
                    .on("hidden.bs.modal",function(){
                        var data = $(modal).find(".modal-body form").serializeJson();
												console.log(data);
                        $(prop).jsonVal(data);
                        $(modal).remove();
                    })
                    .on("shown.bs.modal",function(){
												wbapp.tplInit();
                        $(this).runScripts();
                    });
            });
            $(this).data("wb-dict", true);
        }
   });
});

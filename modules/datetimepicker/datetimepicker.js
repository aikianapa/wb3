$(document).off("datatimepicker-js");
$(document).on("datatimepicker-js",function() {
  wbapp.loadStyles(["/engine/modules/datetimepicker/datetimepicker/bootstrap-datetimepicker.min.css",
                    "/engine/modules/datetimepicker/datetimepicker.less",
                    "/engine/lib/fonts/font-awesome/css/font-awesome.min.css"]);
  var scripts = ["/engine/js/php.js","/engine/modules/datetimepicker/datetimepicker/bootstrap-datetimepicker.min.js"];

  wbapp.loadScripts(scripts, "datetimepicker-js-init");

  $(document).off("datetimepicker-js-init");
  $(document).on("datetimepicker-js-init", function() {
        $(".dtpmod:not(.wb-done)").each(function(){
            $(this).addClass("wb-done");
            var input = this;
            var picker = $(input).prev("input");
            var params = {};
            if ($(input).attr("wb-params")> '') var params = JSON.parse($(input).attr("wb-params"));
            $(input).removeAttr("wb-params");
            var lang = params.lang;
            if (params.lang == undefined && wbapp._session.lang !== undefined) lang = wbapp._session.lang;
            if (params.type !== undefined) $(picker).attr('type',params.type);
            var options = {
              autoclose: true,
              todayBtn: true,
              setDate: new Date(),
              initialDate: $(this).val(),
              language: lang,
              todayHighlight: true,
              fontAwesome: true
            }

            if ($(picker).attr("type")=="datetimepicker") {
                if ($(picker).attr("data-date-format") == undefined) options.format = "dd.mm.yyyy hh:ii";
            } else if ($(picker).attr("type")=="datepicker") {
                if ($(picker).attr("data-date-format") == undefined) options.format = "dd.mm.yyyy";
              options.minView = 2;
            } else if ($(picker).attr("type")=="timepicker") {
              options.format = "hh:ii";
              options.startView = 1;
              options.minView = 0;
              options.viewSelect = 'hour';
              options.todayBtn = false;
            }
            if (lang !== undefined) {
                wbapp.loadScripts(["/engine/modules/datetimepicker/datetimepicker/locales/bootstrap-datetimepicker." + lang + ".js"],null,function(){
                  datetimepicker_start();
                });
            } else {
                datetimepicker_start()
            }

            function datetimepicker_start() {
              $(picker).datetimepicker(options).datetimepicker("show").datetimepicker("hide").off("change");
              $(picker).datetimepicker(options).datetimepicker("show").datetimepicker("hide").on("change",function(){
                  if ($(picker).attr("type")=="datetimepicker") {
                      $(input).attr("value",date("Y-m-d H:i:s",strtotime($(picker).val())));
                  } else if ($(picker).attr("type")=="datepicker") {
                      $(input).attr("value",date("Y-m-d",strtotime($(picker).val())));
                  } else if ($(picker).attr("type")=="timepicker") {
                      $(input).attr("value",$(picker).val());
                  }
              });
            }

        });
  });

  $(document).on('after_add',()=>{
    $(document).trigger("datetimepicker-js-init")
  })
});

$(document).off("jodit-js");
$(document).on("jodit-js", function() {
    //$(document).on("wb-ajax-done",function(){$(document).trigger("jodit-js");});
    $('.jodit:not(.ready):not(.jodit-box)[id]').each(function() {
        $(this).parents('.modal-body').on('scroll', function() {
            window.dispatchEvent(new Event('resize'));
        })

        $(this).addClass("ready");
        let editable = this;
        $(editable).data("timeout", false);
        var name = $(editable).attr("name");
        var id = $(editable).attr("id");
        if (id == undefined) {
            id = "jd-" + wbapp.newId();
            $(editable).attr("id", id);
        }

        //var lang = wbapp.settings.i18n;
        var lang = $(editable).attr("data-lang");
        if (lang == undefined) lang = wbapp._settings.locale;
        lang = lang.substr(0, 2);
        var theme = $(editable).attr("data-theme");
        if (theme == undefined) theme = "gray";
        wbapp.loadStyles(["/engine/modules/jodit/build/jodit.min.css", "/engine/modules/jodit/jodit.css"]);


        var loadjs = ["/engine/js/php.js",
            "/engine/modules/jodit/build/jodit.min.js",
            "/engine/modules/jodit/caret/jquery.caret-1.5.2.min.js"
        ];
        //if (lang !== "en") loadjs.push("/engine/modules/jodit/lib/js/languages/" + lang + ".js");
        wbapp.loadScripts(loadjs, "jodit-init", function() {

            //lang = explode("-", lang);

            $(document).on('ajax-done wb-tree-change-data', function() {
                // Чистим  "хвосты" jodit
                if (!$('.jodit-workplace').length) {
                    $('.jodit').remove();
                }
            });

            var options = {
                theme: theme,
                language: lang,
                beautyHTML: false,
                defaultActionOnPaste: "insert_clear_html",
                toolbarSticky: true,
                sourceEditorNativeOptions: {
                    theme: "ace/theme/monokai",
                    mode: "ace/mode/php",
                    showGutter: true,
                },
                imageUploadURL: '/modules/uploader/uploader.php',
                //            pastePlain: true,
                //            documentReady: true,
                events: {
                    'change': function(text) {
                        text = json_encode(text);
                        $(editable).text(text);
                        if ($(editable).data("timeout") == false) {
                            $(editable).data("timeout", true);
                            setTimeout(function() {
                                $(editable).trigger("change");
                                $(editable).data("timeout", false);
                            }, 300);
                        }
                    }
                }
            }

            if ($(editable).hasClass("contenteditable")) {
                options.preset = "inline";
                options.toolbarInline = true;
                options.events = {
                    'change': function(text) {
                        $(editable).data("contenteditable", text);
                    }
                };
            }

            new Jodit('#' + id, options);

            window.dispatchEvent(new Event('resize'));
        });
    });
});
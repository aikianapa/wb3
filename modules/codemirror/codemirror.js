$(document).off("codemirror-js");
$(document).on("codemirror-js", function() {
    wbapp.loadStyles([
        '/engine/modules/codemirror/dist/lib/codemirror.css',
        '/engine/modules/codemirror/dist/addon/fold/foldgutter.css'
    ], "codemirror-css");
    wbapp.loadScripts(['/engine/modules/codemirror/dist/lib/codemirror.js'], 'codemirror-js-ready', function() {
        wbapp.loadScripts([
            '/engine/modules/codemirror/dist/addon/edit/closetag.js',
            '/engine/modules/codemirror/dist/addon/edit/closebrackets.js',
            '/engine/modules/codemirror/dist/addon/fold/foldcode.js',
            '/engine/modules/codemirror/dist/addon/fold/foldgutter.js',
            '/engine/modules/codemirror/dist/addon/fold/xml-fold.js',
            '/engine/modules/codemirror/dist/mode/xml/xml.js',
            '/engine/modules/codemirror/dist/mode/javascript/javascript.js',
            '/engine/modules/codemirror/dist/mode/css/css.js',
            '/engine/modules/codemirror/dist/mode/htmlmixed/htmlmixed.js'
        ], "codemirror-js-addons");
    });
});

$(document).off("codemirror-js-addons");
$(document).on("codemirror-js-addons", function() {
    $('textarea.codemirror').each(function() {
        var that = this;
        var name = $(that).attr("name");
        var form = $(that).parents("form")[0];
        var params = $(this).data('params');

        if (that.done == undefined) {
            var theme = 'cobalt';
            var mode = 'htmlmixed';
            var value = html_entity_decode($(that).text());
            $(that).text('');
            $(that).attr("data-theme") == undefined ? null : params.theme = $(that).attr("data-theme");

            $(that).attr("data-mode") == undefined ? null : params.mode = $(that).attr("data-mode");

            wbapp.loadStyles(['/engine/modules/codemirror/dist/theme/' + params.theme + '.css']);
            wbapp.loadScripts(['/engine/modules/codemirror/dist/mode/' + params.mode + '/' + params.mode + '.js']);
            let options = {
                mode: params.mode,
                theme: params.theme,
                lineNumbers: true,
                lineWrapping: true,
                styleActiveLine: true,
                autoCloseTags: true,
                autoCloseBrackets: true,
                autoRefresh: true,
                autofocus: true,
                fixedGutter: true,
                foldGutter: true,
                gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
            }
            let editor = CodeMirror.fromTextArea(that, options);
            editor.setValue(value);
            //editor.foldCode(CodeMirror.Pos(0, 0));
            $(that).html(htmlentities(editor.getValue()));
            $(that).trigger('change');
            editor.on("change", function() {
                $(that).html(htmlentities(editor.getValue()));
                setTimeout(function() {
                    editor.refresh();
                    $(that).trigger('change');
                }, 300);
            });
            this.done = true;
            this.editor = editor;
            $(this).data("editor", editor);
            wbapp.trigger('codemirror-init', this);
            setTimeout(() => { editor.refresh(); }, 50);

        }
    });
});
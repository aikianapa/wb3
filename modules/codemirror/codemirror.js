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
        if (that.done == undefined) {
            var theme = 'cobalt';
            var mode = 'htmlmixed';
            var value = html_entity_decode($(that).text());
            $(that).text('');
            console.log(value);
            if ($(that).attr("data-theme") !== undefined) {
                theme = $(that).attr("data-theme");
            }
            if ($(that).attr("data-mode") !== undefined) {
                mode = $(that).attr("data-mode");
            }
            wbapp.loadStyles(['/engine/modules/codemirror/dist/theme/' + theme + '.css']);
            wbapp.loadScripts(['/engine/modules/codemirror/dist/mode/' + mode + '/' + mode + '.js']);


            let editor = CodeMirror.fromTextArea(that, {
                mode: mode,
                theme: theme,
                lineNumbers: true,
                lineWrapping: true,
                styleActiveLine: true,
                autoCloseTags: true,
                autoCloseBrackets: true,
                autoRefresh: true,
                autofocus: true,
                fixedGutter: true
            });
            editor.setValue(value);
            //editor.foldCode(CodeMirror.Pos(0, 0));
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
            setTimeout(() => { editor.refresh(); }, 300);

        }
    });
});
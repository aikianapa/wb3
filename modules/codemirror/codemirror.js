$(document).off("codemirror-js");
$(document).on("codemirror-js", function() {
    wbapp.loadStyles([
        '/engine/modules/codemirror/dist/lib/codemirror.css',
        '/engine/modules/codemirror/dist/addon/fold/foldgutter.css',
        '/engine/modules/codemirror/dist/addon/display/fullscreen.css'
    ], "codemirror-css");
    wbapp.loadScripts(['/engine/modules/codemirror/dist/lib/codemirror.js'], 'codemirror-js-ready', function() {
        wbapp.loadScripts([
            '/engine/modules/codemirror/dist/addon/edit/closetag.js',
            '/engine/modules/codemirror/dist/addon/edit/closebrackets.js',
            '/engine/modules/codemirror/dist/addon/fold/foldcode.js',
            '/engine/modules/codemirror/dist/addon/fold/foldgutter.js',
            '/engine/modules/codemirror/dist/addon/fold/xml-fold.js',
            '/engine/modules/codemirror/dist/addon/display/fullscreen.js',
            '/engine/modules/codemirror/dist/mode/xml/xml.js',
            '/engine/modules/codemirror/dist/mode/javascript/javascript.js',
            '/engine/modules/codemirror/dist/mode/css/css.js',
            '/engine/modules/codemirror/dist/mode/htmlmixed/htmlmixed.js'
        ], "codemirror-js-addons");
    });
});

$(document).off("codemirror-js-addons");
$(document).on("codemirror-js-addons", function() {
    $('.mod-codemirror').each(function() {
        $(this).removeClass("mod-codemirror")
        let that = this
        let name = $(that).attr("name");
        let form = $(that).parents("form")[0];
        let params = $(that).data('params');
        let value = $(that).text();
        let data = $(that).data()
        console.log(params);
        params.theme == undefined ? params.theme = 'cobalt' : null
        params.mode == undefined ? params.mode = 'htmlmixed' : null
        $(that).attr("data-mode") == undefined ? null : params.mode = $(that).attr("data-mode");
        that.wait = false;
        //params.oconv == 'base64_encode' ? value = base64_decode(value) : null;
        if ($(that).data('iconv')) eval(`value = ${$(that).data('iconv')}(value)`);
        wbapp.loadStyles(['/engine/modules/codemirror/dist/theme/' + params.theme + '.css']);
        wbapp.loadScripts(['/engine/modules/codemirror/dist/mode/' + params.mode + '/' + params.mode + '.js']);
        let options = {
            mode: params.mode,
            theme: params.theme,
            lineNumbers: true,
            lineWrapping: true,
            fullScreen: false,
            styleActiveLine: true,
            autoCloseTags: true,
            autoCloseBrackets: true,
            autoRefresh: true,
            autofocus: true,
            fixedGutter: true,
            foldGutter: true,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
        }
        params.readonly == undefined ? null : options.readOnly = 'nocursor';
        let editor = CodeMirror.fromTextArea(that, options);
        //editor.foldCode(CodeMirror.Pos(0, 0));
        $(that).trigger('change');
        editor.on("change", function() {
            let value = editor.getValue();
            if ($(that).data('oconv')) eval(`value = ${$(that).data('oconv')}(value)`);
            //params.oconv == 'base64_encode' ? value = base64_encode(value) : null;
            $(that).html(value);
            if (params.height == 'auto') {
                $(that).next('.CodeMirror').css('height', 'auto')
            }
            $(that).trigger('change');
        });
        editor.setValue(value);
        that.editor = editor;
        $(that).data("editor", editor);
        wbapp.trigger('codemirror-init', that);
        setTimeout(() => { editor.refresh(); }, 50);
    });
});
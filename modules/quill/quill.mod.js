wbapp.loadStyles([
    "https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.7.1/katex.min.css"
    , "https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/monokai-sublime.min.css"
    , "/engine/modules/quill/quill.snow.css"
], "quill-css");
wbapp.loadScripts([
    "https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.7.1/katex.min.js"
    , "https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"
    , "/engine/modules/quill/quill.min.js"
], "quill-js", function () {
    $('.quill-editor:not(.done)').each(function () {
        let id;
        if ($(this).attr('id') !== undefined) {
            id = $(this).attr('id');
        } else {
            id = 'quill' + wbapp.newId();
            $(this).attr('id', id);
        }
        $(this).find('.toolbar').attr('id','tb-' + id);
        $(this).find('.editor').attr('id', 'ed-' + id);

        var quill = new Quill('#' + 'ed-' + id , {
            modules: {
                toolbar: '#' + 'tb-' + id
            },
            placeholder: 'Compose an epic...'
            ,theme: 'snow'
      });
    });
})
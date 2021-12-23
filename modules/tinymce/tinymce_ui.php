<textarea class="tinymce wb-content-editor" data-lang="{{_sett.locale}}"></textarea>
<script wb-app>
  wbapp.loadScripts([
    "/engine/modules/tinymce/tinymce/tinymce.min.js",
    "/engine/modules/tinymce/tinymce/jquery.tinymce.min.js"
  ], "tinymce-js", function() {

    $(document).find('textarea.tinymce').each(function() {
      if (this.done !== undefined) {
        return;
      } else {
        this.done = true;
        tinymce.init({
          selector: 'textarea.tinymce'
        });

      }
    });
  });
</script>
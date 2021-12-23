<textarea class="ck-editor wb-content-editor" data-lang="{{_sett.locale}}"></textarea>
<script wb-app>
  wbapp.loadScripts([
    "/engine/modules/ckeditor/ckeditor/ckeditor.js"
    ,"/engine/modules/ckeditor/ckeditor/config.js"
    ,"/engine/modules/ckeditor/ckeditor/adapters/jquery.js"], "ckeditor-js", function() {

    // fix ckeditor modals
    $.fn.modal.Constructor.prototype.enforceFocus = function() {
      modal_this = this
      $(document).on('focusin.modal', function (e) {
        if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length 
        && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') 
        && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_textarea')
        && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
          modal_this.$element.focus()
        }
      })
    };
    // fix end

      $(document).find('textarea.ck-editor').each(function() {
      if (this.done !== undefined) {
        return;
      } else {
          this.done = true;
          let that = this;
          $("[tabindex]").removeAttr("tabindex");
          let editor = $(that).ckeditor().editor;
          editor.on( 'change', function( evt ) {
              $(that).html(editor.getData()).trigger('change');
          });
      }

    })
  });
</script>
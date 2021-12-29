<html>
<div id="yongerBlockEditor" class="modal effect-scale removable" data-backdrop="static" data-keyboard="false"
    tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xxl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title ">
                    <ul class="nav nav-tabs" id="yongerBlockEditorTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#yongerBlockEditorView"
                                role="tab">{{_lang.view}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#yongerBlockEditorEdit"
                                role="tab">{{_lang.form}}</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-success save mg-r-30">
                        {{_lang.save}}
                    </button>
                    <i class="fa fa-close r-20 position-absolute cursor-pointer" data-dismiss="modal"
                        aria-label="{{_lang.close}}"></i>

                </div>
            </div>
            <div class="modal-body p-0 ovf-hidden">
                <form id="yongerBlockEditorForm">
                    <div class="tab-content" id="yongerBlockEditorTabContent">
                        <div class="tab-pane show active" id="yongerBlockEditorView" role="tabpanel">
                            <wb-module wb="module=codemirror&name=view&height=auto"></wb-module>
                        </div>
                        <div class="tab-pane" id="yongerBlockEditorEdit" role="tabpanel">
                            <wb-module wb="module=codemirror&name=edit&height=auto"></wb-module>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
<script>
    var yongerBlockEditorResize = function() {
            $('#yongerBlockEditor').find('textarea.codemirror').each(function() {
                let editor = $(this).data('editor');
                if (editor !== undefined) {
                    let height = $(this).parents('.modal').find('.modal-body').height();
                    editor.setSize("100%", height);
                    editor.refresh();
                }
            })
    }
$('#yongerBlockEditor a[data-toggle="tab"]').on('shown.bs.tab', function(ev) {
    yongerBlockEditorResize();
})
$('#yongerBlockEditor').delegate('button.save', wbapp.evClick, function() {
    let edit = base64_encode($("#yongerBlockEditorForm textarea[name=edit]:first").data('editor').getValue());
    let view = base64_encode($("#yongerBlockEditorForm textarea[name=view]:first").data('editor').getValue());
    let form = $(document).find('#yongerBlockEditor').data('form');
    wbapp.post('/module/yonger/editblocksave/', {
        'edit': edit,
        "view": view,
        'form': form
    }, function(data) {
        if (data.error) {
            wbapp.toast(wbapp._settings.sysmsg.save_failed, wbapp._settings.sysmsg.save_failed_msg, {
                bgcolor: 'danger'
            });
        } else {
            wbapp.toast(wbapp._settings.sysmsg.save_success, wbapp._settings.sysmsg.save_success_msg, {
                bgcolor: 'success'
            });
            wbapp.trigger('yongerBlockEditorSave', form);
        }
    });
})
$(document).delegate('.modal', 'shown.bs.modal', function(ev) {
    if ($(ev.target).is('#yongerBlockEditor')) {
        setTimeout(function() {
            yongerBlockEditorResize();
        }, 100);
    }
})
$(window).resize(function(){yongerBlockEditorResize()});
</script>
<wb-lang>
    [en]
    form = Form
    view = View
    save = Save
    close = Close
    [ru]
    form = Форма
    view = Отображение
    save = Сохранить
    close = Закрыть
</wb-lang>

</html>
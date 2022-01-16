<div class="modal fade removable" tabindex="-1" id="wbappConfirmDialog" data-backdrop="static" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{_lang.confirm}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">{{_lang.confirm_text}}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <img src="/module/myicons/interface-essential-109.svg?size=24&stroke=000000">
                    {{_lang.cancel_btn}}</button>
                <button type="button" class="btn btn-warning confirm">
                    <img src="/module/myicons/interface-essential-112.svg?size=24&stroke=000000">
                    {{_lang.confirm_btn}}
                </button>
            </div>
        </div>
    </div>
    <wb-lang>
        [en]
        confirm = "Confirm"
        confirm_text = "Please, confirm action"
        cancel_btn = "Cancel"
        confirm_btn = "Confirm"
        [ru]
        confirm = "Подтверждение"
        confirm_text = "Пожалуйста, подтвердите действие"
        cancel_btn = "Отменить"
        confirm_btn = "Подтвердить"
    </wb-lang>
</div>
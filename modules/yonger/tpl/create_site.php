<html>

<div class="modal fade show" data-backdrop="true" tabindex="-1" role="dialog" id="yongerSiteCreator">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <input class="form-control" type="text" name="name" placeholder="Название сайта">
                    <input type="hidden" name="login" value="{{_sess.user.login}}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary px-5" onclick="yonger.siteCreator();">Создать</button>
            </div>
        </div>
    </div>
</div>
</html>
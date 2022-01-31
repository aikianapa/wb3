<html>
<div class="modal effect-scale show removable" id="modalPagesEdit" data-backdrop="static" tabindex="-1" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header row">
                <div class="col-4">
                    <h5 wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">{{_lang.header}}</h5>
                    <h5 wb-if="'{{_route.id}}' == '_header'">Шапка сайта</h5>
                    <h5 wb-if="'{{_route.id}}' == '_footer'">Подвал сайта</h5>
                </div>
                <div class="col-8">
                    <h5 class='header'></h5>
                </div>
                <i class="fa fa-close r-20 position-absolute cursor-pointer" data-dismiss="modal"
                    aria-label="Close"></i>
            </div>
            <div class="modal-body pd-20">
                <div class="row">
                    <div class="col-5 col-lg-4">
                        <form id="{{_form}}EditForm">
                            <div wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">
                                <div class="form-group row">
                                    <div class="col-12 mt-1">
                                        <div class="btn btn-info btn-block p-2 mb-0 cursor-pointer pagelink">
                                            <img data-src="/module/myicons/link-big.svg?size=20&stroke=FFFFFF">
                                            {{_route.scheme}}://{{_route.hostname}}<span class="path"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row" wb-allow="admin">
                                    <label class="col-lg-4 form-control-label">Путь к странице</label>
                                    <!--wb-module wb="module=yonger&mode=pageselect" /-->
                                    <div class="col-lg-8">
                                        <input name="path" wb="module=yonger&mode=pageselect" class="form-control"
                                            readonly />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-4 form-control-label">Наименование</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text p-1">
                                                    <input name="active" wb-module="swico">
                                                </span>
                                            </div>
                                            <input type="text" name="name" class="form-control" wb="module=smartid"
                                                required wb-enabled="admin">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-4 form-control-label">Заголовок</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="header" class="form-control" placeholder="Заголовок"
                                            wb="module=langinp" required>
                                    </div>
                                </div>

                                <div wb-if="'{{_sett.devmode}}'=='on'">
                                    <div class="divider-text">Присоединённый раздел</div>
                                    <div class="form-group row">
                                        <label class="col-lg-4 form-control-label">Раздел</label>
                                        <div class="col-lg-8">
                                            <select class="form-control" wb-select2 placeholder="{{_lang.attach}}"
                                                name="attach">
                                                <wb-foreach wb-from="_env.forms" wb-tpl="false">
                                                    <option>{{_val}}</option>
                                                </wb-foreach>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <input type="text" name="filter" class="form-control"
                                            placeholder="{{_lang.filter}}">
                                    </div>
                                </div>
                            </div>
                            <wb-module wb="module=yonger&mode=structure" />
                        </form>
                    </div>

                    <div class="col-7 col-lg-8">
                        <div id="yongerBlocksForm">
                            <form method="post">

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer pd-x-20 pd-b-20 pd-t-0 bd-t-0">
                <wb-include wb="{'form':'common_formsave.php'}" />
            </div>
        </div>
    </div>
</div>

<wb-lang>
[ru]
header = Редактирование страницы
search = Поиск
attach = Присоединить раздел
filter = Фильтр (json)
[en]
header = Page edit
search = Search
attach = Attach division
filter = Filter (json)
</wb-lang>

<script wb-app>
let timeout = 50;
// ==============
yonger.pageEditor = function() {
    let $form = $('#{{_form}}EditForm');
    $form.delegate('[name=path]', 'change', function() {
        let path = $(this).val() + '/';
        $form.find('.path').html(path);
        $form.find('[name=name]').trigger('change');
    });
    $form.delegate('[name=name]', 'change keyup', function() {
        let path = $form.find('[name=path]').val() + '/';
        let name = $(this).val();
        if (path == '/' && name == 'home') name = '';
        $form.find('.path').html(path + name);
    });
    $form.find('[name=path]').trigger('change');

    $form.find('.pagelink').on(wbapp.evClick, function() {
        let url = $(this).text();
        let target = md5(url);
        window.open(url, target).focus();
    })
}

yonger.pageEditor();
</script>
</html>
<html>
<div class="modal effect-scale show removable" id="modalPagesEdit" data-backdrop="static" tabindex="-1"
    role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header row">
                <div class="col-4">
                    <h5 wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">Редактирование страницы</h5>
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
                                <div class="col-12">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" name="active"
                                            id="{{_form}}SwitchItemActive">
                                        <label class="custom-control-label" for="{{_form}}SwitchItemActive">Отображать
                                            страницу</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-lg-6">
                                    <label class="form-control-label">Путь к странице</label>
                                    <select class="form-control" placeholder="/" name="path">
                                        <wb-foreach wb='table=pages&sort=url' wb-filter="{'id':{'$ne':'{{id}}'}}">
                                            <wb-var wb-if='"{{url}}" == "/"' url="/home" else="{{url}}" />
                                            <option value="{{path}}/{{name}}">{{_var.url}}</option>
                                        </wb-foreach>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-control-label">Имя страницы</label>
                                    <input type="text" name="name" class="form-control" wb="module=smartid" required>
                                </div>
                                <div class="col-12 mt-1">
                                    <div class="alert alert-info p-2 mb-0 cursor-pointer pagelink">
                                        {{_route.scheme}}://{{_route.hostname}}<span class="path"></span>
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
                            </div>
                            <!--div class="form-group row">
                            <label class="col-12 form-control-label">Шаблон</label>
                            <div class="col-12">
                                <select class="form-control" name="template" placeholder="Шаблон">
                                    <wb-foreach wb='call=wbListTpl()'>
                                        <option value="{{_val}}">{{_val}}</option>
                                    </wb-foreach>
                                </select>
                            </div>
                        </div-->
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

<div class="modal effect-slide-in-right left w-50" id="modalPagesEditBlocks" data-backdrop="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
                <i></i>
                <i class="fa fa-close cursor-pointer" data-dismiss="modal"
                    aria-label="Close"></i>
            </div>
            <div class="modal-body p-0 pb-5 scroll-y">
            <div class="list-group" id="{{_form}}EditFormListBlocks">
                <wb-foreach wb="ajax=/module/yonger/blocklist&render=client&bind=yonger.blocks">
                    <a class="list-group-item list-group-item-action" href="javascript:void(0)" data-name="{{name}}"
                        onclick="yonger.yongerPageBlockAdd('{{id}}')">
                        <span>{{name}}</span>
                        <span class="d-block tx-11 text-muted">{{header}}</span>
                    </a>
                </wb-foreach>
            </div>
            </div>
        </div>
    </div>
</div>

<script wb-app>
let timeout = 50;
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
<wb-lang>
    [ru]
    main = Основное
    prop = Вставки кода
    seo = Оптимизация
    images = Изображения
    visible = Отображать
    header = Заголовок
    [en]
    main = Main
    prop = Code injection
    seo = SEO
    images = Images
    visible = Visible
    header = Header
</wb-lang>

</html>